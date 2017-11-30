<?php namespace Fluxnet\Controllers;

use Fluxnet\Base\ControllerBase;
use Fluxnet\Interfaces\Controller as IController;

class Notfound extends ControllerBase implements IController {
    protected $auth_only = false;
    protected $template = "notfound.twig";
}
