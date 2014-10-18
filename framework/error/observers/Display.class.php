<?php

namespace framework\error\observers;

use framework\mvc\Router;

class Display implements \SplObserver {

    public function __construct() {
        
    }

    public function update(\SplSubject $subject, $isException = false) {
        Router::getInstance()->showDebugger($isException);
    }

}

?>
