<?php

namespace framework\mvc;

use framework\mvc\Template;
use framework\Config;
use framework\Language;
use framework\Logger;
use framework\Session;
use framework\mvc\Model;
use framework\mvc\Router;
use framework\network\Http;
use framework\network\http\Header;

abstract class Controller {

    const HTML = 1;
    const XML = 2;
    const JSON = 3;

    protected $_template = null;
    protected $_templateInitialized = false;
    protected $_autoCallDisplay = true;
    protected $_isAjax = false;
    protected $_ajaxDatas = array();
    protected $_ajaxDatasType = self::JSON;
    protected $_ajaxDatasCache = false;
    protected $_ajaxAutoAddDatas = array(
        'content' => false,
        'post' => false,
        'query' => false,
        'cookie' => false,
    );
    protected $_errors = array();
    //http
    protected $_request = null;
    protected $_response = null;

    public function isTemplateInitialized() {
        return $this->_templateInitialized;
    }

    public function initTemplate($forceReplace = false) {
        if ($this->_templateInitialized && !$forceReplace)
            return;

        $tpl = Template::getTemplate();
        //no template
        if (!$tpl) {
            $this->log->debug('try initialize template, but no template configured', 'router');
            return false;
        }


        $this->_template = $tpl;
        // Set langs/urls vars into tpl
        $this->_template->setVar('urls', Router::getUrls($this->language->getLanguage(), Http::isHttps()), false, true);
        $this->_template->setVar('langs', $this->language->getVars(true), false, true);
        $this->_template->setVar('lang', $this->language->getLanguage(), false, true);
        //init assets
        if (!Http::isAjax())
            $this->_template->initAssets();
        $this->_templateInitialized = true;
        $this->log->debug('Initialize template', 'router');
    }

    public function __get($name) {
        if ($name == 'tpl') {
            if (!$this->_templateInitialized)
                $this->initTemplate();

            return $this->_template;
        }
        if ($name == 'router')
            return Router::getInstance();
        if ($name == 'session')
            return Session::getInstance();
        if ($name == 'config')
            return Config::getInstance();
        if ($name == 'log')
            return Logger::getInstance();
        if ($name == 'language')
            return Language::getInstance();
        if ($name == 'model')
            return Model::getInstance();
    }

    public function display() {
        if ($this->hasErrors())
            $this->tpl->setVar('errors', $this->getErrors());
        if ($this->tpl->post === null)
            $this->tpl->setVar('post', Http::getPost(), false, true);
        if ($this->tpl->query === null)
            $this->tpl->setVar('query', Http::getQuery(), false, true);
        if ($this->tpl->cookie === null)
            $this->tpl->setVar('cookie', Http::getCookie(), false, true);
        $this->tpl->setVar('notifyInformation', $this->session->get('notifyInformation'), false, true);
        $this->tpl->setVar('notifyError', $this->session->get('notifyError'), false, true);
        $this->tpl->setVar('notifySuccess', $this->session->get('notifySuccess'), false, true);


        if ($this->_isAjax) {
            if ($this->hasErrors())
                $this->addAjaxDatas('errors', $this->getErrors());
            if ($this->_ajaxAutoAddDatas['post'] && !array_key_exists('post', $this->_ajaxDatas))
                $this->addAjaxDatas('post', Http::getPost());
            if ($this->_ajaxAutoAddDatas['query'] && !array_key_exists('query', $this->_ajaxDatas))
                $this->addAjaxDatas('query', Http::getQuery());
            if ($this->_ajaxAutoAddDatas['cookie'] && !array_key_exists('cookie', $this->_ajaxDatas))
                $this->addAjaxDatas('cookie', Http::getCookie());
            if ($this->_ajaxAutoAddDatas['content'] && !array_key_exists('content', $this->_ajaxDatas))
                $this->addAjaxDatas('content', $this->tpl->getContent());
            if (!array_key_exists('notifyInformation', $this->_ajaxDatas))
                $this->addAjaxDatas('notifyInformation', $this->session->get('notifyInformation'));
            if (!array_key_exists('notifyError', $this->_ajaxDatas))
                $this->addAjaxDatas('notifyError', $this->session->get('notifyError'));
            if (!array_key_exists('notifySuccess', $this->_ajaxDatas))
                $this->addAjaxDatas('notifySuccess', $this->session->get('notifySuccess'));

            // No cache
            if (!$this->_ajaxDatasCache) {
                Header::sentHeader('Cache-Control', 'no-cache, must-revalidate');
                Header::sentHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
            }
            switch ($this->_ajaxDatasType) {
                case self::HTML:
                    Header::sentHeader('Content-type', 'text/html');
                    foreach ($this->_ajaxDatas as $data)
                        echo $data;
                    break;
                case self::XML:
                    Header::sentHeader('Content-type', 'text/xml');
                    foreach ($this->_ajaxDatas as $data)
                        echo $data;
                    break;
                case self::JSON:
                    Header::sentHeader('Content-type', 'application/json');
                    echo json_encode((object) $this->_ajaxDatas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    break;
                default:
                    throw new \Exception('Ajax datas type not valid');
            }
        } else {
            //display
            $this->tpl->display();
            $this->log->debug('Display template file : "' . $this->tpl->getFile() . '"', 'router');
        }

        // Delete stored messages
        $this->session->delete('notifyInformation', true);
        $this->session->delete('notifyError', true);
        $this->session->delete('notifySuccess', true);
    }

    public function setAutoCallDisplay($autoCallDisplay) {
        if (!is_bool($autoCallDisplay))
            throw new \Exception('autoCallDisplay parameter must be a boolean');
        $this->_autoCallDisplay = $autoCallDisplay;
    }

    public function getAutoCallDisplay() {
        return $this->_autoCallDisplay;
    }

    public function setAjaxController($ajaxDatasType = self::JSON, $ajaxDatasCache = false, $ajaxAutoAddDatas = array()) {
        if (!Http::isAjax())
            $this->log->debug('Trying set controller on ajax when resquest isn\'t ajax', 'router');

        if ($ajaxDatasType != self::HTML && $ajaxDatasType != self::XML && $ajaxDatasType != self::JSON)
            throw new \Exception('ajax datas type parameter must be a valid data type : htmt(1), xml(2) or json(3)');
        if (!is_bool($ajaxDatasCache))
            throw new \Exception('ajaxDatasCache parameter must be a boolean');

        $this->_ajaxDatasCache = $ajaxDatasCache;
        $this->_ajaxDatasType = $ajaxDatasType;
        $this->_isAjax = true;
        if (!is_array($ajaxAutoAddDatas))
            throw new \Exception('ajaxAutoAddDatasparameter must be a boolean');
        if (!empty($ajaxAutoAddDatas))
            $this->setAjaxAutoAddDatas(extract($ajaxAutoAddDatas));

        $this->log->debug('Set controller in ajax', 'router');
    }

    public function setAjaxAutoAddDatas($content = false, $post = false, $query = false, $cookie = false) {
        if (!$this->isAjaxController())
            return;

        if (!is_bool($content) || !is_bool($post) || !is_bool($query) || !is_bool($cookie))
            throw new \Exception('ajaxAutoAddDatas parameters must be a boolean');

        if ($content)
            $this->_ajaxAutoAddDatas['content'] = $content;
        if ($post)
            $this->_ajaxAutoAddDatas['post'] = $content;
        if ($query)
            $this->_ajaxAutoAddDatas['query'] = $content;
        if ($cookie)
            $this->_ajaxAutoAddDatas['cookie'] = $content;
    }

    public function isAjaxController() {
        return $this->_isAjax;
    }

    public function addAjaxDatas($key, $datas) {
        $this->_ajaxDatas[$key] = $datas;
        return $this;
    }

    public function notifyInformation($title, $details = array()) {
        if (!is_array($details))
            throw new \Exception('Details parameters must be an array');
        $object = new \stdClass();
        $object->heading = $title;
        if (count($details) > 0)
            $object->messages = $details;
        $this->session->add('notifyInformation', $object, true);
    }

    public function notifyError($title, $details = array()) {
        if (!is_array($details))
            throw new \Exception('Details parameters must be an array');
        $object = new \stdClass();
        $object->heading = $title;
        if (count($details) > 0)
            $object->messages = $details;
        $this->session->add('notifyError', $object, true);
    }

    public function notifySuccess($title, $details = array()) {
        if (!is_array($details))
            throw new \Exception('Details parameters must be an array');
        $object = new \stdClass();
        $object->heading = $title;
        if (count($details) > 0)
            $object->messages = $details;
        $this->session->add('notifySuccess', $object, true);
    }

    public function addError($error, $key = null) {
        if ($key === null)
            $this->_errors[] = $error;
        else
            $this->_errors[$key] = $error;
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function hasErrors() {
        return (boolean) count($this->_errors);
    }

}

?>