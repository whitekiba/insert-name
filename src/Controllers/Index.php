<?php namespace Fluxnet\Controllers;

use Fluxnet\Base\ControllerBase;
use Fluxnet\DB;
use Fluxnet\Interfaces\Controller as IController;

class Index extends ControllerBase implements IController {
    protected $template = "index.twig";

    public function render() {
        $db = DB::getInstance();
        $this->setVariable("content", "Ich bin die Startseite. Ich bekomme später noch mehr Content");
        return parent::render();
    }
}
