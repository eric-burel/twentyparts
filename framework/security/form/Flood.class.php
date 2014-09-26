<?php

//TODO must be completed

namespace framework\security\form;

use framework\security\IForm;

class Flood implements IForm {

    protected $_formName = '';

    public function __construct($options = array()) {
        throw new \Exception('Not yet');
    }

    public function setFormName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Form name must be a valid variable name');

        $this->_formName = $name;
    }

    public function getFormName() {
        return $this->_formName;
    }

    public function set() {
        
    }

    public function create() {
        
    }

    public function get() {
        
    }

    public function flush() {
        
    }

    public function check($checkingValue, $flush = false) {
        
    }

}

?>
