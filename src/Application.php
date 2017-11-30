<?php namespace Fluxnet;

use Fluxnet\Controllers\Fatal;
use Fluxnet\Exception\FatalException;
use Fluxnet\Factories\ControllerFactory;
use Fluxnet\Interfaces\Controller;
use Zend\Diactoros\ServerRequestFactory;
use Aura\Router\RouterContainer;
use App\Config;

/**
 * Class Application
 *
 * The core framework application. Puts everything together.
 *
 * @package Fluxnet
 */
class Application {
    /** @var \Zend\Diactoros\ServerRequest $request
     * @var Controller $controller
     */
    private $request, $map, $route, $controller, $routes = array();
    function __construct() {
        $this->request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        $routerContainer = new RouterContainer();
        $this->map = $routerContainer->getMap();

        $this->map->get("home", Config::getInstance()->get("path_prefix")."/");
        $this->map->get("get_default", Config::getInstance()->get("path_prefix")."/{controller}/{action}")->wildcard("other");
        $this->map->post("post_default", Config::getInstance()->get("path_prefix")."/{controller}/{action}")->wildcard("other");

        //Matcher holen und request matchen
        $matcher = $routerContainer->getMatcher();
        $this->route = $matcher->match($this->request);
    }

    public function addRoute($route, $class, $action_param = "action") {
        if (!strpos($route, $action_param) !== false) {
            return false;
        }

        $route_name = $this->getRouteName($class);

        /*
         * TODO: Überlegen wie das sauber abstrahiert werden kann
         * Es braucht ein möglichst schlankes Interface um neue Routen hinzuzufügen
         * Wir haben zwar die generischen Routen aber die sind unflexibel und einige möchten das vermutlich nicht
         */

        $this->map->get($route_name, $route);
        $this->routes[$route_name]["action_param"] = $action_param;

        return true;
    }

    /**
     * Construct Controllers, run methods for get,post,ajax
     * eventually return 404 or index
     *
     * @return mixed
     */
    public function run() {
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
                default:
                    $controller->get($this->routes[$this->route->name]["action_param"]);
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
        return "custom_".strtolower($controller_name);
    }
}
