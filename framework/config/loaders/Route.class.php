<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\mvc\Router;
use framework\utility\Validate;
use framework\utility\Tools;

class Route extends Loader {

    public function load(Reader $reader) {
        $routes = $reader->read();
        foreach ($routes as $name => $datas) {
            // Check name
            if (!Validate::isVariableName($name))
                throw new \Exception('Route name must be a valid variable');

            // Check controller info
            if (!isset($datas['controller']))
                throw new \Exception('Miss controller into route "' . $name . '"');

            // Check optionnal parameters
            $forceSsl = isset($datas['forceSsl']) ? is_string($datas['forceSsl']) ? Tools::castValue($datas['forceSsl']) : $datas['forceSsl']  : false;
            $regex = isset($datas['regex']) ? is_string($datas['regex']) ? Tools::castValue($datas['regex']) : $datas['regex']  : false;
            $rules = isset($datas['rules']) ? $datas['rules'] : array();
            if (isset($rules['rule']) && is_array($rules['rule']))
                $rules = $rules['rule'];

            // Check methods
            $methods = isset($datas['methods']) ? $datas['methods'] : array();
            foreach ($methods as $method => $val) {
                //no have parameters, replace wtih empty parameters list
                if (is_int($method)) {
                    //TODO fix : replace methode into good order
                    unset($methods[$method]);
                    $methods[$val] = array();
                }
            }


            // Add into router
            Router::addRoute($name, $datas['controller'], $rules, $methods, $forceSsl, $regex, true);
        }
    }

}

?>