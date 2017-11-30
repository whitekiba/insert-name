<?php namespace InsertName\Commands;

use Symfony\Component\Console\Command\Command;

class SetupCommand extends Command {
    protected function configure() {
        $this->setName('framework:setup')
          ->setDescription('Bootstrap Framework');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //TODO: Wir müssen gewisse defaults erzeugen
        //Wir müssen unter app/ die Ordner für Commands, Controller und Interfaces erstellen
        //Wir müsse app.php in app/ generieren
        //wir müssen console.php in app/ generieren
        //Wir müssen eine default config für das system generieren
    }
}
