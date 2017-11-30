<?php namespace Fluxnet\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\PhpGenerator\PhpNamespace;
use Fluxnet\File\PhpFile;

class CreateCommandCommand extends Command {
    protected function configure() {
        $this->setName('framework:create:command')
            ->setDescription('Create Controller')
            ->setHelp('Create Command Controller scaffold and optional Samplecode for controller.')
            ->addArgument('name', InputArgument::REQUIRED, "Controller name.")
            ->addOption('example', 'e', InputOption::VALUE_NONE, "Generate sample code in class.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //Bestehende Controller prüfen
        if (in_array($input->getArgument("name"), array("CreateCommand", "CreateController", "CreateInitialDb", "MigrateDb")) ||
            class_exists("App\\Commands\\".$input->getArgument("name")))
        {
            $output->write("Command ".$input->getArgument("name")." exists. Please choose another name.\n");
            return false;
        }

        //
        $output->write("Generating Code\n");

        //Namespace erzeugen.
        //Das ist immer App\Controllers
        $ns = new PhpNamespace("App\\Commands");

        $ns->addUse("Symfony\Component\Console\Command\Command")
            ->addUse("Symfony\Component\Console\Input\InputArgument")
            ->addUse("Symfony\Component\Console\Input\InputInterface")
            ->addUse("Symfony\Component\Console\Input\InputOption")
            ->addUse("Symfony\Component\Console\Output\OutputInterface");

        //Die Klasse bekommt den Namen des Parameters
        $class = $ns->addClass($input->getArgument("name")."Command");
        $class->setExtends("Symfony\Component\Console\Command\Command");

        //Grundfunktionen generieren für nen command
        //Erst die configure methode
        $method = $class->addMethod("configure");
        $method->setVisibility("protected")
            ->addBody('$this->setName(\'change:me\');')
            ->addBody('$this->setDescription(\'Change me\');')
            ->addBody('$this->setHelp(\'Change me.\');');

        //dann die execute Methode
        $method = $class->addMethod("execute");
        $method->setVisibility("protected");

        //Datei schreiben
        $output->write("Writing File.\n");
        $file = new PhpFile();
        $file->setFilename(__DIR__."/../../app/Commands/".ucfirst($input->getArgument("name"))."Command.php");
        $file->setContent($ns);
        $file->save();
        $output->write("Don't forget to add your command to app/console.php\n");
        return true;
    }
}
