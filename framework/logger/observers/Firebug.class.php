<?php

namespace framework\logger\observers;

use framework\Logger;
use framework\Cli;

class Firebug implements \SplObserver {

    protected $_fb = false;

    public function __construct() {
        if (!Cli::isCli())
            $this->_fb = \FirePHP::getInstance(true);
    }

    public function update(\SplSubject $subject, $logs = array(), $groups = array()) {
        if ($this->_fb == false)
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
                        $this->_fb->group($groups[$log[0]->group]->label);
                        foreach ($log as &$l)
                            $this->_fireLog($l->message, $l->level);
                        $this->_fb->groupEnd();
                    }
                }
            } else
                $this->_fireLog($log->message, $log->level);
        }
        if (count($bottomLogs) > 0)
            $this->update($subject, $bottomLogs, $groups);
    }

    protected function _fireLog($message, $level) {
        switch ($level) {
            case Logger::EMERGENCY:
            case Logger::ALERT:
            case Logger::CRITICAL:
            case Logger::ERROR:
                $this->_fb->error($message);
                break;
            case Logger::WARNING:
                $this->_fb->warn($message);
                break;
            case Logger::NOTICE:
            case Logger::INFO:
                $this->_fb->info($message);
                break;
            case Logger::DEBUG:
                $this->_fb->log($message);
                break;
            default:
                $this->_fb->log($message);
                break;
        }
    }

}

?>