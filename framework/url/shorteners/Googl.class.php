<?php

// https://developers.google.com/url-shortener/
// TODO : oAuth support
// TODO : multi support : xml and json format for reponse
// TODO : implement details for links: metrics, clicks, etc .. (when oAuth support)

namespace framework\url\shorteners;

use framework\url\Shortener;
use framework\url\IShortener;
use framework\network\Curl;
use framework\utility\Validate;
use framework\Logger;

class Googl implements IShortener {

    protected $_apiUrl = 'https://www.googleapis.com/urlshortener/v1/url?';
    protected $_apiKey = false;

    public function __construct($identifiers) {
        if (!is_array($identifiers))
            throw new \Exception('$identifiers parameter must be an array');

        if (isset($identifiers['key']))
            $this->setApiKey($identifiers['key']);
    }

    public function setApiKey($key) {
        if (!is_int($key) && !is_string($key))
            throw new \Exception('ApiKey parameter must be a string or integer');
        $this->_apiKey = $key;
        return $this;
    }

    public function setApiUrl($url) {
        if (!is_string($url))
            throw new \Exception('Url parameter must be a string');

        if (!Validate::isUrl($url))
            throw new \Exception('Url parameter must be a valid url');
        $this->_apiUrl = $url;
    }

    public function getApiKey() {
        return $this->_apiKey;
    }

    public function getApiUrl() {
        return $this->_apiUrl;
    }

    public function shorten($longUrl, $returnFullReponse = false) {
        if (!Validate::isUrl($longUrl))
            throw new \Exception('longUrl parameter must be a valid url');
        if (!is_bool($returnFullReponse))
            throw new \Exception('returnFullReponse parameter must be an boolean');

        $curl = new Curl($this->getApiUrl());
        if ($this->getApiKey())
            $curl->addArgument('key', $this->getApiKey(), 'GET');
        $curl->addArgument('longUrl', $longUrl, 'POST');
        $curl->setCurlOpt(CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        $curl->execute(true, false, false, true);
        $reponse = json_decode($curl->getResponse());
        if ($returnFullReponse)
            return $reponse;
        else {
            if (isset($reponse->error)) {
                Logger::getInstance()->debug('Goo.gl shorten url error, code : "' . $reponse->error->code . '" and message : "' . $reponse->error->message . '"');
                return false;
            } else {
                Logger::getInstance()->debug('Goo.gl shorten url : "' . $longUrl . '" result is : "' . $reponse->id . '"');
                return $reponse->id;
            }
        }
    }

    public function expand($shortUrlHash, $returnFullReponse = false) {
        if (!is_string($shortUrlHash))
            throw new \Exception('shortUrl parameter must be a string');
        if (!is_bool($returnFullReponse))
            throw new \Exception('returnFullReponse parameter must be an boolean');

        $curl = new Curl($this->getApiUrl());
        if ($this->getApiKey())
            $curl->addArgument('apiKey', $this->getApiKey(), 'GET');
        $curl->addArgument('shortUrl', 'http://goo.gl/' . $shortUrlHash, 'GET');
        $curl->execute(true);
        $reponse = json_decode($curl->getResponse());
        if ($returnFullReponse)
            return $reponse;
        else {
            if (isset($reponse->error)) {
                Logger::getInstance()->debug('Goo.gl expand url error, code : "' . $reponse->error->code . '" and message : "' . $reponse->error->message . '"');
                return false;
            }

            if ($reponse->status == 'OK') {
                Logger::getInstance()->debug('Goo.gl expand url  : "http://goo.gl/' . $shortUrlHash . '" result is : "' . $reponse->longUrl . '"');
                return $reponse->longUrl;
            }
            return false;
        }
    }

}

?>