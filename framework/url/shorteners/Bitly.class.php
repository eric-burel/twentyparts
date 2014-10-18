<?php

// http://dev.bitly.com
// TODO : oAuth support
// TODO : multi support : xml and json format for reponse
// TODO : implement details for links: metrics, clicks, etc .. (when oAuth support)

namespace framework\url\shorteners;

use framework\url\IShortener;
use framework\network\Curl;
use framework\utility\Validate;
use framework\Logger;

class Bitly implements IShortener {

    protected $_apiUrl = 'https://api-ssl.bitly.com/';
    protected $_apiVersion = '3.0';
    protected $_apiKey = null;
    protected $_apiLogin = null;

    public function __construct($identifiers) {
        if (!is_array($identifiers))
            throw new \Exception('$identifiers parameter must be an array');
        if (!isset($identifiers['key']))
            throw new \Exception('indetifiers "key" don\'t exists');
        if (!isset($identifiers['login']))
            throw new \Exception('indetifiers "login" don\'t exists');

        $this->setApiKey($identifiers['key']);
        $this->setApiLogin($identifiers['login']);
    }

    public function setApiKey($key) {
        if (!is_int($key) && !is_string($key))
            throw new \Exception('ApiKey parameter must be a string or integer');
        $this->_apiKey = $key;
        return $this;
    }

    public function setApiLogin($login) {
        if (!is_int($login) && !is_string($login))
            throw new \Exception('ApiLogin parameter must be a string or integer');
        $this->_apiLogin = $login;
        return $this;
    }

    public function setApiUrl($url) {
        if (!is_string($url))
            throw new \Exception('Url parameter must be a string');

        if (!Validate::isUrl($url))
            throw new \Exception('Url parameter must be a valid url');
        $this->_apiUrl = $url;
    }

    public function setApiVersion($version) {
        if (!is_string($version))
            throw new \Exception('Api Version parameter must be a string');
        $this->_apiVersion = $version;
    }

    public function getApiKey() {
        return $this->_apiKey;
    }

    public function getApiLogin() {
        return $this->_apiLogin;
    }

    public function getApiUrl() {
        return $this->_apiUrl;
    }

    public function getApiVersion() {
        return $this->_apiVersion;
    }

    public function shorten($longUrl, $returnFullReponse = false) {
        if (!Validate::isUrl($longUrl))
            throw new \Exception('Url parameter must be a valid url');

        if (!is_bool($returnFullReponse))
            throw new \Exception('returnFullReponse parameter must be an boolean');

        $curl = new Curl($this->getApiUrl() . 'shorten');
        $curl->addArgument('version', $this->getApiVersion(), 'GET');
        $curl->addArgument('longUrl', $longUrl, 'GET');
        $curl->addArgument('login', $this->getApiLogin(), 'GET');
        $curl->addArgument('apiKey', $this->getApiKey(), 'GET');
        $curl->execute(true);
        $reponse = json_decode($curl->getResponse());
        if ($returnFullReponse)
            return $reponse;
        else {
            if ($reponse->errorCode != 0)
                throw new \Exception('Bit.Ly shorten url error, code : "' . $reponse->errorCode . '" and message : "' . $reponse->errorMessage . '"');

            if ($reponse->statusCode == 'OK') {
                Logger::getInstance()->debug('Bit.Ly shorten url : "' . $longUrl . '" result is : "' . $reponse->results->{$longUrl}->shortUrl . '"');
                return $reponse->results->{$longUrl}->shortUrl;
            }
            return false;
        }
    }

    public function expand($shortUrlHash, $returnFullReponse = false) {
        if (!is_string($shortUrlHash))
            throw new \Exception('shortUrl parameter must be a string');

        if (!is_bool($returnFullReponse))
            throw new \Exception('returnFullReponse parameter must be an boolean');

        $curl = new Curl($this->getApiUrl() . 'expand');
        $curl->addArgument('version', $this->getApiVersion(), 'GET');
        $curl->addArgument('hash', $shortUrlHash, 'GET');
        $curl->addArgument('login', $this->getApiLogin(), 'GET');
        $curl->addArgument('apiKey', $this->getApiKey(), 'GET');
        $curl->execute(true);
        $reponse = json_decode($curl->getResponse());
        if ($returnFullReponse)
            return $reponse;
        else {
            if ($reponse->errorCode != 0) {
                Logger::getInstance()->debug('Bit.Ly expand url error, code : "' . $reponse->errorCode . '" and message : "' . $reponse->errorMessage . '"');
                return false;
            }
            if ($reponse->statusCode == 'OK') {
                Logger::getInstance()->debug('Bit.Ly expand url  : "http://bit.ly/' . $shortUrlHash . '" result is : "' . $reponse->results->{$shortUrlHash}->longUrl . '"');
                return $reponse->results->{$shortUrlHash}->longUrl;
            }
            return false;
        }
    }

}
?>