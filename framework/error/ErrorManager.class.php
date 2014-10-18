<?php

namespace framework\error;

use framework\mvc\Router;
use framework\Application;
use framework\Language;

class ErrorManager implements \SplSubject {

    use \framework\pattern\Singleton;

    protected $_observers; //object SplObjectStorage
    protected $_error = false;
    protected $_clearErrorAfterSending = true;
    protected $_catchFatal = true;

    protected function __construct() {
        $this->_observers = new \SplObjectStorage();
    }

    public function start($catchFatal = true, $displayErrors = true, $displayStartupErrors = true) {
        if (!is_bool($catchFatal))
            throw new \Exception('catchFatal parameter must be a boolean');
        if (!is_bool($displayErrors) && !is_int($displayErrors))
            throw new \Exception('displayErrors parameter must be an int or bool');
        if (!is_bool($displayStartupErrors))
            throw new \Exception('displayStartupErrors parameter must be a boolean');
        if ($catchFatal) {
            register_shutdown_function(array($this, 'fatalErrorHandler'));
            $this->_catchFatal = true;
        }

        ini_set('display_errors', (int) $displayErrors);
        ini_set('display_startup_errors', $displayStartupErrors);
        set_error_handler(array($this, 'errorHandler'));

        return $this;
    }

    public function stop() {
        restore_error_handler();
    }

    public function attach(\SplObserver $observer) {
        if ($this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" is already attached');
        $this->_observers->attach($observer);
        return $this;
    }

    public function detach(\SplObserver $observer) {
        if (!$this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" don\'t exists');
        $this->_observers->detach($observer);
        return $this;
    }

    public function notify() {
        // Erase buffer
        $buffer = ob_get_status();
        if (!empty($buffer))
            ob_end_clean();

        if ($this->_observers->count()) {
            foreach ($this->_observers as $observer)
                $observer->update($this);
        }
        // Clear error for avoid multiple call
        if ($this->_clearErrorAfterSending)
            $this->_error = false;

        // Show internal server error (500)
        if (!Application::getDebug())
            Router::getInstance()->show500();

        // Exit script
        exit();
    }

    public function errorHandler($code, $message, $file, $line) {
        $this->_setError($code, $message, $file, $line);
        $this->notify();
        // Do not execute the PHP error handler
        return true;
    }

    public function fatalErrorHandler() {
        if (error_get_last() !== null) {
            $lastError = error_get_last();
            if ($lastError['type'] === E_ERROR) {
                $this->_setError('E_FATAL', $lastError['message'], $lastError['file'], $lastError['line']);
                $this->notify();
                // Do not execute the PHP error handler
                return true;
            }
        }
    }

    public function getError() {
        return $this->_error;
    }

    public function setClearErrorAfterSending($bool) {
        if (!is_bool($bool))
            throw new \Exception('clearExceptionAfterSending parameter must be a boolean');
        $this->_clearErrorAfterSending = $bool;
        return $this;
    }

    protected function _setError($code, $message, $file, $line) {
        $error = new \stdClass();
        $error->code = $code;
        $error->type = $this->_getErrorType($code);
        $error->message = $message;
        $error->file = $file;
        $error->line = $line;

        $this->_error = $error;
    }

    protected function _getErrorType($errCode) {
        $language = Language::getInstance();
        switch ($errCode) {
            case 'E_FATAL':
                return $language->getVar('e_fatal');
            case E_ERROR:
                return $language->getVar('e_error');
            case E_WARNING:
                return $language->getVar('e_warning');
            case E_PARSE:
                return $language->getVar('e_parse');
            case E_NOTICE:
                return $language->getVar('e_notice');
            case E_CORE_ERROR:
                return $language->getVar('e_core_error');
            case E_CORE_WARNING:
                return $language->getVar('e_core_warning');
            case E_COMPILE_ERROR:
                return $language->getVar('e_compile_error');
            case E_COMPILE_WARNING:
                return $language->getVar('e_compile_warning');
            case E_USER_ERROR:
                return $language->getVar('e_user_error');
            case E_USER_WARNING:
                return $language->getVar('e_user_warning');
            case E_USER_NOTICE:
                return $language->getVar('e_user_notice');
            case E_STRICT:
                return $language->getVar('e_strict');
            case E_RECOVERABLE_ERROR:
                return $language->getVar('e_recoverable_error');
            case E_DEPRECATED:
                return $language->getVar('e_deprecated');
            case E_USER_DEPRECATED:
                return $language->getVar('e_user_deprecated');
            default:
                return $language->getVar('e_unknown');
        }
    }

}

?>