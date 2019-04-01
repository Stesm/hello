<?
namespace Core\Helpers;
use Core\Prototypes\Controller;

/**
 * Class Route
 *
 * @property string $path readonly
 * @property string $host readonly
 *
 * @package Core\Helpers
 */
class Route {

    private $host;
    private $run_stack = [];
    private $uri = [];

    /** @var array $middlewares */
    private static $middlewares = [];

    /** @var string $psr_prefix */
    private static $psr_prefix = '\App\Controllers\\';

    /** @var array $routes */
    private static $routes = [];

    /** @var null|self $instance */
    private static $instance = null;

    /**
     * Route constructor.
     */
    private function __construct()
    {
        $this->host = @$_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
        $this->uri = parse_url($_SERVER['REQUEST_URI']);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($name == 'host')
            return $this->host;

        if ($name == 'path')
            return $this->uri['path'];

        throw new \Exception("Property {$name} does not exists");
    }

    /**
     * @param $name
     * @param $path
     * @param $handler
     * @throws \Exception
     */
    public static function set($name, $path, $handler)
    {
        if(is_string($handler) && is_callable(self::$psr_prefix.$handler))
            $handler = self::$psr_prefix.$handler;

        if (array_key_exists($name, self::$routes))
            throw new \Exception("Route {$name} already exists.");

        self::$routes[$name] = [
            'type' => 'route',
            'path' => $path,
            'callable' => $handler
        ];
    }

    public static function middleware($path, \Closure $middleware)
    {
        self::$middlewares[] = [
            'type' => 'middleware',
            'path' => $path,
            'callable' => $middleware
        ];
    }

    /**
     * @throws \Throwable
     */
    public function start()
    {
        if(!count(self::$routes))
            throw new \Exception('No one route on this site.');

        foreach(self::$routes as $rout)
            if($this->check_masks(['rs' => $rout['path']], $vars)) {
                $route = $rout + [
                    'data' => $vars
                ];
                break;
            }

        if(!isset($route))
            throw new \Exception('No one route for this path.');

        foreach(self::$middlewares as $middleware)
            if($middleware['path'] == '*' || $r = $this->check_masks(['rs' => $middleware['path']]))
                if(is_callable($middleware['callable']))
                    $this->run_stack[] = $middleware;

        $this->run_stack[] = $route;
        $this->next();
    }

    /**
     * @throws \Throwable
     */
    private function next()
    {
        $action = array_shift($this->run_stack);
        $handler = &$action['callable'];

        if (is_string($handler) && strpos($handler, '@')) {
            [$class, $method] = explode('@', $handler);

            $object = new \ReflectionClass(self::$psr_prefix.$class);

            if ($object->isSubclassOf(Controller::class)) {
                $object->getMethod('before')->invoke($object->newInstance());
                echo $object->getMethod($method)->invokeArgs($object->newInstance(),$action['data'] ?? []);
            } elseif ($object->hasMethod($method)) {
                echo $object->getMethod($method)->invokeArgs($object->newInstance(),$action['data'] ?? []);
            } else
                throw new \Exception("Cant start controller:{$class}, method:{$method} not found");
        } elseif (is_callable($handler) && $handler instanceof \Closure){
            if ($action['type'] == 'route')
                echo call_user_func_array($handler, $action['data'] ?? []);
            else
                echo call_user_func_array($handler, ['next' => function() {
                    $this->next();
                }]);
        }
    }

    private function check_masks($masks = [], &$vars = [], $greed = true)
    {
        $vars = [];
        if(count($masks) > 0){
            foreach($masks as $m_name => $_masks){
                if(!is_array($_masks))
                    $_masks = [$_masks];

                foreach($_masks as $mask){
                    if(preg_match_all('/(#([a-z0-9_-]+)#)+/i', $mask, $matches)){
                        $needles = array_fill(0, count($matches[1]), '([\s_a-zа-я0-9-]+)');
                        $mask = str_replace($matches[1], $needles, $mask);
                    }

                    $mask = '/^'.str_replace(['/', '*'], ['\/', '.*?'], $mask).''.($greed ? '$' : '').'/iu';

                    if(preg_match_all($mask, urldecode($this->uri['path']), $_matches)){
                        foreach($matches[2] as $n => $key)
                            $vars[$key] = $_matches[$n+1][0];
                        return $m_name;
                    }
                }
            }
        }
        return '';
    }

    public function getCurrentPath()
    {
        return $this->uri['path'];
    }

    public static function redirect($path, $code = 301)
    {
         return header('Location: '.$path, 1, intval($code) ? intval($code) : 301) and die();
    }

    /**
     * @param string $route_name
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public static function buildUri(string $route_name, $data = []) : string
    {
        if (!array_key_exists($route_name, self::$routes))
            throw new \Exception("Route {$route_name} is not set");

        $route = self::$routes[$route_name];
        $vars = [];

        if(preg_match_all('/(\/?\??#([a-z0-9_-]+)#\?)/i', $route['path'], $matches))
            $vars = array_combine($matches[2], $matches[0]);

        if ($vars){
            $required_vars = array_filter($vars, function ($item) {
                return substr($item, -1) != '?';
            });
            if (count(array_intersect($required_vars, array_keys($data))) != count($required_vars))
                throw new \Exception("Bad params number");

            $route['path'] = str_replace(
                array_values($vars),
                array_map(function ($var, $mask) use ($data) {
                    if (!array_key_exists($var, $data))
                        return '';

                    if (substr($mask, 0, 1) == '/')
                        return "/{$data[$var]}";

                    return $data[$var];
                }, array_keys($vars), $vars),
                $route['path']
            );
        }

        return $route['path'];
    }

    /**
     * @return Route
     */
    public static function instance() : self
    {
        if (is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

    public static function isActive(string $name, bool $greed = true) : bool
    {
        if (array_key_exists($name, self::$routes)) {
            $route = self::$routes[$name];
            return Route::instance()->check_masks(['rx' => $route['path']], $_, $greed) !== '';
        }

        return false;
    }
}
