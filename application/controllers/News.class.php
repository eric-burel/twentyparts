<?php

namespace controllers;

use controllers\Index;
use framework\utility\Validate;

class News extends Index {

    public function __construct() {
        parent::__construct();
    }

    public function view($slug) {
        $new = $this->_read('new', $slug);
        if (is_null($new))
            $this->router->show404(true);

        $this->tpl->setVar('new', $new, false, true);
        $this->tpl->setVar('title', ucfirst($new->langTitle->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($new->langDescr->{$this->tpl->lang}))
            $this->tpl->setVar('desc', ucfirst($new->langDescr->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($new->langKeywords->{$this->tpl->lang}))
            $this->tpl->setVar('keywords', $new->langKeywords->{$this->tpl->lang}, false, true);

        $this->tpl->setFile('controllers' . DS . 'News' . DS . 'new.tpl.php');
    }

}

?>