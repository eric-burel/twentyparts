<?php

namespace framework\autoloader;

trait Classes {

    protected static $_classes = array();

    public static function getClassInfo($className) {
        if (!is_string($className))
            throw new \Exception('Class name must be a string');

        if (array_key_exists($className, self::$_classes))
            return self::$_classes[$className];

        return false;
    }

    public static function getClasses() {
        return self::$_classes;
    }

    public static function countGlobalizedClasses() {
        $number = 0;
        foreach (self::$_classes as &$class) {
            if ($class['isGlobalized'])
                $number++;
        }
        return $number;
    }

    protected static function _setClassInfo($className, $path, $isCached = false, $isGlobalized = false) {
        if (!is_bool($isCached))
            throw new \Exception('isCached parameter must be a boolean');
        if (!is_bool($isGlobalized))
            throw new \Exception('isGlobalized parameter must be a boolean');
        if (!is_string($className))
            throw new \Exception('Class name must be a string');
        if (!is_string($path))
            throw new \Exception('Path must be a string');

        self::$_classes[$className] = array(
            'sourceFilePath' => $path,
            'isCached' => $isCached,
            'isGlobalized' => $isGlobalized);
    }

}

?>