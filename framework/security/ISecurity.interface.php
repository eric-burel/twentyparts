<?php

namespace framework\security;

interface ISecurity {

    public function __construct($options = array());

    public function run();

    public function stop();
}

?>
