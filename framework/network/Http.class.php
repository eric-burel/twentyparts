<?php

namespace framework\network;

use framework\utility\Superglobals;
use framework\network\http\Method;

class Http {

    const PROTOCOL_VERSION_0_9 = '0.9';
    const PROTOCOL_VERSION_1_0 = '1.0';
    const PROTOCOL_VERSION_1_1 = '1.1';
    const PROTOCOL_VERSION_2_0 = '2.0';

    protected static $_protocolVersionList = array(
        self::PROTOCOL_VERSION_0_9,
        self::PROTOCOL_VERSION_1_0,
        self::PROTOCOL_VERSION_1_1,
        self::PROTOCOL_VERSION_2_0
    );
    protected static $_requestOrder = array('REQUEST');

    public static function existsHttpProtocolVersion($httpProtocolVersion) {
        if (!is_string($httpProtocolVersion))
            throw new \Exception('httpProtocolVersion parameter must be a string');
        return in_array($httpProtocolVersion, self::$_protocolVersionList);
    }

    public static function getServerHttpProtocolVersion() {
        return self::getServer('SERVER_PROTOCOL');
    }

    /**
     * Get a Query request by key ($_GET)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    public static function getQuery($key = null, $default = null, $allowHtmlTags = false) {
        return self::getDataFromArray($_GET, $key, $default, $allowHtmlTags);
    }

    /**
     * Get a Post request by key ($_POST)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    public static function getPost($key = null, $default = null, $allowHtmlTags = false) {
        return self::getDataFromArray($_POST, $key, $default, $allowHtmlTags);
    }

    /**
     * Get a Cookie request by key ($_COOKIE)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    public static function getCookie($key = null, $default = null, $allowHtmlTags = false) {
        return self::getDataFromArray($_COOKIE, $key, $default, $allowHtmlTags);
    }

    /**
     * Get a server request by key ($_SERVER)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    public static function getServer($key = null, $default = null, $allowHtmlTags = false) {
        return self::getDataFromArray($_SERVER, $key, $default, $allowHtmlTags);
    }

    /**
     * Get a file request by key ($_FILES)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    public static function getFile($key = null, $default = null, $allowHtmlTags = false) {
        return self::getDataFromArray($_FILES, $key, $default, $allowHtmlTags);
    }

    /**
     * Get a environnement request by key ($_ENV)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    public static function getEnv($key = null, $default = null, $allowHtmlTags = false) {
        return self::getDataFromArray($_ENV, $key, $default, $allowHtmlTags);
    }

    /**
     * Get a request by key (Check in order defined on this class)
     *
     * @access public
     * @static
     * @param Mixed $key By default is null for get all Query values
     * @param Mixed $default Default value if key don't exist
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     *
     * @return Mixed
     */
    public static function getRequest($key = null, $default = null, $allowHtmlTags = false) {
        foreach (self::$_requestOrder as &$adata) {
            switch ($adata) {
                case Superglobals::POST:
                    $data = self::getDataFromArray($_POST, $key, $default, $allowHtmlTags);
                    break;
                case Superglobals::GET:
                    $data = self::getDataFromArray($_GET, $key, $default, $allowHtmlTags);
                    break;
                case Superglobals::REQUEST:
                    $data = self::getDataFromArray($_REQUEST, $key, $default, $allowHtmlTags);
                    break;
                case Superglobals::COOKIE:
                    $data = self::getDataFromArray($_COOKIE, $key, $default, $allowHtmlTags);
                    break;
                default:
                    throw new \Exception('Whats happening ?');
                    break;
            }
            if ($data !== $default)
                return $data;
        }
        return $default;
    }

    /**
     * Get a data from a reference array
     *
     * @access private
     * @static
     * @param Array $array
     * @param String $key
     * @param Mixed $default
     * @param Mixed $allowHtmlTags By default false : Remove all HTML tags, can be true : allow all html tags, can be a list of accepted HTML tags (see strip_tags documentation)
     * @return Mixed
     */
    protected static function getDataFromArray(&$array, $key = false, $default = null, $allowHtmlTags = false) {
        if ($key === null)
            return !$allowHtmlTags ? self::secure($array, $allowHtmlTags) : $array;
        else {
            if (!array_key_exists($key, $array))
                return $default;

            return !$allowHtmlTags ? self::secure($array[$key], $allowHtmlTags) : $array[$key];
        }
    }

    /**
     * Secure a value, remove all, or allow html tags
     *
     * @access private
     * @static
     * @param Mixed $value Value to secure
     * @param Mixed $allowHtmlTags It can be a string with HTML tags to allow (see strip_tags documentation)
     * @see http://www.php.net/strip_tags
     * @return Mixed
     */
    protected static function secure($value, $allowHtmlTags) {
        if (is_array($value)) {
            foreach ($value as &$v)
                $v = self::secure($v, $allowHtmlTags);
        } elseif (is_string($value))
            $value = htmlspecialchars(strip_tags($value, ((is_string($allowHtmlTags) && !empty($allowHtmlTags)) ? $allowHtmlTags : null)), ENT_QUOTES);
        return $value;
    }

    /**
     * Set order of check parameter for getRequest method (Just add in order on parameter constant of Http class)
     *
     * @access public
     * @static
     * @param void
     * @return void
     *
     * @exemple http->setRequestOrder(Http::POST,Http::GET);
     */
    public static function setRequestOrder() {
        if (func_num_args() == 0)
            throw new \Exception('You must be set at least on parameter');

        $args = func_get_args();
        foreach ($args as &$value)
            if ($value != Superglobals::COOKIE && $value != Superglobals::GET && $value != Superglobals::POST && $value != Superglobals::REQUEST)
                throw new \Exception('Invalid parameter "' . $value . "'");
        self::$_requestOrder = $args;
    }

    /**
     * Redirection to another url. With Javascript if header was alreadry send
     *
     * @access public
     * @static
     * @param String $url Redirection of redirection
     * @param Boolean $permanent Set if this is a permanently redirection or not
     * @param Mixed $timer Redirect time: in seconds (optional: default 0)
     * @param Boolean $die Die script after redirected
     * @return void
     */
    public static function redirect($url, $permanent = false, $timer = 0, $die = true, $noForceJsRedirect = false) {
        if (headers_sent()) {
            if ($noForceJsRedirect) {
                echo '<script language="javascript" type="text/javascript">window.setTimeout("location=(\'' . $url . '\');", ' . $timer . '*1000);</script>';
                // if javascript deactived, redirect with meta
                echo '<noscript><meta http-equiv="refresh" content="' . $timer . ';url=' . $url . '" /></noscript>';
            }
        } else {
            if ($timer != 0)
                header('refresh: ' . $timer . ';location=' . $url);
            else
                header('location:' . $url);

            if ($permanent)
                header('Status: 301 Moved Permanently', false, 301);
        }
        if ($die)
            exit();
    }

    /**
     * Check if this is an ajx request
     * @return Boolean
     */
    public static function isAjaxRequest() {
        return (self::getServer('HTTP_X_REQUESTED_WITH') && (stripos(self::getServer('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest') !== false));
    }

    public static function isPost() {
        return (Method::isPostMethod(self::getServer('REQUEST_METHOD')));
    }

    /**
     * This method checks to see if the request is secured via HTTPS
     *
     * @return bool true if https, false otherwise
     */
    public static function isHttps() {
        return (self::getServer('HTTPS') && (stripos(self::getServer('HTTPS'), 'on') !== false));
    }

    public static function getRootRequest($withPort = true) {
        if (!is_bool($withPort))
            throw new \Exception('withPort parameter must an boolean');

        $rootRequest = '';
        if (self::getServer('HTTP_X_FORWARDED_HOST')) {
            // explode the host list separated by comma and use the first host
            $hosts = explode(',', self::getServer('HTTP_X_FORWARDED_HOST'));
            $rootRequest = $hosts[0];
        } elseif (self::getServer('HTTP_X_FORWARDED_SERVER'))
            $rootRequest = self::getServer('HTTP_X_FORWARDED_SERVER');
        else
            $rootRequest = self::getServer('SERVER_NAME') ? self::getServer('SERVER_NAME') : self::getServer('HTTP_HOST');

        // get port
        if ($withPort && !strpos($rootRequest, ':')) {
            if ((self::isHttps() && self::getServer('SERVER_PORT') != 443) || (!self::isHttps() && self::getServer('SERVER_PORT') != 80)) {
                $rootRequest .= ':';
                $rootRequest .= self::getServer('SERVER_PORT');
            }
        }
        return $rootRequest;
    }

    public static function getCurrentUrl($withPort = false) {
        $url = 'http' . (self::isHttps() ? 's' : '') . '://' . self::getServer('SERVER_NAME');

        // add port
        if ($withPort) {
            if ((self::isHttps() && self::getServer('SERVER_PORT') != 443) || (!self::isHttps() && self::getServer('SERVER_PORT') != 80))
                $url .= ':' . self::getServer('SERVER_PORT');
        }

        return $url . self::getServer('REQUEST_URI');
    }

}

?>