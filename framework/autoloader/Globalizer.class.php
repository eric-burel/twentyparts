<?php

namespace framework\autoloader;

use framework\Autoloader;
use framework\autoloader\adaptaters\Finder;
use framework\autoloader\adaptaters\Cache;

class Globalizer extends Autoloader {

    protected $_globalizedClasses = array();
    protected $_compressClasses = false;

    public function __construct($globalizedClasses = array(), $compressClasses = true) {
        if (!empty($globalizedClasses))
            $this->addGlobalizedClasses($globalizedClasses);
        if ($compressClasses)
            $this->setCompressClasses($compressClasses);
    }

    public function addGlobalizedClass($class) {
        if (!is_string($class))
            throw new \Exception('class value must be a string');

        if ($this->isGlobalizedClassRegistered($class))
            throw new \Exception('class class value "' . $class . '" is already defined');

        if (!in_array($class, get_declared_classes()) && !in_array($class, get_declared_interfaces()))
            $this->_globalizedClasses[] = $class;
    }

    public function addGlobalizedClasses($classes) {
        if (!is_array($classes))
            throw new \Exception('classes parameter must be an array');

        foreach ($classes as &$class)
            $this->addGlobalizedClass($class);
    }

    public function deleteGlobalizedClass($class) {
        if (!is_string($class))
            throw new \Exception('class value must be a string');


        if (!$this->isGlobalizedClassRegistered($class))
            throw new \Exception('Class "' . $class . '" isn\'t registered');

        unset($this->_globalizedClasses[$class]);
    }

    public function deleteGlobalizedClasses($classes) {
        if (!is_array($classes))
            throw new \Exception('classes parameter must be an array');

        foreach ($classes as &$class)
            $this->deleteGlobalizedClass($class);
    }

    public function resetGlobalizedClasses() {
        $this->_globalizedClasses = array();
    }

    public function getGlobalizedClasses() {
        return $this->_globalizedClasses;
    }

    public function isGlobalizedClassRegistered($class) {
        if (!is_string($class))
            throw new \Exception('class parameter must be a string');
        return (in_array($class, $this->getGlobalizedClasses()));
    }

    public function setCompressClasses($compress) {
        if (!is_bool($compress))
            throw new \Exception('compress parameter must be a boolean');
        $this->_compressClasses = $compress;
    }

    public function getCompressClasses() {
        return $this->_compressClasses;
    }

    public function loadGlobalizedClass($checkCache = true, $forceUpdateCache = false) {
        if (!self::getCache())
            return;

        if (self::getDebug()) {
            $benchTime = microtime(true);
            $benchMemory = memory_get_usage();
        }
        if (!is_bool($checkCache))
            throw new \Exception('checkCache parameter must be a boolean');
        if (!is_bool($forceUpdateCache))
            throw new \Exception('forceUpdateCache parameter must be a boolean');

        if ($forceUpdateCache)
            $this->_writeCache();
        else {
            if ($checkCache && $this->_isExpiredCache())
                $this->_writeCache();

            // If don't check cache, classes informations must be setted manually
            if (!$checkCache) {
                self::_addLog('Globalizer classes cache NOT CHECKED!');
                $globalizedClasses = $this->getGlobalizedClasses();
                foreach ($globalizedClasses as &$class) {
                    $classSourceFile = self::_checkClassFileSource($class);
                    self::_setClassInfo($class, $classSourceFile['sourceFilePath'], true, true);
                }
            }
        }
        // Include globale cache
        $cache = self::getCache()->read('GlobalizedClassesCache');
        if ($cache) {
            eval($cache);
            self::_addLog('Globalizer classes cache was loaded by cache');
        }




        if (self::getDebug())
            self::_setBenchmark(microtime(true) - $benchTime, memory_get_usage() - $benchMemory);
    }

    protected function _isExpiredCache() {
        $globalizedClasses = $this->getGlobalizedClasses();
        $cache = self::getCache()->read('GlobalizedClassesCache');
        if (!$cache)
            return true;
        foreach ($globalizedClasses as &$class) {
            $classSourceFile = Finder::findClassSourceFile($class);
            if (!$classSourceFile) {
                self::_addLog('Globalizer classes cache is invalid, a globalized class have not a file source');
                return true;
            }
            if (!$classSourceFile['isCached'])
                Cache::writeClassPath($class, $classSourceFile['sourceFilePath']);


            $classInfos = pathinfo($classSourceFile['sourceFilePath']);
            $ClassCacheName = str_replace(DS, '-', str_replace(PATH_ROOT, '', realpath($classInfos['dirname'])) . DS . $classInfos['basename']);
            $ClassCache = self::getCache()->read($ClassCacheName);
            if (!$ClassCache)
                return true;
            if ($ClassCache != (filemtime($classSourceFile['sourceFilePath']))) {
                self::_addLog('Globalizer classes cache is expired, a globalized class metadata outdated');
                return true;
            }

            // if class isn't into global file
            $classNameElements = explode('\\', $class);
            $classBaseName = end($classNameElements);
            $needleList = array('class ' . $classBaseName, 'abstract class ' . $classBaseName, 'final class ' . $classBaseName, 'interface ' . $classBaseName, 'trait ' . $classBaseName);
            $match = false;
            foreach ($needleList as &$needle) {
                if ((bool) strstr($cache, $needle)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                self::_addLog('Globalizer classes cache is expired, a globalized class isn\'t into global file');
                return true;
            }

            // set class info
            self::_setClassInfo($class, $classSourceFile['sourceFilePath'], true, true);
        }
        self::_addLog('Globalizer classes cache is valid and not expired');
        return false;
    }

    protected function _writeCache() {
        $globalizedClasses = $this->getGlobalizedClasses();
        $globalizeCacheContents = '';
        foreach ($globalizedClasses as &$class) {
            // check and get class infos
            $classSourceFile = $this->_checkClassFileSource($class);

            // Write meta file
            $classInfos = pathinfo($classSourceFile['sourceFilePath']);
            if (self::getCache()) {
                $cacheName = str_replace(DS, '-', str_replace(PATH_ROOT, '', realpath($classInfos['dirname'])) . DS . $classInfos['basename']);
                self::getCache()->write($cacheName, filemtime($classSourceFile['sourceFilePath']), true);
            }

            // Clean & fix classes content and compress
            $classContents = file_get_contents($classSourceFile['sourceFilePath']);
            $classContentsNSFixed = $this->_fixNamespace($classContents);
            // compress file ...
            $classContentsCompressed = $this->getCompressClasses() ? $this->_compressClassContents($classContentsNSFixed) : $classContentsNSFixed;
            // delete Php Tags
            $contentsCleaned = $this->_deletePhpTags($classContentsCompressed);
            // Add into global cache contents
            $globalizeCacheContents .= $contentsCleaned;

            // set class info
            self::_setClassInfo($class, $classSourceFile['sourceFilePath'], true, true);
        }

        //write
        if (self::getCache())
            self::getCache()->write('GlobalizedClassesCache', $globalizeCacheContents, true);
    }

    protected function _deletePhpTags($classContents) {
        return str_replace(array('<?php', '?>'), ' ', $classContents);
    }

    protected function _checkClassFileSource($class) {
        $classSourceFile = Finder::findClassSourceFile($class);
        if (!$classSourceFile)
            throw new \Exception('Class :  "' . $class . '" can\'t find source file path');
        if (!file_exists($classSourceFile['sourceFilePath']))
            throw new \Exception('Class :  "' . $classSourceFile['sourceFilePath'] . '" don\'t exists');
        if (self::getCache() && !$classSourceFile['isCached'])
            Cache::writeClassPath($class, $classSourceFile['sourceFilePath']);
        return $classSourceFile;
    }

    protected function _fixNamespace($contents) {
        // Rewrite namespaces declaration for fix this fatal error :  "Cannot mix bracketed namespace declarations with unbracketed namespace declarations"
        if ($this->_haveNamespace($contents)) {
            // Rewrite unbracketed namespaces
            if ($this->_isUnbracketedNamespace($contents)) {
                return $contents;
            }
            else
                return $contents;
        } //else
        return $contents;
    }

    protected function _haveNamespace($classContents) {
        return (bool) strstr($classContents, 'namespace');
    }

    protected function _isUnbracketedNamespace($classContents) {
        
    }

    protected function _compressClassContents($contents) {
        if (!function_exists('token_get_all'))
            throw new \Exception('Compress class not possible, "token_get_all" don\'t exists');

        $output = '';
        foreach (token_get_all($contents) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= $token[1];
            }
        }
        // http://php.net/manual/fr/function.strip-tags.php voir les commentaires pour paufiner la compression
        // replace multiple new lines with a single newline
        $output = preg_replace(array('/\s+$/Sm', '/\n+/S'), '', $output);
        $output = str_replace(chr(10), '', $output);
        // replace multi spaces with a single space
        $output = str_replace(chr(32) . chr(32), chr(32), $output); //$string = trim(preg_replace('/ {2,}/', ' ', $string)); ???
        return $output;
    }

}

?>