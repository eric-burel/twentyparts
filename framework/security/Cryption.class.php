<?php

//TODO must be completed

namespace framework\security;

use framework\security\ISecurity;
use framework\security\Security;
use framework\security\cryptography\Hash;

class Cryption extends Security implements ISecurity {

    protected $_key = '';
    protected $_passwordAlgorithmList = array('des', 'desExt', 'md5', 'blowfish', 'sha256', 'sha512');

    public function __construct($options = array()) {
        throw new \Exception('Not yet');

        if (!isset($options['key']))
            throw new \Exception('Security cryption need a key');
        $this->_key = Hash::hashString($options['key'], Hash::ALGORITHM_SHA1, false, 10);
    }

    public function run() {
        
    }

    public function stop() {
        
    }

    public function isValidPasswordAlgorithm($algorithm) {
        return (in_array((string) $algorithm, $this->_passwordAlgorithmList));
    }

    public function cryptPassword($password, $algorithm, $depth) {
        
    }

    public function checkPassword($cryptedPassword, $passwordCheck, $algorithm, $string, $depth) {
        
    }

    public function getPasswordInfo($cryptedPassword) {
        // algo, depth
    }

}

?>
