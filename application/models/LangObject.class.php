<?php

namespace models;

use framework\mvc\Model;
use framework\mvc\IModelObject;

class LangObject extends Model implements IModelObject {

    protected $_id = null;
    protected $_fr_FR = null;
    protected $_en_EN = null;

    public function __construct() {
    }

}

?>
