<?php

namespace framework\logger\observers;

use framework\Logger;
use framework\Cli;
use framework\network\Http;

class Display implements \SplObserver {

    protected $_logs = '';

    public function __construct() {
        
    }

    public function __destruct() {
        if (!empty($this->_logs)) {
            if (!Http::isAjaxRequest()) {
                if (!Cli::isCli())
                    echo '<pre>';
                echo $this->_logs;
                if (!Cli::isCli())
                    echo '</pre>';
            }
        }
    }

    public function update(\SplSubject $subject, $logs = array(), $groups = array()) {
        ob_start();
        $bottomLogs = array();
        foreach ($logs as &$log) {
            if (is_array($log)) {
                if (count($log) > 0) {
                    if ($groups[$log[0]->group]->onBottom) {
                        $groups[$log[0]->group] = clone $groups[$log[0]->group];
                        $bottomLogs[] = $log;
                        $groups[$log[0]->group]->onBottom = false;
                    } else {
                        $this->_displayGroupTop($log[0]->date, $groups[$log[0]->group]->label);
                        foreach ($log as &$l)
                            $this->_displayLog($l->message, $l->level, $l->date, $l->isTrace);
                        $this->_displayGroupBottom($l->date);
                    }
                }
            } else
                $this->_displayLog($log->message, $log->level, $log->date, $log->isTrace);
        }
        $this->_logs .= ob_get_clean();

        if (count($bottomLogs) > 0)
            $this->update($subject, $bottomLogs, $groups);
    }

    private function _displayLog($message, $level, $date, $isTrace = false) {
        $name = $isTrace ? 'TRACE' : Logger::getLevelName($level);
        $head = '[' . $date . '][' . $name . '] ';
        echo $head . $message . chr(10);
    }

    private function _displayGroupTop($date, $label) {
        echo '[' . $date . '][GROUP] ' . str_pad($label, 120, '-', STR_PAD_BOTH) . chr(10);
    }

    private function _displayGroupBottom($date) {
        echo '[' . $date . '][GROUP] ' . str_repeat('-', 120) . chr(10);
    }

}

?>