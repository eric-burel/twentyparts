<?php

namespace framework\security;

interface IForm {

    public function __construct($options = array());

    public function setFormName($name);

    public function getFormName();

    public function create();

    public function get();

    public function set();

    public function flush();

    public function check($checkingValue, $flush = false);
}

?>
