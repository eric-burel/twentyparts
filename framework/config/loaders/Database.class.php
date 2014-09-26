<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\Database as DatabaseManager;
use framework\database\Server;
use framework\utility\Validate;
use framework\utility\Tools;

class Database extends Loader {

    public function load(Reader $reader) {
        $databases = $reader->read();
        foreach ($databases as $name => $datas) {
            // Check name
            if (!Validate::isVariableName($name))
                throw new \Exception('Name of database must be a valid variable name');

            // Check essential parameters
            if (!isset($datas['adaptater']))
                throw new \Exception('Miss adaptater config param for database : "' . $name . '"');
            if (!isset($datas['type']))
                throw new \Exception('Miss type config param for database : "' . $name . '"');
            if (!isset($datas['server']))
                throw new \Exception('Miss server config param for database : "' . $name . '"');

            // Create database instance
            $database = new DatabaseManager($name, $datas['type'], DatabaseManager::factory($datas['adaptater'], $name, 'framework\database\adaptaters', 'framework\database\IAdaptater'));

            // Fetch servers
            foreach ($datas['server'] as $server) {
                // extract server informations
                extract($server);
                // extract dsn
                if (isset($dsn))
                    extract(Tools::parseDsn($dsn));

                // check required infos
                if (!isset($type))
                    throw new \Exception('Miss server type');
                if (!isset($dbuser))
                    throw new \Exception('Miss server dbuser type');
                if (!isset($dbpassword))
                    throw new \Exception('Miss server dbpassword type');
                if (!isset($driver))
                    throw new \Exception('Miss driver type');
                if (!isset($host))
                    throw new \Exception('Miss server host type');
                if (!isset($port))
                    throw new \Exception('Miss server port type');
                if (!isset($dbname))
                    throw new \Exception('Miss server dbname type');
                if (!isset($charset))
                    throw new \Exception('Miss server charset type');


                // Check driver is supported by database adaptater
                if (!$database->isValidDriver($driver))
                    throw new \Exception('Invalid driver : "' . $driver . '", not supported database adaptater : "' . $datas['adaptater'] . '"');

                // Create server instance
                $serverInstance = new Server($type, $dbuser, $dbpassword, $driver, $host, $port, $dbname, $charset);
                if (isset($dsn))
                    $serverInstance->setDsn($dsn, false);

                // Add into servers list
                $database->addServer($serverInstance);

                //flush vars
                unset($type, $dbuser, $dbpassword, $driver, $host, $port, $dbname, $charset, $dsn, $serverInstance);
            }


            // Add database
            DatabaseManager::addDatabase($name, $database, true);
        }
    }

}

?>
