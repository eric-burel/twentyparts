<?php

namespace framework\mvc;

use framework\Database;
use framework\database\IAdaptater;

abstract class Model {

    protected $_modelDBName = '';
    protected $_modelDBTable = '';
    protected $_engine;

    // Find type
    const FIND_LIKE = 'LIKE';
    const FIND_EQUAL = '=';
    const FIND_LT = '<';
    const FIND_LTE = '<=';
    const FIND_GT = '>';
    const FIND_GTE = '>=';
    //orderby
    const ORDER_BY_DESC = 'DESC';
    const ORDER_BY_ASC = 'ASC';

    public static function factoryManager($name, $dbName, $dbTable) {
        // Factory model
        if (!is_string($name))
            throw new \Exception('Model name must be a string');

        if (\class_exists('models\\' . ucfirst($name) . 'Manager'))
            $modelClass = 'models\\' . ucfirst($name) . 'Manager';
        else
            $modelClass = $name;

        $inst = new \ReflectionClass($modelClass);
        if (!in_array('framework\\mvc\\IModelManager', $inst->getInterfaceNames()))
            throw new \Exception('Model class must be implement framework\mvc\IModelManager');

        $manager = $inst->newInstance();
        $manager->setModelDBName($dbName);
        $manager->setModelDBTable($dbTable);
        $manager->setEngine($manager->getDb(true));
        return $manager;
    }

    public static function factoryObject($name, $datas = array()) {
        // Factory model
        if (!is_string($name))
            throw new \Exception('Model name must be a string');

        if (\class_exists('models\\' . ucfirst($name) . 'Object'))
            $modelClass = 'models\\' . ucfirst($name) . 'Object';
        else
            $modelClass = $name;

        $inst = new \ReflectionClass($modelClass);
        if (!in_array('framework\\mvc\\IModelObject', $inst->getInterfaceNames()))
            throw new \Exception('Model class must be implement framework\mvc\IModelObject');

        $manager = $inst->newInstance();
        $manager->hydrate($datas);
        return $manager;
    }

    public static function isValidFindType($findType) {
        return ($findType == self::FIND_LIKE || $findType == self::FIND_EQUAL || $findType == self::FIND_LT || $findType == self::FIND_LTE || $findType == self::FIND_GT || $findType == self::FIND_GTE);
    }

    public static function existsColumn($name) {
        if (!is_string($name))
            throw new \Exception('Column name must be a string');

        $class = get_called_class();
        if (!array_key_exists($name, $class::$_columnsName))
            return false;


        return true;
    }

    public static function getColumnType($columnName) {
        if (!self::existsColumn($columnName))
            throw new \Exception('Invalid column name : "' . $columnName . '"');

        $class = get_called_class();
        return $class::$_columnsType[$columnName];
    }

    public static function getColumnName($columnName) {
        if (!self::existsColumn($columnName))
            throw new \Exception('Invalid column name : "' . $columnName . '"');

        $class = get_called_class();
        return $class::$_columnsName[$columnName];
    }

    public static function getColumnsName() {
        $class = get_called_class();
        return $class::$_columnsName;
    }

    public static function isValidParameter($name, $type, $val = null) {
        $class = get_called_class();

        if (!self::existsColumn($name))
            return false;
        elseif ($type != $class::$_columnsType[$name])
            return false;
        else {
            if (!is_null($val) && self::getValueParamType($val) != $class::$_columnsType[$name])
                return false;

            return true;
        }
    }

    public static function isValidType($columnName, $type) {
        if (!self::existsColumn($columnName))
            return false;

        if (!is_null($type) && self::getValueParamType($type) !== self::getColumnType($columnName))
            return false;

        return true;
    }

    public static function getValueParamType($val) {
        $typeVal = gettype($val);
        switch ($typeVal) {
            case 'boolean':
                return self::PARAM_BOOL;
            case 'integer':
            case 'double':
                return self::PARAM_INT;
            case 'string':
                return self::PARAM_STR;
            case 'NULL':
                return self::PARAM_NULL;
            default:
                return false;
        }
    }

    public function hydrate($datas = array()) {
        foreach ($datas as $key => $value)
            $this->$key = $value;
    }

    public function __get($name) {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method))
            return $this->$method();
        elseif (property_exists($this, $name))
            return $this->$name;
        else {
            $name = '_' . $name;
            if (property_exists($this, $name))
                return $this->$name;

            return null;
        }
    }

    public function __set($name, $value) {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method))
            $this->$method($value);
        elseif (property_exists($this, $name))
            $this->$name = $value;
        else {
            $name = '_' . $name;
            if (property_exists($this, $name))
                $this->$name = $value;
        }

        return $this;
    }

    public function getDb($returnEngine = false) {
        return Database::getDatabase($this->_modelDBName, $returnEngine);
    }

    public function setEngine(IAdaptater $engine) {
        $this->_engine = $engine;
    }

    public function setModelDBName($dbName) {
        $this->_modelDBName = $dbName;
    }

    public function setModelDBTable($dbName) {
        $this->_modelDBTable = $dbName;
    }

    public function getModelDBName() {
        return $this->_modelDBName;
    }

    public function getModelDBTable() {
        return $this->_modelDBTable;
    }

    public function getEngine() {
        return $this->_engine;
    }

    public function execute($query, $parameters = array(), $returnLastInsertId = false, $closeStatement = false, $checkBindNumber = true) {
        $this->_engine->prepare($query);
        foreach ($parameters as $paramValue => $paramType)
            $this->_engine->bind($paramValue, $paramType);

        $this->_engine->execute($closeStatement, $checkBindNumber);


        if ($returnLastInsertId)
            return $this->_engine->lastInsertId();
    }

}

?>