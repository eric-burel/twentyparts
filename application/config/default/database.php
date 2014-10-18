<?php

$config = array(
    // database name => array(options)
    'default' => array(
        'adaptater' => 'pdo', //class name (implement \framework\database\IAdaptater)
        'type' => 'sql', //database type (sql, cobol etc...)
        'server' => array(
            array(
                'type' => 'master', //master / slave
                'dsn' => 'mysql:dbname=test;host=127.0.0.1;port=3306;charset=utf8', // driver: dbname, host, port, charset
                //id's
                'dbuser' => 'user',
                'dbpassword' => 'password'
            ),
            array(
                'type' => 'slave',
                //No dsn
                'driver' => 'mysql',
                'dbname' => 'test',
                'host' => '127.0.0.1',
                'port' => 3306,
                'charset' => 'utf8',
                //id's
                'dbuser' => 'user',
                'dbpassword' => 'password'
            )
        )
    )
);
?>
