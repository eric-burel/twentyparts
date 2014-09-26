<?php

namespace framework\cache\adaptaters;

use framework\Logger;
use framework\Cache;
use framework\cache\IAdaptater;
use framework\utility\Tools;

class File extends Cache implements IAdaptater {

    public function __construct($params = array()) {
        parent::init($params);
        Logger::getInstance()->addGroup('cache' . $this->_name, 'Cache ' . $this->_name, true, true);

        if (!isset($params['path']) || !is_string($params['path']))
            throw new \Exception('Miss path parameter, or is invalid');

        if (!is_dir($params['path'])) {
            if (!mkdir($params['path'], 0775, true))
                throw new \Exception('Error on creating "' . $params['path'] . '" directory');
        }
        if (!is_writable($params['path']))
            throw new \Exception('Directory "' . $params['path'] . '" is not writable');
        $this->_path = realpath($params['path']) . DS;
    }

    public function runGc() {
        // Garbage collector, remove all expired cached datas
        if ($this->_gcType) {
            Logger::getInstance()->debug('Garbage collector by : ' . $this->_gcType, 'cache' . $this->_name);
            $gc = $this->read($this->_gcName, null);
            // no exist
            if (is_null($gc))
                $this->writeGc($gc); // set gc state
            else {
                //check
                if ($this->checkGc($gc)) {
                    //Delete expired datas
                    $this->clear();
                    $this->delete($this->_gcName);
                } else {
                    if ($this->_gcType == self::TYPE_NUMBER)
                        $this->writeGc($gc); // increment gc state
                }
            }
        }
    }

    public function checkGc($gc) {
        if ($this->_gcType == self::TYPE_NUMBER)
            return $gc >= $this->_gcOption;
        elseif ($this->_gcType == self::TYPE_TIME)
            return ($gc + $this->_gcOption) <= time();
        else
            return false;
    }

    public function writeGc($gc) {
        if (is_null($gc))
            $gc = 0;
        $gc = ($this->_gcType == self::TYPE_NUMBER) ? $gc + 1 : time();
        $this->write($this->_gcName, $gc, true);
    }

    public function write($key, $data, $forceReplace = false, $expire = self::EXPIRE_INFINITE, $type = self::TYPE_TIME) {
        if (!is_string($key))
            throw new \Exception('Key name must be a string');
        if ($this->exist($key)) {
            //override
            if (!$forceReplace)
                throw new \Exception('Write key : "' . $key . '" fail, already defined');

            Logger::getInstance()->debug('Key : "' . $key . '" already exist, override', 'cache' . $this->_name);
            if ($this->isLocked($key)) {
                Logger::getInstance()->debug('Key : "' . $key . '" must be unlocked', 'cache' . $this->_name);
                return;
            }
        }

        //create file
        $file = new \SplFileObject($this->_path . $this->_prefix . $this->_prefixGroups . md5($key), 'w+');
        if ($file->flock(LOCK_EX)) {
            $file->fwrite(base64_encode(serialize(array($this->_calculExpire($expire, $type), $key, $data, $type))));
            $file->flock(LOCK_UN);
        }
        Logger::getInstance()->debug('Key : "' . $key . '" written', 'cache' . $this->_name);
    }

    public function read($key, $default = null, $lockTime = false, $onlyExpireTime = false) {
        if (!is_string($key))
            throw new \Exception('Key must be a string');

        if ($this->exist($key)) {
            if ($this->isLocked($key)) {
                Logger::getInstance()->debug('Read key :  "' . $key . '" fail, is locked', 'cache' . $this->_name);
                return $default;
            }

            //check if is expired
            if ($this->isExpired($key, true, false))
                return $default;
            else {
                $file = new \SplFileObject($this->_path . $this->_prefix . $this->_prefixGroups . md5($key), 'r');
                $data = unserialize(base64_decode($file->fgets()));

                // decrease expire value
                if ($data[3] == self::TYPE_NUMBER && $data[0] > 0) {
                    Logger::getInstance()->debug('Decrease count usage key :  "' . $key . '"', 'cache' . $this->_name);
                    $this->write($key, $data[2], true, $data[0] - 1, $data[3]);
                }
                //create lock and return cache datas
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
        return file_exists($this->_path . $this->_prefix . $this->_prefixGroups . md5($key));
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
            $this->_delete($key);
    }

    protected function _delete($key) {
        unlink($this->_path . $this->_prefix . $this->_prefixGroups . md5($key));
        Logger::getInstance()->debug('Delete key : "' . $key . '"', 'cache' . $this->_name);

        //delete lock
        if (file_exists($this->_path . $this->_prefix . $this->_prefixGroups . md5($this->_lockName . $key))) {
            unlink($this->_path . $this->_prefix . $this->_prefixGroups . md5($this->_lockName . $key));
            Logger::getInstance()->debug('Delete key lock : "' . $key . '"', 'cache' . $this->_name);
        }
    }

    public function isExpired($key, $autoDelete = true, $checkExist = true) {
        if ($checkExist && !$this->exist($key)) {
            Logger::getInstance()->debug('Key : "' . $key . '" not exists', 'cache' . $this->_name);
            return true;
        }

        if (!is_readable($this->_path . $this->_prefix . $this->_prefixGroups . md5($key)))
            throw new \Exception('not readdable file');

        $file = new \SplFileObject($this->_path . $this->_prefix . $this->_prefixGroups . md5($key), 'r');
        $data = unserialize(base64_decode($file->fgets()));

        // Check if is valid cache file
        if (!is_array($data) || count($data) < 4) {
            Logger::getInstance()->debug('Key : "' . $key . '" have not valid cache file', 'cache' . $this->_name);
            return true;
        }
        //check if is expired
        $isExpired = $data[0] == 0 ? true : $data[3] == self::TYPE_NUMBER ? false : time() > $data[0];
        if ($isExpired) {
            Logger::getInstance()->debug('Key : "' . $key . '" expired', 'cache' . $this->_name);
            if ($autoDelete)
                $this->delete($key);
            return true;
        } else
            return false;
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
            $this->write($this->_lockName . $key, '', true, time() + $time);
        }
    }

    public function unlock($key) {
        if ($this->exist($key) && !$this->_isLock($key)) {
            $lock = $this->read($this->_lockName . $key);
            if (!is_null($lock)) {
                if (!$this->isLocked('lock' . $key)) {
                    Logger::getInstance()->debug('Try unlock key : "' . $key . '" fail, key not locked', 'cache' . $this->_name);
                    return;
                }

                $this->delete($this->_lockName . $key);
            }
        }
    }

    public function isLocked($key) {
        if (!$this->exist($this->_lockName . $key))
            return false;

        $lock = $this->read($this->_lockName . $key);
        if ($lock == 0 || time() > $lock)
            return true;

        return false;
    }

    public function clear() {
        $dirs = Tools::cleanScanDir($this->_path);
        foreach ($dirs as &$f) {
            if (is_file($this->_path . $f)) {
                //not a lock or gc
                if (stripos($f, $this->_prefix . $this->_prefixGroups . md5($this->_lockName)) == false && stripos($f, $this->_prefix . $this->_prefixGroups . md5($this->_gcName)) == false) {
                    $file = new \SplFileObject($this->_path . $f, 'r');
                    $data = unserialize(base64_decode($file->fgets()));
                    $key = isset($data[1]) ? $data[1] : $f;
                    $this->isExpired($key); //auto-deleting cache file
                }
            }
        }
        Logger::getInstance()->debug('Cache cleared', 'cache' . $this->_name);
    }

    public function purge($deleteCachePath = true, $chmod = false) {
        $dir = Tools::cleanScandir($this->_path);
        foreach ($dir as &$f) {
            if (is_file($this->_path . $f))
                unlink($this->_path . $f);
            if (is_dir($this->_path . $f))
                Tools::deleteTreeDirectory($this->_path . $f, true, $chmod);
        }
        if ($deleteCachePath) {
            chmod($this->_path, $chmod);
            rmdir($this->_path);
        }
        Logger::getInstance()->debug('Cache purged', 'cache' . $this->_name);
    }

    public function clearGroup($groupName) {
        $dirs = Tools::cleanScanDir($this->_path);
        foreach ($dirs as &$f) {
            if (is_file($this->_path . $f)) {
                //is not a lock
                if (stripos($f, $this->_prefix . $this->_prefixGroups . md5('lock')) == false) {
                    // find
                    if (stripos($f, $groupName) !== false) {
                        $file = new \SplFileObject($this->_path . $f, 'r');
                        $data = unserialize(base64_decode($file->fgets()));
                        $key = isset($data[1]) ? $data[1] : $f;
                        $this->delete($key);
                    }
                }
            }
        }
        Logger::getInstance()->debug('Cache cleared group : "' . $groupName . '"', 'cache' . $this->_name);
    }

    protected function _calculExpire($expire, $type) {
        if ($type != self::TYPE_TIME && $type != self::TYPE_NUMBER)
            throw new \Exception('Invalid cache type : "' . $type . '"');

        if (!is_int($expire))
            throw new \Exception('expire must be a int');

        if ($type == self::TYPE_TIME)
            return $expire == 0 ? 0 : time() + $expire; //0 == infinite, else timestamp

        return $expire;
    }

}

?>