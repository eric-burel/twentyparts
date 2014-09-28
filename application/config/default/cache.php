<?php

$config = array(
    // cache name => array(options)
    'core' => array(
        'adaptater' => 'file', // class name (must be implement \framework\cache\IAdaptater)
        'prefix' => '_', // prefix string
        'path' => '[PATH_CACHE_CORE]',
        'gc' => 'time', // Garbage collection : time/number => toutes les x secondes, ou toutes les x requests
        'gcOption' => 86400, // seconds/request
        'groups' => 'autoloader,logger' // group list separated by ","
    ),
    'default' => array(
        'adaptater' => 'file', // class name (must be implement \framework\cache\IAdaptater)
        'prefix' => '_',
        'path' => '[PATH_CACHE_DEFAULT]',
        'gc' => 'time',
        'gcOption' => 86400,
        'groups' => 'template' // group list separated by ","
    ),
    'bdd' => array(
        'adaptater' => 'file', //file/apc
        'prefix' => '_', // prefix string
        'path' => '[PATH_CACHE_BDD]',
        'gc' => 'time', // Garbage collection : time/number => toutes les x secondes, ou toutes les x requests
        'gcOption' => 86400, // seconds/request
    ),
);
?>
