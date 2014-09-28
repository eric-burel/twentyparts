<?php

namespace models;

use framework\mvc\Model;
use framework\mvc\IModelManager;
use models\PageObject;
use framework\Database;

class PageManager extends Model implements IModelManager {

    protected $_lang;

    public function __construct() {
        $this->_lang = self::factoryManager('lang', 'default', 'lang');
    }

    public function create(PageObject $page, $returnLastId = true) {
        //create langs
        $descrId = $this->_lang->create($page->langDescr, true);
        $keywordsId = $this->_lang->create($page->langKeywords, true);
        $titleId = $this->_lang->create($page->langTitle, true);
        $contentId = $this->_lang->create($page->langContent, true);

        // create page
        $this->_engine->set('INSERT INTO ' . $this->getModelDBTable() . ' VALUES("",?,?,?,?,?,?)');
        $this->_engine->bind($titleId, Database::PARAM_INT);
        $this->_engine->bind($descrId, Database::PARAM_INT);
        $this->_engine->bind($keywordsId, Database::PARAM_INT);
        $this->_engine->bind($contentId, Database::PARAM_INT);
        $this->_engine->bind($page->isRequired, Database::PARAM_BOOL);
        $this->_engine->bind($page->generateSlug(), Database::PARAM_STR);
        $this->_engine->execute();
        if ($returnLastId)
            return $this->_engine->lastInsertId();
    }

    public function read($id, $isSlug = false) {
        $where = ' WHERE ' . ($isSlug ? 'slug' : 'id') . ' = ?';
        $sql = 'SELECT * FROM ' . $this->getModelDBTable() . $where;
        $this->execute($sql, array($id => $isSlug ? Database::PARAM_STR : Database::PARAM_INT));
        $datas = $this->_engine->fetch(Database::FETCH_ASSOC);
        if (empty($datas))
            return null;

        //get langs
        $datas['langDescr'] = $this->_lang->read($datas['descrId']);
        $datas['langKeywords'] = $this->_lang->read($datas['keywordsId']);
        $datas['langTitle'] = $this->_lang->read($datas['titleId']);
        $datas['langContent'] = $this->_lang->read($datas['contentId']);

        //return page object
        return self::factoryObject('page', $datas);
    }

    public function readAll() {
        $this->execute('SELECT * FROM ' . $this->getModelDBTable());
        $datas = $this->_engine->fetchAll(Database::FETCH_ASSOC);
        $all = array();
        foreach ($datas as $data)
            $all[] = $this->read($data['id']);

        return $all;
    }

    public function update(PageObject $page) {
        //update langs
        $this->_lang->update($page->langDescr);
        $this->_lang->update($page->langKeywords);
        $this->_lang->update($page->langTitle);
        $this->_lang->update($page->langContent);


        //update page
        $this->_engine->set('UPDATE ' . $this->getModelDBTable() . ' SET titleId = ?, descrId = ?, keywordsId = ?, contentId = ?, isRequired = ?, slug = ? WHERE id = ?');
        $this->_engine->bind($page->titleId, Database::PARAM_INT);
        $this->_engine->bind($page->descrId, Database::PARAM_INT);
        $this->_engine->bind($page->keywordsId, Database::PARAM_INT);
        $this->_engine->bind($page->contentId, Database::PARAM_INT);
        $this->_engine->bind($page->isRequired, Database::PARAM_BOOL);
        $this->_engine->bind($page->generateSlug($page->slug), Database::PARAM_STR);
        $this->_engine->bind($page->id, Database::PARAM_INT);
        $this->_engine->execute();
    }

    public function delete($id) {
        $this->execute('DELETE FROM ' . $this->getModelDBTable() . ' WHERE id = ?', array(
            $id => Database::PARAM_INT), false, true
        );
        return true;
    }

    public function existsSlug($slug, $lastSlug = null) {
        $sql = 'SELECT * FROM ' . $this->getModelDBTable() . ' WHERE slug = ?';
        if (!is_null($lastSlug))
            $sql .= ' AND slug != "' . $lastSlug . '"';

        $this->execute($sql, array($slug => Database::PARAM_STR), false, false);
        return $this->_engine->rowCount();
    }

}

?>