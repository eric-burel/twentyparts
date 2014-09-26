<?php

namespace framework\autoloader\adaptaters;

use framework\Autoloader;
use framework\autoloader\IAdaptater;

class Cache extends Autoloader implements IAdaptater {

    public function autoload($class) {
        if (!self::getCache())
            return;
        if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false))
            return;

        if (self::getDebug()) {
            $benchTime = microtime(true);
            $benchMemory = memory_get_usage();
        }
        $classInfos = self::getClassInfo($class);
        if ($classInfos) {
            if (!$classInfos['isCached']) {
                self::writeClassPath($class, $classInfos['sourceFilePath']);
                self::_setClassInfo($class, $classInfos['sourceFilePath'], true, false);
            }
        }
        if (self::getDebug())
            self::_setBenchmark(microtime(true) - $benchTime, memory_get_usage() - $benchMemory);
    }

    public static function writeClassPath($class, $path) {
        self::getCache()->write('AutoloaderClassSourceFilePath-' . $class, $path, true);
    }

}

?>