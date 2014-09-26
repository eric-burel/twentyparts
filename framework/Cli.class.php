<?php

// http://www.php.net/manual/fr/features.commandline.php
// http://www.php.net/manual/fr/reserved.variables.argc.php
// http://www.php.net/manual/fr/reserved.variables.argv.php
// http://www.php.net/manual/fr/function.getopt.php
//TODO must be completed

namespace framework;

class Cli {

    public static function isCli() {
        return (PHP_SAPI === 'cli');
    }

}

?>