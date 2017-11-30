<?php namespace Fluxnet\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\PhpGenerator\PhpNamespace;
use Fluxnet\File\PhpFile;
use Fluxnet\DB;

class CreateModelCommand extends Command {
    protected function configure() {
        $this->setName('framework:create:model')
          ->setDescription('Create Controller')
          ->setHelp('Create Model from a database table.')
          ->addArgument('db_table', InputArgument::REQUIRED, "Controller name.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $db = DB::getInstance();
        $columns = array();
        $output->write("Gathering Information.\n");

        $sql = "SHOW COLUMNS FROM ".$db->escape($input->getArgument("db_table")).";";
        $res = $db->query($sql);
        while ($row = mysqli_fetch_array($res)) {
            $columns[$row["Field"]] = $row;
        }
        unset($columns["ID"]); //IDs werden durch generische Setter abgedeckt

        $output->write("Generating Code\n");

        //Namespace erzeugen.
        //Das ist immer App\Controllers
        $ns = new PhpNamespace("App\\Model");
        $ns->addUse("Fluxnet\Base\BaseModel");

        $class = $ns->addClass($this->generateClassName($input->getArgument("db_table")));
        $class->setExtends("Fluxnet\Base\ControllerBase")
          ->setImplements(["App\Controllers\IController"]);

        //Wir iterieren über die Spalten die wir haben und erzeugen getter und setter
        foreach ($columns as $key => $row) {
            $class->addMethod("get".ucfirst($this->generateClassName($key)))
                ->setVisibility("public")
                ->addBody('return $this->get("'.$key.'");')
                ->setSingleLineMethod();

            $class->addMethod("set".ucfirst($this->generateClassName($key)))
                ->setVisibility("public")
                ->addBody('return $this->set("'.$key.'", $value);')
                ->setSingleLineMethod()
                ->addParameter("value");
        }

        print $this->codeFormat((string)$ns);
    }

    private function generateClassName($db_table) {
        preg_match_all('/_/', $db_table, $matches, PREG_OFFSET_CAPTURE);

        $runs = substr_count($db_table, "_");

        for ($i=0;$i<$runs;$i++) {
            preg_match("/[_]/", $db_table, $matches, PREG_OFFSET_CAPTURE);
            /**
             * Da nach einem _ der Buchstabe kommt welcher uppercase werden soll nehmen wir $matches[0][1]+1
             * Wir replacen die position von _ und dem nachfolgenden buchstaben durch den uppercase buchstaben
             */
            $db_table = substr_replace($db_table,
              strtoupper($db_table[$matches[0][1]+1]),
              $matches[0][1], 2);
        }

        //depluralization
        if (substr_compare($db_table, "s", strlen($db_table)-strlen("s"), strlen("s")) === 0) {
            $db_table = substr_replace($db_table, "", -1);
        }
        //uppercasing of classname
        $db_table = ucfirst($db_table);
        return $db_table;
    }

    private function codeFormat($code) {
        return $code;
    }
}
