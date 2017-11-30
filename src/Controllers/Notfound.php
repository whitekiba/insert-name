<?php namespace InsertName\Controllers;

use InsertName\Base\ControllerBase;
use InsertName\Interfaces\Controller as IController;

class Notfound extends ControllerBase implements IController {
    protected $auth_only = false;
    protected $template = "notfound.twig";
}
