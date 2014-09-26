<?php

namespace framework\autoloader;

trait Directories {

    protected static $_directories = array();

    public static function addDirectory($directoryPath, $forceReplace = false, $forceCacheUpdate = false) {
        // Checking
        if (!is_string($directoryPath))
            throw new \Exception('DirectoryPath parameter must be an array');
        if (!is_bool($forceReplace))
            throw new \Exception('ForceReplace parameter must be a boolean');
        if (!is_dir($directoryPath))
            throw new \Exception('Directory : "' . $directoryPath . '" must be a valid directory');
        if (array_key_exists(md5($directoryPath), self::getDirectories()) && !$forceReplace)
            throw new \Exception('Directory : "' . $directoryPath . '" already registered');

        // Add Principal directory
        self::$_directories[md5($directoryPath)] = $directoryPath;
        // Add sub directories (with cache if it's possible)
        if (!self::getCache())
            self::_addSubDirectories($directoryPath);
        else {
            if (self::_isExpiredCacheDirectory($directoryPath) || $forceCacheUpdate) {//expired
                if ($forceCacheUpdate)
                    self::_addLog('Directory : "' . $directoryPath . '" cache force update');
                self::_addSubDirectories($directoryPath);
            } else {
                $cache = self::getCache()->read('AutoloaderCacheDirectories-' . $directoryPath . '-contents');
                if (is_array($cache)) {
                    foreach ($cache as &$dir)
                        self::$_directories[md5($dir)] = $dir;
                }
                self::_addLog('Directory : "' . $directoryPath . '" loaded by cache');
            }
        }
    }

    public static function addDirectories($directories) {
        if (!is_array($directories))
            throw new \Exception('Directories parameter must an array');
        foreach ($directories as &$directory)
            self::addDirectory($directory);
    }

    public static function deleteDirectory($directory, $purgeCache = true) {
        if (!is_string($directory))
            throw new \Exception('Directory parameter must be an array');
        if (!array_key_exists(md5($directory), self::getDirectories()))
            throw new \Exception('Directory : "' . $directory . '" isn\'t registered');

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $path) {
            if ($path->isDir() && !strstr((string) $path, '.svn')) {
                if (isset(self::$_directories[md5((string) $path)]))
                    unset(self::$_directories[md5((string) $path)]);
            }
        }
        unset(self::$_directories[md5($directory)]);
        if ($purgeCache)
            self::purgeCacheDirectory($directory);
    }

    public static function deleteDirectories($directories) {
        if (!is_array($directories))
            throw new \Exception('Directories parameter must an array');
        foreach ($directories as &$directory)
            self::deleteDirectory($directory);
    }

    public static function getDirectories() {
        return self::$_directories;
    }

    protected static function _addSubDirectories($directoryPath) {
        $cache = array();
        $subDirectories = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($subDirectories as $subDirectory) {
            if ($subDirectory->isDir()) {
                if ($subDirectory->getFileName() != '.' && $subDirectory->getFileName() != '..' && $subDirectory->getFileName() != '.svn') {
                    $subName = (string) $subDirectory;
                    self::$_directories[md5($subName)] = $subName;
                    if (self::getCache())
                        $cache[] = $subName;
                }
            }
        }
        // Write subdirectorie list in cache
        if (self::getCache())
            self::_writeCacheDirectory($directoryPath, $cache, filemtime($directoryPath));
    }

    protected static function _isExpiredCacheDirectory($directory) {
        $filemtime = self::getCache()->read('AutoloaderCacheDirectories-' . $directory . '-filemtime');
        if (!$filemtime || $filemtime != filemtime($directory))
            return true;
        $contents = self::getCache()->read('AutoloaderCacheDirectories-' . $directory . '-contents');
        if (!$contents)
            return true;

        return false;
    }

    protected static function _writeCacheDirectory($directory, $directoryContents, $directoryFilemtime) {
        self::getCache()->write('AutoloaderCacheDirectories-' . $directory . '-contents', $directoryContents, true);
        self::getCache()->write('AutoloaderCacheDirectories-' . $directory . '-filemtime', $directoryFilemtime, true);
        self::_addLog('Directory : "' . $directory . '" written on cache');
    }

    public static function purgeCacheDirectories() {
        foreach (self::$_directories as &$directory)
            self::purgeCacheDirectory($directory);
    }

    public static function purgeCacheDirectory($directory) {
        if (self::getCache()) {
            self::getCache()->delete('AutoloaderCacheDirectories-' . $directory . '-contents', true);
            self::getCache()->delete('AutoloaderCacheDirectories-' . $directory . '-filemtime', true);
        }
    }

}

?>