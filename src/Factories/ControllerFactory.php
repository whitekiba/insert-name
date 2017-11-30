<?php namespace InsertName\Factories;

use InsertName\Interfaces\Controller;
use InsertName\Controllers\{
    Fatal, Notfound, Index
};

class ControllerFactory {
    /**
     * @param $controller
     * @param $diactoros Zend\Diactoros\ServerRequest
     * @return Controller|NotfoundController
     */
    public static function get($controller, $diactoros) {
        //Als erstes die Controller der Anwendung
        if (class_exists("\\App\\Controllers\\".ucfirst($controller))){
            $controller_name = "\\App\\Controllers\\".ucfirst($controller);
            return new $controller_name($diactoros);
        }

        //Dann eventuell den default index controller
        if ($controller == "index") {
            return new Index($diactoros);
        }

        if ($controller == "fatal") {
            return new Fatal($diactoros);
        }

        //Und wenn alle Stricke reißen: 404!
        LogFactory::get("controllerfactory")->info("URL ".$diactoros->getUri()." hat einen 404 erzeugt.");
        return new Notfound($diactoros);
    }
}
