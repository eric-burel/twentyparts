<?php

namespace framework\autoloader\adaptaters;

use framework\Autoloader;
use framework\autoloader\IAdaptater;

class Includer extends Autoloader implements IAdaptater {

    public function autoload($class) {
        if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false))
            return;

        if (self::getDebug()) {
            $benchTime = microtime(true);
            $benchMemory = memory_get_usage();
        }
        $classInfos = self::getClassInfo($class);
        if ($classInfos) {
            if (file_exists($classInfos['sourceFilePath'])) {
                require_once $classInfos['sourceFilePath'];
                self::_addLog('Class: "' . $class . '" was included by sourceFile : "' . $classInfos['sourceFilePath'] . '"');
            } else
                self::_addLog('Class : "' . $class . '" can\'t include by source file : "' . $classInfos['sourceFilePath'] . '"');
        } else
            self::_addLog('Class : "' . $class . '" not found');

        if (self::getDebug())
            self::_setBenchmark(microtime(true) - $benchTime, memory_get_usage() - $benchMemory);
    }

}

?>