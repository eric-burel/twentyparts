<?php

namespace framework\cache\adaptaters;

use framework\Cache;
use framework\Logger;
use framework\cache\IAdaptater;

class Apcu extends Cache implements IAdaptater {

    public function __construct($params = array()) {
        if (!extension_loaded('apcu') && !extension_loaded('apc'))
            throw new \Exception('Apcu extension not loaded try change your PHP configuration');

        parent::init($params);
        Logger::getInstance()->addGroup('cache' . $this->_name, 'Cache ' . $this->_name, true, true);

        //create dynamic key and add to prefix (it's for multi applications)
        if (!file_exists(PATH_CACHE . $this->_name)) {
            $key = rand();
            $file = new \SplFileObject(PATH_CACHE . 'ApcuKey' . $this->_name, 'w+');
            if ($file->flock(LOCK_EX)) {
                $file->fwrite($key);
                $file->flock(LOCK_UN);
            }
            $this->_prefix .= $key;
        } else
            $this->_prefix .= file_get_contents(PATH_CACHE . $this->_name);
    }

    public function setGc($gcType, $gcOption) {
        // no need garbage collector
        $this->_gc = false;
        Logger::getInstance()->notice('garbage collector useless on APCu', 'cache' . $this->_name);
    }

    public function checkGc($gc) {
        Logger::getInstance()->notice('garbage collector useless on APCu', 'cache' . $this->_name);
    }

    public function writeGc($gc) {
        Logger::getInstance()->notice('garbage collector useless on APCu', 'cache' . $this->_name);
    }

    public function runGc() {
        Logger::getInstance()->notice('garbage collector useless on APCu', 'cache' . $this->_name);
    }

    public function write($key, $data, $forceReplace = false, $expire = self::EXPIRE_INFINITE, $type = self::TYPE_TIME) {
        if (!is_string($key))
            throw new \Exception('Key name must be a string');
        if (!is_int($expire))
            throw new \Exception('expire must be a int');

        if ($this->exist($key)) {
            //override
            if (!$forceReplace)
                throw new \Exception('Write key : "' . $key . '" fail, already defined');

            Logger::getInstance()->debug('Write key : "' . $key . '" already exist, override', 'cache' . $this->_name);
            if ($this->isLocked($key)) {
                Logger::getInstance()->debug('Write key : "' . $key . '" fail, is locked', 'cache' . $this->_name);
                return;
            }
        }
        $ttl = $type == self::TYPE_NUMBER ? 0 : $expire;
        apc_store($this->_prefix . $this->_prefixGroups . md5($key), base64_encode(serialize(array($expire, $key, $data, $type))), $ttl);
        Logger::getInstance()->debug('Key : "' . $key . '" written', 'cache' . $this->_name);
    }

    public function read($key, $default = null, $lockTime = false, $onlyExpireTime = false) {
        if (!is_string($key))
            throw new \Exception('Key name must be a string');

        if ($this->exist($key)) {
            if ($this->isLocked($key)) {
                Logger::getInstance()->debug('Read key :  "' . $key . '" fail, is locked', 'cache' . $this->_name);
                return $default;
            }

            //check if is expired
            if ($this->isExpired($key))
                return $default;
            else {
                $data = unserialize(base64_decode(apc_fetch($this->_prefix . $this->_prefixGroups . md5($key))));
                // decrease expire value
                if ($data[3] == self::TYPE_NUMBER && $data[0] > 0) {
                    Logger::getInstance()->debug('Read key :  "' . $key . '"', 'cache' . $this->_name);
                    $this->write($key, $data[2], true, $data[0] - 1, $data[3]);
                }

                // lock and return cache datas
                $this->lock($key, $lockTime);

                Logger::getInstance()->debug('Read key :  "' . $key . '"', 'cache' . $this->_name);
                if ($onlyExpireTime)//return expiress time
                    return $data[0];
                return $data[2];
            }
        } else {
            Logger::getInstance()->debug('Read key :  "' . $key . '" fail, not exists', 'cache' . $this->_name);

            return $default;
        }
    }

    public function exist($key) {
        return apc_exists($this->_prefix . $this->_prefixGroups . md5($key));
    }

    public function delete($key, $forceUnlock = true) {
        if (!$this->exist($key)) {
            Logger::getInstance()->debug('Undeletable key : "' . $key . '" because not exists', 'cache' . $this->_name);
            return;
        }

        if ($this->isLocked($key)) {
            if ($forceUnlock)
                $this->_delete($key);
            else
                Logger::getInstance()->debug('Undeletable key : "' . $key . '" because is locked', 'cache' . $this->_name);
        } else
            $this->_delete($key, false);
    }

    protected function _delete($key) {
        apc_delete($this->_prefix . $this->_prefixGroups . md5($key));
        Logger::getInstance()->debug('Delete key : "' . $key . '"', 'cache' . $this->_name);

        if (apc_exists($this->_prefix . $this->_prefixGroups . md5($key) . $this->_lockName))
            apc_delete($this->_prefix . $this->_prefixGroups . md5($key) . $this->_lockName);
    }

    public function isExpired($key, $autoDelete = true) {
        if (!$this->exist($key)) {
            Logger::getInstance()->debug('Key : "' . $key . '" expired', 'cache' . $this->_name);
            return true;
        } else {
            $data = unserialize(base64_decode(apc_fetch($this->_prefix . $this->_prefixGroups . md5($key))));
            if (!is_array($data) || count($data) < 4) {
                Logger::getInstance()->debug('Key : "' . $key . '" have not valid cache data', 'cache' . $this->_name);
                return true;
            }
            if ($data[3] == self::TYPE_NUMBER) {
                if ($data[0] <= 0) {
                    Logger::getInstance()->debug('Key : "' . $key . '" expired', 'cache' . $this->_name);
                    if ($autoDelete)
                        $this->delete($key);

                    return true;
                }
            }
            return false;
        }
    }

    public function lock($key, $time = Cache::EXPIRE_INFINITE) {
        if (!is_string($key))
            throw new \Exception('Key name must be a string');
        if ($time && !is_int($time))
            throw new \Exception('Lock time must be an integer');

        if ($time === false)
            return;
        if ($this->exist($key) && !$this->_isLock($key) && $time >= 0) {
            Logger::getInstance()->debug('Lock key : "' . $key . '"', 'cache' . $this->_name);
            $this->write('lock' . $key, '', true, $time);
        }
    }

    public function unlock($key) {
        if (!$this->isLocked($key)) {
            Logger::getInstance()->debug('Try unlock key : "' . $key . '" fail, key not locked', 'cache' . $this->_name);
            return;
        }

        $this->delete($this->_lockName . $key);
    }

    public function isLocked($key) {
        return $this->_isLock($key) ? false : $this->exist('lock' . $key);
    }

    public function increment($key, $offset = 1, $startValue = 1) {
        if (!$this->exist($key))
            $this->write($key, $startValue);
        if (!$this->isExpired($key) && !$this->isLocked($key)) {
            apc_inc($key, $offset);
            Logger::getInstance()->debug('Increment : "' . $key . '"', 'cache' . $this->_name);
        }
    }

    public function decrement($key, $offset = 1, $startValue = 1) {
        if (!$this->exist($key))
            $this->write($key, $startValue);
        if (!$this->isExpired($key) && !$this->isLocked($key)) {
            apc_dec($key, $offset);
            Logger::getInstance()->debug('Decrement : "' . $key . '"', 'cache' . $this->_name);
        }
    }

    public function clear() {
        Logger::getInstance()->notice('Clear cache useless on APCu', 'cache' . $this->_name);
    }

    public function purge($groupName = false) {
        if ($groupName != false && !is_string($groupName))
            throw new \Exception('Group name must be a string');
        $keys = apc_cache_info();
        foreach ($keys['cache_list'] as &$key) {
            if (stripos($key['key'], $this->_prefix . $this->_prefixGroups) !== false && stripos($key['key'], $this->_lockName) == false) {
                //check group
                if ($groupName != false && stripos($key['key'], $groupName) == false)
                    continue;

                $data = unserialize(base64_decode(apc_fetch($key['key'])));
                $key = isset($data[0]) ? $data[0] : $key['key'];
                $this->delete($key);
            }
        }
        Logger::getInstance()->debug('Cache purged', 'cache' . $this->_name);
    }

    public function clearGroup($groupName) {
        $this->purge($groupName);
        Logger::getInstance()->debug('Cache cleared group : "' . $groupName . '"', 'cache' . $this->_name);
    }

}

?>