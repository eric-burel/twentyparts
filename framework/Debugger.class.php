<?php

namespace framework;

class Debugger {

    public static function dump($expression, $die = false) {
        echo '<pre>';
        var_dump($expression);
        echo '</pre>';
        if ($die)
            die();
    }

    public static function dumpBool($var, $return = false) {
        if (!is_bool($var))
            throw new \Exception('Var must be an boolean');
        if ($var === false)
            $info = 'false';
        else
            $info = 'true';

        if ($return)
            return $info;
        else
            echo $info;
    }

    public static function xdebugIsEnabled() {
        if (extension_loaded('xdebug'))
            return (bool) xdebug_is_enabled();

        return false;
    }

}

?>