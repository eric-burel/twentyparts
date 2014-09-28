<?php

namespace framework\mvc;

use framework\Logger;
use framework\network\Http;
use framework\network\http\ResponseCode;
use framework\network\http\Header;
use framework\utility\Benchmark;
use framework\mvc\Template;
use framework\Application;
use framework\Language;

class Router {

    use \framework\pattern\Singleton;

    protected static $_routes = null;
    protected static $_host = '';
    protected $_controllersNamespace = 'controllers';
    protected $_namespaceSeparator = '\\';
    protected $_urlParameterKey = false;
    protected $_currentRoute = null;
    protected $_currentRule = null;
    protected $_controller = null;

    protected function __construct() {
        if (Application::getProfiler())
            Benchmark::getInstance('router')->startTime()->startRam();

        Logger::getInstance()->addGroup('router', 'Router Benchmark and Informations', true);
    }

    public static function addRoute($name, $controller, $rules = array(), $methods = array(), $forceSsl = false, $regex = false, $forceReplace = false) {
        if (!is_string($name) && !is_int($name))
            throw new \Exception('Route name must be string or integer');

        if (method_exists(self::$_routes, $name)) {
            if (!$forceReplace)
                throw new \Exception('Route : "' . $name . '" already defined');

            Logger::getInstance()->debug('Route : "' . $name . '" already defined, was overloaded');
        }
        if (!is_object(self::$_routes))
            self::$_routes = new \stdClass();

        self::$_routes->$name = new \stdClass();
        self::$_routes->$name->name = $name;
        self::$_routes->$name->forceSsl = $forceSsl;
        self::$_routes->$name->regex = $regex;
        self::$_routes->$name->controller = $controller;
        self::$_routes->$name->rules = $rules;
        self::$_routes->$name->methods = $methods;
    }

    public static function getRoute($routeName) {
        if (array_key_exists($routeName, self::$_routes))
            return self::$_routes->$routeName;

        return false;
    }

    public static function getRoutes() {
        return self::$_routes;
    }

    public function runRoute($routeName, $vars = array(), $die = false) {
        $route = self::getRoute($routeName);
        if ($route) {
            $this->_setCurrentRoute($routeName);
            if (!$route->controller)
                throw new \Exception('Route : "' . $routeName . '" missing datas : controller');

            Logger::getInstance()->debug('Run route : "' . $routeName . '"', 'router');
            $this->runController($route->controller, $route->methods, $vars);
        }
        if ($die)
            exit();
    }

    public static function getUrl($routeName, $vars = array(), $lang = null, $ssl = false, $ruleNumber = null, $varsSeparator = '/', $default = '') {
        $route = self::getRoute($routeName);

        if (!is_array($vars))
            throw new \Exception('Route : "' . $routeName . '" vars must be an array');

        //no exist route
        if (!$route)
            return $default;

        //config lang and ssl
        if ($lang === null)
            $lang = Language::getInstance()->getLanguage();
        if ($route->forceSsl)
            $ssl = true;

        if (empty($route->rules))
            return self::getHost(true, $ssl);

        $ruleCount = 0;
        foreach ($route->rules as &$rule) {
            $matchedRule = self::_matchRule($route, $rule, $lang, $vars, $varsSeparator, $ruleNumber, $ruleCount);
            if ($matchedRule !== false)
                break;

            $ruleCount++;
        }
        // no matched rule, return rule 1
        $matchedRule = self::_matchRule($route, $rule, $lang, $vars, $varsSeparator, 1, 1);

        if ($matchedRule !== false)
            return self::getHost(true, $ssl) . $matchedRule;

        return $default;
    }

    public static function getUrls($lang = null, $ssl = false) {
        $urls = new \stdClass();
        foreach (self::$_routes as $route)
            $urls->{$route->name} = self::getUrl($route->name, array(), $lang, $ssl);

        return $urls;
    }

    public static function setHost($host) {
        if (!is_string($host))
            throw new \Exception('Host must a string');

        self::$_host = $host . ((substr($host, -1) != '/') ? '/' : '');
    }

    public static function getHost($url = false, $ssl = false, $stripLastSlash = false, $stripFirstSlash = false) {
        $host = self::$_host;
        if ($stripLastSlash)
            $host = rtrim($host, '/');
        if ($stripFirstSlash)
            $host = ltrim($host, '/');

        if ($url)
            return 'http' . ($ssl ? 's' : '') . '://' . $host;

        return $host;
    }

    public function setControllersNamespace($namespace, $namespaceSeparator = '\\') {
        if (!is_string($namespace))
            throw new \Exception('Controllers namespace must a string');
        $this->_controllersNamespace = $namespace;
        if (!is_string($namespaceSeparator))
            throw new \Exception('Namespace separator must be must a string');
        $this->_namespaceSeparator = $namespaceSeparator;
    }

    public function getControllersNamespace($withSeparator = false) {
        $ns = $this->_controllersNamespace;
        if ($withSeparator)
            $ns .= $this->getNamespaceSeparator();
        return $ns;
    }

    public function getNamespaceSeparator() {
        return $this->_namespaceSeparator;
    }

    public function setUrlParameterKey($key) {
        if (!is_int($key) && !Validate::isVariableName($key))
            throw new \Exception('Url parameter name must be an integer or a valid variable name');
        $this->_urlParameterKey = $key;
    }

    public function run() {
        if (empty(self::$_routes))
            throw new \Exception('No routes defined');

        //get http request URI (delete hostname)
        if (!$this->_urlParameterKey)
            $request = str_replace(self::getHost(), '', Http::getServer('HTTP_HOST') . Http::getServer('REQUEST_URI'));
        else//Or get url key parameter
            $request = Http::getQuery($this->urlParameterKey, '');

        Logger::getInstance()->debug('Run router for request : "' . $request . '"', 'router');
        $routeMatch = false;
        $routeIndex = self::getRoute('index');
        if ($request === '' && $routeIndex) {
            $routeMatch = true;
            $this->runRoute('index');
        } else {
            // each routes
            foreach (self::$_routes as $route) {
                $vars = array();
                // Check if have rules
                if (!$route->rules)
                    continue;

                // each route rules
                foreach ($route->rules as &$rule) {
                    Logger::getInstance()->debug('Try rule: "' . $rule . '"', 'router');
                    if ($route->regex)
                        $routeMatch = (boolean) preg_match('`^' . $rule . '$`iu', $request, $vars);
                    else
                        $routeMatch = ($request == $rule);


                    if ($routeMatch) {
                        $this->_setCurrentRule($rule);
                        Logger::getInstance()->debug('Match route : "' . $route->name . '" with rule : "' . $rule . '"', 'router');
                        break;
                    }
                }
                // If don't match, pass to next route
                if (!$routeMatch)
                    continue;

                // run route, and break
                if ($routeMatch) {
                    $this->runRoute($route->name, $vars);
                    break;
                }
            }
        }

        if (!$routeMatch) {
            Logger::getInstance()->debug('No route find', 'router');
            $this->show404();
        }
    }

    public function runController($controller, $methods = array(), $vars = array()) {
        $controllerExplode = explode($this->getNamespaceSeparator(), (string) $controller);
        if (is_array($controllerExplode) && count($controllerExplode) > 1) {
            $controllerName = $this->getNamespaceSeparator() . ucfirst(array_pop($controllerExplode));
            $controller = implode($this->getNamespaceSeparator(), $controllerExplode) . $controllerName;
        } else
            $controller = (string) ucfirst($controller);

        Logger::getInstance()->debug('Run controller : "' . $controller . '"', 'router');
        $controllerClass = $this->getControllersNamespace(true) . $controller;

        // Check if controller exists (with controllers namespace)
        if (!class_exists($controllerClass))
            throw new \Exception('Controller "' . $controllerClass . '" not found');
        $controller = $controllerClass;

        if (!is_array($vars))
            throw new \Exception('Controller : "' . $controller . '" vars must be an array');
        if (!is_array($methods))
            throw new \Exception('Controller : "' . $controller . '" methodes must be an array');

        $inst = new \ReflectionClass($controller);
        if ($inst->isInterface() || $inst->isAbstract())
            throw new \Exception('Controller "' . $controller . '" cannot be an interface of an abstract class');

        $ctrl = $inst->newInstance();
        if ($ctrl->getAutoCallDisplay()) {
            if (!$inst->hasMethod('display'))
                throw new \Exception('Controller "' . $controller . '" must be implement method "Diplay');
            if (!$inst->hasMethod('initTemplate'))
                throw new \Exception('Controller "' . $controller . '" must be implement method "initTemplate');
        }

        if ($methods) {
            foreach ($methods as $methodName => $methodParams) {
                Logger::getInstance()->debug('Call method : "' . $methodName . '"', 'router');
                if (!method_exists($ctrl, $methodName) || !$inst->getMethod($methodName)->isPublic())
                    throw new \Exception('Method "' . $methodName . '" don\'t exists or isn\'t public on controller "' . $controller . '"');

                $args = array();
                if (!is_array($methodParams))
                    $args[] = $methodParams;
                else {
                    foreach ($methodParams as $parameter) {
                        //check if is [['key']] type, or direct value
                        if (stripos($parameter, '[[') === false)
                            $args[] = $parameter;
                        else {
                            if (count($vars) > 0) {
                                $key = (int) str_replace(array('[', ']'), '', $parameter);
                                if (array_key_exists($key, $vars))
                                    $args[] = $vars[$key];
                            } else
                                $args[] = $parameter;
                        }
                    }
                }

                foreach ($args as $arg)
                    Logger::getInstance()->debug('Add argument : "' . $arg . '"', 'router');
                // Call method with $args
                \call_user_func_array(array($ctrl, $methodName), $args);
            }
        }

        $this->_controller = $ctrl;
        //call display only when have a template
        if ($ctrl->getAutoCallDisplay() && Template::getTemplate()) {
            Logger::getInstance()->debug('Call method "display"', 'router');
            $ctrl->display();
        }
    }

    public function show400($die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_BAD_REQUEST, true);
        $this->runRoute('error', array(1 => 400), $die);
    }

    public function show401($die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_UNAUTHORIZED, true);
        $this->runRoute('error', array(1 => 401), $die);
    }

    public function show403($die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_FORBIDDEN, true, true, Http::PROTOCOL_VERSION_1_0);
        $this->runRoute('error', array(1 => 403), $die);
    }

    public function show404($die = false) {
        // Set Header
        // Use http protocol 1.0 look this : http://stackoverflow.com/questions/2769371/404-header-http-1-0-or-1-1
        // And http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        // If use Http 1.1 protocol, header connection is keep-alive, else is close
        Header::setResponseStatusCode(ResponseCode::CODE_NOT_FOUND, true, true, Http::PROTOCOL_VERSION_1_0);
        $this->runRoute('error', array(1 => 404), $die);
    }

    public function show405($die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_METHOD_NOT_ALLOWED, true, true, Http::PROTOCOL_VERSION_1_0);
        $this->runRoute('error', array(1 => 404), $die);
    }

    public function show500($die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_INTERNAL_SERVER_ERROR, true);
        $this->runRoute('error', array(1 => 500), $die);
    }

    public function show503($die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_SERVICE_UNAVAILABLE, true);
        $this->runRoute('error', array(1 => 503), $die);
    }

    public function showDebugger($isException, $die = false) {
        Header::setResponseStatusCode(ResponseCode::CODE_INTERNAL_SERVER_ERROR, true);
        $this->runRoute('debugger', array(1 => $isException), $die);
    }

    public function getCurrentRule() {
        return $this->_currentRule;
    }

    public function getCurrentRoute() {
        return $this->_currentRoute;
    }

    protected static function _matchRule($route, $rule, $lang, $vars, $varsSeparator, $ruleNumber, $ruleCount) {
        $matched = false;
        $url = '';
        $args = preg_split('#(\(.+\))#iuU', $rule);
        foreach ($args as $key => $value) {
            //match by lang
            if ($lang !== null && $key == 0 && (stripos($value, $lang . $varsSeparator) !== false  || $lang . $varsSeparator == $value || $lang == $value))
                $matched = true;
            // only one rule or rule number
            elseif (count($route->rules) == 1 || $ruleNumber === $ruleCount)
                $matched = true;

            //add argument (if exist)
            if ($matched) {
                $arg = array_key_exists($key, $vars) && $route->regex ? rawurlencode($vars[$key]) : '';
                //empty arg
                if ($arg == '' && $value == $varsSeparator)
                    continue;

                $url .= $value . $arg;
            }
        }
        if (!empty($url))
            return rtrim($url, $varsSeparator);

        return $matched;
    }

    protected function _setCurrentRoute($currentRoute) {
        $this->_currentRoute = $currentRoute;
    }

    protected function _setCurrentRule($currentRule) {
        $this->_currentRule = $currentRule;
    }

}

?>