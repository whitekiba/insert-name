<?php namespace Fluxnet;

use Symfony\Component\Console\Application;
use Fluxnet\Commands\{
  CreateCommandCommand, CreateControllerCommand, CreateModelCommand, MigrateDbCommand, CreateInitialDbCommand
};
use Symfony\Component\Console\Command\Command;

class Console {
    private $con;
    function __construct() {
        $this->con = new Application();

        $this->con->add(new MigrateDbCommand());
        $this->con->add(new CreateInitialDbCommand());
        $this->con->add(new CreateControllerCommand());
        $this->con->add(new CreateCommandCommand());
        $this->con->add(new CreateModelCommand());

    }

    /**
     * @param $obj
     * @return bool|\Symfony\Component\Console\Command\Command
     */
    public function add($obj) {
        if (is_subclass_of($obj, Command::class)) {
            return $this->con->register($obj);
        }
        return false;
    }

    public function run() {
        return $this->con->run();
    }
}
