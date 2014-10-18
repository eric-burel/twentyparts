<?php

namespace controllers;

use framework\mvc\Controller;
use framework\error\ErrorManager;
use framework\error\ExceptionManager;

class Error extends Controller {

    public function __construct() {
        $this->tpl->setFile('controllers' . DS . 'Error' . DS . 'index.tpl.php');
    }

    public function show($code) {
        $code = (int) $code;
        switch ($code) {
            case 400:
                //ErrorDocument 400 /error/400.html Bad Request La syntaxe de la requête est erronée
                $this->tpl->setVar('errorInfo', array('code' => '400', 'message' => 'Bad Request'), false, true)->setVar('title', 'Bad Request', false, true);
                break;
            case 401:
                //ErrorDocument 401 /error/401.html Unauthorized Une authentification est nécessaire pour accéder à la ressource
                $this->tpl->setVar('errorInfo', array('code' => '401', 'message' => 'Unauthorized'), false, true)->setVar('title', 'Unauthorized', false, true);
                break;
            case 403:
                //ErrorDocument 403 /error/403.html Forbidden L’authentification est refusée. Contrairement à l’erreur 401, aucune demande d’authentification ne sera faite
                $this->tpl->setVar('errorInfo', array('code' => '403', 'message' => 'Forbidden'), false, true)->setVar('title', 'Forbidden');
                break;
            case 405:
                //ErrorDocument 405 /error/405.html Method Not Allowed Méthode de requête non autorisée
                $this->tpl->setVar('errorInfo', array('code' => '405', 'message' => 'Method Not'), false, true)->setVar('title', 'Method Not', false, true);
                break;
            case 500:
                //ErrorDocument 500 /error/500.html Internal Server Error Erreur interne du serveur
                $this->tpl->setVar('errorInfo', array('code' => '500', 'message' => 'Internal Server Error'), false, true)->setVar('title', 'Internal Server Error', false, true);
                break;
            case 502:
                //ErrorDocument 502 /error/502.html Bad Gateway Mauvaise réponse envoyée à un serveur intermédiaire par un autre serveur.
                $this->tpl->setVar('errorInfo', array('code' => '502', 'message' => 'Bad Gateway'), false, true)->setVar('title', 'Bad Gateway', false, true);
                break;
            case 503:
                //ErrorDocument 503 /error/503.html Service Unavailable Service temporairement indisponible ou en maintenance
                $this->tpl->setVar('errorInfo', array('code' => '503', 'message' => 'Service Unavailable Service'), false, true)->setVar('title', 'Service Unavailable Service', false, true);
                break;
            case 404:
            default:
                //ErrorDocument 404 /error/404.html Not Found  Ressource non trouvée
                $this->tpl->setVar('errorInfo', array('code' => '404', 'message' => 'Not Found'), false, true)->setVar('title', 'Not Found', false, true);
                break;
        }
    }

    public function debugger($isException) {
        if ($isException) {
            $ex = ExceptionManager::getInstance()->getException();
            $this->tpl->setVar('exception', $ex, false, true);
            $this->tpl->setFile('controllers' . DS . 'Error' . DS . 'exception.tpl.php');
        } else {
            $err = ErrorManager::getInstance()->getError();
            $this->tpl->setVar('error', $err, false, true);
            $this->tpl->setFile('controllers' . DS . 'Error' . DS . 'error.tpl.php');
        }
    }

}

?>