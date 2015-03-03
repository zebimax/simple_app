<?php

/**
 * @method static Macaw get(string $route, Callable $callback)
 * @method static Macaw post(string $route, Callable $callback)
 * @method static Macaw put(string $route, Callable $callback)
 * @method static Macaw delete(string $route, Callable $callback)
 * @method static Macaw options(string $route, Callable $callback)
 * @method static Macaw head(string $route, Callable $callback)
 */
class Macaw
{
    public static $app = array(); // todo move to class
    public static $ulrBase = '/app/index.php';
    public static $halts = false;
    public static $routes = array();
    public static $methods = array();
    public static $callbacks = array();
    public static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );
    public static $error_callback;
    private static $isConsole = false;
    private static $consoleUsage = array();
    /**
     * Defines a route w/ callback and method
     */
    public static function __callstatic($method, $params) 
    {
        $uri = self::$ulrBase.$params[0];
        $callback = $params[1];
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $callback);
    }
    /**
     * Defines callback if route is not found
    */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }
    
    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }
    /**
     * Runs the callback for the given request
     */
    public static function dispatch()
    {
        if (PHP_SAPI == 'cli') {
            self::$isConsole = true;
            if (!isset($_SERVER['argv'])) {
                throw new \Exception('Can\'t create console request');
            }
            $consoleFile = array_shift($_SERVER['argv']);
            $parseUrl = isset($_SERVER['argv'][0]) ? ' ' . $_SERVER['argv'][0] : '';
            $method = 'CONSOLE';
        } else {
            $parseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $method = $_SERVER['REQUEST_METHOD'];
        }
        $uri = self::$ulrBase . $parseUrl;
        self::setKey('php_url_path', basename($parseUrl));
        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);
        $found_route = false;
        // check if route is defined without regex
        if (in_array($uri, self::$routes)) {
            $route_pos = array_keys(self::$routes, $uri);
            foreach ($route_pos as $route) {
                if (self::$methods[$route] == $method) {
                    $found_route = true;
                    //if route is not an object 
                    if(!is_object(self::$callbacks[$route])){
                        //grab all parts based on a / separator 
                        $parts = explode('/',self::$callbacks[$route]); 
                        //collect the last index of the array
                        $last = end($parts);
                        //grab the controller name and method call
                        $segments = explode('@',$last);
                        //instanitate controller
                        $controller = new $segments[0]();
                        //call method
                        $controller->$segments[1](); 
                        
                        if (self::$halts) return;
                        
                    } else {
                        //call closure
                        call_user_func(self::$callbacks[$route]);
                        
                        if (self::$halts) return;
                    }
                }
            }
        } else {
            // check if defined with regex
            $pos = 0;
            foreach (self::$routes as $route) {
                if (strpos($route, ':') !== false) {
                    $route = str_replace($searches, $replaces, $route);
                }
                if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                    if (self::$methods[$pos] == $method) {
                        $found_route = true;
                        array_shift($matched); //remove $matched[0] as [1] is the first parameter.
                        if(!is_object(self::$callbacks[$pos])){
                            //grab all parts based on a / separator 
                            $parts = explode('/',self::$callbacks[$pos]); 
                            //collect the last index of the array
                            $last = end($parts);
                            //grab the controller name and method call
                            $segments = explode('@',$last); 
                            //instanitate controller
                            $controller = new $segments[0]();
                            //call method and pass any extra parameters to the method
                            $controller->$segments[1](implode(",", $matched)); 
    
                            if (self::$halts) return;
                        } else {
                            call_user_func_array(self::$callbacks[$pos], $matched);
                            
                            if (self::$halts) return;
                        }
                        
                    }
                }
            $pos++;
            }
        }
 
        // run the error callback if the route was not found
        if ($found_route == false) {
            if (!self::$isConsole) {
                if (!self::$error_callback) {
                    self::$error_callback = function() {
                        header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
                        echo '404';
                    };
                }
                call_user_func(self::$error_callback);
            } else {
                echo 'Console usage:' . PHP_EOL . implode(PHP_EOL, self::$consoleUsage);
            }
        }
    }

    public static function getKey($key, $default = null)
    {
        $result = $default;
        if (isset(self::$app[$key])) {
            $result = self::$app[$key];
        }
        return $result;
    }

    public static function setKey($key, $value)
    {
        self::$app[$key] = $value;
    }

    public static function getConfig($key, $default = null, array $params = array())
    {
        $result = $default;
        if (isset(self::$app['config'][$key])) {
            $result = self::$app['config'][$key];
        } elseif (isset(self::$app['app_config'][$key])) {
            $result = self::$app['app_config'][$key];
        }
        return is_callable($result) ? $result($params) : $result;
    }

    /**
     * @param $key
     * @param array $params
     * @return null
     */
    public static function getInsertedConfig($key, array $params = array())
    {
        $keys = explode('/', (string)$key);
        $params = array_values($params) + array_fill_keys(array_keys($keys), null);
        $result = Macaw::getConfig($keys[0], null, (array)$params[0]);

        foreach (array_slice($keys, 1, null, true) as $keysKey => $keysValue) {
            if (isset($result[$keysValue])) {
                $result = is_callable($result[$keysValue]) ? $result[$keysValue]($params[$keysKey]) : $result[$keysValue];
            } else {
                return null;
            }
        }
        return $result;

    }

    public static function console($route, $callback)
    {
        $uri = self::$ulrBase. ' ' . $route;
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper('console'));
        array_push(self::$callbacks, $callback);
        array_push(self::$consoleUsage, $route);
    }
}
