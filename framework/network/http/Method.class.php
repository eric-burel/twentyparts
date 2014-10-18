<?php

// SEE http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol for more informations

namespace framework\network\http;

class Method {

    const GET = 0;
    const HEAD = 1;
    const POST = 2;
    const PUT = 3;
    const DELETE = 4;
    const TRACE = 5;
    const OPTIONS = 6;
    const CONNECT = 7;
    const PATCH = 8;

    protected static $_methodsList = array(0 => 'GET', 1 => 'HEAD', 2 => 'POST', 3 => 'PUT', 4 => 'DELETE', 5 => 'TRACE', 6 => 'OPTIONS', 7 => 'CONNECT', 8 => 'PATCH');

    public static function isSafe($method) {
        // See http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Safe_methods
        return (self::isValid($method) && ($method == self::HEAD || $method == self::GET || $method == self::TRACE || $method == self::OPTIONS));
    }

    public static function isSecure($method) {
        return (self::isValid($method) && $method != self::TRACE);
    }

    public static function isValid($method) {
        return array_key_exists((int) $method, self::$_methodsList);
    }

    public static function isPost($method) {
        return $method == self::$_methodsList[2];
    }

}

?>
