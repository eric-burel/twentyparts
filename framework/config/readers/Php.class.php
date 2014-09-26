<?php

namespace framework\config\readers;

use framework\config\Reader;
use framework\utility\Validate;

class Php extends Reader {

    protected $_filename;
    protected $_datas;

    public function __construct($filename) {
        if (!file_exists($filename))
            throw new \Exception('File : "' . $filename . '" not exists');
        if (!is_readable($filename))
            throw new \Exception('File : "' . $filename . '" is not readable');
        if (!Validate::isFileMimeType('php', $filename))
            throw new \Exception('File : "' . $filename . '" is not a php file');

        $this->_filename = $filename;
    }

    public function read() {
        include $this->_filename;
        if (!isset($config))
            throw new \Exception('Invalid config file : "' . $this->_filename . '"');

        //cast config
        $datas = (array) $config;
        //destruct var
        unset($config);

        return $datas;
    }

}

?>
