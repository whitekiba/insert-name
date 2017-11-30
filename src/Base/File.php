<?php namespace Fluxnet\Base;

use App\Config;

abstract class File {
    private $content;
    protected $config, $filename;

    function __construct() {
        $this->config = Config::getInstance();
    }

    public function getFilename() { return $this->filename; }

    /**
     * Datei speichern
     *
     * @return bool
     */
    public function save() {
        if (!$this->filename)
            return false;

        return file_put_contents($this->filename, $this->content);
    }

    /**
     * Pfad zur Datei setzen
     *
     * @param $n
     */
    public function setFilename($n) {
        $this->filename = $n;
    }

    /**
     * Den Inhalt der Datei setzen
     *
     * @param $c
     */
    protected function setContent($c) {
        $this->content = $c;
    }
}
