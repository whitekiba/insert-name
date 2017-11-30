<?php namespace Fluxnet\Base;
abstract class ApiControllerBase extends ControllerBase {
    protected $override_twig;
    protected $template = "api_template.twig";

    public function render() {
        if (!$this->override_twig)
            $this->override_twig = json_encode($this->variables);

        return parent::render(); // TODO: Change the autogenerated stub
    }
}