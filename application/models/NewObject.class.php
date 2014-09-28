<?php

namespace models;

use framework\mvc\Model;
use framework\mvc\IModelObject;
use framework\utility\Tools;

class NewObject extends Model implements IModelObject {

    protected $_id = null;
    protected $_titleId = null;
    protected $_descrId = null;
    protected $_keywordsId = null;
    protected $_contentId = null;
    protected $_date = false;
    protected $_slug = null;
    //langs datas
    protected $_langTitle = null;
    protected $_langDescr = null;
    protected $_langKeywords = null;
    protected $_langContent = null;

    public function __construct() {
        
    }

    public function generateSlug($lastSlug = null) {
        $manager = self::factoryManager('new', 'default', 'new');
        $exist = true;
        $salt = '';
        $i = 0;
        while ($exist && $i < 50) {
            $this->_slug = Tools::stringToUrl($this->_titre, '-', 'UTF-8', true) . $salt;
            $count = $manager->existsSlug($this->_slug, $lastSlug);
            $exist = $count >= 1 ? true : false;
            $salt = (string) $i;
            $i++;
        }

        return $this->_slug;
    }

}

?>
