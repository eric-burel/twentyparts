<?php

namespace framework\pattern;

trait Factory {

    public static function factory($className, $args, $classNamespace = null, $interfaceName = null, $multiArgs = false, $strictNamespace = false, $parentClass = null, $autoload = true) {
        if (!is_string($className))
            throw new \Exception('Class name parameter must be a string');
        if (!is_null($classNamespace) && !is_string($classNamespace))
            throw new \Exception('Class namespace parameter must be a string or null');
        if (!is_bool($strictNamespace))
            throw new \Exception('strictNamespace parameter must be a boolean');
        if (!is_null($interfaceName) && !is_string($interfaceName))
            throw new \Exception('Interface name parameter must be a string or null');
        if (!is_null($parentClass) && !is_string($parentClass))
            throw new \Exception('Parent class must be a string or null');

        if (is_null($classNamespace) && $strictNamespace)
            throw new \Exception('Cannot mixed null namespace and strictNamespace parameters');

        //remove namespace into className
        if (!is_null($classNamespace) && stripos($className, $classNamespace . '\\') !== false)
            $className = str_replace($classNamespace . '\\', '', $className);

        // check class nampesace and define class
        if (!is_null($classNamespace) && class_exists($classNamespace . '\\' . ucfirst($className), $autoload))
            $class = $classNamespace . '\\' . ucfirst($className);
        else {
            if ($strictNamespace)
                throw new \Exception($classNamespace . '\\' . ucfirst($className) . ' doesn\'t exists');

            $class = ucfirst($className);
            if (!class_exists($class, $autoload))
                throw new \Exception($class . ' doesn\'t exists');
        }

        // new Reflection, and check interface
        $inst = new \ReflectionClass($class);
        if (!is_null($interfaceName)) {
            if (!in_array($interfaceName, $inst->getInterfaceNames()))
                throw new \Exception('Class : "' . $class . '" must be implement "' . $interfaceName . '"');
        }
        //check extended class
        if (!is_null($parentClass)) {
            if (!is_subclass_of($class, $parentClass))
                throw new \Exception('Class : "' . $class . '" must be extend "' . $parentClass . '"');
        }


        // create instance with parameters
        $constructor = new \ReflectionMethod($class, '__construct');
        $params = $constructor->getParameters();
        if (count($params) > 1 && !$multiArgs)
            throw new \Exception('Invalid args parameters, need instanceArgs');
        if (count($params) <= 1 && $multiArgs)
            throw new \Exception('Invalid args parameters, no need instanceArgs');
        if ($multiArgs && !is_array($args))
            throw new \Exception('Invalid args, must be an array');

        $cleanedArgs = array();
        foreach ($params as $key => $param) {
            if ($multiArgs) {
                if (!array_key_exists($key, $args))
                    throw new \Exception('Miss argument : "' . $param . '"');

                if ($param->isPassedByReference())
                    $cleanedArgs[$key] = &$args[$key];
                else
                    $cleanedArgs[$key] = $args[$key];
            } else {
                if ($param->isPassedByReference())
                    $cleanedArgs = &$args;
                else
                    $cleanedArgs = $args;
            }
        }
        $factory = $multiArgs ? $inst->newInstanceArgs($cleanedArgs) : $inst->newInstance($cleanedArgs);

        return $factory;
    }

}

?>