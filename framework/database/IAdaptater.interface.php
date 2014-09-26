<?php

namespace framework\database;

use framework\Database;

interface IAdaptater {

    public function __construct($configName);

    public function isValidDriver($driver);

    public function connection($serverType);

    public function disconnect();

    public function resetQuery();

    public function quote($query, $paramType = Database::PARAM_STR);

    public function exec($query, $safe = false);

    public function prepare($query);

    public function bind($value, $type = Database::PARAM_STR, $key = false, $bindType = Database::BIND_TYPE_PARAM);

    public function execute($closeStatement = false);

    public function fetch($fetchStyle = Database::FETCH_BOTH, $cursorOrientation = Database::FETCH_ORI_NEXT, $offset = 0);

    public function fetchAll($fetchStyle = Database::FETCH_BOTH, $fetchArgument = false, $ctorArgs = false);

    public function lastInsertId();

    public function rowCount();

    public function columnCount();

    public function isReadQuery($query);

    public function getLastError($onlyMsg = false);
}

?>
