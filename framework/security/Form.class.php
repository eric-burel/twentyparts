<?php

namespace framework\security;

use framework\security\ISecurity;
use framework\Security;

class Form extends Security implements ISecurity {

    const PROTECTION_CSRF = 'csrf';
    const PROTECTION_CAPTCHA = 'captcha';
    const PROTECTION_FLOOD = 'flood';
    const PROTECTION_XSS = 'xss';

    protected $_forms = array();

    public function __construct($forms = array()) {
        foreach ($forms['datas'] as $form) {
            $protections = array();
            foreach ($form->protections as $protectionType => $protectionOptions) {
                $class = $this->_checkProtection($protectionType);
                $protections[$protectionType] = array(
                    'class' => $class,
                    'options' => $protectionOptions);
            }
            $this->_forms[$form->name] = $protections;
        }
    }

    protected function _checkProtection($protectionType) {
        // Check type
        if (!is_string($protectionType) || $protectionType != self::PROTECTION_CSRF && $protectionType != self::PROTECTION_CAPTCHA && $protectionType != self::PROTECTION_BRUTEFORCE && $protectionType != self::PROTECTION_XSS)
            throw new \Exception('Invalid protection type : "' . $protectionType . '"');

        // Check class
        if (class_exists('framework\security\form\\' . ucfirst($protectionType)))
            $className = 'framework\security\form\\' . ucfirst($protectionType);
        else
            $className = $protectionType;


        $class = new \ReflectionClass($className);
        if (!in_array('framework\security\IForm', $class->getInterfaceNames()))
            throw new \Exception('Form protection drivers must be implement framework\security\IForm');
        if ($class->isAbstract())
            throw new \Exception('Form protection drivers must be not abstract class');
        if ($class->isInterface())
            throw new \Exception('Form protection drivers must be not interface');

        $classInstance = $class->newInstanceWithoutConstructor();
        $constuctor = new \ReflectionMethod($classInstance, '__construct');
        if ($constuctor->isPrivate() || $constuctor->isProtected())
            throw new \Exception('Protection constructor must be public');

        return $className;
    }

    public function getProtection($formName, $protectionType) {
        if (!is_string($formName))
            throw new \Exception('Form name must be a string');

        if (!array_key_exists($formName, $this->_forms))
            throw new \Exception('unregistered form : "' . $formName . '"');

        if (!array_key_exists($protectionType, $this->_forms[$formName]))
            return false;

        //Return instance of protection
        $options = $this->_forms[$formName][$protectionType]['options'];
        $inst = new $this->_forms[$formName][$protectionType]['class']($options);
        $inst->setFormName($formName);

        return $inst;
    }

    public function run() {
        
    }

    public function stop() {
        
    }

}

?>
