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
        $db = $this->getDb(true);
        $db->set('INSERT INTO ' . $this->getModelDBTable() . ' VALUES("", ?, ?)');
        $db->bind($lang->fr_FR, Database::PARAM_STR);
        $db->bind($lang->en_EN, Database::PARAM_STR);
        $db->execute();
        if ($returnLastId)
            return $db->lastInsertId();
    }

    public function read($id) {
        $engine = $this->getDb(true);
        $sql = 'SELECT * FROM ' . $this->getModelDBTable() . ' WHERE id = ?';
        $this->execute($sql, array($id => Database::PARAM_INT));
        $data = $engine->fetch(Database::FETCH_ASSOC);
        if (empty($data))//void object
            return self::factoryObject('lang');

        return self::factoryObject('lang', $data);
    }

    public function update(LangObject $lang) {
        $db = $this->getDb(true);
        $db->set('UPDATE ' . $this->getModelDBTable() . ' SET fr_FR = ?,en_EN = ? WHERE id = ?');
        $db->bind($lang->fr_FR, Database::PARAM_STR);
        $db->bind($lang->en_EN, Database::PARAM_STR);
        $db->bind($lang->id, Database::PARAM_INT);
        $db->execute();
    }

    public function delete($id) {
        $sql = 'DELETE FROM ' . $this->getModelDBTable() . ' WHERE id = "' . $id . '"';
        $this->execute($sql, array(), false, true);

        return true;
    }

    public function readAll() {
        $all = array();
        $engine = $this->getDb(true);
        $sql = 'SELECT * FROM ' . $this->getModelDBTable();
        $this->execute($sql);
        $datas = $engine->fetchAll(Database::FETCH_ASSOC);

        foreach ($datas as $data)
            $all[] = self::factoryObject('lang', $data);

        return $all;
    }

}

?>