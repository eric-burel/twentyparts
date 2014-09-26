<?php

namespace framework\cache;

use framework\Cache as CacheManager;

trait Cache {

    protected static $_cache = false;

    public static function setCache($cacheName) {
        $cache = CacheManager::getCache($cacheName);
        if (!$cache)
            throw new \Exception('Invalid cache');
        self::$_cache = $cache;
    }

    public static function getCache() {
        return self::$_cache;
    }

}

?>
