<?php

namespace controllers;

use controllers\Index;
use framework\Session;
use framework\utility\Cookie;

class Members extends Index {

    public static function isConnected() {
        return Session::getInstance()->get('isConnected');
    }

    public function __construct() {
        self::checkConnection();
    }

    public function register() {
        if (Http::isPost() && $this->isAjaxController()) {
            
        }
    }

    public static function checkConnection() {
        if (!self::isConnected()) {
            //check if have cookie login, and if is valid
            if (!is_null(Cookie::get('login'))) {
                self::connect(true);
            }
        }
    }

    public static function connect($createCookie = false) {
        
    }

}

?>