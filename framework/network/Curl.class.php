<?php

namespace framework\network;

use framework\Logger;
use framework\utility\Validate;

class Curl {

    const POST = 'POST';
    const GET = 'GET';

    protected $_url = null;
    protected $_curl = null;
    protected $_curlInitialized = false;
    protected $_posts = array();
    protected $_gets = array();
    protected $_response = false;
    protected $_userAgent = false;
    protected $_encodeUrl = false;
    protected $_allowSsl = true;

    public function __construct($url) {
        if (!extension_loaded('curl'))
            throw new \Exception('Curl extension not loaded try change your PHP configuration');
        $this->initCurl();
        $this->setUrl($url);
    }

    public function __destruct() {
        if ($this->_getCurlInitialized())
            $this->close(false);
    }

    public function setUrl($url) {
        if (!$this->_getCurlInitialized())
            throw new \Exception('Curl must be initialized');
        if (!is_string($url))
            throw new \Exception('Url parameter must be a string');

        if (!Validate::isUrl($url))
            throw new \Exception('Url parameter must be a valid url');

        if (Validate::isUrl($url, true)) {
            if (!$this->getAllowSsl())
                throw new \Exception('SSL url cannot be used');
            $this->setCurlOpt(CURLOPT_SSL_VERIFYPEER, false);
            $this->setCurlOpt(CURLOPT_SSL_VERIFYHOST, 2);
        }
        $this->_url = $url;
    }

    public function setAllowSsl($bool) {
        if (!is_bool($bool))
            throw new \Exception('Allow ssl parameter must be a boolean');

        $this->_allowSsl = $bool;
    }

    public function getAllowSsl() {
        return $this->_allowSsl;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function initCurl() {
        $this->_curl = curl_init();
        $this->_setCurlInitialized(true);
    }

    public function getCurl() {
        return $this->_curl;
    }

    public function addArgument($key, $value, $argType = self::POST, $forceReplace = false) {
        if ($argType != self::POST && $argType != self::GET)
            throw new \Exception('Argument type parameter must be a GET or POST');

        if ($this->getEncodeUrl())
            $value = urlencode($value);

        if ($argType == self::POST) {
            if (!$forceReplace && in_array($key, $this->_posts))
                throw new \Exception('Argument POST "' . $key . '" is already defined');
            $this->_posts[$key] = $value;
        } else {
            if (!$forceReplace && in_array($key, $this->_gets))
                throw new \Exception('Argument POST "' . $key . '" is already defined');
            $this->_gets[$key] = $value;
        }
        return $this;
    }

    public function delArgument($key, $argType = self::POST) {
        if ($argType != self::POST && $argType != self::GET)
            throw new \Exception('Argument type parameter must be a GET or POST');
        if ($argType == self::POST) {
            if (!in_array($key, $this->_posts))
                throw new \Exception('Argument POST "' . $key . '" don\'t exists');
            unset($this->_posts[$key]);
        } else {
            if (!in_array($key, $this->_gets))
                throw new \Exception('Argument GET "' . $key . '" don\'t exists');
            unset($this->_gets[$key]);
        }
        return $this;
    }

    public function getArgument($key, $argType = self::POST) {
        if ($argType != self::POST && $argType != self::GET)
            throw new \Exception('Argument type parameter must be a GET or POST');
        if ($argType == self::POST) {
            if (!in_array($key, $this->_posts))
                throw new \Exception('Argument POST "' . $key . '" don\'t exists');
            return $this->_posts[$key];
        } else {
            if (!in_array($key, $this->_gets))
                throw new \Exception('Argument GET "' . $key . '" don\'t exists');
            return $this->_gets[$key];
        }
    }

    public function getArgumentsPost($stringFormat = false) {
        if ($stringFormat) {
            $posts = '';
            foreach ($this->_posts as $k => $v)
                $posts .= $k . '=' . $v . '&';
            return trim($posts, '&');
        }
        else
            return $this->_posts;
    }

    public function getArgumentsGet($stringFormat = false) {
        if ($stringFormat) {
            $gets = (count($this->_gets) > 0) ? '?' : '';
            foreach ($this->_gets as $k => $v)
                $gets .= $k . '=' . $v . '&';
            return trim($gets, '&');
        }
        return $this->_gets;
    }

    public function setProxy($host, $ident = false, $port = false) {
        if (!$this->_getCurlInitialized())
            throw new \Exception('Curl must be initialized');

        $this->setCurlOpt(CURLOPT_HTTPPROXYTUNNEL, true);
        $this->setCurlOpt(CURLOPT_PROXY, $host);
        if ($ident)
            $this->setCurlOpt(CURLOPT_PROXYUSERPWD, $ident);
        if ($port)
            $this->setCurlOpt(CURLOPT_PROXYPORT, $port);
        return $this;
    }

    public function setCurlOpt($option, $value) {
        if (!$this->_getCurlInitialized())
            throw new \Exception('Curl must be initialized');

        curl_setopt($this->getCurl(), $option, $value);
        return $this;
    }

    public function execute($returnTransfer = false, $includeHeader = false, $timeOut = false, $encodePostFieldsJson = false) {
        if (!$this->_getCurlInitialized())
            throw new \Exception('Curl must be initialized');

        if (!is_bool($includeHeader))
            throw new \Exception('Include header parameter must be a boolean');
        if (!is_bool($returnTransfer))
            throw new \Exception('ReturnTransfer parameter must be a boolean');

        if ($timeOut && !is_int($timeOut))
            throw new \Exception('TimeOut parameter must be an integer');

        $this->setCurlOpt(CURLOPT_URL, $this->getUrl() . $this->getArgumentsGet(true));
        $this->setCurlOpt(CURLOPT_HEADER, $includeHeader);
        $this->setCurlOpt(CURLOPT_RETURNTRANSFER, $returnTransfer);
        if ($this->getArgumentsPost() !== array()) {
            $posts = $encodePostFieldsJson ? json_encode($this->getArgumentsPost()) : $this->getArgumentsPost(true);
            $this->setCurlOpt(CURLOPT_POST, 1);
            $this->setCurlOpt(CURLOPT_POSTFIELDS, $posts);
        }
        if ($timeOut)
            $this->setCurlOpt(CURLOPT_TIMEOUT, $timeOut);

        $this->_response = curl_exec($this->getCurl());
        if (!$this->getResponse()) {
            Logger::getInstance()->error('Curl error message : "' . $this->getCurlErrorMsg() . '" and code : "' . $this->getCurlErrorCode() . '"');
            return false;
        } else {
            $info = curl_getinfo($this->getCurl());
            Logger::getInstance()->debug('Curl query took ' . $info['total_time'] . ' seconds to be sent to the url: "' . $info['url'] . '"');
            return true;
        }
    }

    public function getCurlErrorMsg() {
        if (!$this->_getCurlInitialized())
            throw new \Exception('Curl must be initialized');
        return curl_error($this->getCurl());
    }

    public function getCurlErrorCode() {
        if (!$this->_getCurlInitialized())
            throw new \Exception('Curl must be initialized');
        return curl_errno($this->getCurl());
    }

    public function close($resetParameter = true) {
        curl_close($this->getCurl());
        if ($resetParameter) {
            $this->_url = null;
            $this->_curl = null;
            $this->_setCurlInitialized(false);
            $this->resetArgumentsPost();
            $this->resetArgumentsGet();
        }
    }

    public function resetReponse() {
        $this->_response = false;
    }

    public function resetArgumentsPost() {
        $this->_posts = array();
    }

    public function resetArgumentsGet() {
        $this->_gets = array();
    }

    public function getResponse() {
        return $this->_response;
    }

    public function setUserAgent($agent) {
        $this->_userAgent = $agent;
        $this->setCurlOpt(CURLOPT_USERAGENT, $this->getUserAgent());
    }

    public function getUserAgent() {
        $this->_userAgent;
    }

    public function setEncodeUrl($bool) {
        if (!is_bool($bool))
            throw new \Exception('Encode parameter must be an boolean');
        $this->_encodeUrl = $bool;
    }

    public function getEncodeUrl() {
        return $this->_encodeUrl;
    }

    protected function _setCurlInitialized($bool) {
        if (!is_bool($bool))
            throw new \Exception('Initialized curl parameter must be a boolean');

        $this->_curlInitialized = $bool;
    }

    protected function _getCurlInitialized() {
        return $this->_curlInitialized;
    }

}

?>