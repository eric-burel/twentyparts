<?php

namespace framework\logger\observers;

use framework\Logger;
use framework\Cli;

class Chrome implements \SplObserver {

    protected $_chrome = false;

    public function __construct() {
        if (!Cli::isCli())
            $this->_chrome = \ChromePHP::getInstance();
    }

    public function update(\SplSubject $subject, $logs = array(), $groups = array()) {
        if ($this->_chrome == false)
            return;
        $bottomLogs = array();
        foreach ($logs as &$log) {
            if (is_array($log)) {
                if (count($log) > 0) {
                    if ($groups[$log[0]->group]->onBottom) {
                        $groups[$log[0]->group] = clone $groups[$log[0]->group];
                        $bottomLogs[] = $log;
                        $groups[$log[0]->group]->onBottom = false;
                    } else {
                        $this->_chrome->group($groups[$log[0]->group]->label);
                        foreach ($log as &$l)
                            $this->_log($l->message, $l->level);
                        $this->_chrome->groupEnd();
                    }
                }
            } else
                $this->_log($log->message, $log->level);
        }
        if (count($bottomLogs) > 0)
            $this->update($subject, $bottomLogs, $groups);
    }

    protected function _log($message, $level) {
        switch ($level) {
            case Logger::EMERGENCY:
            case Logger::ALERT:
            case Logger::CRITICAL:
            case Logger::ERROR:
                $this->_chrome->error($message);
                break;
            case Logger::WARNING:
                $this->_chrome->warn($message);
                break;
            case Logger::NOTICE:
            case Logger::INFO:
                $this->_chrome->info($message);
                break;
            case Logger::DEBUG:
                $this->_chrome->log($message);
                break;
            default:
                $this->_chrome->log($message);
                break;
        }
    }

}

?>