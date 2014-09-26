
<?php

//TODO must be completed

namespace framework\config\readers;

use framework\config\Reader;
use framework\utility\Validate;

class Yaml extends Reader {

    protected $_filename;
    protected $_datas;

    public function __construct($filename) {
        throw new \Exception('Not yet');

        if (!file_exists($filename))
            throw new \Exception('File : "' . $filename . '" not exists');
        if (!is_readable($filename))
            throw new \Exception('File : "' . $filename . '" is not readable');
        if (!Validate::isFileMimeType('xml', $filename))
            throw new \Exception('File : "' . $filename . '" is not a xml file');

        $this->_filename = $filename;
    }

    public function read() {
        
    }

}

?>
