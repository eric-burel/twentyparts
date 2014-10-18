<?php

namespace framework\mvc\router;

use framework\utility\Validate;
use framework\network\http\Method;
use framework\network\http\ResponseCode;
use framework\network\http\Protocol;

class Route {

    protected $_name;
    protected $_controller;
    protected $_requireSsl = false;
    protected $_regex = false;
    protected $_requireAjax = false;
    protected $_autoSetAjax = true;
    protected $_requireHttpMethod = null;
    protected $_httpResponseStatusCode = null;
    protected $_httpProtocol = null;
    protected $_rules = array();
    protected $_methods = array();

    public function __construct($name, $controller) {
        $this->setName($name);
        $this->setController($controller);
    }

    public function setName($name) {
        // Check name
        if (!Validate::isVariableName($name))
            throw new \Exception('Route name must be a valid variable');

        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setController($controller) {
        if (!is_string($controller))
            throw new \Exception('Route controller parameter must be a string');

        $this->_controller = $controller;
    }

    public function getController() {
        return $this->_controller;
    }

    public function setRequireSsl($requireSsl) {
        if (!is_bool($requireSsl))
            throw new \Exception('Route requireSsl parameter must be a boolean');

        return $this->_requireSsl = $requireSsl;
    }

    public function getRequireSsl() {
        return $this->_requireSsl;
    }

    public function setRegex($regex) {
        if (!is_bool($regex))
            throw new \Exception('Route regex parameter must be a boolean');

        $this->_regex = $regex;
    }

    public function getRegex() {
        return $this->_regex;
    }

    public function setRequireAjax($requireAjax) {
        if (!is_bool($requireAjax))
            throw new \Exception('Route requireAjax parameter must be a boolean');

        return $this->_requireAjax = $requireAjax;
    }

    public function getRequireAjax() {
        return $this->_requireAjax;
    }

    public function setAutoSetAjax($autoSetAjax) {
        if (!is_bool($autoSetAjax))
            throw new \Exception('Route autoSetAjax parameter must be a boolean');

        $this->_autoSetAjax = $autoSetAjax;
    }

    public function getAutoSetAjax() {
        return $this->_autoSetAjax;
    }

    public function setRequireHttpMethod($requireHttpMethod) {
        if (!is_null($requireHttpMethod) && !Method::isValid($requireHttpMethod))
            throw new \Exception('Route requireHttpMethod parameter must null or a valid HTTP METHOD');

        $this->_requireHttpMethod = $requireHttpMethod;
    }

    public function getRequireHttpMethod() {
        return $this->_requireHttpMethod;
    }

    public function setHttpResponseStatusCode($httpResponseStatusCode) {
        if (!is_null($httpResponseStatusCode) && !ResponseCode::isValid($httpResponseStatusCode))
            throw new \Exception('Route httpResponseStatusCode parameter must null or a valid HTTP ResponseCode');

        $this->_httpResponseStatusCode = $httpResponseStatusCode;
    }

    public function getHttpResponseStatusCode() {
        return $this->_httpResponseStatusCode;
    }

    public function setHttpProtocol($httpProtocol) {
        if (!is_null($httpProtocol) && !Protocol::isValid($httpProtocol))
            throw new \Exception('Route httpProtocol parameter must null or a valid HTTP ResponseCode');

        $this->_httpProtocol = $httpProtocol;
    }

    public function getHttpProtocol() {
        return $this->_httpProtocol;
    }

    public function setRules($rules) {
        if (!is_array($rules))
            throw new \Exception('Route rules parameter must be an array');

        $this->_rules = $rules;
    }

    public function getRules() {
        return $this->_rules;
    }

    public function setMethods($methods) {
        if (!is_array($methods))
            throw new \Exception('Route methods parameter must be an array');

        $this->_methods = $methods;
    }

    public function getMethods() {
        return $this->_methods;
    }

}
