<?php namespace InsertName\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InsertName\DB;

class CreateInitialDbCommand extends Command {
    protected function configure() {
        $this->setName('db:create');
        $this->setDescription('Create Initial DB');
        $this->setHelp('Create the initial DB. Should only be run once.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $db = DB::getInstance();

        if ($db->exists()) {
            $db->importFile("sql/db_initial.sql");
        }
    }
}
