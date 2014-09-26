<?php

namespace framework\logger\observers;

use framework\Logger;
use framework\utility\Tools;

class Write implements \SplObserver {

    protected static $_logDir;
    protected static $_logExt = 'log';
    protected $_logs = '';

    public function __construct($logDir, $forceCreate = true) {
        $this->setLogDir($logDir, $forceCreate);
    }

    public static function setLogDir($dir, $forceCreate = true) {
        if ($forceCreate && !is_dir($dir)) {
            if (!mkdir($dir, 0775, true))
                throw new \Exception('Error on creating "' . $dir . '" directory');
        }else {
            if (!is_dir($dir))
                throw new \Exception('Directory "' . $dir . '" do not exists');
        }
        if (!is_writable($dir))
            throw new \Exception('Directory "' . $dir . '" is not writable');
        self::$_logDir = realpath($dir) . DS;
    }

    public static function getLogDir() {
        return self::$_logDir;
    }

    public static function setLogExt($ext) {
        if (is_string($ext))
            throw new \Exception('Log extension must be a string');
        self::$_logExt = $ext;
    }

    public static function getLogExt() {
        return self::$_logExt;
    }

    public function purgeLogsDir($deleteRootLogsDir = true) {
        $dir = Tools::cleanScandir(self::$_logDir);
        foreach ($dir as &$f) {
            if (is_file(self::$_logDir . $f))
                unlink(self::$_logDir . $f);
            if (is_dir(self::$_logDir . $f))
                Tools::deleteTreeDirectory(self::$_logDir . $f);
        }
        if ($deleteRootLogsDir) {
            chmod(self::$_logDir, 0775);
            rmdir(self::$_logDir);
        }
    }

    public function update(\SplSubject $subject, $logs = array(), $groups = array()) {
        $bottomLogs = array();
        foreach ($logs as &$log) {
            if (is_array($log)) {
                if (count($log) > 0) {
                    if ($groups[$log[0]->group]->onBottom) {
                        $groups[$log[0]->group] = clone $groups[$log[0]->group];
                        $bottomLogs[] = $log;
                        $groups[$log[0]->group]->onBottom = false;
                    } else {
                        $this->_addGroupTop($log[0]->date, $groups[$log[0]->group]->label);
                        foreach ($log as &$l)
                            $this->_addLog($l->message, $l->level, $l->date, $l->isTrace);

                        $this->_addGroupBottom($l->date);
                    }
                }
            } else
                $this->_addLog($log->message, $log->level, $log->date, $log->isTrace);
        }
        if (count($bottomLogs) > 0)
            $this->update($subject, $bottomLogs, $groups);

        $this->_writeLogs();
    }

    protected function _addLog($message, $level, $date, $isTrace = false) {
        $name = $isTrace ? 'TRACE' : Logger::getLevelName($level);
        $head = '[' . $date . '][' . $name . '] ';
        $this->_logs .= $head . $message . chr(10);
    }

    protected function _addGroupTop($date, $label) {
        $this->_logs .= '[' . $date . '][GROUP] ' . str_pad($label, 120, '-', STR_PAD_BOTH) . chr(10);
    }

    protected function _addGroupBottom($date) {
        $this->_logs .= '[' . $date . '][GROUP] ' . str_repeat('-', 120) . chr(10);
    }

    protected function _writeLogs() {
        if (!empty($this->_logs)) {
            $file = new \SplFileObject($this->_selectLogDir() . date('d') . '.' . self::$_logExt, 'a+');
            if ($file->flock(LOCK_EX)) {
                $file->fwrite($this->_logs);
                $file->flock(LOCK_UN);
            }
            $this->_logs = '';
        }
    }

    protected function _selectLogDir() {
        $yearDir = self::$_logDir . date('Y');
        if (!is_dir($yearDir)) {
            if (!mkdir($yearDir, 0775, true))
                throw new \Exception('Error on creating "' . $yearDir . '" directory');
        }
        $monthDir = $yearDir . DS . date('m') . DS;
        if (!is_dir($monthDir)) {
            if (!mkdir($monthDir, 0775, true))
                throw new \Exception('Error on creating "' . $monthDir . '" directory');
        }
        return $monthDir;
    }

}

?>