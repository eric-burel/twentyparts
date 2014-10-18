<?php

namespace framework\autoloader;

trait Namespaces {

    protected static $_namespaces = array();
    protected static $_namespacesSeparators = array('\\', '_');

    public static function addNamespace($namespaceName, $namespacePath, $forceReplace = false) {
        if (!is_string($namespaceName))
            throw new \Exception('Namespace name parameter must be a string');
        if (!is_dir($namespacePath))
            throw new \Exception('Namespace : "' . $namespaceName . '" path must be a valid directory');

        if (array_key_exists($namespaceName, self::$_namespaces) && !$forceReplace)
            throw new \Exception('Namespace directory "' . $namespacePath . '" already registered');

        self::$_namespaces[$namespaceName] = realpath($namespacePath) . DS;
    }

    public static function addNamespaces($namespaces) {
        if (!is_array($namespaces))
            throw new \Exception('Namespaces parameter must be an array');
        foreach ($namespaces as $namespaceName => &$namespacePath)
            self::addNamespace($namespaceName, $namespacePath);
    }

    public static function deleteNamespace($namespaceName) {
        if (!is_string($namespaceName))
            throw new \Exception('Namespace name parameter must be a string');
        if (!array_key_exists($namespaceName, self::$_namespaces))
            throw new \Exception('Namespace "' . $namespaceName . '" isn\'t registered');

        unset(self::$_namespaces[$namespaceName]);
    }

    public static function deleteNamespaces($namespaces) {
        if (!is_array($namespaces))
            throw new \Exception('Namespaces parameter must be an array');
        foreach ($namespaces as &$namespaceName)
            self::deleteNamespace($namespaceName);
    }

    public static function getNamespace($namespaceName) {
        if (!is_string($namespaceName))
            throw new \Exception('Namespace name parameter must be a string');
        if (!array_key_exists($namespaceName, self::$_namespaces))
            throw new \Exception('Namespace : "' . $namespaceName . '" don\'t exists');

        return self::$_namespaces[$namespaceName];
    }

    public static function getNamespaces() {
        return self::$_namespaces;
    }

    public static function addNamespacesSeparator($separator) {
        if (!is_string($separator))
            throw new \Exception('Namespaces Separator parameter must be a string');

        if (in_array($separator, self::getNamespacesSeparators()))
            throw new \Exception('Namespaces Separator "' . $separator . '" already registered');
        self::$_namespacesSeparators[] = $separator;
    }

    public static function deleteNamespacesSeparator($separator) {
        if (!is_string($separator))
            throw new \Exception('Namespaces Separator parameter must be a string');

        if (!in_array($separator, self::getNamespacesSeparators()))
            throw new \Exception('Namespaces Separator "' . $separator . '" isn\'t registered');
        unset(self::$_namespacesSeparators['$separator']);
    }

    public static function getNamespacesSeparators() {
        return self::$_namespacesSeparators;
    }

}

?>