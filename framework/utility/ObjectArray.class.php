<?php

namespace framework\utility;

class ObjectArray {

    protected $_datas;

    public function __construct($array = array()) {
        if (!is_array($array))
            throw new \Exception('must be an array');

        $this->_datas = $array;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->_datas))
            return $this->_datas[$name];

        return null;
    }

    public function __set($name, $value) {
        if (array_key_exists($name, $this->_datas))
            $this->_datas[$name] = $value;

        return $this;
    }

    public function __isset($name) {
        return array_key_exists($name, $this->_datas);
    }

}
