<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\Cache as CacheManager;

class Cache extends Loader {

    public function load(Reader $reader) {
        $caches = $reader->read();
        foreach ($caches as $cacheName => $cacheValue) {
            // Check name
            if (!Validate::isVariableName($cacheName))
                throw new \Exception('Name of cache must be a valid variable name');

            // Check options
            $params = array();
            foreach ($cacheValue as $name => $value) {
                // no use comment (for xml file)
                if ($name == 'comment')
                    continue;

                // cast
                if (is_string($value))
                    $value = Tools::castValue($value);

                // add value into cache parameters
                $params[$name] = $value;
            }
            // check adaptater
            if (!isset($params['adaptater']))
                throw new \Exception('Miss adaptater parameter for cache : "' . $cacheName . '"');

            // Add param name
            $params['name'] = $cacheName;

            // Add cache
            CacheManager::addCache($cacheName, CacheManager::factory($params['adaptater'], $params, 'framework\cache\adaptaters', 'framework\cache\IAdaptater'), true);
        }
    }

}

?>
