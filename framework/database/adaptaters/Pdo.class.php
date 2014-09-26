<?php

namespace framework\database\adaptaters;

use framework\database\IAdaptater;
use framework\database\Server;
use framework\utility\Benchmark;
use framework\utility\Validate;
use framework\Database;
use framework\Application;
use framework\Logger;

class Pdo implements IAdaptater {

    //Conn and config
    protected $_configName = null;
    protected $_serverConf = null;
    protected $_connection = null;
    //reqs
    protected $_query = null;
    protected $_statement = false; //PdoStatement
    protected $_execute = false;
    protected $_params = array();
    protected $_paramsNumberNecesary = 0;
    protected $_namedParamOrder = array();
    protected $_bindParamType = null;
    //params type
    protected $_paramType = array(
        Database::PARAM_NULL => \PDO::PARAM_NULL,
        Database::PARAM_INT => \PDO::PARAM_INT,
        Database::PARAM_STR => \PDO::PARAM_STR,
        Database::PARAM_LOB => \PDO::PARAM_LOB,
        Database::PARAM_STMT => \PDO::PARAM_STMT,
        Database::PARAM_BOOL => \PDO::PARAM_BOOL,
        Database::PARAM_INPUT_OUTPUT => \PDO::PARAM_INPUT_OUTPUT);
    //fetch style
    protected $_fetchStyle = array(
        Database::FETCH_LAZY => \PDO::FETCH_LAZY,
        Database::FETCH_ASSOC => \PDO::FETCH_ASSOC,
        Database::FETCH_NUM => \PDO::FETCH_NUM,
        Database::FETCH_BOTH => \PDO::FETCH_BOTH,
        Database::FETCH_OBJ => \PDO::FETCH_OBJ,
        Database::FETCH_BOUND => \PDO::FETCH_BOUND,
        Database::FETCH_COLUMN => \PDO::FETCH_COLUMN,
        Database::FETCH_CLASS => \PDO::FETCH_CLASS,
        Database::FETCH_INTO => \PDO::FETCH_INTO,
        Database::FETCH_FUNC => \PDO::FETCH_FUNC,
        Database::FETCH_NAMED => \PDO::FETCH_NAMED,
        Database::FETCH_KEY_PAIR => \PDO::FETCH_KEY_PAIR,
        Database::FETCH_GROUP => \PDO::FETCH_GROUP,
        Database::FETCH_UNIQUE => \PDO::FETCH_UNIQUE,
        Database::FETCH_CLASSTYPE => \PDO::FETCH_CLASSTYPE,
        Database::FETCH_SERIALIZE => \PDO::FETCH_SERIALIZE,
        Database::FETCH_PROPS_LATE => \PDO::FETCH_PROPS_LATE,
    );
    //fetch orientation
    protected $_fetchOrientation = array(
        Database::FETCH_ORI_NEXT => \PDO::FETCH_ORI_NEXT,
        Database::FETCH_ORI_PRIOR => \PDO::FETCH_ORI_PRIOR,
        Database::FETCH_ORI_FIRST => \PDO::FETCH_ORI_FIRST,
        Database::FETCH_ORI_LAST => \PDO::FETCH_ORI_LAST,
        Database::FETCH_ORI_ABS => \PDO::FETCH_ORI_ABS,
        Database::FETCH_ORI_REL => \PDO::FETCH_ORI_REL
    );

    public function __construct($configName) {
        $this->_configName = $configName;
    }

    public function __destruct() {
        if ($this->_connection)
            $this->disconnect();
    }

    public function isValidDriver($driver) {
        if (!is_string($driver))
            return false;
        return in_array($driver, \PDO::getAvailableDrivers());
    }

    public function connection($serverType) {
        $server = Database::getDatabase($this->_configName)->getServer($serverType);
        if ($server !== $this->_serverConf) {
            if ($this->_connection)
                $this->disconnect();

            $this->_serverConf = $server;
            // Connect
            try {
                if (!is_null($this->_serverConf->getDsn()))
                    $dsn = $this->_serverConf->getDsn();
                else
                    $dsn = $this->_serverConf->getDriver() . ':dbname=' . $this->_serverConf->getDbname() . ';host=' . $this->_serverConf->getHost() . ';port=' . $this->_serverConf->getPort() . ';charset=' . $this->_serverConf->getDbcharset();

                $this->_connection = new \PDO($dsn, $this->_serverConf->getDbuser(), $this->_serverConf->getDbpassword());
            } catch (\PDOException $e) {
                throw new \Exception('Error : ' . $e->getMessage() . ' N° : ' . $e->getCode() . '');
            }
            Logger::getInstance()->debug('Connect server : "' . $dsn . '"', 'database' . $this->_configName);
        }
        return $this;
    }

    public function disconnect() {
        //reset query
        $this->resetQuery();

        // Close connexion
        if ($this->_connection)
            $this->_connection = null;

        // Clean server Configuration
        if ($this->_serverConf)
            $this->_serverConf = null;

        return $this;
    }

    public function resetQuery() {
        if ($this->_statement)
            $this->_statement->closeCursor();

        $this->_query = null;
        $this->_params = array();
        $this->_paramsNumberNecesary = 0;
        $this->_bindParamType = null;
        $this->_namedParamOrder = array();
        $this->_statement = false;
        $this->_execute = false;
    }

    public function quote($query, $paramType = Database::PARAM_STR) {
        if (!is_string($paramType) && !is_int($paramType))
            throw new \Exception('Type "' . $paramType . '" must be an integer or a string');
        if (!array_key_exists($paramType, $this->_paramType))
            throw new \Exception('Type "' . $paramType . '" don\'t exist');

        if (is_null($this->_connection))
            throw new \Exception('Connect before quote query');

        return $this->_connection->quote($query, $paramType);
    }

    public function exec($query, $safe = false) {
        if (Application::getDebug())
            Benchmark::getInstance($this->_configName)->startTime()->startRam();

        //Clean previous
        $this->resetQuery();

        if ($this->isReadQuery($query))
            throw new \Exception('Read query cannot allow by exec function, use prepare and execute');
        $this->connection(Server::TYPE_MASTER);

        if ($safe)
            $query = $this->quote($query);

        $exec = $this->_connection->exec($query);

        // Debug
        if (Application::getDebug())
            Database::getDatabase($this->_configName)->logQuery($query);

        return $exec;
    }

    public function prepare($query, $options = array()) {
        if (!is_string($query))
            throw new \Exception('Query must be a string');

        //Clean previous
        $this->resetQuery();

        $this->_query = $query;
        $server = $this->isReadQuery($this->_query) ? Server::TYPE_SLAVE : Server::TYPE_MASTER;
        $this->connection($server);

        // Check query and determine paramters type (by position with ? or by name with :name)
        preg_match_all('#:([0-9a-zA-Z_-]+)#', $query, $namedParam);
        if (count($namedParam[1]) > 0) {
            if (strpos($this->_query, '?') !== false)
                throw new \Exception('You cannot mixed positional and named parameter on query');
            $query = preg_replace('#:([0-9a-zA-Z_-]+)#', '?', $query);
            // set param bind type to named
            $this->_bindParamType = Database::PARAM_BIND_NAMED;
            $this->_namedParamOrder = $namedParam[1];
        } else
            $this->_bindParamType = Database::PARAM_BIND_POSITIONAL;


        // Count parameters necessary
        $this->_paramsNumberNecesary = $this->_bindParamType === Database::PARAM_BIND_POSITIONAL ? substr_count($this->_query, '?') : count($namedParam[1]);

        // Now prepare : create PdoStatement
        $this->_statement = $this->_connection->prepare($this->_query, $options);

        if (!$this->_statement)
            throw new \Exception('Error : "' . $this->_connection->errorInfo()[2] . '" when prepare your query : "' . $this->_query . '"');

        return $this;
    }

    public function bind($value, $type = Database::PARAM_STR, $key = false, $isParam = false) {
        if (!is_bool($isParam))
            throw new \Exception('Is param must be a boolean');

        if (!is_string($type) && !is_int($type))
            throw new \Exception('Type must be an integer or a string');
        if (!array_key_exists($type, $this->_paramType))
            throw new \Exception('Type "' . $type . '" don\'t exist');

        // If key setted, check if it's variable normalization format
        if ($key !== false && !Validate::isVariableName($key))
            throw new \Exception('Key for param must bet start with letter and can have caracters : a-zA-Z0-9_-');

        // Search if is not mixed key format
        if ($key !== false && $this->_bindParamType === Database::PARAM_BIND_POSITIONAL)
            throw new \Exception('You cannot mixed positionnal and named parameter');
        if ($key === false && count($this->_params) > 0 && $this->_bindParamType === Database::PARAM_BIND_NAMED)
            throw new \Exception('You cannot mixed positionnal and named parameter');

        // Add datas on params array
        if ($key) {
            $this->_params[$key] = array(
                'value' => $value,
                'type' => $this->_paramType[$type],
                'key' => $key,
                'isParam' => $isParam
            );
        } else {
            $this->_params[] = array(
                'value' => $value,
                'type' => $this->_paramType[$type],
                'key' => $key,
                'isParam' => $isParam
            );
        }
        return $this;
    }

    public function execute($checkBindNumber = true) {
        if (Application::getDebug())
            Benchmark::getInstance($this->_configName)->startTime()->startRam();

        if (is_null($this->_query) || !$this->_statement)
            throw new \Exception('Prepare query before execute');

        if ($checkBindNumber) {
            if (count($this->_params) < $this->_paramsNumberNecesary)
                throw new \Exception('Miss bind parameters');
        }

        // Bind parameters
        $i = 0;
        foreach ($this->_params as $param) {
            $bindName = $this->_bindParamType === Database::PARAM_BIND_POSITIONAL ? $i + 1 : ':' . $this->_namedParamOrder[$i];
            if ($param['isParam'])
                $this->_statement->bindParam($bindName, $param['value'], $param['type']);
            else
                $this->_statement->bindValue($bindName, $param['value'], $param['type']);

            $i++;
        }
        // Execute
        $this->_execute = $this->_statement->execute();

        //check error
        $lastError = $this->getLastError(true);
        if (!is_null($lastError))
            Logger::getInstance()->error('Sql query : ' . $this->_query . ' has error : ' . $lastError);

        // Debug
        if (Application::getDebug())
            Database::getDatabase($this->_configName)->logQuery($this->_query, $this->_params, $this->_bindParamType, $lastError);

        return $this->_execute;
    }

    public function fetch($fetchStyle = Database::FETCH_BOTH, $cursorOrientation = Database::FETCH_ORI_NEXT, $offset = 0) {
        if (!$this->_execute)
            throw new \Exception('You must execute query before fetch result');

        if (!array_key_exists($fetchStyle, $this->_fetchStyle))
            throw new \Exception('Fetch style "' . $fetchStyle . '" don\'t exist');

        if (!array_key_exists($cursorOrientation, $this->_fetchOrientation))
            throw new \Exception('Cursor orientation "' . $cursorOrientation . '" don\'t exist');

        return $this->_statement->fetch($this->_fetchStyle[$fetchStyle], $this->_fetchOrientation[$cursorOrientation], $offset);
    }

    public function fetchAll($fetchStyle = Database::FETCH_BOTH, $fetchArgument = false, $ctorArgs = false) {
        if (!$this->_execute)
            throw new \Exception('You must execute query before fetch result');

        if (!array_key_exists($fetchStyle, $this->_fetchStyle))
            throw new \Exception('Fetch style "' . $fetchStyle . '" don\'t exist');

        if ($this->_fetchStyle[$fetchStyle] == \PDO::FETCH_CLASS) {
            if ($ctorArgs)
                return $this->_statement->fetchAll($this->_fetchStyle[$fetchStyle], $fetchArgument, $ctorArgs);

            return $this->_statement->fetchAll($this->_fetchStyle[$fetchStyle], $fetchArgument);
        } else
            return $this->_statement->fetchAll($this->_fetchStyle[$fetchStyle]);
    }

    public function lastInsertId() {
        if (!is_null($this->_connection))
            return $this->_connection->lastInsertId();

        return null;
    }

    public function rowCount() {
        if (!$this->_execute)
            throw new \Exception('You must execute query before see row count result');

        return $this->_statement->rowCount();
    }

    public function columnCount() {
        if (!$this->_statement)
            throw new \Exception('You must prepare query before see column count result');

        return $this->_statement->columnCount();
    }

    public function isReadQuery($query) {
        return stripos($query, 'select') !== false || stripos($query, 'show') !== false || stripos($query, 'describe') !== false;
    }

    public function getLastError($onlyMsg = false) {
        if ($this->_statement) {
            $errorInfos = $this->_statement->errorInfo();
            // no error
            if ($errorInfos[0] === '00000')
                return null;
            if ($onlyMsg)
                return $errorInfos[2];

            $error = new \stdClass();
            $error->sqlstate = $errorInfos[0];
            $error->code = $errorInfos[1];
            $error->msg = $errorInfos[2];
            return $error;
        }

        return null;
    }

}

?>