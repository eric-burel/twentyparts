<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\mvc\Router;
use framework\mvc\router\Route as RouterRoute;
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

            // create instance of route 
            $route = new RouterRoute($name, $datas['controller']);

            // Optionnals parameters
            if (isset($datas['regex']))
                $route->setRegex(Tools::castValue($datas['regex']));

            if (isset($datas['requireSsl']))
                $route->setRequireSsl(Tools::castValue($datas['requireSsl']));

            if (isset($datas['requireAjax']))
                $route->setRequireAjax(Tools::castValue($datas['requireAjax']));

            if (isset($datas['autoSetAjax']))
                $route->setAutoSetAjax(Tools::castValue($datas['autoSetAjax']));

            if (isset($datas['requireHttpMethod']))
                $route->setRequireHttpMethod(Tools::castValue($datas['requireHttpMethod']));

            if (isset($datas['httpResponseStatusCode']))
                $route->setHttpResponseStatusCode(Tools::castValue($datas['httpResponseStatusCode']));

            if (isset($datas['httpProtocol']))
                $route->setHttpProtocol(Tools::castValue($datas['httpProtocol']));

            if (isset($datas['rules'])) {
                if (is_array($datas['rules'])) {
                    if (isset($datas['rules']['rule']) && is_array($datas['rules']['rule']))
                        $datas['rules'] = $datas['rules']['rule'];
                }
                $route->setRules($datas['rules']);
            }

            if (isset($datas['methods'])) {
                if (is_array($datas['methods'])) {
                    $methods = $datas['methods'];
                    foreach ($methods as $method => $val) {
                        //no have parameters, replace wtih empty parameters list
                        if (is_int($method)) {
                            //TODO fix : replace methode into good order
                            unset($methods[$method]);
                            $methods[$val] = array();
                        }
                    }
                    $route->setMethods($methods);
                }
            }

            // Add into router
            Router::addRoute($route, true);
        }
    }

}

?>