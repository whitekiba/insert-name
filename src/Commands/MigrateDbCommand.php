<?php namespace InsertName\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InsertName\DB;
use App\Config;

class MigrateDbCommand extends Command {
    protected function configure() {
        $this->setName('db:migrate')
          ->setDescription('Migrate DB')
          ->setHelp('Migrate DB to current version.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $migration_prefix = "sql/";

        $last_migration = "";
        $error = false;

        $db = DB::getInstance();
        $config = Config::getInstance();
        $files = scandir($migration_prefix);

        $old_timestamp = $config->get("db_timestamp");

        foreach ($files as $file) {
            print "Bearbeite Datei: $file\n";

            if (preg_match("/migrate_([0-9]+)\.sql/", $file, $result)) {
                print "Datei $file ist eine migration. Los gehts.\n";
                $timestamp = $result[1];

                if ($timestamp > $old_timestamp) {
                    print "Migration aktueller als Timestamp. Migriere.\n";

                    if (!$db->importFile($migration_prefix.$file)) {
                        $last_migration = $file;
                        $error = true;
                        break;
                    }
                }
            }

            //$config->set("db_timestamp", $timestamp);
        }

        if ($error) {
            print "FEHLER BEI MIGRATION!!!\n";
            print "Datei $last_migration hat einen Fehler ausgelöst. Bitte prüfe die Migration manuell!\n";
        } else {
            print "Scheint als wäre alles gut gegangen.\n";
        }
    }
}
