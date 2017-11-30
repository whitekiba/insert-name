<?php namespace InsertName\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\PhpGenerator\PhpNamespace;
use InsertName\File\PhpFile;

class CreateControllerCommand extends Command {
    protected function configure() {
        $this->setName('framework:create:controller')
            ->setDescription('Create Controller')
            ->setHelp('Create Controller scaffold and optional Samplecode for controller.')
            ->addArgument('name', InputArgument::REQUIRED, "Controller name.")
            ->addOption('example', 'e', InputOption::VALUE_NONE, "Generate sample code in class.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //Bestehende Controller prüfen
        if (in_array($input->getArgument("name"), array("Index", "Notfound")) ||
            class_exists("App\\Controllers\\".$input->getArgument("name")))
        {
            $output->write("Controller ".$input->getArgument("name")." exists. Please choose another name.\n");
            return false;
        }

        //
        $output->write("Generating Code\n");

        //Namespace erzeugen.
        //Das ist immer App\Controllers
        $ns = new PhpNamespace("App\\Controllers");

        $ns->addUse("InsertName\Base\ControllerBase")
            ->addUse("InsertName\Interfaces\Controller", "IController");

        //Die Klasse bekommt den Namen des Parameters
        $class = $ns->addClass($input->getArgument("name"));
        $class->setExtends("InsertName\Base\ControllerBase")
            ->setImplements(["App\Controllers\IController"]);

        //Beispielcode für den Fall dass wir mehr automatisiert haben wollen
        if ($input->getOption("example")) {
            $prop = $class->addProperty("template");
            $prop->setVisibility("protected")
                ->setValue(strtolower($input->getArgument("name")).".twig");

            $method = $class->addMethod("render");
            $method->setVisibility("public")
                ->addBody('$this->setVariable("content", "I am sample content");')
                ->addBody('return parent::render();');
        }

        //Datei schreiben
        $output->write("Writing File.\n");
        $file = new PhpFile();
        $file->setFilename(__DIR__."/../../app/Controllers/".ucfirst($input->getArgument("name")).".php");
        $file->setContent($ns);
        $file->save();
    }
}
