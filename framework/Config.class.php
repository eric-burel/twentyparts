<?php

namespace framework;

use framework\config\Loader;
use framework\config\Reader;
use framework\config\loaders\Constant;
use framework\utility\Tools;
use framework\network\Http;

class Config {

    use pattern\Singleton;

    const LOADER = 'loader';
    const READER = 'reader';

    protected static $_path = null;

    public static function setPath($path) {
        if (!is_dir($path))
            throw new \Exception('Path "' . $path . '" do not exists');
        if (!is_readable($path))
            throw new \Exception('Directory "' . $path . '" is not readable');

        self::$_path = realpath($path) . DS;
    }

    public static function getPath() {
        return self::$_path;
    }

    protected function __construct() {
        if (!is_null(self::$_path)) {
            // Check config default path
            if (!is_dir(self::$_path . 'default'))
                throw new \Exception('Config error, please set default config directory');

            //load default config
            $this->loadPath(self::$_path . 'default');

            //load by host
            $hostname = Http::getServer('HTTP_HOST');
            if ($hostname && is_dir(self::$_path . $hostname))
                $this->loadPath(self::$_path . $hostname);

            // Define default constants
            Constant::defineCons();
        }
    }

    public function loadPath($path) {
        $dir = Tools::cleanScandir($path);
        foreach ($dir as &$f) {
            if (is_file($path . DS . $f))
                $this->loadFile($path . DS . $f);
        }
    }

    public function load(Loader $loader, Reader $reader) {
        $loader->load($reader);
    }

    public function loadFile($filename, Loader $loader = null, Reader $reader = null) {
        if (!file_exists($filename))
            throw new \Exception('File : "' . $filename . '" not exists');

        if ($loader === null && $reader === null)
            $ext = Tools::getFileExtension($filename);

        //get reader by name of file
        if ($reader === null)
            $reader = $this->_factory($ext, self::READER, $filename);

        //get loader by name of file
        if ($loader === null)
            $loader = $this->_factory(basename($filename, '.' . $ext), self::LOADER);


        if ($reader && $loader)
            $this->load($loader, $reader);
    }

    protected function _factory($class, $type = self::READER, $filename = null) {
        if ($type != self::READER && $type != self::LOADER)
            throw new \Exception('Invalid type');
        if (!is_string($class))
            throw new \Exception('Class parameter must be a string');

        $namespace = $type == self::READER ? 'framework\config\readers\\' : 'framework\config\loaders\\';
        if (!class_exists($namespace . ucfirst($class)))
            throw new \Exception('Invalid class :  "' . $class . '"');

        $class = $namespace . ucfirst($class);
        $inst = new \ReflectionClass($class);
        return !is_null($filename) ? $inst->newInstance($filename) : $inst->newInstance();
    }

}

?>