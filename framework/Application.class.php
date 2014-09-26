<?php

namespace framework;

use framework\Autoloader;
use framework\Cli;
use framework\Cache;
use framework\Database;
use framework\Language;
use framework\Logger;
use framework\database\Server;
use framework\error\ErrorManager;
use framework\error\ExceptionManager;
use framework\mvc\Router;
use framework\mvc\Template;
use framework\network\Http;
use framework\utility\Benchmark;

final class Application {

    use pattern\Singleton,
        debugger\Debug;

    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    protected static $_env = self::ENV_DEV;
    protected static $_profiler = true;
    protected $_isInit = false;
    protected $_isRun = false;
    protected static $_globalizeClassList = array(
        'framework\Config',
        'framework\config\Reader',
        'framework\config\Loader',
        'framework\mvc\Controller',
        'framework\Logger',
        'framework\mvc\Router',
        'framework\error\ErrorManager',
        'framework\error\ExceptionManager'
    );

    public static function getGlobalizeClassList() {
        return self::$_globalizeClassList;
    }

    public static function setEnv($env) {
        if ($env != self::ENV_DEV && $env != self::ENV_TEST && $env != self::ENV_PROD)
            throw new \Exception('Invalid environnement type');

        if ($env == self::ENV_DEV)
            self::setDebug(true);
        if ($env == self::ENV_DEV || $env == self::ENV_TEST)
            self::setProfiler(true);

        self::$_env = $env;
    }

    public static function getEnv() {
        return self::$_env;
    }

    public static function setProfiler($bool) {
        self::$_profiler = $bool;
    }

    public static function getProfiler() {
        return self::$_profiler;
    }

    protected function __construct($boostrapFile) {
        // Start benchmark
        Benchmark::getInstance('global')->startTime(Benchmark::TIME_MS, microtime(true))->startRam(Benchmark::RAM_MB, memory_get_usage());

        if (!file_exists($boostrapFile))
            throw new \Exception('Invalid bootstrap file');

        require $boostrapFile;

        $this->_isInit = true;
    }

    public function __destruct() {
        $this->stop();
    }

    public function stop() {
        if ($this->_isInit && $this->_isRun) {
            // run caches gc
            $caches = Cache::getCaches();
            foreach ($caches as $cache)
                $cache->runGc();

            //profiling
            if (self::getProfiler()) {
                // Caches
                foreach ($caches as $cache)
                    Logger::getInstance()->debug('Adaptater : "' . get_class($cache) . '"', 'cache' . $cache->getName());

                // Databases
                $databases = Database::getDatabases();
                foreach ($databases as $database) {
                    Logger::getInstance()->debug('Type : ' . $database->getType(), 'database' . $database->getName());
                    Logger::getInstance()->debug('Adaptater : ' . get_class($database->getAdaptater()), 'database' . $database->getName());
                    $stats = $database->getStats();
                    Logger::getInstance()->debug('Queries : ' . (string) $database->getQueryCount() . ' (Aproximately memory used  : ' . $stats['ram'] . ' KB in aproximately ' . $stats['time'] . ' ms)', 'database' . $database->getName());
                    Logger::getInstance()->debug('Servers : ' . $database->countServers() . ' (Masters : ' . $database->countServers(Server::TYPE_MASTER) . '  Slaves : ' . $database->countServers(Server::TYPE_SLAVE) . ')', 'database' . $database->getName());
                }

                // Templates
                $templates = Template::getTemplates();
                foreach ($templates as $template)
                    Logger::getInstance()->debug('Adaptater : ' . get_class($template), 'template' . $template->getName());

                // Language
                Logger::getInstance()->debug('Language default is : "' . Language::getInstance()->getDefaultLanguage() . '"', 'language');
                Logger::getInstance()->debug(Language::getInstance()->countVars() . ' vars defined', 'language');

                // Router
                Logger::getInstance()->debug('Current url : ' . Http::getCurrentUrl(), 'router');
                Logger::getInstance()->debug('Current route : ' . Router::getInstance()->getCurrentRoute(), 'router');
                Logger::getInstance()->debug('Current route rule : ' . Router::getInstance()->getCurrentRule(), 'router');
                Logger::getInstance()->debug('Request dispatched in aproximately : ' . Benchmark::getInstance('router')->stopTime()->getStatsTime() . ' ms', 'router');
                Logger::getInstance()->debug('Aproximately memory used  : ' . Benchmark::getInstance('router')->stopRam()->getStatsRam() . ' KB', 'router');

                // Logger debug informations and benchmark
                Logger::getInstance()->addGroup('logger', 'Logger Benchmark and Informations', true);
                Logger::getInstance()->debug(Logger::getInstance()->countObservers() . ' observers registered', 'logger');
                Logger::getInstance()->debug(Logger::getInstance()->countGroups() . ' groups and ' . (Logger::getInstance()->countLogs() + 3) . ' logs', 'logger');
                Logger::getInstance()->debug('In aproximately ' . Benchmark::getInstance('logger')->stopTime()->getStatsTime() . ' ms', 'logger');
                Logger::getInstance()->debug('Aproximately memory used  : ' . Benchmark::getInstance('logger')->stopRam()->getStatsRam() . ' KB', 'logger');

                // Autoloader
                Logger::getInstance()->addGroup('autoloader', 'Autoloader report', true);
                $logs = Autoloader::getLogs();
                foreach ($logs as &$log)
                    Logger::getInstance()->debug($log, 'autoloader');
                Logger::getInstance()->debug(count(Autoloader::getAutoloaders()) . ' autoloader adaptaters, ' . count(Autoloader::getDirectories()) . ' directories and ' . count(Autoloader::getNamespaces()) . ' namespaces registered', 'autoloader');
                Logger::getInstance()->debug('Loading ' . count(Autoloader::getClasses()) . ' classes (' . Autoloader::countGlobalizedClasses() . ' globalized classes)  in aproximately ' . round(Autoloader::getBenchmark('time') * 1000, 4) . ' ms', 'autoloader');
                Logger::getInstance()->debug('Aproximately memory used  : ' . round(Autoloader::getBenchmark('memory') / 1024, 4) . ' KB', 'autoloader');
                Autoloader::purgeLogs();
                Autoloader::purgeBenchmark();

                // Global informations && Benchmark
                Logger::getInstance()->addGroup('global', 'Global Benchmark and Informations', true);
                Logger::getInstance()->debug('Page generated in aproximately : ' . Benchmark::getInstance('global')->stopTime()->getStatsTime() . ' ms', 'global');
                Logger::getInstance()->debug('Aproximately memory used  : ' . Benchmark::getInstance('global')->stopRam()->getStatsRam() . ' KB - Memory allocated : ' . memory_get_peak_usage(true) / 1024 . ' KB', 'global');
            }

            //notify logger
            Logger::getInstance()->notify();
            // Stop managers
            ExceptionManager::getInstance()->stop();
            ErrorManager::getInstance()->stop();


            // avoid multi call
            $this->_isInit = false;
            $this->_isRun = false;
        }
    }

    public function run() {
        if ($this->_isRun)
            throw new \Exception('Application already runned');
        //Cli
        if (Cli::isCli())
            throw new \Exception('CLI not yet');

        // Check maitenance mode activated => show503
        if (defined('SITE_MAINTENANCE') && SITE_MAINTENANCE)
            Router::getInstance()->show503();
        else // Run router
            Router::getInstance()->run();

        $this->_isRun = true;
    }

}

?>