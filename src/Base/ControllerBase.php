<?php namespace InsertName\Base;

use Twig_Environment;
use Twig_Loader_Filesystem;
use InsertName\Factories\AuthFactory;

abstract class ControllerBase {
    protected $variables = array();
    protected $template;
    protected $override_twig = false; //Um Twig zu deaktivieren und nen eigenen response zu zeigen

    /** @var Auth */
    protected $_auth;
    /** @var  Aura\Router\Map */
    protected $_map;
    /** @var  Aura\Router\Route */
    protected $_router;
    /** @var  Twig_Environment */
    protected $twig;

    /** @var  Zend\Diactoros\ServerRequest */
    protected $_diactoros;

    function __construct($diactoros) {
        $loader = new Twig_Loader_Filesystem(__DIR__.'/../../templates/');
        /*$this->twig = new Twig_Environment($loader, array(
          'cache' => 'cache/',
        ));*/

        $this->_auth = AuthFactory::get();
        $this->twig = new Twig_Environment($loader);
        $this->_diactoros = $diactoros;

        //Einige Variablen grundsätzlich setzen
        //Das meiste hiervon wird vom head genutzt. Eventuell ist es irgendwo anders noch nützlich

        $this->variables["base_path"] = get_installation_path();
        //base_url macht mehr sinn
        $this->variables["base_url"] = get_installation_path();
        $this->variables["auth"] = $this->_auth;
    }

    /**
     * Render the given template
     *
     * Falls man einen Controller hat der nichts ausgeben soll reicht es
     * wenn man diese Methode überschreibt und das original nicht aufruft.
     * In diesem Fall wird nichts gerendert und Twig gibt nen leeren String aus
     *
     * @return string
     */
    public function render() {
        //Falls eigenes HTML erzeugt wird
        //Oder für Plaintextresponses
        if ($this->override_twig) {
            return $this->override_twig;
        }

        if (empty($this->template)) {
            $this->template = $this->templateName();
        }

        $template = $this->twig->load($this->template);
        return $template->render($this->variables);
    }

    /**
     * Ausgeführt vom Router nach dem setzen von Router und Map
     * Ist dazu da überschrieben zu werden.
     */
    public function init() {

    }

    /**
     * Actionhandler. ruft _actionHandler auf
     * @param $action
     * @param array $params
     * @return bool
     */
    public function action($action, $params = array()) {
        return $this->_actionHandler("action", $action, $params);
    }

    /**
     * Actionhandler. ruft _actionHandler auf
     *
     * @param $action
     * @param array $params
     * @return bool
     */
    public function get($action, $params = array()) {
        return $this->_actionHandler("action", $action, $params);
    }

    /**
     * Actionhandler. ruft _actionHandler auf
     *
     * @param $action
     * @param array $params
     * @return bool
     */
    public function post($action, $params = array()) {
        return $this->_actionHandler("post", $action, $params);
    }

    /**
     * Actionhandler. ruft _actionHandler auf
     * @param $action
     * @param array $params
     * @return bool
     */
    public function post_action($action, $params = array()) {
        return $this->_actionHandler("post", $action, $params);
    }

    /**
     * Actionhandler. ruft _actionHandler auf
     * @param $action
     * @param array $params
     * @return bool
     */
    public function ajax($action, $params = array()) {
        return $this->_actionHandler("ajax", $action, $params);
    }

    /**
     * Aura.router map dependency injection
     *
     * @param Map $map
     */
    public function setMap($map) {
        $this->_map = $map;
    }

    /**
     * Aura.router dependency injection
     *
     * @param $router
     */
    public function setRouter($router) {
        $this->_router = $router;
    }

    /**
     * Fehlermeldung setzen. Da wir das für alle Templates nutzen gibts eine extra methode
     *
     * @param $msg
     */
    public function setError($msg) {
        $this->variables["error"] = $msg;
    }

    /**
     * Setzt eine Notice. Wird unterm Header angezeigt
     *
     * @param $msg string
     */
    public function setNotice($msg) {
        $this->variables["notice"] = $msg;
    }

    /**
     * Titel erweitern.
     *
     * @param $title
     */
    public function setTitle($title) {
        $this->variables["title_add"] = $title;
    }

    /**
     * @param $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * Eine Templatevariable setzen
     * Der checkt ob eine variable schon gesetzt ist um zu vermeiden dass aus versehen bestehende überschrieben werden
     *
     * @param $variable
     * @param $value
     * @param bool $force
     * @return bool
     */
    protected function setVariable($variable, $value, $force = false) {
        if ($force) {
            $this->variables[$variable] = $value;
        } else {
            if (isset($this->variables[$variable])) {
                //TODO: Exception werfen.
                return false;
            } else {
                $this->variables[$variable] = $value;
            }
        }
        return true;
    }

    public function getVariable($variable) {
        if (isset($this->variables[$variable]))
            return $this->variables[$variable];

        return false;
    }

    /**
     * Get post field.
     *
     * Wenn man bestimmte Validierungen wünscht kann man das überschreiben.
     *
     * @param $field
     * @return bool
     */
    protected function getPost($field) {
        /*
        if (isset($this->_diactoros->getParsedBody()[$field])) {
            return $this->_diactoros->getParsedBody()[$field];
        }*/
        if (isset($_POST[$field]))
            return $_POST[$field];
        return false;
    }

    /**
     * Get a field from the $_REQUEST global
     *
     * Macht bisher nicht viel. Soll erst mal die API festlegen.
     *
     * @param $field
     * @return bool
     */
    protected function getRequestValue($field) {
        if (isset($_REQUEST[$field])) {
            if ($_REQUEST[$field] == "") {
                return true;
            }

            return $_REQUEST[$field];
        }
        return false;
    }

    /**
     * Extra Parameter abfragen.
     *
     * @param $pos
     * @return bool|string|int
     */
    public function getOtherParameter($pos) {
        if (isset($this->_router->attributes["other"][$pos])) {
            return $this->_router->attributes["other"][$pos];
        }
        return false;
    }

    /**
     * Calculate template name
     *
     * It uses the class name for calculating the template name
     *
     * ManageController will be manage.twig
     *
     * @return string
     */
    protected function templateName() {
        $class_name = get_called_class();
        $class_name = substr($class_name, strrpos($class_name, '\\') + 1);

        preg_match_all('/[A-Z]/', $class_name, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $key => $value) {
            if ($key > 0)
                $class_name = substr_replace($class_name, "_".strtolower($value[0]), $value[1], 1);
        }

        $final = strtolower($class_name).".twig";
        return $final;
    }

    /**
     * Generischer Actionhandler. Versucht Aktionen auf Methoden zu mappen
     * Das geschieht nach dem Schema ${action}Action
     * und dem $type
     *
     * @param $type
     * @param $action
     * @param array $params
     * @return bool
     */
    protected function _actionHandler($type, $action, $params = array()) {
        if ($action == "")
            return false;

        switch ($type) {
            case "action":
                $type_name = "Action";
                break;
            case "post":
                $type_name = "PostAction";
                break;
            case "ajax":
                $type_name = "Ajax";
                break;
            default:
                $type_name = "Action";
        }

        $lowercase_action = strtolower($action);
        if (method_exists($this, $lowercase_action.$type_name)) {
            $method_name = $lowercase_action.$type_name;
            $this->$method_name($params);
            return true;
        }
        return false;
    }

    protected function needs() {
        foreach (func_get_args() as $arg) {
            if (!$this->getPost($arg)) {
                throw new Exception("POST-Parameter $arg wurde nicht gesendet!");
            }
        }
    }
}
