<?php

namespace framework\mvc;

interface IModelManager {

    public function __construct();

    public function setModelDBName($dbName);

    public function setModelDBTable($dbTable);

    public function getModelDBName();

    public function getModelDBTable();

    public function getDB();

    public function execute($query, $parameters = array(), $returnLastInsertId = false, $closeStatement = false, $checkBindNumber = true);
}

?>