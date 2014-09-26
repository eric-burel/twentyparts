<?php

namespace controllers;

use framework\Application;
use framework\Cache;
use framework\Security;
use framework\mvc\Controller;
use framework\mvc\Model;
use framework\security\Form;
use framework\network\Http;
use framework\utility\Cookie;

class Index extends Controller {

    protected $_cache = null;

    public function __construct() {
        //check lang is in url and auto set
        if ($this->router->getCurrentRoute() == 'index' && stripos(Http::getCurrentUrl(), $this->language->getLanguage()) === false)
            Http::redirect($this->tpl->urls->index);

        $this->tpl->setVar('langAvaible', ($this->language->getLanguage() == 'fr_FR') ? 'en_EN' : 'fr_FR');
        //cache
        $this->_cache = Cache::getCache('bdd');

        $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'index.tpl.php');
    }

    public function setAjax($check = false) {
        if (!Http::isAjaxRequest() && $check)
            Http::redirect($this->router->getUrl('index'));

        if (Http::isAjaxRequest())
            $this->setAjaxController();
    }

    public function language($language) {
        if (!is_string($language))
            $language = (string) $language;

        $this->session->add('language', $language, true, false);
        $this->addAjaxDatas('updated', true);

        //create cookie
        new Cookie('language', $language, true, Cookie::EXPIRE_TIME_INFINITE, str_replace(Http::getServer('SERVER_NAME'), '', $this->router->getHost()));
    }

    public function captcha($formName, $type) {
        $captcha = Security::getSecurity(Security::TYPE_FORM)->getProtection($formName, Form::PROTECTION_CAPTCHA);
        if (!$captcha)
            $this->router->show404(true);

        if ($type == 'refresh') {
            $this->setAjaxController();
            $captcha->flush();
            $this->addAjaxDatas('imageUrl', $captcha->get('image', true));
            $this->addAjaxDatas('audioUrl', $captcha->get('audio', true));
        } else {
            if ($type == 'image') {
                if (!$captcha->getImage())
                    $this->router->show404(true);
                $captcha->get('image');
            } elseif ($type == 'audio') {
                if (!$captcha->getAudio())
                    $this->router->show404(true);
                $captcha->get('audio');
            } else
                $this->router->show404(true);

            $this->setAutoCallDisplay(false);
        }
    }

    private function _read($modelType, $id, $checkIfisFront = false) {
        $cache = $this->_cache->read($modelType . $id);
        if (!is_null($cache) && !Application::getDebug())
            $data = $cache;
        else {
            $manager = Model::factoryManager($modelType, 'default', $modelType);
            $data = $manager->read($id, $checkIfisFront);
            if (!is_null($data))
                $this->_cache->write($modelType . $id, $data, true);
        }

        return $data;
    }

    private function _readAll($modelType, $option = null, $option2 = null) {
        $stringOptions = (string) $option . $option2;
        $cache = $this->_cache->read($modelType . 'listing' . $stringOptions);
        if (!is_null($cache) && !Application::getDebug())
            $datas = $cache;
        else {
            $manager = Model::factoryManager($modelType, 'default', $modelType);
            $datas = $manager->readAll($option, $option2);
            if (!is_null($datas))
                $this->_cache->write($modelType . 'listing' . $stringOptions, $datas, true);
        }

        return $datas;
    }

}

?>