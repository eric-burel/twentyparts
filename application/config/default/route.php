<?php

$config = array(
    // route name => array(options)
    'index' => array(
        'rules' => array(
            'fr_FR',
            'en_EN'
        ),
        'controller' => 'pages',
        'methods' => array(
            'view' => array('home'),
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
    //pages
    'page' => array(
        'regex' => true,
        'rules' => array(
            'fr_FR/pages/([a-zA-Z0-9_-]+)',
            'en_EN/pages/([a-zA-Z0-9_-]+)',
        ),
        'controller' => 'pages',
        'methods' => array(
            'view' => array('[[1]]')
        )
    ),
    //news
    'new' => array(
        'regex' => true,
        'rules' => array(
            'fr_FR/news/([a-zA-Z0-9_-]+)',
            'en_EN/news/([a-zA-Z0-9_-]+)',
        ),
        'controller' => 'news',
        'methods' => array(
            'view' => array('[[1]]')
        )
    ),
    // ajax routes
    'contact' => array(
        'rules' => array(
            'contact'
        ),
        'requireAjax' => true,
        'requireHttpMethod' => 'POST', //(GET, HEAD, POST, PUT', DELETE, TRACE, OPTIONS, CONNECT, PATCH, optional default is null (all))
        'controller' => 'index',
        'methods' => array(
            'contact'
        )
    ),
);
?>
