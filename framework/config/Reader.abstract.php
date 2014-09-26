<?php

namespace framework\config;

abstract class Reader {

    abstract public function __construct($filename);

    abstract public function read();
}

?>
