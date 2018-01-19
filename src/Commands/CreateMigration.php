<?php namespace InsertName\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InsertName\DB;

class CreateMigration extends Command {
    protected function configure() {
        $this->setName('db:migration:create');
        $this->setDescription('Create DB migration stub');
        $this->setHelp('Creates a empty new migration for the current date');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $timestamp = time();
        file_put_contents(__DIR__."/../../sql/migrate_".$timestamp.".sql", "/*\n Created at ".date("m-M-Y")."\n by ".get_current_user()."\n*/\n\n");
    }
}
