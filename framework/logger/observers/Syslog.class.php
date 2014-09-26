<?php

namespace framework\logger\observers;

use framework\utility\Tools;

class Syslog implements \SplObserver {

    public function __construct($ident = false, $option = LOG_CONS, $facility = LOG_USER) {
        if ($ident != false) {
            if (!is_string($ident))
                throw new \Exception('ident parameter must be a string');
        }

        switch ($facility) {
            case LOG_AUTH:
            case LOG_AUTHPRIV:
            case LOG_CRON:
            case LOG_DAEMON:
            case LOG_KERN:
            case LOG_LPR:
            case LOG_MAIL:
            case LOG_NEWS:
            case LOG_SYSLOG:
            case LOG_USER:
            case LOG_UUCP:
                break;
            case LOG_LOCAL0:
            case LOG_LOCAL1:
            case LOG_LOCAL2:
            case LOG_LOCAL3:
            case LOG_LOCAL4:
            case LOG_LOCAL5:
            case LOG_LOCAL6:
            case LOG_LOCAL7:
                if (Tools::isWindows())
                    throw new \Exception('This facility parameter isn\'t valid on your system');
                break;
            default:
                throw new \Exception('This facility parameter is invalid');
        }
        switch ($option) {
            case LOG_CONS:
            case LOG_NDELAY:
            case LOG_ODELAY:
            case LOG_PERROR:
            case LOG_PID:
                break;
            default:
                throw new \Exception('This facility parameter is invalid');
        }
        openlog($ident, $option, $facility);
    }

    public function __destruct() {
        closelog();
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
                        syslog($log[0]->level, $groups[$log[0]->group]->label);
                        foreach ($log as &$l)
                            syslog($l->level, $l->message);
                        syslog($log[0]->level, $groups[$log[0]->group]->label);
                    }
                }
            } else
                syslog($log->level, $log->message);
        }
        if (count($bottomLogs) > 0)
            $this->update($subject, $bottomLogs, $groups);
    }

}

?>