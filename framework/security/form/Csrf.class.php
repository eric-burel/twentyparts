<?php

namespace framework\security\form;

use framework\security\IForm;
use framework\Session;
use framework\network\Http;
use framework\Logger;
use framework\mvc\Router;
use framework\utility\Validate;

class Csrf implements IForm {

    protected $_formName = '';
    protected $_timeValidity = 0;
    protected $_urlsReferer = array();
    protected $_token = null;

    public function __construct($options = array()) {
        if (isset($options['timeValidity']))
            $this->_timeValidity = (int) $options['timeValidity'];

        if (isset($options['urlReferer'])) {
            if (is_array($options['urlReferer'])) {
                foreach ($options['urlReferer'] as &$url)
                    $this->_urlsReferer[] = Router::getUrl($url);
            } else
                $this->_urlsReferer[] = Router::getUrl($options['urlReferer']);
        }
    }

    public function setFormName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Form name must be a valid variable name');

        $this->_formName = $name;
    }

    public function getFormName() {
        return $this->_formName;
    }

    public function create() {
        $this->_token = uniqid(rand(), true);
        Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" create token value : "' . $this->_token . '"', 'security');
    }

    public function set() {
        if (is_null($this->_token)) {
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" trying set uncreated token', 'security');
            return;
        }

        Session::getInstance()->add($this->getFormName() . 'CsrfToken', $this->_token, true, true);
        Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" set token value : "' . $this->_token . '" into session', 'security');
        if ($this->_timeValidity > 0) {
            $time = time();
            Session::getInstance()->add($this->getFormName() . 'CsrfTokenTime', $time, true, true);
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" set token time value : "' . $time . '"', 'security');
        }
    }

    public function get() {
        Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" get token value : "' . $this->_token . '"', 'security');
        return $this->_token;
    }

    public function check($checkingValue, $flush = false) {
        if (is_null($this->_token))
            return false;
        $tokenRealValue = Session::getInstance()->get($this->getFormName() . 'CsrfToken');
        $tokenTimeRealValue = Session::getInstance()->get($this->getFormName() . 'CsrfTokenTime');
        if ($flush)
            $this->flush();

        if (is_null($tokenRealValue)) {
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" token miss"', 'security');
            return false;
        }
        if ($this->_timeValidity > 0 && is_null($tokenTimeRealValue)) {
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" tokenTime miss"', 'security');
            return false;
        }
        if (!empty($this->_urlsReferer)) {
            foreach ($this->_urlsReferer as &$url) {
                if (stripos(Http::getServer('HTTP_REFERER'), $url) !== false || Http::getServer('HTTP_REFERER') == $url) {
                    $match = true;
                    break;
                }
            }
            if (!isset($match)) {
                Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" url referer : "' . Http::getServer('HTTP_REFERER'), 'security');
                return false;
            }
        }
        if ($tokenRealValue != $checkingValue) {
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" token : "' . $checkingValue . '" invalid, need : "' . $tokenRealValue . '" value', 'security');
            return false;
        }
        if ($tokenTimeRealValue <= time() - $this->_timeValidity) {
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" tokenTime too old"', 'security');
            return false;
        }

        return true;
    }

    public function flush() {
        Session::getInstance()->delete($this->getFormName() . 'CsrfToken', true);
        if ($this->_timeValidity > 0)
            Session::getInstance()->delete($this->getFormName() . 'CsrfTokenTime', true);

        $this->_token = null;
    }

}

?>