<?php
include "functions.php";

$loader = new \Aura\Autoload\Loader();
$loader->addPrefix('Fluxnet\\', __DIR__);
$loader->register();
