<?php namespace Fluxnet\Base;

use Fluxnet\Exception\FatalException;

class TemplateBase {
    protected $_log;

    protected $template = false, $template_fields = array();
	protected $template_prefix = "";
	protected $template_path = __DIR__."/../../templates/";
	protected $template_filetype = ".tpl";
	protected $text = false;

	function __construct($templatepath = false) {
        $this->_log = LogFactory::get();

        if ($templatepath)
            $this->template_path = $templatepath;

	    if (!is_dir($this->template_path)) {
	        throw new FatalException("Template Directory not found. Create ".realpath($this->template_path)." ok?");
        }
    }

    /**
     * Set Templatename
     *
     * @param $tpl_name
     */
    public function setTemplateName($tpl_name) {
	    $this->template = $tpl_name;
    }

    /**
	 * @param array $fields
	 */
	public function setFields(array $fields) {
		$this->template_fields = $fields;
	}

	/**
	 * @param $field
	 * @param $value
	 */
	public function setField($field, $value) {
		$this->template_fields[$field] = $value;
	}

	/**
     * Template "rendern"
     * Wir packen die variablen in die platzhalter
     *
     * Bei Bedarf kann diese Methode überschrieben werden um eine echte Templateengine zu nutzen
     *
	 * @return bool|string
	 */
	public function fillTemplate() {
        $tpl_filepath = $this->template_path.$this->template_prefix.$this->template.$this->template_filetype;

		#Wir brechen ab falls es nicht mal Felder zu füllen gibt
		if (count($this->template_fields) > 0) {
			$this->text = "";
			$handle = fopen($tpl_filepath, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					foreach ($this->template_fields as $key => $value) {
						$line = str_replace("[$key]", $value, $line);
					}
					$this->text .= $line;
				}
				fclose($handle);
				return $this->text;
			}
		} else {
			#TODO: Eventuell hier einen Fehler werfen weil keine Felder gesetzt wurden.
			#eine warnung wäre auch okay
		}
		$this->text = false;
		return false;
	}
}