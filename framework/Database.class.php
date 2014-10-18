<?php

namespace framework;

use framework\Logger;
use framework\database\IAdaptater;
use framework\database\Server;
use framework\utility\Benchmark;

class Database {

    use pattern\Factory;

    const PARAM_NULL = 0;
    const PARAM_INT = 1;
    const PARAM_STR = 2;
    const PARAM_LOB = 3;
    const PARAM_STMT = 4;
    const PARAM_BOOL = 5;
    const PARAM_INPUT_OUTPUT = 6;
    // bind order type
    const PARAM_BIND_POSITIONAL = 0;
    const PARAM_BIND_NAMED = 1;
    //fetch style
    const FETCH_LAZY = 1;
    const FETCH_ASSOC = 2;
    const FETCH_NUM = 3;
    const FETCH_BOTH = 4;
    const FETCH_OBJ = 5;
    const FETCH_BOUND = 6;
    const FETCH_COLUMN = 7;
    const FETCH_CLASS = 8;
    const FETCH_INTO = 9;
    const FETCH_FUNC = 10;
    const FETCH_NAMED = 11;
    const FETCH_KEY_PAIR = 12;
    const FETCH_GROUP = 13;
    const FETCH_UNIQUE = 14;
    const FETCH_CLASSTYPE = 15;
    const FETCH_SERIALIZE = 16;
    const FETCH_PROPS_LATE = 17;
    //fetch orientation
    const FETCH_ORI_NEXT = 0;
    const FETCH_ORI_PRIOR = 1;
    const FETCH_ORI_FIRST = 2;
    const FETCH_ORI_LAST = 3;
    const FETCH_ORI_ABS = 4;
    const FETCH_ORI_REL = 5;

    //For debug message information
    protected static $_paramTypeName = array(
        self::PARAM_NULL => 'null',
        self::PARAM_INT => 'int',
        self::PARAM_STR => 'str',
        self::PARAM_LOB => 'lob',
        self::PARAM_STMT => 'stmt',
        self::PARAM_BOOL => 'bool',
        self::PARAM_INPUT_OUTPUT => 'input output');
    protected static $_databases = array();
    protected $_name;
    protected $_type;
    protected $_adaptater = null;
    protected $_masters = array();
    protected $_slaves = array();
    protected $_stats = array('time' => 0, 'ram' => 0); //Queries totals stats
    protected $_queryCount = 0;

    public static function getDatabase($name, $returnAdaptater = false) {
        if (!is_string($name))
            throw new \Exception('Database name must be a string');

        if (!array_key_exists($name, self::$_databases))
            return false;

        $db = self::$_databases[$name];
        if ($returnAdaptater)
            return $db->getAdaptater();

        return $db;
    }

    public static function getDatabases() {
        return self::$_databases;
    }

    public static function addDatabase($name, Database $instance, $forceReplace = false) {
        if (!is_string($name) && !is_int($name))
            throw new \Exception('Database name must be string or integer');


        if (array_key_exists($name, self::$_databases)) {
            if (!$forceReplace)
                throw new \Exception('Database : "' . $name . '" already defined');

            Logger::getInstance()->debug('Database : "' . $name . '" already defined, was overloaded');
        }

        self::$_databases[$name] = $instance;
    }

    public function getStats() {
        return $this->_stats;
    }

    public function setStats($time, $ram) {
        $this->_stats['time'] = $this->_stats['time'] + $time;
        $this->_stats['ram'] = $this->_stats['ram'] + $ram;
    }

    public function __construct($name, $type, $adaptater) {
        $this->setName($name);
        $this->setType($type);
        $this->setAdaptater($adaptater);
        Logger::getInstance()->addGroup('database' . $this->_name, 'Database ' . $this->_name, true, true);
    }

    public function isValidDriver($driver) {
        if (!$this->_adaptater)
            throw new \Exception('Please set adaptater before check driver supported');
        return $this->_adaptater->isValidDriver($driver);
    }

    // Setters
    public function setName($name) {
        if (!is_string($name))
            throw new \Exception('Name must be a string');
        $this->_name = $name;
    }

    public function setType($type) {
        if (!is_string($type))
            throw new \Exception('Type must be a string');

        $this->_type = $type;
    }

    public function setAdaptater(IAdaptater $adaptater) {
        $this->_adaptater = $adaptater;
    }

    // Getters
    public function getName() {
        return $this->_name;
    }

    public function getType() {
        return $this->_type;
    }

    public function getAdaptater() {
        return $this->_adaptater;
    }

    public function getQueryCount() {
        return $this->_queryCount;
    }

    // Servers
    public function addServers($servers) {
        if (!is_array($servers))
            throw new \Exception('Servers list must be an array');

        foreach ($servers as $server)
            $this->addServer($server);
    }

    public function addServer(Server $server) {
        if ($this->existServer($server))
            throw new \Exception('Already registered server');
        $type = $server->getType();
        if ($type == Server::TYPE_MASTER)
            $this->_masters[] = $server;
        elseif ($type == Server::TYPE_SLAVE)
            $this->_slaves[] = $server;
    }

    public function getServer($type) {
        $nbServers = $this->countServers($type);
        switch ($type) {
            case Server::TYPE_MASTER:
                if ($nbServers == 0)
                    throw new \Exception('Not servers exists');
                elseif ($nbServers == 1)
                    return $this->_masters[0];
                else// Load Balancing
                    return $this->_masters[array_rand($this->_masters)];
                break;
            case Server::TYPE_SLAVE:
                if ($nbServers == 0)
                    return $this->getServer(Server::TYPE_MASTER);
                elseif ($nbServers == 1)
                    return $this->_slaves[0];
                else // Load Balancing
                    return $this->_slaves[array_rand($this->_slaves)];
                break;
            default:
                throw new \Exception('Server type ' . $type . ' don\'t exist !');
        }
    }

    // Cette fonction retourne tous les serveurs (possible de définir le type), dans array global des serveurs...
    public function getServers($type = null) {
        if ($type == Server::TYPE_MASTER)
            return $this->_masters;
        elseif ($type == Server::TYPE_SLAVE)
            return $this->_slaves;
        else
            return array_merge($this->_masters, $this->_slaves);
    }

    public function countServers($type = null) {
        if ($type == Server::TYPE_MASTER)
            return count($this->_masters);
        elseif ($type == Server::TYPE_SLAVE)
            return count($this->_slaves);
        else
            return count($this->_masters) + count($this->_slaves);
    }

    public function existServer(Server $server) {
        $type = $server->getType();
        switch ($type) {
            case Server::TYPE_MASTER:
                foreach ($this->_masters as &$master)
                    if ($master == $server)
                        return true;
                break;
            case Server::TYPE_SLAVE:
                foreach ($this->_slaves as &$slave)
                    if ($slave == $server)
                        return true;
                break;
            default:
                throw new \Exception('Server type ' . $type . ' don\'t exist !');
        }
        return false;
    }

    public function incrementQueryCount() {
        $this->_queryCount++;
    }

    public function logQuery($query, $params = array(), $bindParamType = null, $lastError = null) {
        if (!is_array($params))
            throw new \Exception('Params must be an array');
        // Query
        Logger::getInstance()->debug('Query : ' . $query, 'database' . $this->getName());
        if (count($params) > 0) {
            // Parameters
            Logger::getInstance()->debug('Params (' . count($params) . ') bind by ' . $this->_getBindParamType($bindParamType), 'database' . $this->getName());
            if ($bindParamType == self::PARAM_BIND_POSITIONAL)
                $i = 0;
            foreach ($params as &$param) {
                $key = $bindParamType == self::PARAM_BIND_POSITIONAL ? $i : $param['key'];
                Logger::getInstance()->debug('Key : ' . $key . ', Value : ' . $param['value'] . ', Type : ' . (int) $param['type'] . ' (' . self::$_paramTypeName[$param['type']] . '), IsParam : ' . (int) $param['isParam'], 'database' . $this->getName());
                if ($bindParamType == self::PARAM_BIND_POSITIONAL)
                    $i++;
            }
        }
        // Benchmark
        $time = Benchmark::getInstance($this->getName())->stopTime()->getStatsTime();
        $ram = Benchmark::getInstance($this->getName())->stopRam()->getStatsRam();
        Logger::getInstance()->debug('Benchmark time : ' . $time . ' ms Ram : ' . $ram, 'database' . $this->getName());
        // Error
        if (!is_null($lastError))
            Logger::getInstance()->debug('Error : ' . $lastError, 'database' . $this->getName());


        // increment stats
        $this->setStats($time, $ram);
        $this->incrementQueryCount();
    }

    protected function _getBindParamType($bindParamType) {
        if ($bindParamType == self::PARAM_BIND_POSITIONAL)
            return 'positional';
        elseif ($bindParamType == self::PARAM_BIND_NAMED)
            return 'named';
        else
            return 'unknow';
    }

}

?>
