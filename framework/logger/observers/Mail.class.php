<?php

namespace framework\logger\observers;

use framework\Logger;
use framework\mail\SwiftMailer;
use framework\utility\Validate;

class Mail implements \SplObserver {

    protected $_mailConfig = array();
    protected $_logs = '';

    public function __construct($mailConfig) {
        SwiftMailer::getInstance();
        //Set mail config
        if (!is_array($mailConfig))
            throw new \Exception('mailConfig parameter must be an array');

        // sender params
        if (!isset($mailConfig['fromEmail']))
            throw new \Exception('fromEmail parameter don\'t exists');
        if (!Validate::isEmail($mailConfig['fromEmail']))
            throw new \Exception('fromEmail parameter must be a valid email');
        $this->_mailConfig['fromEmail'] = $mailConfig['fromEmail'];
        if (!isset($mailConfig['fromName']))
            throw new \Exception('fromName parameter don\'t exists');
        if (!is_string($mailConfig['fromName']))
            throw new \Exception('fromName parameter must be a string');
        $this->_mailConfig['fromName'] = $mailConfig['fromName'];
        // receiver params
        if (!isset($mailConfig['toEmail']))
            throw new \Exception('toEmai parameter don\'t exists');
        if (!Validate::isEmail($mailConfig['toEmail']))
            throw new \Exception('toEmail parameter must be a valid email');
        $this->_mailConfig['toEmail'] = $mailConfig['toEmail'];
        if (!isset($mailConfig['toName']))
            throw new \Exception('toName parameter don\'t exists');
        if (!is_string($mailConfig['fromName']))
            throw new \Exception('fromName parameter must be a string');
        $this->_mailConfig['toName'] = $mailConfig['toName'];

        //Optional subject of mail params
        if (isset($mailConfig['mailSubject'])) {
            if (!is_string($mailConfig['mailSubject']))
                throw new \Exception('mailSubject parameter must be a string');
            $this->_mailConfig['mailSubject'] = $mailConfig['mailSubject'];
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
            }
            else
                $this->_addLog($log->message, $log->level, $log->date, $log->isTrace);
        }
        if (count($bottomLogs) > 0)
            $this->update($subject, $bottomLogs, $groups);

        $this->_mailLogs();
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

    protected function _mailLogs() {
        if (!empty($this->_logs)) {
            $mail = \Swift_Message::newInstance();
            $mail->setFrom($this->_mailConfig['fromEmail'], $this->_mailConfig['fromName']);
            $mail->setTo($this->_mailConfig['toEmail'], $this->_mailConfig['toName']);
            if (isset($this->_mailConfig['mailSubject']))
                $mail->setSubject($this->_mailConfig['mailSubject']);


            $mail->addPart(nl2br($this->_logs), 'text/html');
            // send email
            $transport = defined('SMTP_SERVER') && !is_null(SMTP_SERVER) && SMTP_SERVER != '' ? \Swift_SmtpTransport::newInstance(SMTP_SERVER, 25) : \Swift_MailTransport::newInstance();
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($mail);
            //reset logs
            $this->_logs = '';
        }
    }

}

?>