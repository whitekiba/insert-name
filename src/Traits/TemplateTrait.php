<?php namespace Fluxnet\Traits;

use Fluxnet\Base\TemplateBase;

trait TemplateTrait {
    /** @var TemplateBase */
    private $tpl;

    /**
     * Den Templatepfad zu setzen erfordert eine neue instanz. Daher überschreiben wir die hier
     * @param $tpl_path
     */
    protected function setTemplatePath($tpl_path) {
        $this->tpl = new TemplateBase($tpl_path);
    }

    protected function setTemplateName($tpl_name) {
        $this->tpl->setTemplateName($tpl_name);
    }

    protected function fillTemplate() {
        return $this->tpl->fillTemplate();
    }

    protected function setFields(array $fields) {
        $this->tpl->setFields($fields);
    }

    protected function setField($field, $value) {
        $this->tpl->setField($field, $value);
    }
}
