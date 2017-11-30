<?php namespace InsertName\Controllers;

use InsertName\Base\ControllerBase;
use InsertName\DB;
use InsertName\Interfaces\Controller as IController;

class Index extends ControllerBase implements IController {
    protected $template = "index.twig";

    public function render() {
        $db = DB::getInstance();
        $this->setVariable("content", "Ich bin die Startseite. Ich bekomme später noch mehr Content");
        return parent::render();
    }
}
