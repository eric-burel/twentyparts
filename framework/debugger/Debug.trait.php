<?php

namespace framework\debugger;

trait Debug {

    protected static $_debug = false;

    public static function setDebug($bool) {
        if (!is_bool($bool))
            throw new \Exception('debug parameter must be a boolean');
        self::$_debug = $bool;
    }

    public static function getDebug() {
        return self::$_debug;
    }

}

?>