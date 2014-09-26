<?php

namespace framework\security\cryptography\hash;

class Algorithm {

    const ALGORITHM_MD2 = 'md2';
    const ALGORITHM_MD4 = 'md4';
    const ALGORITHM_MD5 = 'md5';
    const ALGORITHM_SHA1 = 'sha1';
    const ALGORITHM_SHA224 = 'sha224';
    const ALGORITHM_SHA256 = 'sha256';
    const ALGORITHM_SHA384 = 'sha384';
    const ALGORITHM_SHA512 = 'sha512';
    const ALGORITHM_RIPEMD128 = 'ripemd128';
    const ALGORITHM_RIPEMD160 = 'ripemd160';
    const ALGORITHM_RIPEMD256 = 'ripemd256';
    const ALGORITHM_RIPEMD320 = 'ripemd320';
    const ALGORITHM_WHIRLPOOL = 'whirlpool';
    const ALGORITHM_TIGER128_3 = 'tiger128,3';
    const ALGORITHM_TIGER160_3 = 'tiger160,3';
    const ALGORITHM_TIGER192_3 = 'tiger192,3';
    const ALGORITHM_TIGER128_4 = 'tiger128,4';
    const ALGORITHM_TIGER160_4 = 'tiger160,4';
    const ALGORITHM_TIGER192_4 = 'tiger192,4';
    const ALGORITHM_SNEFRU = 'snefru';
    const ALGORITHM_ = 'snefru256';
    const ALGORITHM_GOST = 'gost';
    const ALGORITHM_ADLER32 = 'adler32';
    const ALGORITHM_CRC32 = 'crc32';
    const ALGORITHM_CRC32B = 'crc32b';
    const ALGORITHM_FNV132 = 'fnv132';
    const ALGORITHM_FNV164 = 'fnv164';
    const ALGORITHM_JOAAT = 'joaat';
    const ALGORITHM_HAVAL128_3 = 'haval128,3';
    const ALGORITHM_HAVAL160_3 = 'haval160,3';
    const ALGORITHM_HAVAL192_3 = 'haval192,3';
    const ALGORITHM_HAVAL224_3 = 'haval224,3';
    const ALGORITHM_HAVAL256_3 = 'haval256,3';
    const ALGORITHM_HAVAL128_4 = 'haval128,4';
    const ALGORITHM_HAVAL1160_4 = 'haval160,4';
    const ALGORITHM_HAVAL192_4 = 'haval192,4';
    const ALGORITHM_HAVAL224_4 = 'haval224,4';
    const ALGORITHM_HAVAL256_4 = 'haval256,4';
    const ALGORITHM_HAVAL128_5 = 'haval128,5';
    const ALGORITHM_HAVAL160_5 = 'haval160,5';
    const ALGORITHM_HAVAL192_5 = 'haval192,5';
    const ALGORITHM_HAVAL224_5 = 'haval224,5';
    const ALGORITHM_HAVAL256_5 = 'haval256,5';

    public static function isValidAlgorithm($algo) {
        return (in_array($algo, hash_algos()));
    }

}

?>
