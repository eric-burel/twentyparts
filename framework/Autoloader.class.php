<?php

namespace framework;

use framework\Cache;

class Autoloader {

    use autoloader\Classes,
        autoloader\Directories,
        autoloader\Namespaces;

    protected static $_autoloaders = array();
    protected static $_debug = false;
    protected static $_logs = array();
    protected static $_benchmarkTime = 0;
    protected static $_benchmarkMemory = 0;
    protected static $_cache = false;

    public static function setCache($cacheName) {
        $cache = Cache::getCache($cacheName);
        if (!$cache)
            throw new \Exception('Invalid cache');
        self::$_cache = $cache;
    }

    public static function getCache() {
        return self::$_cache;
    }

    public function __construct($debug = false) {
        if ($debug)
            self::setDebug($debug);
    }

    public static function setDebug($bool) {
        if (!is_bool($bool))
            throw new \Exception('debug parameter must be a boolean');
        self::$_debug = $bool;
    }

    public static function getDebug() {
        return self::$_debug;
    }

    public static function getLogs() {
        return self::$_logs;
    }

    public static function purgeLogs() {
        self::$_logs = array();
    }

    public static function purgeBenchmark() {
        self::$_benchmarkTime = 0;
        self::$_benchmarkMemory = 0;
    }

    public static function getBenchmark($type) {
        if (!is_string($type) && !$type == 'time' && !$type == 'memory')
            throw new \Exception('type parameter must be a string : time or memory');
        if ($type == 'time')
            return self::$_benchmarkTime;
        if ($type == 'memory')
            return self::$_benchmarkMemory;
    }

    public static function registerAutoloader($loaderName, $loaderArguments = array(), $throw = true, $prepend = false, $forceReplace = false) {
        if (!is_string($loaderName))
            throw new \Exception('LoaderName parameter must be a string');

        if (class_exists('framework\autoloader\adaptaters\\' . $loaderName, false))
            $loaderClass = 'framework\autoloader\adaptaters\\' . $loaderName;
        else
            $loaderClass = $loaderName;


        // Instantiate loader
        $loaderInstance = new \ReflectionClass($loaderClass);
        if (!in_array('framework\autoloader\IAdaptater', $loaderInstance->getInterfaceNames()))
            throw new \Exception('Loader class must be implement framework\autoloader\IAdaptater');
        if ($loaderInstance->isAbstract())
            throw new \Exception('Loader class must be not abstract class');
        if ($loaderInstance->isInterface())
            throw new \Exception('Loader class must be not interface');

        // Check if is already registered
        if (self::isRegisteredAutoloader($loaderInstance->getShortName()) && !$forceReplace)
            throw new \Exception('Loader is already registered');

        // Checking arguments for create an instance with good parameters
        if (count($loaderArguments) > 1) {
            $loaderConstructor = new \ReflectionMethod($loaderClass, '__construct');
            $params = $loaderConstructor->getParameters();
            $cleanedLoaderArguments = array();
            foreach ($params as $key => $param) {
                if ($param->isPassedByReference())
                    $cleanedLoaderArguments[$key] = &$loaderArguments[$key];
                else
                    $cleanedLoaderArguments[$key] = $loaderArguments[$key];
            }
            $loader = $loaderInstance->newInstanceArgs($cleanedLoaderArguments);
        } else
            $loader = $loaderInstance->newInstance();


        // Register spl autoload
        if (!function_exists('spl_autoload_register'))
            throw new \Exception('spl_autoload_register does not exists in this PHP installation');
        if (!is_bool($throw))
            throw new \Exception('throw parameter must be an boolean');
        if (!is_bool($prepend))
            throw new \Exception('prepend parameter must be an boolean');

        spl_autoload_register(array($loader, 'autoload'), $throw, $prepend);
        // Stock
        self::$_autoloaders[$loaderInstance->getShortName()] = $loader;
    }

    public static function registerAutoloaders($autoloaders) {
        if (!is_array($autoloaders))
            throw new \Exception('autoloaders parameter must be an array');

        foreach ($autoloaders as $autoloader => $autoloaderParameters) {
            if (is_string($autoloader)) {
                $loaderArguments = isset($autoloaderParameters['loaderArguments']) ? $autoloaderParameters['loaderArguments'] : array();
                $throw = isset($autoloaderParameters['throw']) ? $autoloaderParameters['throw'] : true;
                $prepend = isset($autoloaderParameters['prepend']) ? $autoloaderParameters['prepend'] : false;
                $forceReplace = isset($forceReplace['forceReplace']) ? $autoloaderParameters['forceReplace'] : false;
                self::registerAutoloader($autoloader, $loaderArguments, $throw, $prepend, $forceReplace);
            } elseif (is_int($autoloader)) {
                self::registerAutoloader($autoloaderParameters);
            }
        }
    }

    public static function isRegisteredAutoloader($autoloader) {
        return (array_key_exists($autoloader, self::getAutoloaders()));
    }

    public static function getAutoloader($loaderName) {
        if (!self::isRegisteredAutoloader($loaderName))
            throw new \Exception('Loader isn\'t registered');

        return self::$_autoloaders[$loaderName];
    }

    public static function getAutoloaders() {
        return self::$_autoloaders;
    }

    public static function unregisterAutoloader($loaderName) {
        if (!self::isRegisteredAutoloader($loaderName))
            throw new \Exception('Loader isn\'t registered');
        if (!function_exists('spl_autoload_unregister'))
            throw new \Exception('spl_autoload_unregister does not exists in this PHP installation');
        spl_autoload_unregister(array(self::$_autoloaders[$loaderName], 'autoload'));
        unset(self::$_autoloaders[$loaderName]);
    }

    public static function setAutoloadExtensions($exts) {
        if (!function_exists('spl_autoload_extensions'))
            throw new \Exception('spl_autoload_extensions does not exists in this PHP installation');

        if (is_array($exts)) {
            $extList = '';
            foreach ($exts as &$ext) {
                if (!is_string($ext))
                    throw new \Exception('Extension parameter must be a string');
                $extList .= '.' . $ext . ',';
            }
            if (!empty($extList))
                spl_autoload_extensions(trim($extList, ','));
        } elseif (is_string($exts))
            spl_autoload_extensions('.' . $exts);
        else
            throw new \Exception('Extensions parameter must be an array or a string');
    }

    public static function getAutoloadExtensions() {
        if (!function_exists('spl_autoload_extensions'))
            throw new \Exception('spl_autoload_extensions does not exists in this PHP installation');
        return spl_autoload_extensions();
    }

    protected static function _addLog($log) {
        if (!is_string($log))
            throw new \Exception('log parameter must be a string');
        self::$_logs[] = $log;
    }

    protected static function _setBenchmark($time, $memory) {
        if (!is_float($time) && !is_int($time))
            throw new \Exception('time parameter must be an int or a float');
        if (!is_float($memory) && !is_int($memory))
            throw new \Exception('memory parameter must be an int or a float');

        self::$_benchmarkTime = self::$_benchmarkTime + $time;
        self::$_benchmarkMemory = self::$_benchmarkMemory + $memory;
    }

}

?>