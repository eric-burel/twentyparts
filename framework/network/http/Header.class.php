<?php

// SEE http://en.wikipedia.org/wiki/List_of_HTTP_header_fields for more informations

namespace framework\network\http;

use framework\network\http\ResponseCode;
use framework\network\http\Protocol;
use framework\network\Http;
use framework\utility\Validate;
use framework\Logger;

class Header {

    const DIRECTIVE_TYPE_REQUEST = 0;
    const DIRECTIVE_TYPE_RESPONSE = 1;

    protected static $_directivesList = array(
        'accept',
        'accept-aharset',
        'cache-control',
        'access-control-allow-origin',
        'content-disposition',
        'content-length',
        'content-type',
        'expires',
        'http',
        'pragma',
        'status',
        'via',
        'x-requested-with'
    );

    public static function sentHeader($directiveName, $directiveValue, $replace = true, $responseCode = null, $checkIfHeaderSent = true, $directiveSeparator = ':') {
        if (!is_bool($checkIfHeaderSent))
            throw new \Exception('CheckIfHeaderSent parameter must be an boolean');
        if (!is_bool($replace))
            throw new \Exception('replace parameter must be an boolean');
        if (!is_null($responseCode) && !ResponseCode::isValid($responseCode))
            throw new \Exception('forceResponseCode parameter must be a valid http response code');

        if (!is_string($directiveValue))
            throw new \Exception('Directive value parameter must be a string');

        if (!in_array(strtolower((string) $directiveName), self::$_directivesList))
            throw new \Exception('Directive name : "' . $directiveName . '" must be a valid directive');

        if ($checkIfHeaderSent) {
            if (!headers_sent())
                self::_sentHeader($directiveName, $directiveValue, $replace, $responseCode, $directiveSeparator);
            else
                Logger::getInstance()->notice('Try to send header when is already sent');
        } else {
            Logger::getInstance()->notice('Try to send header but you don\'t check if is already sent');
            self::_sentHeader($directiveName, $directiveValue, $replace, $responseCode, $directiveSeparator);
        }
    }

    protected static function _sentHeader($directiveName, $fieldValue, $replace, $responseCode, $directiveSeparator) {
        if (is_null($directiveSeparator))
            header($directiveName . $fieldValue, $replace, $responseCode);
        else
            header($directiveName . ': ' . $fieldValue, $replace, $responseCode);
    }

    public static function getServerResponse($url = null, $format = false, $onlyResponseCode = false, $checkIfSent = false) {
        if (!is_bool($checkIfSent))
            throw new \Exception('CheckIfSent parameter must be an boolean');
        if (!is_bool($onlyResponseCode))
            throw new \Exception('onlyResponseCode parameter must be an boolean');
        if ($onlyResponseCode)
            return self::getHeaderResponseStatusCode();

        if (is_null($url)) {
            $request = Http::getRootRequest();
            $url = Http::isHttps() ? 'https://' . $request : 'http://' . $request;
        }
        if (!Validate::isUrl($url))
            throw new \Exception('Url must be a valid url');
        if (!is_bool($format))
            throw new \Exception('Format parameter must be an boolean');

        $response = $checkIfSent ? headers_sent() ? get_headers($url, $format) : null : get_headers($url, $format);

        if (!$response && !$checkIfSent)
            throw new \Exception('Error while get headers response status');

        return $response;
    }

    public static function getScriptResponse($checkIfSent = false) {
        if (!is_bool($checkIfSent))
            throw new \Exception('CheckIfSent parameter must be an boolean');

        $response = $checkIfSent ? headers_sent() ? headers_list() : null : headers_list();

        return $response;
    }

    public static function getRequest($noUseApacheFnc = false) {
        if (!is_bool($noUseApacheFnc))
            throw new \Exception('noUseApacheFnc parameter must be an boolean');

        if (function_exists('apache_request_headers') && $noUseApacheFnc)
            $request = apache_request_headers();
        else {
            $request = array();
            $globalServer = Http::getServer();
            foreach ($globalServer as $key => $val) {
                if (preg_match('/\AHTTP_/', $key)) {
                    $arhKkey = preg_replace('/\AHTTP_/', '', $key);
                    $matches = explode('_', $arhKkey);
                    if (count($matches) > 0 and strlen($arhKkey) > 2) {
                        foreach ($matches as $akKey => $akVal)
                            $matches[$akKey] = ucfirst($akVal);
                        $arhKkey = implode('-', $matches);
                    }
                    $request[$arhKkey] = $val;
                }
            }
        }

        return $request;
    }

    public static function getResponseStatusCode() {
        return http_response_code();
    }

    public static function setResponseStatusCode($code, $sentHttpStatus = false, $checkIfHeaderSent = true, $httpProtocol = null) {
        if (!ResponseCode::isValid($code))
            throw new \Exception('Response Code parameter must be a valid http response code');
        if (!is_bool($sentHttpStatus))
            throw new \Exception('withHttpStatus parameter must be an boolean');

        http_response_code($code);

        if ($sentHttpStatus) {
            $httpProtocolVersion = (!is_null($httpProtocol) && Protocol::isValid($httpProtocol)) ? $httpProtocol : str_replace('HTTP/', '', Http::getServer('SERVER_PROTOCOL'));
            $statusMessage = ResponseCode::getMessage($code, false);
            self::sentHeader('HTTP', '/' . (string) $httpProtocolVersion . ' ' . $statusMessage, true, $code, $checkIfHeaderSent, null);
            
            self::sentHeader('Status', $statusMessage, true, $code, $checkIfHeaderSent);
        }
    }

}

?>