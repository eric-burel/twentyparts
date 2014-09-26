<?php

namespace framework\error;

use framework\Application;
use framework\mvc\Router;

class ExceptionManager implements \SplSubject {

    use \framework\pattern\Singleton;

    protected $_observers; //object SplObjectStorage
    protected $_exception = false;
    protected $_clearExceptionAfterSending = true;

    protected function __construct() {
        $this->_observers = new \SplObjectStorage();
    }

    public function start() {
        set_exception_handler(array($this, 'exceptionHandler'));
        return $this;
    }

    public function stop() {
        restore_exception_handler();
    }

    public function attach(\SplObserver $observer) {
        if ($this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" is already attached');
        $this->_observers->attach($observer);
        return $this;
    }

    public function detach(\SplObserver $observer) {
        if (!$this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" don\'t exist');
        $this->_observers->detach($observer);
        return $this;
    }

    public function notify() {
        // Erase buffer
        $buffer = ob_get_status();
        if (!empty($buffer))
            ob_end_clean();

        // Notify observers
        if ($this->_observers->count()) {
            foreach ($this->_observers as $observer)
                $observer->update($this, true);
        }

        // Clear exception for avoid multiple call
        if ($this->_clearExceptionAfterSending)
            $this->_exception = false;

        // Show internal server error (500)
        if (!Application::getDebug())
            Router::getInstance()->show500();

        // Exit
        exit();
    }

    public function exceptionHandler($ex) {
        $exception = new \stdClass();
        $exception->message = $ex->getMessage();
        $exception->file = $ex->getFile();
        $exception->line = $ex->getLine();
        $exception->trace = $ex->getTraceAsString();

        $this->_exception = $exception;
        $this->notify();
    }

    public function getException() {
        return $this->_exception;
    }

    public function setClearExceptionAfterSending($bool) {
        if (!is_bool($bool))
            throw new \Exception('clearExceptionAfterSending parameter must be a boolean');
        $this->_clearExceptionAfterSending = $bool;
        return $this;
    }

}

?>