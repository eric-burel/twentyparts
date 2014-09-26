<?php

namespace framework\utility;

use framework\utility\Benchmark;

class Timer {

    protected $_begin = 0;
    protected $_end = 0;
    protected $_mode = 1;
    protected $_roundResult = true;
    protected $_roundResultPrecision = 4;
    protected $_roundResultMode = PHP_ROUND_HALF_UP;

    public function __construct($autoStart = true, $mode = 1) {
        if ($autoStart)
            $this->start();

        $this->setMode($mode);
    }

    public function setRoundResult($bool) {
        if (!is_bool($bool))
            throw new \Exception('roundResult parameter must be a boolean');

        $this->_roundResult = $bool;
    }

    public function getRoundResult() {
        return $this->_roundResult;
    }

    public function setRoundResultPrecision($roundPrecision) {
        if (!is_int($roundPrecision))
            throw new \Exception('roundResultPrecision parameter must be a integer');

        $this->_roundResultPrecision = $roundPrecision;
    }

    public function getRoundResultPrecision() {
        return $this->_roundResultPrecision;
    }

    public function setRoundResultMode($mode) {
        if (is_int($mode) || $mode < PHP_ROUND_HALF_UP || $mode > PHP_ROUND_HALF_ODD)
            throw new \Exception('roundResultMode parameter must be an integer between 1 and 4');

        $this->_roundResultMode = $mode;
    }

    public function getRoundResultMode() {
        return $this->_roundResultMode;
    }

    public function setMode($mode) {
        if (!is_int($mode) || $mode < Benchmark::TIME_SECOND || $mode > Benchmark::TIME_MICROSECOND)
            throw new \Exception('Mode parameter must be an integer between 1 and 3');
        $this->_mode = $mode;
    }

    public function getMode() {
        return $this->_mode;
    }

    public function start() {
        $this->_begin = microtime(true);
        return $this;
    }

    public function setBegin($begin) {
        //TODO check value...
        $this->_begin = $begin;
    }

    public function stop() {
        $this->_end = microtime(true);
        return $this;
    }

    public function getInterval() {
        switch ($this->_mode) {
            //second
            case Benchmark::TIME_SECOND:
                $time = ($this->_end - $this->_begin);
                break;
            //millisecond
            case Benchmark::TIME_MS:
                $time = ($this->_end - $this->_begin) * 1000;
                break;
            //microsecond
            case Benchmark::TIME_MICROSECOND:
                $time = ($this->_end - $this->_begin) * 1000000;
                break;
            default:
                throw new \Exception('Invalid mode defined');
                break;
        }
        if ($this->_roundResult)
            $time = round($time, $this->_roundResultPrecision, $this->_roundResultMode);

        return $time;
    }

}

?>