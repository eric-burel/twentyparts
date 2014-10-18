<?php

//http://php.net/manual/fr/language.variables.superglobals.php
//TODO must be completed

namespace framework\utility;

class Superglobals {

    const GLOBALS = 0; // all superglobals
    const SERVER = 1;
    const GET = 2;
    const POST = 3;
    const FILES = 4;
    const COOKIE = 5;
    const SESSION = 6;
    const REQUEST = 7;
    const ENV = 8;

}

?>