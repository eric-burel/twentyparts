<?php

namespace framework;

use framework\Logger;

class Security {

    const TYPE_CRYPTION = 'cryption';
    const TYPE_FORM = 'form';
    const TYPE_SNIFFER = 'sniffer';

    protected static $_security = array();
    protected static $_autorun = array();

    public static function addSecurity($type, $options = array(), $forceReplace = false) {
        if (!self::isValid($type))
            throw new \Exception('Invalid security type : "' . $type . '"');

        if (self::exist($type)) {
            if (!$forceReplace)
                throw new \Exception('Trying register security : "' . $type . '" already registered');

            Logger::getInstance()->debug('Trying register security : "' . $type . '" already registered, was overloaded');
        }

        self::$_security[$type] = self::_factory($type, $options);
        if (isset($options['autorun']) && $options['autorun'])
            self::$_autorun[] = $type;
    }

    public static function getSecurity($type = false) {
        if (!$type)
            return self::$_security;
        if (self::exist($type))
            return self::$_security[$type];
        else {
            Logger::getInstance()->debug('Trying get unregistered security');
            return false;
        }
    }

    public static function exist($type) {
        if (!is_string($type) && !is_int($type))
            throw new \Exception('Security type must be string or integer');

        return array_key_exists($type, self::$_security);
    }

    public static function isValid($type) {
        return (is_string($type) && $type == self::TYPE_CRYPTION || $type == self::TYPE_FORM || $type == self::TYPE_SNIFFER);
    }

    public static function autorun() {
        foreach (self::$_autorun as $security)
            self::runSecurity($security, false);
    }

    public static function runSecurity($type, $check = true) {
        if (!$check) {
            self::$_security[$type]->run();
            return;
        }
        if (self::exist($type))
            self::$_security[$type]->run();
    }

    protected static function _factory($type, $options = array()) {
        if (class_exists('framework\security\\' . ucfirst($type)))
            $class = 'framework\security\\' . ucfirst($type);
        else
            $class = $type;

        $classInstance = new \ReflectionClass($class);
        if (!in_array('framework\security\ISecurity', $classInstance->getInterfaceNames()))
            throw new \Exception('Security class must be implement framework\security\ISecurity');
        if ($classInstance->isAbstract())
            throw new \Exception('Security class must be not abstract class');
        if ($classInstance->isInterface())
            throw new \Exception('Security class must be not interface');

        Logger::getInstance()->addGroup('security', 'Security report', true, true);
        return $classInstance->newInstance($options);
    }

}

?>
