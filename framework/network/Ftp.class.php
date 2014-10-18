<?php

namespace framework\network;

use framework\Logger;

class Ftp {

    protected $_host = null;
    protected $_port = 21;
    protected $_timeout = 90;
    protected $_ssl = false;
    protected $_conn = false;
    protected $_username;
    protected $_password;

    public function __construct($host, $port = 21, $timeout = 90, $connectDirectly = false) {
        $this->setHost($host);
        $this->setPort($port);
        $this->setTimeout($timeout);
        if ($connectDirectly)
            $this->connect();
    }

    public function __destruct() {
        if ($this->getConn())
            $this->disconect();
    }

    public function setSsl($bool) {
        $this->_ssl = $bool;
        return $this;
    }

    public function getSsl() {
        return $this->_ssl;
    }

    public function setHost($host) {
        $this->_host = $host;
        return $this;
    }

    public function getHost() {
        return $this->_host;
    }

    public function setPort($port) {
        if ($port < 1 || $port > 65535)
            throw new \Exception('Port must be between 1 and 65535');
        $this->_port = $port;
        return $this;
    }

    public function getPort() {
        return $this->_port;
    }

    public function setTimeout($timeout) {
        $this->_timeout = $timeout;
        return $this;
    }

    public function getTimeout() {
        return $this->_timeout;
    }

    public function setUsername($username) {
        $this->_username = $username;
        return $this;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function setPassword($password) {
        $this->_password = $password;
        return $this;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function getConn() {
        return $this->_conn;
    }

    public function setOption($option, $value) {
        if (!$this->getConn())
            throw new \Exception('No etablished connexion ...');
        if ($option !== FTP_TIMEOUT_SEC && $option !== FTP_AUTOSEEK)
            throw new \Exception('Invalid option');

        ftp_set_option($this->getConn(), $option, $value);
    }

    public function getOption($option, $value) {
        if (!$this->getConn())
            throw new \Exception('No etablished connexion ...');
        if ($option !== FTP_TIMEOUT_SEC && $option !== FTP_AUTOSEEK)
            throw new \Exception('Invalid option');

        ftp_get_option($this->getConn(), $option, $value);
    }

    public function connect() {
        if (!$this->getSsl())
            $this->_conn = ftp_connect($this->getHost(), $this->getPort(), $this->getTimeout());
        else
            $this->_conn = ftp_ssl_connect($this->getHost(), $this->getPort(), $this->getTimeout());

        if (!$this->getConn()) {
            Logger::getInstance()->debug('Connection to the ftp server  "' . $this->getHost() . '" on port "' . $this->getPort() . '" failed');

            return false;
        } else {
            Logger::getInstance()->debug('Connected to the ftp server "' . $this->getHost() . '" on port "' . $this->getPort() . '"');
            return true;
        }
    }

    public function disconect() {
        if (!$this->getConn())
            throw new \Exception('No etablished connexion ...');
        ftp_close($this->getConn());
    }

    public function login($username = 'anonymous', $password = 'anonymous') {
        if (!$this->getConn())
            throw new \Exception('No etablished connexion ...');
        $this->setUsername($username);
        $this->setPassword($password);

        ftp_raw($this->getConn(), 'USER ' . $this->getUsername());
        $logRep = ftp_raw($this->getConn(), 'PASS ' . $this->getPassword());
        if (strpos($logRep[0], '230') !== false) {
            Logger::getInstance()->debug('Login to the ftp server with username "' . $this->getUsername() . '" and password "' . $this->getPassword() . '" succefull');
            return true;
        } else {
            Logger::getInstance()->debug('Login to the ftp server with username "' . $this->getUsername() . '" and password "' . $this->getPassword() . '" failed');

            return false;
        }
    }

}

?>