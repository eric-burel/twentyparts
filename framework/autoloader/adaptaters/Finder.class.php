<?php

namespace framework\autoloader\adaptaters;

use framework\Autoloader;
use framework\autoloader\IAdaptater;

class Finder extends Autoloader implements IAdaptater {

    public function autoload($class) {
        if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false))
            return;

        if (self::getDebug()) {
            $benchTime = microtime(true);
            $benchMemory = memory_get_usage();
        }

        $classSourceFile = self::findClassSourceFile($class);
        if ($classSourceFile) {
            if ($classSourceFile['isCached'])
                self::_addLog('Class: "' . $class . '" find by cache');

            self::_setClassInfo($class, $classSourceFile['sourceFilePath'], $classSourceFile['isCached']);
        } else
            self::_addLog('Can\'t find classSourceFile for class : "' . $class . '"');

        if (self::getDebug())
            self::_setBenchmark(microtime(true) - $benchTime, memory_get_usage() - $benchMemory);
    }

    public static function findClassSourceFile($class) {
        if (self::getCache()) {
            $cache = self::getCache()->read('AutoloaderClassSourceFilePath-' . $class);
            if ($cache && file_exists($cache))
                return array('isCached' => true, 'sourceFilePath' => $cache);
        }

        // Find directly class into namespaces and directories
        $namespace = self::_getRootNamespace($class);
        $namespaces = self::getNamespaces();
        if ($namespace && array_key_exists($namespace['namespaceValue'], $namespaces)) {
            $classFile = self::_getClassFile(str_replace(array($namespace['namespaceValue'] . $namespace['namespaceSeparator'], $namespace['namespaceSeparator']), array($namespaces[$namespace['namespaceValue']], DS), $class));
            if (!$classFile)
                return false;
            return array('isCached' => false, 'sourceFilePath' => $classFile);
        } else {
            $directories = self::getDirectories();
            foreach ($directories as &$directory) {
                $fileClass = self::_getClassFile($directory . DS . $class);
                if ($fileClass)
                    return array('isCached' => false, 'sourceFilePath' => $fileClass);
            }
            return false;
        }
    }

    protected static function _getRootNamespace($class) {
        $rootNamespaceLenght = 0;
        $namespacesSeparators = self::getNamespacesSeparators();
        $separatorValue = null;
        foreach ($namespacesSeparators as &$separator) {
            if (strpos($class, $separator)) {
                $rootNamespaceLenght = strcspn($class, $separator);
                $separatorValue = $separator;
                break;
            }
        }
        if ($rootNamespaceLenght == 0)
            return false;

        return array('namespaceValue' => substr($class, 0, $rootNamespaceLenght), 'namespaceSeparator' => $separatorValue);
    }

    protected static function _getClassFile($classPath) {
        $exts = explode(',', self::getAutoloadExtensions());
        foreach ($exts as &$ext) {
            if (file_exists($classPath . $ext))
                return $classPath . $ext;
        }
        return false;
    }

}

?>