<?php

namespace framework\config;

use framework\config\Reader;

abstract class Loader {

    abstract public function load(Reader $reader);
}

?>
