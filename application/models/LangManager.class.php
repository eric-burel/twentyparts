<?php

namespace models;

use framework\mvc\Model;
use framework\mvc\IModelManager;
use models\LangObject;
use framework\Database;

class LangManager extends Model implements IModelManager {

    public function __construct() {
        
    }

    public function create(LangObject $lang, $returnLastId = true) {
        $sql = 'INSERT INTO ' . $this->getModelDBTable() . ' VALUES("", ?, ?)';
        $lastId = $this->execute($sql, array(
            $lang->fr_FR => Database::PARAM_STR,
            $lang->en_EN => Database::PARAM_STR), $returnLastId
        );

        if ($returnLastId)
            return $lastId;
    }

    public function read($id) {
        $sql = 'SELECT * FROM ' . $this->getModelDBTable() . ' WHERE id = ?';
        $this->execute($sql, array(
            $id => Database::PARAM_INT)
        );
        $data = $this->_engine->fetch(Database::FETCH_ASSOC);
        if (empty($data))//void object
            return self::factoryObject('lang');

        return self::factoryObject('lang', $data);
    }

    public function update(LangObject $lang) {
        $sql = 'UPDATE ' . $this->getModelDBTable() . ' SET fr_FR = ?,en_EN = ? WHERE id = ?';
        $this->execute($sql, array(
            $lang->fr_FR => Database::PARAM_STR,
            $lang->en_EN => Database::PARAM_STR,
            $lang->id => Database::PARAM_INT)
        );
    }

    public function delete($id) {
        $sql = 'DELETE FROM ' . $this->getModelDBTable() . ' WHERE id = ?';
        $this->execute($sql, array(
            $id => Database::PARAM_INT), false, true
        );
    }

    public function readAll() {
        $sql = 'SELECT * FROM ' . $this->getModelDBTable();
        $this->execute($sql);
        $datas = $this->_engine->fetchAll(Database::FETCH_ASSOC);

        $all = array();
        foreach ($datas as $data)
            $all[] = self::factoryObject('lang', $data);

        return $all;
    }

}

?>