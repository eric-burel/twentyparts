<?php

namespace controllers;

use controllers\Index;
use framework\utility\Validate;
use framework\security\Form;
use framework\Security;

class Pages extends Index {

    public function __construct() {
        parent::__construct();
    }

    public function view($slug) {
        $page = $this->_read('page', $slug);
        //check if exists
        if (is_null($page))
            $this->router->show404(true);
        
        //define vars and template
        $this->tpl->setVar('page', $page, false, true);
        $this->tpl->setVar('title', ucfirst($page->langTitle->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($page->langDescr->{$this->tpl->lang}))
            $this->tpl->setVar('desc', ucfirst($page->langDescr->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($page->langKeywords->{$this->tpl->lang}))
            $this->tpl->setVar('keywords', $page->langKeywords->{$this->tpl->lang}, false, true);
        $this->tpl->setFile('controllers' . DS . 'Pages' . DS . 'page.tpl.php');
        
        //run custom function for required pages (news, home, contact)
        $this->_defaultPage($slug);
    }

    private function _defaultPage($slug) {
        switch ($slug) {
            case 'home':
            case 'contact':
                //init security (crsf and captcha)
                $security = Security::getSecurity(Security::TYPE_FORM);
                $crsf = $security->getProtection('form1', Form::PROTECTION_CSRF);
                $crsf->create();
                $captcha = $security->getProtection('form1', Form::PROTECTION_CAPTCHA);
                $this->tpl->setVar('captchaImageUrl', $captcha->get('image', true), false, true)
                        ->setVar('captchaAudioUrl', $captcha->get('audio', true), false, true)
                        ->setVar('captchaRefreshUrl', $captcha->getRefreshUrl(), false, true);
                $this->tpl->setVar('token', $crsf->get(), false, true);

                //define vars and  overwrite template
                if ($slug == 'contact') {
                    $this->tpl->setFile('controllers' . DS . 'Pages' . DS . 'contact.tpl.php');
                } elseif ($slug == 'register') {
                    if (Member::isConnected())
                        Http::redirect($this->router->getUrl('index'));
                    $this->tpl->setFile('controllers' . DS . 'Pages' . DS . 'register.tpl.php');
                } else {
                    $this->tpl->setVar('news', $this->_readAll('new'), false, true);
                    $this->tpl->setFile('controllers' . DS . 'Pages' . DS . 'index.tpl.php');
                }
                //set in session
                $crsf->set();
                break;
            case 'news':
                $this->tpl->setVar('news', $this->_readAll('new'), false, true);
                $this->tpl->setFile('controllers' . DS . 'Pages' . DS . 'news.tpl.php');
            default:
                break;
        }
    }

}

?>