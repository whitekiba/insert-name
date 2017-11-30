<?php namespace InsertName\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFrameworkCommand extends Command {
    private $update_url;

    protected function configure() {
        $this->setName('framework:update');
        $this->setDescription('Update Framework to latest release');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

    }

    private function checkForUpdate() {

    }
}
