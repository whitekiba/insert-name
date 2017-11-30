## [insert name] a micro framework

[insert name] is a framework based on some of the best components available in composer. It relies heavily on composer for most functionality.
It features automated processes for finding templates, controllers and routes.
It automatically maps controllers and their methods to routes.

app/Controllers/Hello.php
```php
<?php namespace App\Controllers;
 
use InsertName\Base\ControllerBase;
use InsertName\Interfaces\Controller as IController;

class Hello extends ControllerBase implements IController {
 public function worldAction() {
     $this->setVariable("hello_world", "Hello world!");
 }
}
```

templates/hello.twig
```twig
{{ hello_world }}
```

###Requirements
* PHP 7.1+
* a Webserver (developed and tested on Apache)
* optional: MySQL

### Features
* a basic ORM
* semi-automatic routing
* a nice and easy to learn template engine
* automatic autoloading
* convention over configuration
* fast and flexible

### Why
[insert name] started as a learning project to understand the basic concepts of creating an MVC framework

Everything started as a set of functions that evolved into a set of classes that evolved into something losely based on MVC paradigms.
After it reached a certain state i decided to convert it into a real framework that could be useful for other people.

### [insert name]? WTF?
I had no idea for a name. So i just wrote [insert name] everywhere. And until i find a great name it will be called [insert name]