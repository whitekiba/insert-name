<?php namespace InsertName\File;

use InsertName\Base\File;

class PhpFile extends File {
    public function setContent($c) {
        $c = "<?php\n$c\n";
        parent::setContent($c); // TODO: Change the autogenerated stub
    }
}
