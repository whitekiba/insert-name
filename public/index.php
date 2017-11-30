<?php
require __DIR__."/../vendor/autoload.php";
require "../src/autoloader.php";
require "../config/production.php";
require "../app/app.php";

print $app->run();