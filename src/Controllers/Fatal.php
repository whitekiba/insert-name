<?php namespace InsertName\Controllers;

use InsertName\Base\ControllerBase;
use InsertName\Exception\FatalException;
use InsertName\Interfaces\Controller;

class Fatal extends ControllerBase implements Controller {
    /** @var FatalException */
    private $exception;

    public function setException(FatalException $e) {
        $this->exception = $e;
    }

    public function render() {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 300');//300 seconds
        $return = "<h3>503 - Service Temporarily Unavailable</h3>";
        $return .= "<p>".$this->exception->getMessage()."</p>";
        return $return;
    }
}
