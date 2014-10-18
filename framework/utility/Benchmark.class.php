<?php

namespace framework\utility;

use framework\utility\Timer;

class Benchmark {

    const TIME_SECOND = 1;
    const TIME_MS = 2;
    const TIME_MICROSECOND = 3;
    const RAM_BYTE = 1;
    const RAM_KB = 2;
    const RAM_MB = 3;

    protected static $_instance = null;
    protected $_benchmark;
    protected $_multiBenchmark = true;
    protected $_specificBenchmark = null;

    protected function __construct($benchMarkName) {
        $this->_benchmark = new \stdClass();

        if ($benchMarkName == null) {
            $this->_benchmark->time = new \stdClass();
            $this->_benchmark->ramBegin = 0;
            $this->_benchmark->ramEnd = 0;
            $this->_benchmark->ramInfoStarted = false;
            $this->_benchmark->ramInfoStopped = false;
            $this->_benchmark->timeInfoStarted = false;
            $this->_benchmark->timeInfoStopped = false;
            $this->_benchmark->ramMode = self::RAM_BYTE;
            $this->_benchmark->ramRoundResult = true;
            $this->_benchmark->ramRoundResultPrecision = 4;
            $this->_benchmark->ramRoundResultMode = PHP_ROUND_HALF_UP;
        }
    }

    public static function getInstance($benchMarkName = null, $forceRecreate = false) {
        if (self::$_instance === null)
            self::$_instance = new self($benchMarkName);

        if ($benchMarkName !== null) {
            if (!self::$_instance->_isRegisteredBenchmark($benchMarkName) || $forceRecreate)
                self::$_instance->_registerBenchmark($benchMarkName, $forceRecreate);

            self::$_instance->_specificBenchmark = $benchMarkName;
        }
        return self::$_instance;
    }

    public function getBenchmark($benchMarkName = null) {
        if ($benchMarkName !== null) {
            if (!is_string($benchMarkName))
                throw new \Exception('BenchmarkName parameter must be a string');
            if (!property_exists($this->_benchmark, $benchMarkName))
                throw new \Exception('BenchmarkName parameter "' . $benchMarkName . '" don`\'t exists');

            return $this->_benchmark->{$benchMarkName};
        } else {
            if ($this->_specificBenchmark)
                return $this->_benchmark->{$this->_specificBenchmark};
            else
                return $this->_benchmark;
        }
    }

    public function destructBenchmark($benchMarkName = null) {
        if ($benchMarkName !== null) {
            if (!is_string($benchMarkName))
                throw new \Exception('BenchmarkName parameter must be a string');
            if (!property_exists($this->_benchmark, $benchMarkName))
                throw new \Exception('BenchmarkName parameter "' . $benchMarkName . '" don`\'t exists');

            unset($this->_benchmark->{$benchMarkName});
        } else {
            if ($this->_specificBenchmark)
                unset($this->_benchmark->{$this->_specificBenchmark});
            else
                unset($this->_benchmark);
        }
    }

    public function setMultiBenchmark($multi) {
        if (!is_bool($multi))
            throw new \Exception('Multi parameter must be a boolean');

        $this->_multiBenchmark = $multi;
    }

    public function getMultiBenchmark() {
        return $this->_multiBenchmark;
    }

    public function setRamRoundResult($bool) {
        if (!is_bool($bool))
            throw new \Exception('ramRoundResult parameter must be a boolean');

        if ($this->_specificBenchmark !== null)
            $this->_benchmark->{$this->_specificBenchmark}->ramRoundResult = $bool;
        else
            $this->_benchmark->ramRoundResult = $bool;
    }

    public function getRamRoundResult() {
        if ($this->_specificBenchmark !== null)
            return $this->_benchmark->{$this->_specificBenchmark}->ramRoundResult;
        else
            return $this->_benchmark->ramRoundResult;
    }

    public function setRamRoundResultPrecision($roundPrecision) {
        if (!is_int($roundPrecision))
            throw new \Exception('ramRoundResultPrecision parameter must be a integer');
        if ($this->_specificBenchmark !== null)
            $this->_benchmark->{$this->_specificBenchmark}->ramRoundResultPrecision = $roundPrecision;
        else
            $this->_benchmark->ramRoundResultPrecision = $roundPrecision;
    }

    public function getRamRoundResultPrecision() {
        if ($this->_specificBenchmark !== null)
            return $this->_benchmark->{$this->_specificBenchmark}->ramRoundResultPrecision;
        else
            return $this->_benchmark->ramRoundResultPrecision;
    }

    public function setRamRoundResultMode($mode) {
        if (is_int($mode) || $mode < PHP_ROUND_HALF_UP || $mode > PHP_ROUND_HALF_ODD)
            throw new \Exception('ramRoundResultMode parameter must be an integer between 1 and 4');
        if ($this->_specificBenchmark !== null)
            $this->_benchmark->{$this->_specificBenchmark}->ramRoundResultMode = $mode;
        else
            $this->_benchmark->ramRoundResultMode = $mode;
    }

    public function getRamRoundResultMode() {
        if ($this->_specificBenchmark !== null)
            return $this->_benchmark->{$this->_specificBenchmark}->ramRoundResultMode;
        else
            return $this->_benchmark->ramRoundResultMode;
    }

    public function setRamMode($mode) {
        if (!is_int($mode) || $mode < 1 || $mode > 3)
            throw new \Exception('ramMode parameter must be an integer between 1 and 3');
        if ($this->_specificBenchmark !== null)
            $this->_benchmark->{$this->_specificBenchmark}->ramMode = $mode;
        else
            $this->_benchmark->ramMode = $mode;
    }

    public function getRamMode() {
        if ($this->_specificBenchmark !== null)
            return $this->_benchmark->{$this->_specificBenchmark}->ramMode;
        else
            return $this->_benchmark->ramMode;
    }

    public function setTimeRoundResult($bool) {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not set TimeRoundResult because Benchmark of time it\'s has not been started');
            $this->_benchmark->{$this->_specificBenchmark}->time->setRoundResult($bool);
        }else {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not set TimeRoundResult because Benchmark of time it\'s has not been started');
            $this->_benchmark->time->setRoundResult($bool);
        }
    }

    public function getTimeRoundResult() {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not get TimeRoundResult because Benchmark of time it\'s has not been started');
            return $this->_benchmark->{$this->_specificBenchmark}->time->getRoundResult();
        }else {
            if ($this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not get TimeRoundResult because Benchmark of time it\'s has not been started');
            return $this->_benchmark->time->getRoundResult();
        }
    }

    public function setTimeRoundResultPrecision($roundPrecision) {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not set TimeRoundResultPrecision because Benchmark of time it\'s has not been started');
            $this->_benchmark->{$this->_specificBenchmark}->time->setRoundResultPrecision($roundPrecision);
        }else {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not set TimeRoundResultPrecision because Benchmark of time it\'s has not been started');
            $this->_benchmark->time->setRoundResultPrecision($roundPrecision);
        }
    }

    public function getTimeRoundResultPrecision() {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not get TimeRoundResultPrecision because Benchmark of time it\'s has not been started');
            return $this->_benchmark->{$this->_specificBenchmark}->time->getRoundResultPrecision();
        }else {
            if ($this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not get TimeRoundResultPrecision because Benchmark of time it\'s has not been started');
            return $this->_benchmark->time->getRoundResultPrecision();
        }
    }

    public function setTimeRoundResultMode($mode) {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not set RoundResultMode because Benchmark of time it\'s has not been started');
            $this->_benchmark->{$this->_specificBenchmark}->time->setRoundResultMode($mode);
        }else {
            if ($this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not set RoundResultMode because Benchmark of time it\'s has not been started');
            $this->_benchmark->time->setRoundResultMode($mode);
        }
    }

    public function getTimeRoundResultMode() {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not get TimeRoundResultMode because Benchmark of time it\'s has not been started');
            return $this->_benchmark->{$this->_specificBenchmark}->time->getRoundResultMode();
        }else {
            if ($this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not get TimeRoundResultMode because Benchmark of time it\'s has not been started');
            return $this->_benchmark->time->getRoundResultMode();
        }
    }

    public function setTimeMode($mode) {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not set Time Mode because Benchmark of time it\'s has not been started');
            $this->_benchmark->{$this->_specificBenchmark}->time->setMode($mode);
        } else {
            if ($this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not stop set Time Mode because Benchmark of time it\'s has not been started');
            $this->_benchmark->time->setMode($mode);
        }
    }

    public function getTimeMode() {
        if ($this->_specificBenchmark !== null) {
            if ($this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not get Time Mode because Benchmark of time it\'s has not been started');
            return $this->_benchmark->{$this->_specificBenchmark}->time->getMode();
        }
        else {
            if ($this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not get Time Mode because Benchmark of time it\'s has not been started');
            return $this->_benchmark->time->getMode();
        }
    }

    public function startTime($timeMode = self::TIME_MS, $startTime = null) {
        if (!is_int($timeMode) || $timeMode < self::TIME_SECOND || $timeMode > self::TIME_MICROSECOND)
            throw new \Exception('time mode parameter must be an integer between 1 and 3');
        if ($this->_specificBenchmark !== null) {
            if ($startTime !== null) {
                $this->_benchmark->{$this->_specificBenchmark}->time = new Timer(false, $timeMode);
                $this->_benchmark->{$this->_specificBenchmark}->time->setBegin($startTime);
            }
            else
                $this->_benchmark->{$this->_specificBenchmark}->time = new Timer($timeMode);

            $this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted = true;
        } else {
            if ($startTime !== null) {
                $this->_benchmark->time = new Timer(false, $timeMode);
                $this->_benchmark->time->setBegin($startTime);
            }
            else
                $this->_benchmark->time = new Timer($timeMode);

            $this->_benchmark->timeInfoStarted = true;
        }

        return $this;
    }

    public function startRam($ramMode = self::RAM_MB, $startRam = null) {
        if (!is_int($ramMode) || $ramMode < self::RAM_BYTE || $ramMode > self::RAM_MB)
            throw new \Exception('ram mode parameter must be an integer between 1 and 3');


        if ($this->_specificBenchmark !== null) {
            $this->_benchmark->{$this->_specificBenchmark}->ramMode = $ramMode;
            $this->_benchmark->{$this->_specificBenchmark}->ramBegin = ($startRam !== null) ? $startRam : memory_get_usage();
            $this->_benchmark->{$this->_specificBenchmark}->ramInfoStarted = true;
        } else {
            $this->_benchmark->ramMode = $ramMode;
            $this->_benchmark->ramBegin = ($startRam !== null) ? $startRam : memory_get_usage();
            $this->_benchmark->ramInfoStarted = true;
        }
        return $this;
    }

    public function stopRam() {
        if ($this->_specificBenchmark !== null) {
            if (!$this->_benchmark->{$this->_specificBenchmark}->ramInfoStarted)
                throw new \Exception('Can not stop the benchmark "' . $this->_specificBenchmark . '" of RAM, it\'s has not been started');

            $this->_benchmark->{$this->_specificBenchmark}->ramEnd = memory_get_usage();
            $this->_benchmark->{$this->_specificBenchmark}->ramInfoStopped = true;
        } else {
            if (!$this->_benchmark->ramInfoStarted)
                throw new \Exception('Can not stop the benchmark of RAM, it\'s has not been started');
            $this->_benchmark->ramEnd = memory_get_usage();
            $this->_benchmark->ramInfoStopped = true;
        }
        return $this;
    }

    public function stopTime() {
        if ($this->_specificBenchmark !== null) {
            if (!$this->_benchmark->{$this->_specificBenchmark}->timeInfoStarted)
                throw new \Exception('Can not stop the benchmark "' . $this->_specificBenchmark . '" of Time, it\'s has not been started');

            $this->_benchmark->{$this->_specificBenchmark}->time->stop();
            $this->_benchmark->{$this->_specificBenchmark}->timeInfoStopped = true;
        } else {
            if (!$this->_benchmark->timeInfoStarted)
                throw new \Exception('Can not stop the benchmark of Time, it\'s has not been started');
            $this->_benchmark->time->stop();
            $this->_benchmark->timeInfoStopped = true;
        }
        return $this;
    }

    public function getStatsTime() {
        if ($this->_specificBenchmark !== null) {
            if (!$this->_benchmark->{$this->_specificBenchmark}->timeInfoStopped)
                $this->stopTime();

            return $this->_benchmark->{$this->_specificBenchmark}->time->getInterval();
        } else {
            if (!$this->_benchmark->timeInfoStopped)
                $this->stopTime();

            return $this->time->getInterval();
        }
    }

    public function getStatsRam() {
        if ($this->_specificBenchmark !== null) {
            if (!$this->_benchmark->{$this->_specificBenchmark}->ramInfoStopped)
                $this->stopRam();

            return $this->_calculRamUsage($this->_benchmark->{$this->_specificBenchmark}->ramBegin, $this->_benchmark->{$this->_specificBenchmark}->ramEnd);
        } else {
            if (!$this->_benchmark->ramInfoStopped)
                $this->stopRam();

            return $this->_calculRamUsage($this->_benchmark->ramBegin, $this->_benchmark->ramEnd);
        }
    }

    protected function _registerBenchmark($benchMarkName, $forceRecreate) {
        if (!is_string($benchMarkName))
            throw new \Exception('BenchmarkName parameter must be a string');
        if (!$this->_multiBenchmark)
            throw new \Exception('MultBenchmark isn\'t allowed');
        if (!$forceRecreate) {
            if (property_exists($this->_benchmark, $benchMarkName))
                throw new \Exception('BenchmarkName parameter "' . $benchMarkName . '" was already defined');
        }

        $this->_benchmark->{$benchMarkName} = new \stdClass();
        $this->_benchmark->{$benchMarkName}->time = new \stdClass();
        $this->_benchmark->{$benchMarkName}->ramBegin = 0;
        $this->_benchmark->{$benchMarkName}->ramEnd = 0;
        $this->_benchmark->{$benchMarkName}->ramInfoStarted = false;
        $this->_benchmark->{$benchMarkName}->ramInfoStopped = false;
        $this->_benchmark->{$benchMarkName}->timeInfoStarted = false;
        $this->_benchmark->{$benchMarkName}->timeInfoStopped = false;
        $this->_benchmark->{$benchMarkName}->ramMode = self::RAM_BYTE;
        $this->_benchmark->{$benchMarkName}->ramRoundResult = true;
        $this->_benchmark->{$benchMarkName}->ramRoundResultPrecision = 4;
        $this->_benchmark->{$benchMarkName}->ramRoundResultMode = PHP_ROUND_HALF_UP;
        //$this->_specificBenchmark = $benchMarkName;
    }

    protected function _isRegisteredBenchmark($benchMarkName) {
        return (property_exists($this->_benchmark, $benchMarkName)) ? true : false;
    }

    protected function _calculRamUsage($benginRam, $endRam) {
        $mode = ($this->_specificBenchmark !== null) ? $this->_benchmark->{$this->_specificBenchmark}->ramMode : $this->_benchmark->ramMode;
        switch ($mode) {
            case self::RAM_BYTE:
                $ram = ($endRam - $benginRam);
                break;
            case self::RAM_KB:
                $ram = ($endRam - $benginRam) / 1024;
                break;
            case self::RAM_MB:
                $ram = ($endRam - $benginRam) / 1048576;
                break;
            default:
                throw new \Exception('Invalid ramMode definined');
                break;
        }
        $ramRoundResult = ($this->_specificBenchmark !== null) ? $this->_benchmark->{$this->_specificBenchmark}->ramRoundResult : $this->_benchmark->ramRoundResult;
        if ($ramRoundResult) {
            $ramRoundResultPrecision = ($this->_specificBenchmark !== null) ? $this->_benchmark->{$this->_specificBenchmark}->ramRoundResultPrecision : $this->_benchmark->ramRoundResultPrecision;
            $ramRoundResultMode = ($this->_specificBenchmark !== null) ? $this->_benchmark->{$this->_specificBenchmark}->ramRoundResultMode : $this->_benchmark->ramRoundResultMode;
            $ram = round($ram, $ramRoundResultPrecision, $ramRoundResultMode);
        }

        return $ram;
    }

}

?>