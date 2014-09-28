<?php

namespace controllers;

use framework\Application;
use framework\Cache;
use framework\Security;
use framework\mvc\Controller;
use framework\mvc\Model;
use framework\security\Form;
use framework\network\Http;
use framework\utility\Cookie;
use framework\utility\Validate;
use framework\mail\SwiftMailer;
use framework\mail\MailContents;

class Index extends Controller {

    protected $_cache = null;

    public function __construct() {
        //check lang is in url and auto set
        if ($this->router->getCurrentRoute() == 'index' && stripos(Http::getCurrentUrl(), $this->language->getLanguage()) === false)
            Http::redirect($this->tpl->urls->index);

        $this->tpl->setVar('langAvaible', ($this->language->getLanguage() == 'fr_FR') ? 'en_EN' : 'fr_FR');
        //cache
        $this->_cache = Cache::getCache('bdd');


        $this->tpl->setVar('isConnected', false);
    }

    public function setAjax($check = false) {
        if (!Http::isAjaxRequest() && $check)
            Http::redirect($this->router->getUrl('index'));

        if (Http::isAjaxRequest())
            $this->setAjaxController();
    }

    public function language($language) {
        if (!is_string($language))
            $language = (string) $language;

        $this->session->add('language', $language, true, false);
        $this->addAjaxDatas('updated', true);

        //create cookie
        new Cookie('language', $language, true, Cookie::EXPIRE_TIME_INFINITE, str_replace(Http::getServer('SERVER_NAME'), '', $this->router->getHost()));
    }

    public function captcha($formName, $type) {
        $captcha = Security::getSecurity(Security::TYPE_FORM)->getProtection($formName, Form::PROTECTION_CAPTCHA);
        if (!$captcha)
            $this->router->show404(true);

        if ($type == 'refresh') {
            $this->setAjaxController();
            $captcha->flush();
            $this->addAjaxDatas('imageUrl', $captcha->get('image', true));
            $this->addAjaxDatas('audioUrl', $captcha->get('audio', true));
        } else {
            if ($type == 'image') {
                if (!$captcha->getImage())
                    $this->router->show404(true);
                $captcha->get('image');
            } elseif ($type == 'audio') {
                if (!$captcha->getAudio())
                    $this->router->show404(true);
                $captcha->get('audio');
            } else
                $this->router->show404(true);

            $this->setAutoCallDisplay(false);
        }
    }

    public function page($slug) {
        $page = $this->_read('page', $slug);
        if (is_null($page))
            $this->router->show404(true);

        $this->tpl->setVar('page', $page, false, true);
        $this->tpl->setVar('title', ucfirst($page->langTitle->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($page->langDescr->{$this->tpl->lang}))
            $this->tpl->setVar('desc', ucfirst($page->langDescr->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($page->langKeywords->{$this->tpl->lang}))
            $this->tpl->setVar('keywords', $page->langKeywords->{$this->tpl->lang}, false, true);

        $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'page.tpl.php');
        $this->_defaultPage($slug);
    }

    public function newView($slug) {
        $new = $this->_read('new', $slug);
        if (is_null($new))
            $this->router->show404(true);

        $this->tpl->setVar('new', $new, false, true);
        $this->tpl->setVar('title', ucfirst($new->langTitle->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($new->langDescr->{$this->tpl->lang}))
            $this->tpl->setVar('desc', ucfirst($new->langDescr->{$this->tpl->lang}), false, true);
        if (!Validate::isEmpty($new->langKeywords->{$this->tpl->lang}))
            $this->tpl->setVar('keywords', $new->langKeywords->{$this->tpl->lang}, false, true);

        $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'new.tpl.php');
    }

    public function contact() {
        if (Http::isPost() && $this->isAjaxController()) {
            $security = Security::getSecurity(Security::TYPE_FORM);
            $crsf = $security->getProtection('form1', Form::PROTECTION_CSRF);
            $captcha = $security->getProtection('form1', Form::PROTECTION_CAPTCHA);
            //create new and add to ajax data
            $crsf->create();
            $this->addAjaxDatas('token', $crsf->get());
            $error = false;
            if (!$crsf->check(Http::getPost('token')))
                $error = true;

            if (!$captcha->check(Http::getPost('captcha'))) {
                $this->addError($this->language->getVar('validate_security'), 'captcha');
                $error = true;
            }
            if ($error)
                $this->notifyError($this->language->getVar('validate_error'));
            else {
                //send mail
                SwiftMailer::getInstance();
                $mail = \Swift_Message::newInstance();
                $mail->setFrom(array(ADMIN_EMAIL => $this->language->getVar('site_name')));
                $mail->setTo(CONTACT_EMAIL);
                $mail->setSubject($this->language->getVar('site_name') . ' demande de contact');
                $contents = new MailContents($this->tpl->getPath() . 'mails' . DS . 'contact.tpl.php');
                $contents->addVar('message', nl2br(Http::getPost('message')))
                        ->addVar('name', Http::getPost('name'))
                        ->addVar('email', Http::getPost('email'))
                        ->addVar('subject', Http::getPost('subject'));
                $mail->addPart($contents->getMailContents(), 'text/html');
                $transport = defined('SMTP_SERVER') && !is_null(SMTP_SERVER) && SMTP_SERVER != '' ? \Swift_SmtpTransport::newInstance(SMTP_SERVER, 25) : \Swift_MailTransport::newInstance();
                $mailer = \Swift_Mailer::newInstance($transport);
                $mailer->send($mail);

                $this->notifySuccess($this->language->getVar('validate_success'));
            }
            //set in session
            $crsf->set();
        }
    }

    private function _defaultPage($slug) {
        switch ($slug) {
            case 'home':
            case 'contact':
                //init security
                $security = Security::getSecurity(Security::TYPE_FORM);
                $crsf = $security->getProtection('form1', Form::PROTECTION_CSRF);
                $crsf->create();
                $captcha = $security->getProtection('form1', Form::PROTECTION_CAPTCHA);
                $this->tpl->setVar('captchaImageUrl', $captcha->get('image', true), false, true)
                        ->setVar('captchaAudioUrl', $captcha->get('audio', true), false, true)
                        ->setVar('captchaRefreshUrl', $captcha->getRefreshUrl(), false, true);
                $this->tpl->setVar('token', $crsf->get(), false, true);

                //define vars and template
                if ($slug == 'contact') {
                    $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'contact.tpl.php');
                } else {
                    $this->tpl->setVar('news', $this->_readAll('new'), false, true);
                    $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'index.tpl.php');
                }
                //set in session
                $crsf->set();
                break;
            case 'news':
                $this->tpl->setVar('news', $this->_readAll('new'), false, true);
                $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'news.tpl.php');
            default:
                break;
        }
    }

    private function _readAll($type) {
        $cache = $this->_cache->read($type . 'listing');
        if (!is_null($cache) && !Application::getDebug())
            $datas = $cache;
        else {
            $manager = Model::factoryManager($type, 'default', $type);
            $datas = $manager->readAll();
            if (!is_null($datas))
                $this->_cache->write($type . 'listing', $datas, true);
        }

        return $datas;
    }

    private function _read($type, $slug) {
        $cache = $this->_cache->read($type . $slug);
        if (!is_null($cache) && !Application::getDebug())
            $data = $cache;
        else {
            $manager = Model::factoryManager($type, 'default', $type);
            $data = $manager->read($slug, true); //try by slug
            if (is_null($data))
                $data = $manager->read($slug); //try by id

            if (!is_null($data))
                $this->_cache->write($type . $slug, $data, true);
        }

        return $data;
    }

}

?>