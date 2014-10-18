<?php

namespace framework\pattern;

trait Singleton {

    protected static $_singletons = array();
    protected static $_multiSingleton = false;
    protected static $_lock = array();

    public static function setMultiSingleton($bool) {
        if (!is_bool($bool))
            throw new \Exception('multiSingleton parameter must be a boolean');
        self::$_multiSingleton = $bool;
    }

    public static function getInstance($parameters = array(), $singletonNumber = null) {
        $singleton = get_called_class();
        if (!isset(self::$_singletons[$singleton]) || ((self::$_multiSingleton) && $singletonNumber != null))
            self::_registerSingleton($singleton, $singletonNumber, $parameters);

        return self::_getSingleton($singleton, $singletonNumber);
    }

    public static function getAllSingletons() {
        return self::$_singletons;
    }

    public static function getSingleton($singleton) {
        if (!isset(self::$_singletons[$singleton]))
            throw new \Exception('Singleton "' . $singleton . '" don\'t exists');
        return self::$_singletons[$singleton];
    }

    public static function countAllSingletons() {
        return count(self::$_singletons);
    }

    public static function countAllSingletonsIntoSingleton($singleton) {
        if (!isset(self::$_singletons[$singleton]))
            throw new \Exception('Singleton "' . $singleton . '" don\'t exists');
        return count(self::$_singletons[$singleton]);
    }

    public static function destructAllSingletons() {
        self::$_singletons = array();
    }

    public static function destructSingleton($singleton) {
        if (!isset(self::$_singletons[$singleton]))
            throw new \Exception('Singleton "' . $singleton . '" don\'t exists');
        unset(self::$_singletons[$singleton]);
    }

    public static function destructSingletonIntoSingleton($singleton, $singletonNumber) {
        if (!isset(self::$_singletons[$singleton]))
            throw new \Exception('Singleton "' . $singleton . '" don\'t exists');
        if (!isset(self::$_singletons[$singleton][$singletonNumber]))
            throw new \Exception('Singleton number "' . $singletonNumber . '" don\'t exists');

        unset(self::$_singletons[$singleton][$singletonNumber]);
    }

    protected static function _registerSingleton($singleton, $singletonNumber, $parameters) {
        if (self::_isLocked($singleton))
            throw new \Exception('Loop singleton');

        self::_lockSingleton($singleton);
        $num = ($singletonNumber == null) ? 0 : $singletonNumber;

        // No parameters, or empty, instance directly
        if (empty($parameters))
            self::$_singletons[$singleton][$num] = new $singleton();
        else {
            // create reflectionClass instance, check if is abstract class
            $ref = new \ReflectionClass($singleton);
            if ($ref->isAbstract())
                throw new \Exception('Your singletoned class :"' . $singleton . '" must be non-abstract class');

            // Instance, and check constructor, setAccessible if is not, for invoke
            $inst = $ref->newInstanceWithoutConstructor();
            $constuctor = new \ReflectionMethod($inst, '__construct');
            if ($constuctor->isPrivate() || $constuctor->isProtected())
                $constuctor->setAccessible(true);


            //Finally invoke (with args) and asign object into singletons list
            if (!is_array($parameters))// Single parameter
                $constuctor->invoke($inst, $parameters);
            else
                $constuctor->invokeArgs($inst, $parameters);

            self::$_singletons[$singleton][$num] = $inst;
        }

        self::_unLockSingleton($singleton);
    }

    protected static function _isLocked($singleton) {
        return (bool) (isset(self::$_lock[$singleton]));
    }

    protected static function _lockSingleton($singleton) {
        self::$_lock[$singleton] = true;
    }

    protected static function _unLockSingleton($singleton) {
        if (!self::_isLocked($singleton))
            throw new \Exception('Singleton "' . $singleton . '" can not be locked because it is not locked');
        unset(self::$_lock[$singleton]);
    }

    protected static function _getSingleton($singleton, $singletonNumber) {
        if ($singletonNumber !== null) {
            if (!is_int($singletonNumber))
                throw new \Exception('Singleton number parameter must an interger');
            if (!isset(self::$_singletons[$singleton][$singletonNumber]))
                throw new \Exception('Singleton number "' . $singletonNumber . '" don\'t exists');

            return self::$_singletons[$singleton][$singletonNumber];
        }
        return self::$_singletons[$singleton][0];
    }

    protected function __clone() {
        if (!self::$_multiSingleton)
            throw new \Exception('Cloning singleton is forbidden');
    }

}

?>