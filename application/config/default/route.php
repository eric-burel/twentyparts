<?php

$config = array(
    // route name => array(options)
    'index' => array(
        'rules' => array(
            'fr_FR',
            'en_EN'
        ),
        'controller' => 'index',
        'methods' => array(
            'page' => array('home'),
        )
    ),
    'captcha' => array(
        'regex' => true,
        'rules' => array(
            'captcha/([0-9a-zA-Z]+)/([a-z]+)',
            'captcha/([0-9a-zA-Z]+)/([a-z]+)/([0-9]+)'
        ),
        'controller' => 'index',
        'methods' => array(
            'captcha' => array('[[1]]', '[[2]]', '[[3]]')
        )
    ),
    'language' => array(
        'regex' => true,
        'rules' => array(
            'language/([A-Za-z0-9_]+)'
        ),
        'controller' => 'index',
        'methods' => array(
            'setAjax' => true,
            'language' => array('[[1]]')
        )
    ),
    'error' => array(
        'regex' => true,
        'rules' => array(
            'error/([0-9]+)'
        ),
        'controller' => 'error',
        'methods' => array(
            'show' => array('[[1]]')
        )
    ),
    'debugger' => array(
        'regex' => true,
        'rules' => array(
            'error/debugger/([a-z]+)'
        ),
        'controller' => 'error',
        'methods' => array(
            'debugger' => array('[[1]]')
        )
    ),
    'page' => array(
        'regex' => true,
        'rules' => array(
            'fr_FR/([a-zA-Z0-9_-]+)',
            'en_EN/([a-zA-Z0-9_-]+)',
        ),
        'controller' => 'index',
        'methods' => array(
            'page' => array('[[1]]')
        )
    ),
    'new' => array(
        'regex' => true,
        'rules' => array(
            'fr_FR/news/([a-zA-Z0-9_-]+)',
            'en_EN/news/([a-zA-Z0-9_-]+)',
        ),
        'controller' => 'index',
        'methods' => array(
            'newView' => array('[[1]]')
        )
    ),
    'contact' => array(
        'rules' => array(
            'contact'
        ),
        'controller' => 'index',
        'methods' => array(
            'setAjax' => true,
            'contact'
        )
    ),
);
?>
