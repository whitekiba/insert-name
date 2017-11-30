<?php namespace InsertName\Base;

use InsertName\Exception\FatalException;
use InsertName\DB;
use InsertName\Factories\LogFactory;

abstract class BaseModel {
	protected $_multi = false, $_multi_index = 0; //multi index defaults to 0. we need predictable errors
	protected $values = array();
	protected $changed = array();
	protected $_db, $db_table, $_log;
	protected $new_entry = true;

	/**
	 * BaseModel constructor.
	 */
	function __construct() {
		$this->_db = DB::getInstance();

		$this->_log = LogFactory::get(strtolower(get_called_class()));

		if (!$this->db_table)
			$this->db_table = $this->tableName();
	}

	/**
	 * Set Index of item to work on
	 * only needed for multiproducts
	 * @param $id
	 */
	public function setIndex($id) {
		if ($this->_multi) {
			$this->_multi_index = $id;
		}
	}

	/**
	 * Generischer getter für die ID. Eine ID hat jede Tabelle
	 * @return bool|mixed
	 */
	public function getID() { return $this->get("ID"); }
	public function isMulti() { return $this->_multi; }
	public function isNewEntry() { return $this->new_entry; }

	/**
	 * Generischer getter
	 * @param $index
	 * @return bool|mixed
	 */
	protected function get($index) {
		if (!isset($this->values[$index]) || ($this->isMulti() && !isset($this->values[$this->_multi_index][$index]))) {
			if ($this->load()) {
			    $this->new_entry = false;
            }
		}

		if ($this->_multi) {
			if (isset($this->values[$this->_multi_index][$index]))
				return $this->values[$this->_multi_index][$index];
		} else {
			if (isset($this->values[$index]))
				return $this->values[$index];
		}

		return false;
	}

	/**
	 * Generischer Setter
	 * @param $index
	 * @param $value
	 * @return bool
	 */
	protected function set($index, $value) {
	    if ($this->columnExists($index)) {
            $this->changed[$index] = $value;
            if ($this->_multi) {
                $this->values[$this->_multi_index][$index] = $value;
            } else {
                $this->values[$index] = $value;
            }
            return true;
        }
		return false;
	}

	/**
	 * Leerer Dummy
	 * Wird von Kindklassen überschrieben wenn nötig
	 */
	protected function load() {
	    return true;
	}

	/**
	 * Generisches erstellen
	 * @return bool|mysqli_result
	 */
	public function create() {
		if ($this->_multi)
			throw new FatalException("generisches create nicht möglich wenn multi");

		$keys = "(`ID`";
		$values = "(NULL";
		if (!$this->getID()) {
			if (count($this->values) > 0) {
                $query = "INSERT INTO " . $this->db_table . " ";
				$key = "";
				foreach ($this->values as $key => $value) {
					if ($this->columnExists($key)) {
						$keys .= ",`" . $key . "`";
						$values .= ",'" . $value . "'";
					}
				}
				$final_query = $query . $keys . ") VALUES " . $values . ");";
				$res = $this->_db->query($final_query);
				$this->values["ID"] = $this->_db->last_id();
				return $res;
			}
		} else {
			#TODO: Exception werfen wenn User noch nicht existiert
		}
		return false;
	}

	/**
	 * generisches Speichern
	 * @return bool|mysqli_result
	 */
	public function save() {
		if ($this->_multi)
			throw new FatalException("generisches create nicht möglich wenn multi");

		if ($this->getID()) {
			$first = true;
			$query = "UPDATE ".$this->db_table." SET ";
			foreach ($this->changed as $key => $value) {
				if ($this->columnExists($key)) {
					if (!$first)
						$query .= ',';
					$query .= "$key = \"$value\"";
					$first = false;
				}
			}
			$query .= " WHERE ID = ".$this->getID();
			return $this->_db->query($query);
		} else {
			$this->_log->err("Save nicht aufgerufen weil getID failte");
			return false;
			#TODO: Exception werfen wenn User noch nicht existiert
		}
	}

	/**
	 * Delete this Entry.
	 */
	public function delete() {
		if ($this->getID()) {
			$sql = "DELETE FROM " . $this->db_table . " WHERE ID = " . $this->getID();
			$this->_db->query($sql);

			//Kram leeren und alles zurücksetzen
			$this->values = array();
			$this->changed = array();
			$this->_multi = false;
			$this->_multi_index = 0;

			return true;
		}
		return false;
	}

    /**
     * @param $field
     * @param $value
     * @return array|bool|null
     */
	protected function loadBy($field, $value) {
	    $this->values = array();
	    $this->changed = array();

	    if (is_numeric($value)) {
	        $where = " = ".$this->_db->escape($value);
        } else {
	        $where = "LIKE \"".$this->_db->escape($value)."\"";
        }

		$sql = "SELECT * FROM ".$this->db_table." WHERE ".$this->_db->escape($field)." $where;";
		$res = $this->_db->query($sql);
		if ($res) {
			$this->values = mysqli_fetch_array($res);
			$this->new_entry = false;
			return $this->values;
		}
		return false;
	}

    /**
     * Diese Methode ist da um das 1+n Problem zu vermeiden
     * Sie wird von Listen von *Entry über den ::withPreparedData($arr) Wrapper aufgerufen
     *
     * Da wir Listen haben welche *Entry Objekte zurückgeben wollen wir nicht jedes Entry einzeln abfragen
     * Die Liste ruft in diesem Fall ihre Daten + Filter ab und erzugt die Objekte mit ::withPreparedData
     * So filtern wir die Daten in der Liste vor, haben nur eine Query und trotzdem pro Entry ein Objekt
     * welches manipulierbar bleibt
     *
     * @param $arr
     * @return array
     */
	protected function constructWithPreparedData($arr) {
        $this->changed = array();

        $this->values = $arr;
        return $this->values;
    }

	/**
	 * Check if column of table exists
	 * @param $column
	 * @return bool
	 */
	protected function columnExists($column) {
		$result = $this->_db->query("SHOW COLUMNS FROM `".$this->db_table."` LIKE '".$column."'");
		return (mysqli_num_rows($result))? TRUE:FALSE;
	}

	/**
	 * sometimes you need to set a single value without going through all the set, validate, save stuff
	 * this is for cases in wich we only want to update a date or set a simple boolean.
	 * for more complex values please use the set, validate, save workflow
	 * @param $field
	 * @param $value
	 * @return bool|mysqli_result
	 */
	protected function setSingleField($field, $value) {
		$sql = "UPDATE ".$this->db_table." SET $field = $value WHERE ID = ".$this->getID().";";
		return $this->_db->query($sql);
	}

	/**
     * Alias to BaseModel::getDbTableName()
     *
     * @deprecated Use BaseModel::getDbTableName instead!
	 * @return string
	 */
	protected function tableName() {
	    return self::getDbTableName();
	}

    /**
     * Calculate table name
     *
     * It uses the class name for calculating the table name
     *
     * Object will be objects
     * LongObject will be long_objects
     * VeryLongObject will be very_long_objects
     *
     * We use simple pluralization. It simply appends s to the class name
     *
     * @return string
     */
	public static function getDbTableName() {
        $class_name = get_called_class();

        preg_match_all('/[A-Z]/', $class_name, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $key => $value) {
            if ($key > 0)
                $class_name = substr_replace($class_name, "_".strtolower($value[0]), $value[1], 1);
        }

        $final = strtolower($class_name)."s";
        return $final;
    }

    /**
     * @param $ID
     * @return static
     */
    public static function byID($ID) {
        $i = new static();
        $i->loadBy("ID", $ID);
        return $i;
    }

    /**
     * Siehe constructWithPreparedData
     *
     * @param $arr
     * @return BaseModel
     */
    public static function withPreparedData($arr) {
        $i = new static();
        $i->constructWithPreparedData($arr);
        return $i;
    }
}
