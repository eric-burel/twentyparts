<?php

namespace framework\security;

use framework\security\ISecurity;
use framework\Security;
use framework\Logger;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\network\Http;
use framework\Session;
use framework\mvc\Router;

class Sniffer extends Security implements ISecurity {

    const CRAWLER_BAD = 'bad';
    const CRAWLER_GOOD = 'good';
    const CRAWLER_UNKNOWN = 'unknown';

    protected static $_isRun = false;
    protected $_trapName = 'trap';
    protected $_badCrawlerFile = null;
    protected $_goodCrawlerFile = null;
    protected $_logBadCrawler = false;
    protected $_logGoodCrawler = false;
    protected $_logUnknownCrawler = false;

    public function __construct($options = array()) {
        if (isset($options['trapName']) && Validate::isVariableName($options['trapName']))
            $this->_trapName = $options['trapName'];
        if (isset($options['badCrawlerFile'])) {
            if (!file_exists($options['badCrawlerFile']))
                throw new \Exception('badCrawlerFile does\'t exists');
            if (!Validate::isFileMimeType('xml', $options['badCrawlerFile']))
                throw new \Exception('goodCrawlerFile invalid format, must be xml');
            $this->_badCrawlerFile = $options['badCrawlerFile'];
        }
        if (isset($options['goodCrawlerFile'])) {
            if (!file_exists($options['goodCrawlerFile']))
                throw new \Exception('goodCrawlerFile does\'t exists');
            if (!Validate::isFileMimeType('xml', $options['goodCrawlerFile']))
                throw new \Exception('goodCrawlerFile invalid format, must be xml');
            $this->_goodCrawlerFile = $options['goodCrawlerFile'];
        }

        if (isset($options['logBadCrawler'])) {
            if (!is_bool($options['logBadCrawler']))
                throw new \Exception('logBadCrawler parameter must be a boolean');
            $this->_logBadCrawler = $options['logBadCrawler'];
        }
        if (isset($options['logGoodCrawler'])) {
            if (!is_bool($options['logGoodCrawler']))
                throw new \Exception('logGoodCrawler parameter must be a boolean');
            $this->_logBadCrawler = $options['logGoodCrawler'];
        }
        if (isset($options['logUnknownCrawler'])) {
            if (!is_bool($options['logUnknownCrawler']))
                throw new \Exception('logUnknownCrawler parameter must be a boolean');
            $this->_logUnknownCrawler = $options['logUnknownCrawler'];
        }
    }

    public function run() {
        $ip = Tools::getUserIp();
        $userAgent = Http::getServer('HTTP_USER_AGENT');
        //badcrawler detected
        if (Session::getInstance()->get(md5($ip . 'badcrawler')))
            Router::getInstance()->show403(true);

        $this->_check($ip, $userAgent);
        Logger::getInstance()->debug('Sniffer security was run', 'security');
    }

    protected function _check($ip, $userAgent) {
        if (Http::getQuery($this->_trapName) && !Validate::isGoogleBot()) {
            $isBadCrawler = false;
            $isGoodCrawler = false;

            if ($this->_badCrawlerFile) {
                $badCrawlerXml = simplexml_load_file($this->_badCrawlerFile);
                if (is_null($badCrawlerXml) || !$badCrawlerXml)
                    throw new \Exception('Invalid xml file : "' . $this->_badCrawlerFile . '"');
            }
            if ($this->_goodCrawlerFile) {
                $goodCrawlerXml = simplexml_load_file($this->_goodCrawlerFile);
                if (is_null($goodCrawlerXml) || !$goodCrawlerXml)
                    throw new \Exception('Invalid xml file : "' . $this->_goodCrawlerFile . '"');
            }

            if ($badCrawlerXml) {
                $badCrawlerList = $badCrawlerXml->crawler;
                foreach ($badCrawlerList as $crawler) {
                    if (isset($crawler->ip) && (string) $crawler->ip == $ip)
                        $isBadCrawler = true;
                    if (isset($crawler->userAgent) && strripos((string) $crawler->userAgent, $userAgent) !== false)
                        $isBadCrawler = true;
                    if ($isBadCrawler) {
                        $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
                        Session::getInstance()->add(md5($ip . 'badcrawler'), true, true, true);
                        Router::getInstance()->show403(true);
                        break;
                    }
                }
                unset($crawler);
            }
            if ($goodCrawlerXml) {
                $goodCrawlerList = $goodCrawlerXml->crawler;
                foreach ($goodCrawlerList as $crawler) {
                    if (isset($crawler->ip) && (string) $crawler->ip == $ip)
                        $isGoodCrawler = true;
                    if (isset($crawler->userAgent) && strripos((string) $crawler->userAgent, $userAgent) !== false)
                        $isGoodCrawler = true;
                    if ($isGoodCrawler) {
                        $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
                        break;
                    }
                }
                unset($crawler);
            }
            // unknown
            if (!$isBadCrawler && !$isGoodCrawler)
                $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
        }
    }

    protected function _catch($ip, $userAgent, $type) {
        $log = false;
        if ($this->_logBadCrawler && $type == self::CRAWLER_BAD)
            $log = true;
        if ($this->_goodCrawlerFile && $type == self::CRAWLER_GOOD)
            $log = true;
        if ($this->_logUnknownCrawler && $type == self::CRAWLER_UNKNOWN)
            $log = true;

        if ($log)
            Logger::getInstance()->warning($type . ' crawler detected, ip : "' . $ip . '" and user-agent : "' . $userAgent . '"');
    }

    public function stop() {
        self::$_isRun = false;
        Logger::getInstance()->debug('Sniffer security was stopped', 'security');
    }

}

?>