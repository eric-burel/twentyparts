<?php

namespace framework\cache;

use framework\Cache;

interface IAdaptater {

    public function __construct($params = array());

    public function getName();

    public function setGc($gcType, $gcOption);

    public function checkGc($gc);

    public function writeGc($gc);

    public function runGc();

    public function write($key, $data, $forceReplace = false, $expire = Cache::EXPIRE_INFINITE, $type = Cache::TYPE_TIME);

    public function read($key, $default = null, $lockTime = false, $onlyExpireTime = false);

    public function exist($key);

    public function delete($key, $forceUnlock = true);

    public function isExpired($key, $autoDelete = true);

    public function getExpire($key); //time/request left

    public function lock($key, $time = Cache::EXPIRE_INFINITE);

    public function unlock($key);

    public function isLocked($key);

    public function increment($key, $offset = 1, $startValue = 1);

    public function decrement($key, $offset = 1);

    public function clear();

    public function purge();

    public function clearGroup($groupName);

    public function clearGroups();
}

?>