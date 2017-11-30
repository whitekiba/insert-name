<?php
include "functions.php";

$loader = new \Aura\Autoload\Loader();
$loader->addPrefix('InsertName\\', __DIR__);
$loader->register();
