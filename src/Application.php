<?php namespace InsertName;

use InsertName\Controllers\Fatal;
use InsertName\Exception\FatalException;
use InsertName\Factories\ControllerFactory;
use InsertName\Interfaces\Controller;
use Zend\Diactoros\ServerRequestFactory;
use Aura\Router\RouterContainer;
use App\Config;

/**
 * Class Application
 *
 * The core framework application. Puts everything together.
 *
 * @package InsertName
 */
class Application {
    /** @var \Zend\Diactoros\ServerRequest $request
     * @var Controller $controller
     */
    private $request, $map, $route, $routes = array(), $routerContainer;
    function __construct() {
        session_start();
        
        $this->request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        $this->routerContainer = new RouterContainer();
        $this->map = $this->routerContainer->getMap();

        $this->map->get("home", Config::getInstance()->get("path_prefix")."/");
        $this->map->get("controller_default_route", Config::getInstance()->get("path_prefix")."/{controller}");
        $this->map->get("get_default", Config::getInstance()->get("path_prefix")."/{controller}/{action}");
        $this->map->post("post_default", Config::getInstance()->get("path_prefix")."/{controller}/{action}");
    }

    public function addRoute($route, $method, $class = false, $action_param = "action") {
        if (!strpos($route, $action_param) !== false) {
            return false;
        }

        if ($class) {
            $route_name = $this->getRouteName($class);
        } else {
            $route_name = uniqid("custom_".$method."_");
        }

        /*
         * TODO: Überlegen wie das sauber abstrahiert werden kann
         * Es braucht ein möglichst schlankes Interface um neue Routen hinzuzufügen
         * Wir haben zwar die generischen Routen aber die sind unflexibel und einige möchten das vermutlich nicht
         */

        if ($method == "get") {
            $this->map->get($route_name, Config::getInstance()->get("path_prefix") . $route)->wildcard("other");
        } elseif ($method == "post") {
            $this->map->post($route_name, Config::getInstance()->get("path_prefix") . $route)->wildcard("other");
        } else {
            return false;
        }

        $this->routes[$route_name]["action_param"] = $action_param;
        $this->routes[$route_name]["method"] = $method;

        return true;
    }

    /**
     * Construct Controllers, run methods for get,post,ajax
     * eventually return 404 or index
     *
     * @return mixed
     */
    public function run() {
        //Matcher holen und request matchen
        $matcher = $this->routerContainer->getMatcher();
        $this->route = $matcher->match($this->request);

        //404 handler
        if (!$this->route) {
            return ControllerFactory::get("notfound", $this->request)->render();
        }

        try {
            //home route
            if ($this->route->name == "home") {
                return ControllerFactory::get("index", $this->request)->render();
            }

            if (isset($this->route->attributes["controller"])) {
                $controller = ControllerFactory::get($this->route->attributes["controller"], $this->request);
            } else {
                $controller = ControllerFactory::get($this->getControllerName($this->route->name), $this->request);
            }

            $controller->setMap($this->map);
            $controller->setRouter($this->route);
            $controller->init();

            switch ($this->route->name) {
                case 'ajax_default':
                    $controller->ajax($this->route->attributes["action"]);
                    break;
                case 'get_default':
                    $controller->get($this->route->attributes["action"]);
                    break;
                case 'post_default':
                    $controller->post($this->route->attributes["action"]);
                    break;
                case 'controller_default_route':
                    break;
                default:
                    $method = $this->routes[$this->route->name]["method"];
                    $action = $this->route->attributes[$this->routes[$this->route->name]["action_param"]];
                    //dynamically call the matching method
                    $controller->$method($action);
            }

            return $controller->render();
        } catch (FatalException $e) {
            /** @var Fatal $fatal_error */
            $fatal_error = ControllerFactory::get("fatal", $this->request);
            $fatal_error->setException($e);
            return $fatal_error->render();
        }
    }

    private function getControllerName($route_name) {
        $controller_name = ltrim($route_name, "custom_");
        return $controller_name;
    }

    private function getRouteName($controller_name) {
        $controller_name = rtrim($controller_name, "Controller");
        $controller_name = substr($controller_name, strrpos($controller_name, '\\') + 1);
        return "custom_".strtolower($controller_name);
    }
}
