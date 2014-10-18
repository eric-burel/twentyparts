<?php

//TODO must be completed

namespace framework\security\cryptography;

use framework\security\cryptography\hash\Algorithm;

class Hash extends Algorithm {

    public static function hashString($string, $algorithm = self::ALGORITHM_SHA1, $rawOutput = false, $depth = 0) {
        if (!is_string($string))
            throw new \Exception('String must be a string');
        if (!self::isValidAlgorithm($algorithm))
            throw new \Exception('Hash algorithm parameter must be a valid algo');
        if (!is_bool($rawOutput))
            throw new \Exception('rawOutput parameter must be a boolean');
        if (!is_int($depth))
            throw new \Exception('hash depth (iteration) parameter must an integer');

        hash_init($algorithm);
        for ($i = 0; $i <= $depth; $i++) {
            $hash = hash($algorithm, $string, $rawOutput);
        }
        return $hash;
    }

    public static function hashFile() {
        // TODO ...
    }

}

?>
