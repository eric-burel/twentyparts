<?php

namespace framework\network;

class Ldap {

    protected $_host = null;
    protected $_port = 389;
    protected $_timeout = 20485;
    protected $_conn = false;
    protected $_bind = false;
    protected $_username;
    protected $_password;
    protected $_dn;

    public function __construct($host, $username = null, $password = null, $port = 389, $timeout = 20485, $version = 3) {
        if (!extension_loaded('ldap'))
            throw new \Exception('LDAP extension not loaded try change your PHP configuration');


        $this->setHost($host);
        $this->setPort($port);

        // Ressource Ldap
        $this->connect();

        // Set timeoutOptions
        $this->setOptions(LDAP_OPT_NETWORK_TIMEOUT, $timeout);
        $this->setOptions(LDAP_OPT_PROTOCOL_VERSION, $version);

        // Authentifcation access
        if (!is_null($username))
            $this->setUsername($username);
        if (!is_null($password))
            $this->setPassword($password);

        //Auth
        $this->bind();
    }

    public function setOptions($option, $value) {
        if (!$this->getConn())
            throw new \Exception('No etablished connexion, setting options not possible');
        // Check Option
        switch ($option) {
            case LDAP_OPT_DEREF:
            case LDAP_OPT_SIZELIMIT:
            case LDAP_OPT_TIMELIMIT:
            case LDAP_OPT_NETWORK_TIMEOUT:
            case LDAP_OPT_PROTOCOL_VERSION:
            case LDAP_OPT_ERROR_NUMBER:
                if (!is_int($value))
                    throw new \Exception('This option : "' . $option . '" must be an integer');
                break;
            case LDAP_OPT_REFERRALS:
            case LDAP_OPT_RESTART:
                if (!is_bool($value))
                    throw new \Exception('This option : "' . $option . '" must be a boolean');
                break;
            case LDAP_OPT_HOST_NAME:
            case LDAP_OPT_ERROR_STRING:
            case LDAP_OPT_MATCHED_DN:
                if (!is_bool($value))
                    throw new \Exception('This option : "' . $option . '" must be a string');
                break;
            case LDAP_OPT_SERVER_CONTROLS:
            case LDAP_OPT_CLIENT_CONTROLS:
                if (!is_array($value))
                    throw new \Exception('This option : "' . $option . '" must be an array');
                break;
            default:
                throw new \Exception('This option : "' . $option . '" is\'t valid ldap option');
                break;
        }
        ldap_set_option($this->_conn, $option, $value);
    }

    public function __destruct() {
        if ($this->getConn())
            $this->disconect();
    }

    public function getConn() {
        return $this->_conn;
    }

    public function connect() {
        $this->_conn = ldap_connect($this->getHost(), $this->getPort());

        // Connection failed
        if (!$this->_conn)
            throw new \Exception('Connection to the LDAP server failed "' . $this->getHost() . '" on port "' . $this->getPort() . '"');
    }

    public function bind($username = null, $password = null) {
        if (!is_null($username))
            $this->setUsername($username);
        if (!is_null($password))
            $this->setPassword($password);

        if (!$this->getConn())
            throw new \Exception('No etablished connexion, no bind authentifcation possible');

        if (!is_null($this->getUsername()) && !is_null($this->getPassword()))
            $this->_bind = ldap_bind($this->_conn, $this->getUsername(), $this->getPassword());
        elseif (!is_null($this->getUsername()) && is_null($this->getPassword()))
            $this->_bind = ldap_bind($this->_conn, $this->getUsername());
        else//Anonymous
            $this->_bind = ldap_bind($this->_conn);

        return $this->_bind;
    }

    public function disconect() {
        if (!$this->getConn())
            throw new \Exception('No etablished connexion ...');

        if ($this->_bind) {
            ldap_unbind($this->_conn);
            $this->_bind = false;
        }

        $this->_conn = false;
    }

    public function setHost($host) {
        if (!is_string($host))
            throw new \Exception('Host must be a string');
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

    public function setUsername($username) {
        if (!is_string($username))
            throw new \Exception('Username must be a string');
        $this->_username = $username;
        return $this;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function setPassword($password) {
        if (!is_string($password))
            throw new \Exception('Password must be a string');
        $this->_password = $password;
        return $this;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function setDn($dn) {
        if (!is_string($dn))
            throw new \Exception('Dn must be a string');
        $this->_dn = $dn;
        return $this;
    }

    public function getDn() {
        return $this->_dn;
    }

}

?>