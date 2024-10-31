<?php
$ne_pages =  array(
	'eventsCtrlAdmin_add' => 'eventsCtrlAdmin_add.php',
	'eventsCtrlAdmin_bulkDelete' => 'eventsCtrlAdmin_bulkDelete.php',
	'eventsCtrlAdmin_bulkPublish' => 'eventsCtrlAdmin_bulkPublish.php',
	'eventsCtrlAdmin_bulkUnpublish' => 'eventsCtrlAdmin_bulkUnpublish.php',
	'eventsCtrlAdmin_create' => 'eventsCtrlAdmin_create.php',
	'eventsCtrlAdmin_delete' => 'eventsCtrlAdmin_delete.php',
	'eventsCtrlAdmin_edit' => 'eventsCtrlAdmin_edit.php',
	'eventsCtrlAdmin_index' => 'eventsCtrlAdmin_index.php',
	'eventsCtrlAdmin_togglePublished' => 'eventsCtrlAdmin_togglePublished.php',
	'eventsCtrlAdmin_update' => 'eventsCtrlAdmin_update.php',
	'eventsCtrl_bytag' => 'eventsCtrl_bytag.php',
	'eventsCtrl_detail' => 'eventsCtrl_detail.php',
	'eventsCtrl_index' => 'eventsCtrl_index.php',
	'eventsCtrl_listView' => 'eventsCtrl_listView.php',
	'eventsCtrl_title' => 'eventsCtrl_title.php',
	'newsAndEvents_ajaxEditorButtonDialog' => 'newsAndEvents_ajaxEditorButtonDialog.php',
	'newsAndEvents_confirmInstall' => 'newsAndEvents_confirmInstall.php',
	'newsAndEvents_confirmUninstall' => 'newsAndEvents_confirmUninstall.php',
	'newsAndEvents_error_' => 'newsAndEvents_error_.php',
	'newsAndEvents_install' => 'newsAndEvents_install.php',
	'newsAndEvents_settings' => 'newsAndEvents_settings.php',
	'newsAndEvents_uninstall' => 'newsAndEvents_uninstall.php',
	'newsCtrlAdmin_add' => 'newsCtrlAdmin_add.php',
	'newsCtrlAdmin_ajaxSetTimeAtTop' => 'newsCtrlAdmin_ajaxSetTimeAtTop.php',
	'newsCtrlAdmin_ajaxTackHeadline' => 'newsCtrlAdmin_ajaxTackHeadline.php',
	'newsCtrlAdmin_ajaxTackHeadlineToTop' => 'newsCtrlAdmin_ajaxTackHeadlineToTop.php',
	'newsCtrlAdmin_bulkDelete' => 'newsCtrlAdmin_bulkDelete.php',
	'newsCtrlAdmin_bulkPublish' => 'newsCtrlAdmin_bulkPublish.php',
	'newsCtrlAdmin_bulkUnpublish' => 'newsCtrlAdmin_bulkUnpublish.php',
	'newsCtrlAdmin_create' => 'newsCtrlAdmin_create.php',
	'newsCtrlAdmin_delete' => 'newsCtrlAdmin_delete.php',
	'newsCtrlAdmin_edit' => 'newsCtrlAdmin_edit.php',
	'newsCtrlAdmin_index' => 'newsCtrlAdmin_index.php',
	'newsCtrlAdmin_populateDatabase' => 'newsCtrlAdmin_populateDatabase.php',
	'newsCtrlAdmin_previewHeadlines' => 'newsCtrlAdmin_previewHeadlines.php',
	'newsCtrlAdmin_resetHeadline' => 'newsCtrlAdmin_resetHeadline.php',
	'newsCtrlAdmin_update' => 'newsCtrlAdmin_update.php',
	'newsCtrl_ajaxRSS' => 'newsCtrl_ajaxRSS.php',
	'newsCtrl_article' => 'newsCtrl_article.php',
	'newsCtrl_bytag' => 'newsCtrl_bytag.php',
	'newsCtrl_headlines' => 'newsCtrl_headlines.php',
	'newsCtrl_index' => 'newsCtrl_index.php',
	'newsCtrl_tags' => 'newsCtrl_tags.php',
	'optionsCtrlAdmin_index' => 'optionsCtrlAdmin_index.php',
	'optionsCtrlAdmin_save' => 'optionsCtrlAdmin_save.php',
);

class ne_Request {
	static $controller;
	static $action;
	static $namespace;

	static $args = array(); // This is present only in the exported version because $this->ktf_args doesn't exist in the exported version

	static function set($n, $c, $a) {
		self::$namespace = $n;
		self::$action = $a;
		self::$controller = $c;
	}
}

ne_Request::$args = array_merge($_GET, ne_Request::$args);

function ne_redirect($location) {
	global $ne_folder_name;
	$full_location = $ne_folder_name . '/' . $location;
	header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?page=$full_location");
	exit();
}

function ne_get_plugin_folder() {
	global $ne_folder_name;
	return $ne_folder_name;
}
class ne_Model {
	
	var $field_types = array();
	var $fields = array();
	var $primary_field = '';
	
	var $recordset;
	var $m_data = array();
	private $changed_data = array();

	public $errors = array();
	public $last_error;

	var $hasOneModels = array();
	var $oneToManyModels = array();

	private $relationships = array();

	function __construct() {
		$model = get_class($this);

		if(!function_exists($model)) { }

		$this->fields = '';
		$this->field_types = '';
		$this->primary_field = '';
		$this->primary_field_increment = '';

		foreach($this->relationships as $index => $relationship) {
			if($relationship['relationship'] == 'has_one') {
				$this->hasOneModels[strtolower($relationship['model'])] = false;
			}
			else if($relationship['relationship'] == 'has_many') {
				$this->oneToManyModels[strtolower($relationship['models'])] = false;
			}
		}

		if(empty($this->primary_field)) $this->primary_field = 'id';
	}

	/**
	 * @brief Access database field data or object variables
	 * @return Returns either database data or object variables.
	 */
	function __get($name) {
		if ($name == 'data') {
			if(count($this->changed_data) > 0) {
				return array_merge($this->m_data, $this->changed_data);
			}
			return $this->m_data;
		} else if(isset($this->field_types[$name])) {
			// If the data has been modified in code pending a write, return that data
			if(isset($this->changed_data[$name])) {
				return $this->changed_data[$name];
			}

			// Return data from array
			if(isset($this->m_data[$name])) {
				return $this->m_data[$name];
			}
		}
		else if(isset($this->hasOneModels[$name])) {
			// THIS NEEDS TO BE TESTED. DO THIS FIRST BEFORE ANYTHING ELSE. FIXME
			// Set up a three table sequence with three relationships. $model->relationship1->relationship2->data. relationship2 has to be loaded lazily. test that.
			if($this->hasOneModels[$name] == false) {
				//Create the model

				$class = new $name();
				$class->load($this->{$class->modelName . '_' . $this->primary_field});

				//Save the model back into the array
				$this->hasOneModels[$name] = $class;
			}

			return $this->hasOneModels[$name];
		}
		else if(isset($this->oneToManyModels[$name])) {
			$models = new $name();
			$model = new $models->model();

			if($this->oneToManyModels[$name] == false) {
				$this->oneToManyModels[$name] = $models;
			}

			if(isset($this->{$this->primary_field})) {
				$this->oneToManyModels[$name]->constraints[] = strtolower($this->modelName) . '_' . $model->primary_field . ' = ' . $this->{$this->primary_field};
			}

			return $this->oneToManyModels[$name];
		}

		return $this->$name;
	}

	/**
	 * @brief Sets either database data or object variables. If you set something which is a field name, (e.g. $events->title = 'test') it will interpret that as you trying to set database data.
	 */
	function __set($name, $value) {
		if ($name === 'data' && is_array($value)) { // Use this to bulk assign an array to the model
			$p_field = $this->primary_field;
			foreach ($value as $key => $val) {				
				if ($key !== $p_field || ($key == $p_field && $this->primary_field_increment == false)) {
					$this->$key = $this->_sanitizeInput($val);
				} else if (isset($this->m_data[$p_field]) === false) {
					$this->{$p_field} = stripslashes($val);
				}
			}
		} else if($name == 'db_data' && is_array($value)) { 
			$relationshipData = array();

			// This code handles data directly from the database, from one of these functions: Models::getModel, and Model::load
			// Every field name must be prefixed with the model name, e.g. People_title. This is to differentiate which columns go to which model in joined queries.
			foreach($value as $key => $val) {
				//Break apart the key to get to the field name. Right now its in the form of $class_$field 
				$broken = explode('_', $key);

				$sliceLength = 0;
				do {
					$sliceLength++;
					$class = implode('_', array_slice($broken, 0, $sliceLength));

					if($sliceLength > count($broken)) {
						break;
					}
				} while(!class_exists($class));

				$field = implode('_', array_slice($broken, $sliceLength));

				if($class == strtolower($this->modelName)) { // If the data belongs to this model
					$this->m_data[$field] = $this->_sanitizeInput($val);
				}
				else { // Otherwise, we save the data for later.
					if(!isset($relationshipData[$class])) {
						$relationshipData[$class] = array();
					}

					$relationshipData[$class][$class . '_' . $field] = $val;
				}
			}

			// Go through all the data that belongs to other models, but was joined into this query.
			// Create a new model for each one, plug it into $this->hasOneModels
			foreach($relationshipData as $class => $fieldData) {
				$this->hasOneModels[$class] = new $class();
				$this->hasOneModels[$class]->db_data = $fieldData;
			}
		} else if(isset($this->field_types[$name])) {
			$this->changed_data[$name] = $value;

			//Check to see if a model validator function has been declared for this field.
			$validator = "validate" . ucwords($name);
			if(method_exists($this, $validator)) {
				$this->$validator($value);
			}
		}
		else {
			$this->$name = $value;
		}
	}

	function __isset($name) {
		if(isset($this->$name)) return true;
		if(isset($this->changed_data[$name])) return true;
		if(isset($this->m_data[$name])) return true;

		return false;
	}
	
	/**
	 * @brief Escapes a field depending on database column type
	 * 
	 * @param fieldName			the name of the field to format
	 * @param fieldValue		the value of the field to format
	 * @return Value escaped and wrapped in quotation marks
	 */
	function formatFieldForQuery($fieldName, $fieldValue) {
		$stringtypes = array('varchar', 'text', 'char', 'blob', 'longtext', 'tinytext', 'mediumtext', 'longblob', 'mediumblob', 'date', 'datetime', 'timestamp', 'enum');
		
		if(is_null($fieldValue)) {
			return 'null';
		} elseif($fieldValue == '') {
			return 'null'; //convert zero length strings to null
		} elseif(in_array($this->field_types[$fieldName], $stringtypes)) {
			return '"' . mysql_real_escape_string($fieldValue) . '"';
		} else {
			return $fieldValue;
		}
	}
	
	function _sanitizeInput($input) {
		if(is_array($input)) {
			foreach($input as &$i) {
				$i = stripslashes($i);
			}
			return $input;
		}
		else {
			$input = stripslashes($input);
			return $input;
		}
	}

	/**
	 * @brief load  record by id
	 * @param id string or int
	 */
	function load($id, $field = '') {
		if (empty($field)) {
			$field = $this->primary_field;
		}

		$qstring = "SELECT " . $this->includeFields() . " FROM " . $this->table . $this->joinRelationships() . " WHERE `" . $this->table . "`.`" . $field . "` = " . $this->formatFieldForQuery($field, $id);

		global $wpdb;
		$results = $wpdb->get_results($qstring, ARRAY_A);


		if($results != null) {
			list($this->db_data) = $results;
			return true;
		}

		return false;
	}

	/**
	 * @brief Updates the row we are currently pointing at in the DB
	 */
	function save() {
		//Call model hook first
		if(method_exists($this, 'onSave')) {
			$this->onSave();
		}

		$primary = $this->primary_field;
		$first = true;
		
		// if record exists
		if(isset($this->m_data[$primary])) {
			// update
			if (empty($this->changed_data))
				return;

			$query = "UPDATE `{$this->table}` SET ";
			foreach($this->fields as $field) {
				if(!isset($this->changed_data[$field])) {
					continue;
				}
				
				// If it's auto incrementing, don't bother updating it. If it isn't, then the user may have changed it manually.
				if($field === $primary && $this->primary_field_increment == true) {
					continue;
				}
					
				if(isset($this->changed_data[$field]) === false) {
					$value = null;
				} else {
					$value = $this->changed_data[$field];
				}

				if(!$first) {
					$query .= ', ';
				}
				$first = false;
				
				$query .= $field . ' = ' . $this->formatFieldForQuery($field, $value);
			}
			
			$query .= ' WHERE ' . $primary . ' = ';
			$query .= $this->formatFieldForQuery($primary, $this->m_data[$primary]);
			$query .= ';';

		} else {
			// insert
			$query = "INSERT INTO {$this->table} (";

			$keys = '';
			$values = '';
			$first = true;
			foreach($this->fields as $field) {			
				if( $field == $primary && $this->primary_field_increment == true ) {
					continue;	
				}

				if(!$first) {
					$keys .= ', ';
					$values .= ', ';
				}
				$first = false;
				
				if(isset($this->changed_data[$field]) === false) {
					$value = null;
				} else {
					$value = $this->changed_data[$field];
				}

				$keys .= "`{$field}`";
				$values .= $this->formatFieldForQuery($field, $value);
			}
			
			$query .= "{$keys}) VALUES ({$values});";
		}
		
		global $wpdb;

		if($wpdb->query($query) === FALSE) {
			//Save the error so it can be retrieved if desired
			$this->last_error = mysql_error($wpdb->dbh);
			return false;
		} else {
			//If the id field wasn't set then this is a new record 
			//so get the id of the newly inserted record and store it.
			if (isset($this->field_types['id']) && isset($this->id) === false) {
				$this->id = $wpdb->insert_id;
			}
			return true;
		}
	}

	/**
	 * @brief Creates a model from a data array, and saves it to the database
	 * 
	 * @param data Associative array of model record data
	 * 
	 * @return Model consisting of new row
	 */
	function create($data = array()) {
		if (empty($data))
		{
			return false;
		}

		$class = get_class($this);
		$model = new $class;
		$model->m_data = $data;

		return $model->save();
	}

	/**
	 * @brief Updates a specific row and field in the database. For use in updating something without actually retrieving the row model first.
	 *
	 * @param column The column (field) that you want to update
	 * @param value The value to set the column to
	 * @param primary_field_value The primary field value of the row to be updated
	 */
	function update($column, $value, $primary_field_value) {
		$primary = $this->primary_field;
		$query = "UPDATE {$this->table} SET ({$column}) VALUES (";

		$query .= $this->formatFieldForQuery($column, $value);
		$query .= ") WHERE {$primary} = ";
		$query .= $this->formatFieldForQuery($primary, $primary_field_value);

		//Why the ";"?
		global $wpdb;
		$wpdb->query($query . ";");
	}

	/**
	 * @brief Drops the current model from the database. Use this when you have an instantiated model.
	 */
	function drop() {
		if(method_exists($this, 'onDelete')) {
			$this->onDelete();
		}

		$query = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primary_field . ' = ';
		$query .= $this->formatFieldForQuery($this->primary_field, $this->{$this->primary_field});

		global $wpdb;
		$wpdb->query($query);
	}


	/**
	 * @brief Deletes a specific row from the model database table.
	 * 
	 * @param value The primary field value of the row to be deleted
	 * @param field The name of the primary field. If not supplied, the function will guess the primary field
	 */
	function delete($value = '', $field = '') {
		$field = ($field === '') ? $this->primary_field : $field;
		$query = "DELETE FROM {$this->table} WHERE `{$field}` = ";
		$query .= $this->formatFieldForQuery($field, ($value === '') ? $this->$field : $value);
		$query .= ';';

		global $wpdb;
		$wpdb->query($query);
	}

	function includeFields() {
		$statements = $this->myFieldIncludes();
		
		foreach($this->relationships as $relationship) {
			if($relationship['join'] == true) {
				$model = new $relationship['model'];
				$statements = array_merge($statements, $model->myFieldIncludes());
			}

		}

		return ' ' . implode(',', $statements) . ' ';
	}

	function myFieldIncludes() {
		foreach($this->fields as $field) {
			$statements[] = "{$this->table}.$field AS " . $this->internalFieldName($field);
		}

		return $statements;
	}

	function internalFieldName($field) {
		return strtolower($this->modelName) . '_' . $field;
	}

	function joinRelationships() {
		$statements = array();

		foreach($this->relationships as $relationship) {
			if($relationship['join'] == true) {
				$model = new $relationship['model']();
				$modelName = strtolower($relationship['model']);
				$statements[] = "{$model->table} ON {$model->table}.{$model->primary_field} = {$this->table}.{$modelName}_{$this->primary_field}";
			}
		}

		if(count($statements) > 0) {
			return ' JOIN ' . implode(",", $statements) . ' ';
		}
		else {
			return '';
		}
	}

	 /**
	  * @brief Sets an error message on a field. Should be used from a validator function in a model.
	  *
	  * @param field The field to set the message for
	  * @param error The error message
	  */
	 function setError($field, $error) {
		ne_sessionHelper_setError($field, $error);
	 }

	 /**
	 * @deprecated Use Model::delete or Model::drop instead
	 */
	function dropRecord() {
		self::bad_function('Model::dropRecord', 'Model::delete');
	}

	 /**
	 * @deprecated Use ResultSet::move instead
	 */
	function move($to = 0) {
		self::bad_function('move', 'ResultSet::move');
	}

	/**
	 * @deprecated Use Model::save instead
	 */
	function createRecord() {
		self::bad_function('Model::createRecord', 'Model::create');
	}

	/**
	 * @deprecated Use Model::save instead
	 */
	function updateRecord() {
		self::bad_function('Model::updateRecord', 'Model::save');
	}
	
	function bad_function($func, $better_func = '') {
		$msg = "{$func} is deprecated and no longer works; ";
		 
		if (empty($better_func)) {
			$msg .= "there is not another function recommended, {$func} was not needed.";
		} else {
			$msg .= "please use {$better_func} instead.";
		}

		print_r(debug_backtrace(), true);
		die($msg);
	 }

}

class ne_Models {
	private $recordset;
	private $position = 0;

	private $joins = false;

	public $constraints = array(); // An array of constraints for what this model can find. For example, set a default of returning rows with only a certain ID.

	function __construct() {
		$model = new $this->model();
		$this->table = $model->table;

		foreach($model->relationships as $relationship) {
			if($relationship['join'] == true) {
				$this->joins = true;
				break;
			}
		}
	}

	/**
	 * @brief A robust SELECT statement constructor
	 * 
	 * If $what is a single value, e.g. 'merlin', find() retrieves the
	 * row whose primary field value is 'merlin'.
	 * 
	 * If $what is an expression, e.g. 'occupation = wizard', find()
	 * retrieves the rows where the occupation field is 'wizard'. Any
	 * standard SQL statement can be used.
	 * 
	 * $start and $limit can be used together for pagination. $order can
	 * be used to sort the results by any standard ORDER BY value. All three
	 * parameters are optional.
	 * 
	 * @param $what			what to fetch (see note above, defaults to empty)
	 * @param $start		which row to start on, for pagination (optional)
	 * @param $limit		how many rows to return, for pagination (optional)
	 * @param $order		equivalent to ORDER BY in SQL (optional)
	 * 
	 * @return ResultSet object
	 */
	function find($what = '', $options = array()) {
		$stringtypes = array('varchar', 'text', 'char', 'blob', 'longtext', 'mediumtext', 'longblob', 'mediumblob', 'date', 'datetime', 'timestamp', 'enum');
		$operators   = array('=', '!=', '<>', '>', '<', '>=', '<=', 'BETWEEN', 'LIKE');

		$qstring  = "SELECT " . $this->includeFields() . " FROM {$this->table} ";

		if($this->joins == true) {
			$qstring .= $this->joinRelationships();
		}

		if(!empty($what)) {
			/* Search for a comparison operator, to tell if we have
			 * an expression or just a primary key value.
			 */
			$opfound = false;
			foreach( $operators as $op ) {
				if( strpos( $what, $op ) ) {
					$opfound = true;
					break;					
				}
			}

			if(!$opfound) {
				// We have to sanitize this ourselves
				$what = trim($what, "\"' ");
				$what = mysql_real_escape_string($what);
				
				// If it's a string, escape it...
				if(in_array($this->field_types[$this->primary_field], $stringtypes) 
				   && $what != 'null')
					$what = '\'' . $what . '\'';
					
				// Make it a boolean comparison
				$what = $this->primary_field . ' = ' . $what . ' ';
			}
			
			$qstring .= 'WHERE ' . $what . ' ';
		}

		if(count($this->constraints) > 0) {
			$str = implode(' AND ', $this->constraints);

			if(empty($what)) {
				$qstring .= 'WHERE ' . $str . ' ';
			}
			else {
				$qstring .= $str . ' ';
			}
		}

		// Append the order by if necessary
		if(!empty($options['order']))
			$qstring .= 'ORDER BY ' . mysql_real_escape_string($options['order']) . ' ';
		
		// Append the limit, if necessary 
		if(!empty($options['start']) || !empty($options['limit'])) 
			$qstring .= 'LIMIT ' . intval($options['start']) . ', ' . intval($options['limit']) . ' ';
		
		
		// Query the database
		$qstring = trim($qstring) . ';';

		// uncomment for testing
		//var_dump($qstring);

		global $wpdb;
		$this->recordset = $wpdb->get_results($qstring, ARRAY_A);

		return (count($this->recordset) > 0 ? false : true);
		// TODO: Error reporting
	}

	function includeFields() {
		$myModel = new $this->model();
		
		return $myModel->includeFields();
	}

	function joinRelationships() {
		$myModel = new $this->model();

		return $myModel->joinRelationships();
	}

	function internalFieldName($name) {
		return strtolower($this->model) . "_$name";
	}

	/**
	 * @brief Runs a user-supplied query
	 * @param query the query to run
	 */
	function query($query) {
		global $wpdb;
		$this->recordset = $wpdb->get_results($query, ARRAY_A);
		return (count($this->recordset) > 0 ? false : true);
	}

	/**
	 * @brief Returns a model containing the data at the current recordset position
	 * 
	 * @return Model of current recordset row
	 */
	function getModel() {
		$class = $this->model;
		$model = new $class();
		$model->db_data = $this->recordset[$this->position];
		
		return $model;
	}

	/**
	 * @brief Moves the recordset cursor to the next row, and returns the corresponding model
	 *
	 * @return Model of next row in recordset
	 */
	function next() {
		// TEST THIS; FIXME
		if(!isset($this->recordset)) {
			$this->find();
		}

		if($this->EOF()) {
			return null;
		}

		$model = $this->getModel();
		$this->position++;

		return $model;
	}

	/**
	 * @brief Returns whether or not there are any more rows in the recordset
	 *
	 * @return Boolean
	 */
	function EOF() {
		return ($this->position >= $this->count() ? true : false );
	}
	
	/**
	 * @brief Moves the recordset cursor to the specified row
	 * 
	 * @param to		[optional] row to move the recordset cursor to
	 *
	 * @return Model of row at specific position
	 */

	function move($to = 0) {
		$this->position = $to;

		return $this->getModel();
	}
	
	/**
	 * @brief Moves the recordset cursor to the first row
	 * 
	 * @return Model of first row in recordset
	 */
	function first() {
		$this->position = 0;

		return $this->getModel();
	}

	/**
	 * @brief Retrieves the first row without moving the recordset cursor
	 *
	 * @return Model of first row in recordset
	 */
	function getFirst() {
		$original = $this->position;
		$this->position = 0;

		$model = $this->getModel();

		$this->position = $original;

		return $model;
	}

	/**
	 * @brief Retrieves the last row without moving the recordset cursor
	 *
	 * @return Model of last row in recordset
	 */
	function getLast() {
		$original = $this->position;
		$this->position = $this->count();

		$model = $this->getModel();

		$this->position = $original;

		return $model;
	}

	/**
	 * @brief Moves the recordset cursor to the last row
	 * 
	 * @return Model of last row in recordset
	 */
	function last() {
		$this->position = $this->count();

		return $this->getModel();
	}

	/**
	 * @brief Returns the number of rows in the recordset
	 * 
	 * @return number of rows in recordset
	 */
	function count() {
		return count($this->recordset);
	}

}


/**
 * @brief Reduces the coding needed to get form elements on a form
 *
 *
 * Parameters are passed in to each function by an associative array 
 * Example: $form->addTextField(array('field_name'=>'title'));
 * 
 * Optional parameters are handled by the validParameters function which 
 * attempts to set reasonable defaults for the specified parameter
 * 
 * Values can be passed in at the form level as an associative array.  This
 * allows passing of only the field name for each field and this class looks
 * up the current value.
 * 
 * Instructions can also be passed in at the form level if this is easier 
 * than specifying for each field
 */

class ne_Form {
	//The values for use with this form
	public $values;

	//An associative array of error messages with the keys being field names
	private $errors = array();

	//An associative array of instructions with the keys being field names
	private $instructions = array();
	
	//some defaults for text areas
	public $default_text_field_size = 40;
	public $default_text_area_rows = 10;
	public $default_text_area_columns = 34;
	public $default_text_area_rich_text = true;

	//Whether to have the labels to the left of form inputs
	private $useTables = false;
	

	/**
	 * @brief sets the basic attributes of the form to be created
	 * 
	 * @param required action			name of the function that should be called upon submitting the form
	 * @param required page				domain/controller in which the above function is located
	 * @param required formPart			name of a part for this form used with some fields and by the function named above
	 * @param optional buffered			supposedly holds output until the end but this isn't working on a Windows client and server
	 * 																It may work better on other platforms but is untested
	 * @param optional method			the means by which parameters will be passed to the function named above
	 */
	public function __construct( $action, $page, $formPart, $buffered = false, $useTables = false, $method = 'post' ) {
		if($buffered == true) {
			ob_start();
		}

		if(substr($page, 0, 4) !== 'ktf_') $page = 'ktf_' . $page;

		$this->useTables = $useTables;
		$this->formPart = $formPart;
		 ne_Form()->startForm($action, $page);
		 ne_Form()->setFormPart($formPart);

		if($this->useTables == true) {
			 ne_Table()->startTable(array('class' => 'form-table'));
		}
	}

	/**
	 * @brief adds a hidden nonce field (see more at http://markjaquith.wordpress.com/2006/06/02/wordpress-203-nonces/)
	 */
	public function addNonce() {
		 ne_Form()->newNonce();
	}

	/**
	 * @brief creates a label given a field name
	 * 
	 * @param required fieldName		name of the field for which a label is needed
	 */
	public function createLabel( $fieldName ) {
		//Format the label from a field name
		$title = str_replace("_", " ", $fieldName);
		$title = ucwords($title);
		return $title;
	}

	/**
	 * @brief adds a single line to the array of instructions that will be read later as each field is added to the form
	 * 
	 * @param required field			name of the field to which the instructions apply
	 * @param required instructions		instructions for the field
	 */
	public function setInstruction($field, $instructions) {
		$this->instructions[$field] = $instructions;
	}

	/**
	 * @brief sets an array of instructions that are read later as each field is added to the form
	 * 
	 * @param required arr			associative array of instructions where
	 *  
	 */
	public function setInstructions($arr) {
		$this->instructions = array_merge( $this->instructions, $arr );
	}
	
	/**
	 * @brief Adds a text field to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name		name of the database field
	 * @param optional label			label for the field
	 * @param optional value			current value of the field
	 * @param optional size				width of the field
	 * @param optional disabled			indicates if the field should be disabled
	 * @param optional instructions		instructions for this field
	 * 
	 */
	public function addTextField( $parameters ) {
		if ($this->validParameters($parameters)) {
			$this->startVisualOutput();

			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);
			
			//Determine the value (is there a saved value from an errored form?)
			$value = $parameters['value'];
			
			if(ne_sessionHelper_savedValue($this->formPart, $parameters['field_name'])) {
				$value = ne_sessionHelper_savedValue($this->formPart, $parameters['field_name']);
			}

			//field
			if($this->useTables) {
				$this->formTableColumn();
			}

			echo  ne_Form()->newTextField($parameters['field_name'], 
					$value,
					$parameters['size'], 
					$parameters['disabled']);

			//Errors?
			$this->printErrors($parameters['field_name']);

			//instructions
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}

	/**
	 * @brief Adds a date picker field to the form being generated by this class
	 *
	 * Optional parameters have default values handled in function validParameters
	 *
	 * @param required field_name 		name of the database field
	 * @param optional label 		label for the field
	 * @param optional value 		current value of the field. Will be interpreted by strtotime.
	 * @param optional instructions 	instructions for this field
	 * @param optional showdate 		boolean, whether to show the month, day, year inputs
	 * @param optional showtime 		boolean, whether to show the hour, minute, AM/PM inputs
	 *
	 */
	public function addDatePicker( $parameters ) {
		if ($this->validParameters($parameters)) { //$parameters passed by reference
			$this->startVisualOutput();

			//Label
			$this->formatLabel( $parameters['label'], $parameters['field_name']);

			//Errors?
			$this->printErrors($parameters['field_name']);
			
			if($this->useTables) {
				$this->formTableColumn();
			}

			if(strlen($value) > 0) {
				$timeValue = strtotime($parameters['value']);
				echo  ne_Form()->newDatePicker($parameters['field'], $parameters['showdate'], $parameters['showtime'], date("Y", $timeValue), date("m", $timeValue), date("d", $timeValue), date("g", $timeValue));
			}
			else {
				echo  ne_Form()->newDatePicker($parameters['field'], $parameters['showdate'], $parameters['showtime']);
			}
			
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}
	
	/**
	 * @brief Adds a file upload field to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name		name of the database field
	 * @param optional label		label for the field
	 * @param optional value		current value of the field
	 * @param optional instructions		instructions for this field
	 * 
	 */
	public function addFileUpload( $parameters ) {
		if ($this->validParameters($parameters)) {
			$this->startVisualOutput();

			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);

			//Errors?
			$this->printErrors($parameters['field_name']);

			if($this->useTables) {
				$this->formTableColumn();
			}

			//field
			echo   ne_Form()->newFileField($parameters['field_name'], $parameters['value']);
						
			//instructions
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}

	/**
	 * @brief Adds a photo file upload field to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name		name of the database field
	 * @param optional label			label for the field
	 * @param optional value			current value of the field
	 * @param optional instructions		instructions for this field
	 * 
	 */
	public function addPhotoUpload( $parameters ) {
		//This is untested
		
		if ($this->validParameters($parameters)) {
			$this->startVisualOutput();

			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);

			//Errors?
			$this->printErrors($parameters['field_name']);
			
			if($this->useTables) {
				$this->formTableColumn();
			}

			//field
			//Add a special photo field
			echo  ne_Form()->newFileField($parameters['field_name'], $parameters['value']);
			
			if( $parameters['value'] ) {
				echo  " Current photo: " . $parameters['value'];
			}
			
			//instructions
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}

	/**
	 * @brief Adds a Text Area field to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name		name of the database field
	 * @param optional label			label for the field
	 * @param optional value			current value of the field
	 * @param optional instructions		instructions for this field
	 * @param optional columns			width of this field
	 * @param optional rows				rows of text to display
	 * @param optional rich_text		false for a plain text field and true for rich text
	 * 
	 */
	public function addTextArea( $parameters ) {
		if ($this->validParameters($parameters)) {
			$this->startVisualOutput();

			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);
			
			//Errors?
			$this->printErrors($parameters['field_name']);
			
			//Value? Saved value from errored form?
			$value = $parameters['value'];
			if(ne_sessionHelper_savedValue($this->formPart, $parameters['field_name'])) {
				$value = ne_sessionHelper_savedValue($this->formPart, $parameters['field_name']);
			}

			if($this->useTables) {
				$this->formTableColumn();
			}

			//field
			echo  ne_Form()->newTextArea($parameters['field_name'], 
				$parameters['columns'], 
				$parameters['rows'], 
				$value,
				$parameters['rich_text']);

			//instructions
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}

	/**
	 * @brief Adds a Text field for entering an integer to the form being generated by this class
	 *
	 * Optional parameters have default values handled in function validParameters
	 *
	 * @param required field_name 	name of the database field
	 * @param optional label 	label for the field
	 * @param optional value 	current value of the field
	 * @param optional instructions instructions for this field
	 *
	 */
	function addInteger($parameters) {
		if($this->validParameters($parameters)) {
			$this->startVisualOutput();
			
			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);

			//Errors?
			$this->printErrors($parameters['field_name']);

			//Value? Saved value from errored form?
			$value = $parameters['value'];
			if(ne_sessionHelper_savedValue($this->formPart, $parameters['field_name'])) {
				$value = ne_sessionHelper_savedValue($this->formPart, $parameters['field_name']);
			}

			//field
			echo  ne_Form()->newIntegerField($parameters['field_name'], $value);

			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}
	
	/**
	 * @brief Adds a select box to the form being generated by this class
	 *
	 * Optional parameters have default values handled in function validParameters
	 *
	 * @param required field_name 	name of the database field
	 * @param required options 	an array of options for the select box, in the form $value => $text
	 * @param optional label 	label for the field
	 * @param optional value 	the value of the option that should be selected by default
	 * @param optional instructions instructions for the field
	 *
	 */
	function addSelect($parameters) {
		$this->startVisualOutput();
		
		//Label
		$this->formatLabel($parameters['label'], $parameters['field_name']);

		//Errors?
		$this->printErrors($parameters['field_name']);

		//Value?
		$value = $parameters['value'];
		if(ne_sessionHelper_savedValue($this->formPart, $parameters['field_name'])) {
			$value = ne_sessionHelper_savedValue($this->formPart, $parameters['field_name']);
		}
		
		if($this->useTables) {
			$this->formTableColumn();
		}

		//Field
		 ne_Form()->startSelect($parameters['field_name']);
		
		foreach($parameters['options'] as $value => $text) {
			if($parameters['value'] == $value)  ne_Form()->addOption($value, $text, true);
			else  ne_Form()->addOption($value, $text);
		}

		 ne_Form()->endSelect();
	}

	public function addRadioButtons($parameters, $values, $checked_button) {
		$this->startVisualOutput();
		if ($this->validParameters($parameters)) {
			$this->formatLabel($parameters['label'], $parameters['field_name']);

			if($this->useTables) {
				$this->formTableColumn();
			}
			foreach ($values as $name => $value) {
				$checked = false;
				if ($checked_button == $value) {
					$checked = true;
				}
				echo $name.'&nbsp;';
				 ne_Form()->newRadioButton($parameters['field_name'], $value, $checked);
				echo '<br/>';
			}
		}
		$this->endVisualOutput();
	}

	/**
	 * @brief Adds a select (drop down) field to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name						name of the database field
	 * @param required dbSelect_recordset				recordset of options for the select list
	 * @param optional label							label for the field
	 * @param optional value							current value of the field
	 * @param optional dbSelect_foreign_key_id_field	key field name of the options recordset
	 * @param optional dbSelect_foreign_key_label_field	display field name of the options recordset
 	 * @param optional instructions						instructions for this field
 	 * @param optional dbSelect_multi					allow multiple selections at the same time
 	 * @param optional dbSelect_size					number of rows to show - This might be buggy
 	 * 																		leaving it blank seems to work
	 * 
	 */
	public function addDbSelect( $parameters ) {
		if (($this->validParameters($parameters)) && ($parameters['dbSelect_recordset'])) {
			$this->startVisualOutput();

			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);
			
			if($this->useTables) {
				$this->formTableColumn();
			}

			//field
			echo   ne_Form()->db_select($this->formPart,
				$parameters['field_name'], 
				$parameters['dbSelect_recordset'], 
				$parameters['dbSelect_foreign_key_id_field'], 
				$parameters['dbSelect_foreign_key_label_field'], 
				$parameters['value'], 
				$parameters['dbSelect_multi'], 
				$parameters['dbSelect_size']);
			//instructions
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}

	/**
	 * @brief Adds a check box to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name		name of the database field
	 * @param optional label			label for the field
	 * @param optional checked			current value of the field: true or false
	 * @param optional instructions		instructions for this field
	 * 
	 */
	public function addCheckbox( $parameters ) {
		if ($this->validParameters($parameters)) {
			$this->startVisualOutput();

			//Label
			$this->formatLabel($parameters['label'], $parameters['field_name']);
			
			if($this->useTables) {
				$this->formTableColumn();
			}

			$value = $parameters['value'];
			if(ne_sessionHelper_savedValue($this->formPart, $parameters['field_name'])) {
				$value = ne_sessionHelper_savedValue($this->formPart, $parameters['field_name']);
			}

			//field
			echo  ne_Form()->newCheckBox($parameters['field_name'], $value);

			//instructions
			if( $parameters['instructions']) {
				echo $this->addInstructions($parameters['instructions']);
			}

			$this->endVisualOutput();
		}
	}

	/**
	 * @brief Adds a hidden field to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required field_name		name of the database field
	 * @param optional value			current value of the field
	 * 
	 */
	public function addHidden( $parameters ) {
		if ($this->validParameters($parameters)) {
			$value = $parameters['value'];
			if(ne_sessionHelper_savedValue($this->formPart, $parameters['field_name'])) {
				$value = ne_sessionHelper_savedValue($this->formPart, $parameters['field_name']);
			}

			 ne_Form()->newHidden($this->formPart, $parameters['field_name'], $value);
		}
	}

	/**
	 * @brief Adds a submit button to the form being generated by this class
	 * 
	 * Optional parameters have default values handled in function validParameters
	 * 
	 * @param required message		text to appear on the button
	 * 
	 */
	public function addSubmit( $message ) {
		if($this->useTables) {
			 ne_Table()->endTable();
		}

		$this->useTables = false;

		$this->startVisualOutput();
		
		if($this->useTables) {
			 ne_Table()->newTableColumn();
		}

		 ne_Form()->newSubmitButton($message);

		$this->endVisualOutput();

	}

	/**
	 * @brief Private function to handle output of instructions.
	 *
	 * @param message the instructions to output
	 *
	 * @see Form::setInstruction
	 * @see Form::setInstructions
	 */
	private function addInstructions($message) {
		if($this->useTables) {
			 ne_Table()->newTableColumn(array('style' => 'vertical-align: top'));
			 ne_Form()->instructions($message);
		}
		else {
			 ne_Form()->instructions($message);
		}
	}
	
	/**
	 * @brief Makes sure we have valid parameters for most of the public functions
	 * 
	 * The field_name is required for most of the fields being added to the form
	 * In all other cases, this function adds some sort of default as needed to 
	 * the parameters array.  Note that this function receives the parameter array
	 * by reference so that it can do some work on the array as well as pass back
	 * a true or false depending on success
	 * 
	 * @param required parameters		an associative array of the parameters needed for 
	 * 										each of the calling functions
	 * 
	 */
	private function validParameters(&$parameters) {
		//If parameters isn't an array we can't go any further
		if(!is_array($parameters)) {
			echo '<p>Error: The variable "$parameters" should have been an array.';
			return false;
		}
		
		//We need at least the field name to get anywhere
		if(!array_key_exists('field_name', $parameters)){
			echo '<p>Error: "field_name" should have been passed in "$parameters." ';
			return false;
		}
		
		$field_name = $parameters['field_name'];
		
		$defaults = array(
			'label' => $this->createLabel($field_name),
			'value' => ($this->values->$field_name ? $this->values->$field_name : ''),
			'checked' => ($this->values->$field_name != 0 ? true : false),
			'size' => $this->default_text_field_size,
			'columns' => $this->default_text_area_columns,
			'rows' => $this->default_text_area_rows,
			'rich_text' => $this->default_text_area_rich_text,
			'disabled' => false,
			'instructions' => $this->instructions[$field_name],
			'dbSelect_foreign_key_id_field' => 0,
			'dbSelect_foreign_key_label_field' => 1,
			'dbSelect_multi' => false,
			'dbSelect_size' => '',
			'showdate' => true,
			'showtime' => false
		);

		$parameters = array_merge($defaults, $parameters);
		return true;
	}

	/**
	 * @brief This function should be called before the output of every form element. It either outputs a paragraph tag or a new table row.
	 */
	private function startVisualOutput() {
		if($this->useTables) {
			 ne_Table()->newTableRow();
		}
		else {
			echo "<p>";
		}
	}

	/**
	 * @brief This function should be called after the output of every form element. It ends the paragraph tag.
	 */
	private function endVisualOutput() {
		if($this->useTables) {
			
		}
		else {
			echo "</p>";
		}
	}

	/**
	 * @brief wraps the label in html tags for output
	 * 
	 * @param required text			text for the label 
	 * @param required $field_name 	name of the field to which this label corresponds
	 *  
	 */
	private function formatLabel($text, $field_name) {
		if($this->useTables) {
			 ne_Table()->newHeaderColumn();
			echo  ne_Form()->newLabel($text, $field_name);
		}
		else {
			echo  ne_Form()->newLabel($text, $field_name);
			echo '<br/>';
		}

	}

	private function formTableColumn() {
		 ne_Table()->newTableColumn(array());
	}

	private function printErrors($fieldName) {
		 ne_Form()->error(ne_sessionHelper_getError($fieldName));
	}

	public function output() {
		ob_end_flush();
	}

	public function getAsString() {
		if($this->buffered) {
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
	}

	function __destruct() {
		if($this->buffered) {
			ob_end_flush();
		}
	}
}

class ne_NewsItem extends ne_Model {
	function __construct() {
$this->fields = array('id','headline','author','date','sort_date','excerpt','article','image','thumbnail','published');
$this->field_types = array('id' => 'int','headline' => 'varchar','author' => 'varchar','date' => 'date','sort_date' => 'date','excerpt' => 'text','article' => 'text','image' => 'varchar','thumbnail' => 'varchar','published' => 'tinyint');
$this->primary_field = 'id';
$this->primary_field_increment = '';

	}

	var $table = "ne_news";
	var $modelName = "ne_NewsItem";
	
	function has_headline() {
		return (strlen($this->headline) > 0 ? true : false);
	}
	
	function has_author() {
		return (strlen($this->author) > 0 ? true : false);
	}
	
	function has_date() {
		return (strlen($this->date) > 0 ? true : false);
	}
	
	function has_sort_date() {
		return (strlen($this->sort_date) > 0 ? true : false);
	}
	
	function has_excerpt() {
		return (strlen($this->excerpt) > 0 ? true : false);
	}
	
	function has_article() {
		return (strlen($this->article) > 0 ? true : false);
	}
	
	function has_image() {
		return (strlen($this->image) > 0 ? true : false);
	}
	
	function has_thumbnail() {
		return (strlen($this->thumbnail) > 0 ? true : false);
	}
	
	function has_tags() {
		return (strlen($this->tag_str()) > 0 ? true : false);
	}
	
	function validateHeadline($headline) {
		if(empty($headline)) {
			$this->setError('headline', 'Headline cannot be blank');
		}
	}

	function print_date($setting = null) {
		echo $this->format_date($setting);
	}

	function format_date($setting = null) {
		if($setting == null or $setting == '0') {
			return date("F jS, Y", strtotime($this->date));
		}
		else if($setting == '1') {
			return date("m/d/y", strtotime($this->date));
		}
	}

	function editable_date() {
		return date("m/d/Y", strtotime($this->date));
	}

	function day() {
		return date("d", $this->dateAsTimestamp);
	}

	function month() {
		return date("m", $this->dateAsTimestamp());
	}

	function year() {
		return date("Y", $this->dateAsTimestamp());
	}

	function sortDay() {
		return intval(date("d", $this->sortDateAsTimestamp()));
	}

	function sortMonth() {
		return intval(date("m", $this->sortDateAsTimestamp()));
	}

	function sortYear() {
		return intval(date("Y", $this->sortDateAsTimestamp()));
	}
	
	function truncText() {
		$len = 50;
		$after = '...';

		if(strlen($this->article) > $len) {
			$shortened = substr($this->article, 0, 50);
			$shortened .= $after;

			//Find last space so we don't chop up halfway through a word
			$last = strrpos($shortened, ' ');
			$shortened = substr($shortened, 0, $last);

			return $shortened;
		}
		else {
			return $this->article;
		}
	}

	function dateAsTimestamp() {
		if(isset($this->dateAsTimestamp)) {
			return $this->dateAsTimestamp;
		}
		else {
			$this->dateAsTimestamp = strtotime($this->date);
			return $this->dateAsTimestamp();
		}
	}

	function sortDateAsTimestamp() {
		if($this->sort_date > 0) {
			return strtotime($this->sort_date);
		}
		else {
			return $this->dateAsTimestamp();
		}
	}

	function sortDateSet() {
		return $this->sort_date > 0;
	}

	/**
	 * @brief Finds the Nth item from the top of the default sorted list
	 * @param $positionFromTop the one to pick
	 */
	function fromTop($positionFromTop) {
		$newsItems = new ne_NewsItems();
		$newsItems->query("SELECT *, IF(sort_date > 0, sort_date, date) as sort FROM `ne_news` ORDER BY `sort` DESC LIMIT $positionFromTop, 1");

		$this->m_data = $newsItems->first()->m_data;
	}

	function tags() {
		$rel = new ne_TagRelationships();
		$rel->find("entry_id = {$this->id} AND type = 'news'", array('order' => 'tag_id ASC'));

		$tags = array();
		while (($t = $rel->next()) !== null) {
			$db_tag = new ne_Tag;
			$db_tag->load($t->tag_id);

			$tags[] = $db_tag;
		}

		return $tags;
	}

	function set_tags($tags) {
		// remove old tags, if any
		$id = $this->id;

		if (isset($id)) {
			$rel = new ne_TagRelationships();
			$rel->find("`entry_id` = {$id} AND `type` = 'news'");

			while (($r = $rel->next()) !== null) {
				$r->drop();
			}
			echo 'here';

			// create new tags
			ne_TagRelationships::tag_entry($id, $tags, 'news');
		}

		$unusedTags = new ne_Tags();
		$unusedTags->query("SELECT ne_tags.id AS " . strtolower($unusedTags->model) . "_id, ne_tags.name AS " . strtolower($unusedTags->model) . "_name
			FROM ne_tag_relationships 
			RIGHT JOIN ne_tags ON ne_tags.id = ne_tag_relationships.tag_id 
			WHERE ne_tag_relationships.tag_id IS NULL");

		while($tag = $unusedTags->next()) {
			$tag->drop();
		}
	}

	function tag_str($delim = ',') {
		$tags = array();
		foreach($this->tags() as $tag) {
			$tags[] = $tag->name;
		}

		return implode($delim . " ", $tags);
	}

	function thumbnailURL() {
		return get_option('siteurl') . $this->thumbnail;
	}

	function imageURL() {
		return get_option('siteurl') . $this->image;
	}

	function onSave() {
		// Format the start and end dates properly if they exist
		if (empty($this->date)) {
			$this->date = date( "Y-m-d" );
		} else {
			$this->date = date( "Y-m-d", strtotime($this->date) );
		}
		
		// Note: wp_handle_upload and wp_insert_attachment are both built in wordpress functions.

		// Read any uploaded files into an array
		$image = wp_handle_upload($this->fileArr, array(
			'test_form' => false
		));

		//If there was an error and there was a photo being uploaded
		if(isset($image['error']) && $this->fileArr['name'] != '') {
			//TODO: Add more sophisticated error handling
			die("Image Upload Error: " . $image['error']);
		}

		wp_insert_attachment(array(
			'post_title' => $this->headline,
			'post_mime_type' => $image['type'],
			'guid' => $image['url']
		));

		// Check to see if a new image was uploaded
		if (isset($image['file'])) {
			$thumbnail = wp_create_thumbnail($image['file'], 100);
	
			$this->image = '/' . str_replace(ABSPATH, '', $image['file']);
			$this->thumbnail = '/' . str_replace(ABSPATH, '', $thumbnail);		
		}
		
		// clean up the checkbox input for storage in the database
		if($this->published === 'on') {
			$this->published = 1;
		}

		if(!isset($this->published) or $this->published == '') {
			$this->published = 0;
		}
	}

}
class ne_NewsItems extends ne_Models {
	var $model = "ne_NewsItem";
	public $show_unpublished = 0;
	public $items_per_page = 10;
	public $show_top_number = -1;
	public $newer_than = 0;
	public $current_page = 1;
	public $sort = 'sort_date DESC';
	public $page_above = false;
	public $filter_tags = '';

	function allByDate() {
		//This query combines the date and sort_date fields into one column, `sort`, and then sorts by that field.
		$this->query("SELECT *, IF(sort_date > 0, sort_date, date) as sort FROM `ne_news` ORDER BY `sort` DESC");
	}

	function byDate($start, $limit) {
		$this->query("SELECT *, IF(sort_date > 0, sort_date, date) as sort FROM `ne_news` ORDER BY `sort` DESC LIMIT $start, $limit");
	}

	/*
	 * @brief Executes a query based on NewsItems class-level parameters
	 * and sets the page_above property
	 */
	function execute() {
		global $wpdb;

		//Calculate paging
		$start = $this->items_per_page * ($this->current_page - 1);
		$limit = "LIMIT $start, " . $this->items_per_page;

		//Start building the query
		$query = "SELECT " . $this->includeFields();

		//From
		if($this->filter_tags != '') { //If we need to filter by tag, then we need to join in all the tag tables
			$query .= "FROM ne_tag_relationships
				JOIN ne_tags ON ne_tag_relationships.tag_id = ne_tags.id 
				JOIN ne_news ON ne_tag_relationships.entry_id = ne_news.id ";
		}
		else {
			$query .= "FROM `ne_news` ";
		}

		//Where
		if($this->show_unpublished != 1 or $this->newer_than > 0) {
			$query .= "WHERE ";
		}

		$conditions = array();
		if($this->show_unpublished != 1) {
			$conditions[] = "ne_news.published = 1";
		}

		if($this->newer_than > 0) {
			//Newer_than is in number of days. We need to convert that into an absolute date.
			$absoluteTime = time() - $this->newer_than * 24 * 60 * 60;

			$conditions[] = "ne_news.date > '" . date("Y-m-d", $absoluteTime) . "'";
		}

		if($this->filter_tags != '') {
			$conditions[] = "ne_tag_relationships.type = 'news'";

			$tag_conditions = array();
			foreach(preg_split('/[\s,]+/', $this->filter_tags) as $tag) {
				$tag_conditions[] = "ne_tags.name = '{$tag}'";
			}
			$conditions[] = '(' . implode(' OR ', $tag_conditions) . ')';
		}

		$query .= implode(" AND ", $conditions) . ' ';

		//Group is needed so duplicate tags don't cause duplicate entries
		$query .= 'GROUP BY ne_news.id ';

		//Sort
		if(false !== strpos($this->sort, 'sort_date')) {
			if($this->sortby == 0) { // Sort by date
				$query .= "ORDER BY IF(sort_date > 0, sort_date, date)  ";
			}
			else if($this->sortby == 1) { // Sort alphabetically
				$query .= "ORDER BY `". $this->internalFieldName('headline') . "` ";
			}

			if(substr($this->sort, -4) == 'DESC') {
				$query .= 'DESC ';
			}
		}

		//Add the limit to the query but keep the query
		// so the limit can be changed later if necessary
		$query_with_limit = $query . $limit;

		//echo $query_with_limit;

		//Execute query
		//echo $query_with_limit;
		$this->query($query_with_limit);

		//Check for more records in case the Next Page link is to be displayed
		//If there are fewer records in the current recordset than the limit 
		//per page, then there is no need to check.
		if($this->count() == $this->items_per_page) {
			//Calculate what record would be next based on the current page
			$new_start = ($this->items_per_page * ($this->current_page));
			
			//Check to see if there is at least one record beyond the current records
			$limit = "LIMIT " . $new_start . ", 1";
			
			//The full new query
			$query_next = $query . $limit;
			
			//Execute the query
			$next = $wpdb->get_results($query_next, ARRAY_A);
			
			//echo $query_next . "  " . count($next);
			
			// Check to see if there is at least one more record
			if(count($next) > 0) {
				$this->page_above = true;	
			} else {
				$this->page_above =  false;
			}
		} else {
			$this->page_above = false;
		}
	}

}
class ne_Event extends ne_Model {
	function __construct() {
$this->fields = array('id','title','start_date','end_date','sort_date','location','date_location_text','short_description','long_description','image','thumbnail','published');
$this->field_types = array('id' => 'int','title' => 'varchar','start_date' => 'datetime','end_date' => 'datetime','sort_date' => 'date','location' => 'varchar','date_location_text' => 'text','short_description' => 'text','long_description' => 'text','image' => 'varchar','thumbnail' => 'varchar','published' => 'tinyint');
$this->primary_field = 'id';
$this->primary_field_increment = '';

	}

	var $table = "ne_events";
	var $modelName = "ne_Event";

	function has_thumbnail() {
		return (strlen($this->thumbnail) > 0 ? true : false);
	}
	
	function has_title() {
		return (strlen($this->title) > 0 ? true : false);
	}
	
	function has_start_date() {
		return (strlen($this->start_date) > 0 ? true : false);
	}
	
	function has_end_date() {
		return (strlen($this->end_date) > 0 ? true : false);
	}
	
	function has_location() {
		return (strlen($this->location) > 0 ? true : false);
	}
	
	function has_date_location_text() {
		return (strlen($this->date_location_text) > 0 ? true : false);
	}
	
	function has_tags() {
		return (strlen($this->tag_str()) > 0 ? true : false);
	}
	
	function has_short_description() {
		return (strlen($this->short_description) > 0 ? true : false);
	}
	
	function has_long_description() {
		return (strlen($this->long_description) > 0 ? true : false);
	}
	
	function has_image() {
		return (strlen($this->image) > 0 ? true : false);
	}
	
	
	function start_date_formatted($format = null) {
		if($format == null or $format == '0') {
			return date("F j, Y", strtotime($this->start_date));
		}
		else if($format == '1') {
			return date("m/d/y", strtotime($this->start_date));
		}
	}

	function end_date_formatted( $format = null) {
		if($format == null or $format == '0') {
			return date("F j, Y", strtotime($this->end_date));
		}
		else if($format == '1') {
			return date("m/d/y", strtotime($this->end_date));
		}
	}

	function editableStartDate() {
		if ( is_null ( $this->start_date ) || '' == $this->start_date ) {
			return '';
		} else {
			return date("m/d/Y G:i", strtotime($this->start_date));	
		}
	}

	function editableEndDate() {
		if ( is_null ( $this->end_date ) || '' == $this->end_date ) {
			return '';
		} else {
			return date("m/d/Y G:i", strtotime($this->end_date));	
		}
	}

	function tags() {
		$rel = new ne_TagRelationships;
		$rel->find("entry_id = {$this->id} AND type = 'event'", array('order' => 'tag_id ASC'));

		$tags = array();
		while (($t = $rel->next()) !== null) {
			$db_tag = new ne_Tag;
			$db_tag->load($t->tag_id);

			$tags[] = $db_tag;
		}

		return $tags;
	}

	function set_tags($tags) {
		$id = $this->id;

		if (isset($id)) {
			// remove old tags, if any
			$relationships = new ne_TagRelationships;
			$relationships->find("`entry_id` = {$id} AND `type` = 'event'");

			while (($r = $relationships->next()) !== null) {
				$r->drop();
			}

			// create new tags
			ne_TagRelationships::tag_entry($id, $tags, 'event');
		}
	}

	function tag_str($delim = ',') {
		$tags = array();
		foreach($this->tags() as $tag) {
			$tags[] = $tag->name;
		}
		
		return implode($delim . " ", $tags);
	}

	function thumbnailURL() {
		return get_option('siteurl') . $this->thumbnail;
	}

	function imageURL() {
		return get_option('siteurl') . $this->image;
	}

	function validateTitle() {
		if(strlen($this->title) == 0) {
			$this->setError('title', 'You must specify a title');
		}
	}

	function validateStart_date() {
		if(strlen($this->start_date) == 0) {
			$this->setError('start_date', 'You must specify a starting date');
		}
		if(strtotime($this->start_date) == false) {
			$this->setError('start_date', 'Invalid Date');
		}
	}

	/*
	 * @brief Handles the part of saving common to both the create and update functions
	 */
	function onSave() {
		//Format the start and end dates properly if they exist
		if (strlen($this->start_date) > 0) {
			$this->start_date = date( "Y-m-d H:i:s", strtotime($this->start_date) );
		}
		if (strlen($this->end_date) > 0) {
			$this->end_date = date( "Y-m-d H:i:s", strtotime($this->end_date) );
		}
		
		//Read any uploaded files into an array
		$image = wp_handle_upload($this->fileArr, array(
			'test_form' => false
		));

		//Check to see if a new image was uploaded
		if (isset($image['file'])) {
			$thumbnail = wp_create_thumbnail($image['file'], 100);
	
			$this->image = '/' . str_replace(ABSPATH, '', $image['file']);
			$this->thumbnail = '/' . str_replace(ABSPATH, '', $thumbnail);		
		}
		
		//clean up the checkbox input for storage in the database
		//Leave the value alone if it is already 0 or 1
		if($this->published === 'on') {
			$this->published = 1;
		}
		if(!isset($this->published) or $this->published == '') {
			$this->published = 0;
		}
	}

}
class ne_Events extends ne_Models {
	var $model = "ne_Event";

	//Where
	public $show_unpublished = 0;
	public $newer_than = 0;
	public $filter_tags = '';

	public $items_per_page = 10;
	public $current_page = 1;
	public $sort_field = 'start_date';
	public $sort_descending = false;
	public $page_above = false;

	
	/*
	 * @brief Executes a query based on Events class-level parameters
	 * and sets the page_above property
	 */
	function execute() {
		global $wpdb;
		
		//Calculate paging
		$start = $this->items_per_page * ($this->current_page - 1);
		$limit = " LIMIT $start, " . $this->items_per_page;
		
		//Start building the query - Specify the table so that if tag tables are, 
									//JOINed in then those fields don't disrupt the Group By statment below

		//In some versions of MySQL (or possbly Apache - not sure) the GROUP BY clause negates sorting
			//so the SQL is handled as an inner query and an outer query
		$inner_query = 'SELECT ' . $this->includeFields();
		
		//Special field for sorting - for each record use sort date if it exists, 
		//otherwise use start date
		//if(substr($this->sort, 0, 9) == 'sort_date') {
			//$query .= ', IF(sort_date > 0, sort_date, start_date) as event_sort ';
		//}
		
		//From
		if($this->filter_tags != '') { 
			//Filtering by tag so the tag tables need to be included
			$inner_query .= "FROM ne_tag_relationships
					JOIN ne_tags ON ne_tag_relationships.tag_id = ne_tags.id
					JOIN ne_events ON ne_tag_relationships.entry_id = ne_events.id ";
		}
		else {
			$inner_query .= "FROM `ne_events` ";
		}
		
		//Where - check for various conditions and add to an array of conditions
		if($this->show_unpublished <> 1) {
			//Only show published events unless unpublished have been specifically requested
			$conditions[] = "(ne_events.published = 1)";
		}

		if(is_numeric($this->newer_than)) {
			//Newer_than is in number of days so convert that into an absolute date.
			$absoluteTime = time() - $this->newer_than * 24 * 60 * 60;

			$conditions[] = "(start_date >= '" . date("Y-m-d", $absoluteTime) . "')";
		}

		if($this->filter_tags != '') {
			//Filter for any tags that were given
			$conditions[] = "(ne_tag_relationships.type = 'event')";

			$tag_conditions = array();
			foreach(preg_split('/[\s,]+/', $this->filter_tags) as $tag) {
				$tag_conditions[] = "(ne_tags.name = '{$tag}')";
			}
			$conditions[] = '(' . implode(' OR ', $tag_conditions) . ')';
		}
		
		if (count($conditions) > 0) {
			$inner_query .= 'WHERE ';
			$inner_query .= implode(" AND ", $conditions) . ' ';
		}
		
		//Group by is needed so multiple tags don't cause duplicate entries
		$inner_query .= 'GROUP BY ne_event_id ';
		
		//Prepare the inner query to be nested inside another
		$inner_query = '(' . $inner_query . ')';
		
		//Create a new outer query for sorting
		$query = 'SELECT * FROM ' . $inner_query . ' AS events ';
		
		//Sort - There is a 'sort_date' field but the user interface for filling it isn't working yet
		$query .= "ORDER BY " . $this->internalFieldName($this->sort_field);
		if($this->sort_descending) {
			$query .= ' DESC ';
		}
				
		//Add the limit to the query but keep the query 
		//so the limit can be changed later if necessary
		$query_with_limit = $query . $limit;
		
		//Execute the query
		$this->query($query_with_limit);

		//Check for more records in case the Next Page link is to be displayed
		//If there are fewer records in the current recordset than the limit 
		//per page, then there is no need to check.
		if($this->count() == $this->items_per_page) {
			//Calculate what record would be next based on the current page
			$new_start = ($this->items_per_page * ($this->current_page));
			
			//Check to see if there is at least one record beyond the current records
			$limit = "LIMIT " . $new_start . ", 1";
			
			//The full new query
			$query_next = $query . $limit;
			
			//Execute the query
			$next = $wpdb->get_results($query_next, ARRAY_A);
			
			//echo $query_next . "  " . count($next);
			
			// Check to see if there is at least one more record
			if(count($next) > 0) {
				$this->page_above = true;	
			} else {
				$this->page_above =  false;
			}
		} else {
			$this->page_above = false;
		}
	}
	

}
class ne_TagRelationship extends ne_Model {
	function __construct() {
$this->fields = array('id','entry_id','tag_id','type');
$this->field_types = array('id' => 'int','entry_id' => 'int','tag_id' => 'int','type' => 'enum');
$this->primary_field = 'id';
$this->primary_field_increment = '';

	}

	var $table = "ne_tag_relationships";
	var $modelName = "ne_TagRelationship";

}
class ne_TagRelationships extends ne_Models {
	var $model = "ne_TagRelationship";

/*
         * @brief Adds new tags as needed to the Tags table and adds relationship
         * records between the entry and the tags in the Tag Relationships table
         */
        function tag_entry($id, $tags, $type) {
                //Loop through each tag name in the array
                foreach ($tags as $tag_name) {
                        //Try to get a reference to an existing tag with this name
                        $t = new ne_Tag;
                        $t->load($tag_name, 'name');
                        
                        //Only add the tag if the name has a value
                        //Sometimes $tags comes in with a null tag name
                        if(!empty($tag_name)) {
                                if (empty($t->id)) {
                                        //A tag doesn't exist with this name so create one
                                        $t->name = $tag_name;
                                        $t->save();
                                }
                                
                                //Create the record in the Tag Relationships table to connect
                                        //the tag with the entry
                                $rel = new ne_TagRelationship;
                                $rel->entry_id = $id;
                                $rel->tag_id = $t->id;
                                $rel->type = $type;
        
                                $rel->save();
                                
                        }
                }
        }

}
class ne_Option extends ne_Model {
	function __construct() {
$this->fields = array('name','value','type');
$this->field_types = array('name' => 'varchar','value' => 'varchar','type' => 'enum');
$this->primary_field = 'name';
$this->primary_field_increment = '';

	}

	var $table = "ne_options";
	var $modelName = "ne_Option";
	
	/*
	 * @brief Converts number values to true|false - 0 == false
	 */
	function is_on() {
		return ($this->value == 0) ? false : true;
	}
	
	/*
	 * @brief Converts 'on' to 1 and everything else to 0
	 */
	function set_on($on) {
		$this->value = ($on === 'on') ? 1 : 0;
	}
	
/*	
	static function load_option($name) {
		$op = new self;
		$op->load($name);

		return $name->value;
	}
*/

}
class ne_Options extends ne_Models {
	var $model = "ne_Option";
	static public $options;  //Holds all the options
				 //so they don't have to be loaded one at a time
	
	static public $viewsDef = array(
		'headlines' => array(
			'header' => 'Headlines View',
			'action' => 'headlines',
			'description' => 'The headlines view displays a list of all the news items headlines.',
			'type' => 'news'
		),
		'newslist' => array(
			'header' => 'News List View',
			'action' => 'list',
			'description' => 'The news list view shows a list of all the published articles, including the headline, date, and an excerpt of the body of each item.',
			'type' => 'news'
		),
		'article' => array(
			'header' => 'Article View',
			'action' => 'article',
			'description' => 'The article view displays one article.',
			'type' => 'news'
		),
		'eventTitle' => array(
			'header' => 'Event Titles View',
			'action' => 'title',
			'description' => 'The Event Titles view allows a few events to be displayed in part of a page such as the home page or sidebar.',
			'type' => 'events'
		),
		'eventList' => array(
			'header' => 'Event List View',
			'action' => 'listView',
			'description' => 'The events list view shows a list of all the published events.',
			'type' => 'events'
		),
		'eventDetail' => array(
			'header' => 'Event Detail View',
			'action' => 'detail',
			'description' => 'The event detail view shows one event.',
			'type' => 'events'
		)
	);

	//Keep this array sorted by views!
	//The part of the 'name' before the '_' must match the view to which it belongs
		//This relationship is used in ne_optionsHelper_filterOptions 
			//and in optionsCtrlAdmin/index.phtml
	static public $optionsDef = array(
		//Headlines View Settings
		array(
			'name' 	=> 'headlines_filter_tags',
			'value' => '',
			'type' 	=> 'text',
			'label' => 'Show articles tagged',
			'short_tag' => 'tagged'
		),
		array(
			'name' 	=> 'headlines_filter_number',
			'value' => 5,
			'type' 	=> 'integer',
			'size' => 3,
			'label' => 'Show top X articles',
			'short_tag' => 'top'
		),
		array(
			'name' => 'headlines_filter_date',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Show articles newer than',
			'options' => array(
				'0' => 'disable',
				'7' => 'one week ago',
				'30' => 'one month ago',
				'180' => 'six months ago',
				'365' => 'one year ago'
			),
			'short_tag' => 'since'
		),
		array(
			'name' => 'headlines_date_format',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Date Format',
			'options' => array(
				'0' => 'January 21st, 2010',
				'1' => '01/21/10'
			),
			'short_tag' => 'date_format'
		),
		array(
			'name' => 'headlines_show_headline',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Headline',
			'short_tag' => 'headline'
		),
		array(
			'name' => 'headlines_show_date',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Date',
			'short_tag' => 'date'
		),
		array(
			'name' => 'headlines_show_author',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Author',
			'short_tag' => 'author'
		),
		array(
			'name' => 'headlines_show_excerpt',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Excerpt',
			'short_tag' => 'excerpt'
		),
		array(
			'name' => 'headlines_show_thumbnail_image',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Image Thumbnail',
			'short_tag' => 'thumbnail'
		),
		// News List View Settings
		array(
			'name' 	=> 'newslist_filter_tags',
			'value' => '',
			'type' 	=> 'text',
			'label' => 'Show articles tagged',
			'short_tag' => 'tagged'
		),
		array(
			'name' 	=> 'newslist_items_per_page',
			'value' => 10,
			'type' 	=> 'integer',
			'label' => 'Items per page',
			'size' => 5,
			'short_tag' => 'length'
		),
		array(
			'name' => 'newslist_sort',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Sort by',
			'options' => array(
				'0' => 'Date',
				'1' => 'Headline alphabetically'
			),
			'short_tag' => 'sort'
		),
		array(
			'name' => 'newslist_date_format',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Date Format',
			'options' => array(
				'0' => 'January 21st, 2010',
				'1' => '01/21/10'
			),
			'short_tag' => 'date_format'
		),
		array(
			'name' => 'newslist_show_more',
			'value' => '1',
			'type' => 'checkbox',
			'label' => 'Show "more" link at the end of excerpt',
			'short_tag' => 'more_link'
		),
		array(
			'name' => 'newslist_link_detail',
			'value' => '1',
			'type' => 'checkbox',
			'label' => 'Headline links to full article',
			'short_tag' => 'headline_link'
		),
		array(
			'name' 	=> 'newslist_show_unpublished',
			'value' => 0,
			'type' 	=> 'checkbox',
			'label' => 'Show unpublished articles',
			'short_tag' => 'unpublished'
		),
		array(
			'name' 	=> 'newslist_show_headline',
			'value' => 1,
			'type' 	=> 'checkbox',
			'label' => 'Show Headline',
			'short_tag' => 'headline'
		),
		array(
			'name' 	=> 'newslist_show_tags',
			'value' => 1,
			'type' 	=> 'checkbox',
			'label' => 'Show tags',
			'short_tag' => 'tags'
		),
		array(
			'name' 	=> 'newslist_show_date',
			'value' => 1,
			'type' 	=> 'checkbox',
			'label' => 'Show date',
			'short_tag' => 'date'
		),
		array(
			'name' 	=> 'newslist_show_author',
			'value' => 1,
			'type' 	=> 'checkbox',
			'label' => 'Show author',
			'short_tag' => 'author'
		),
		array(
			'name' 	=> 'newslist_show_excerpt',
			'value' => 1,
			'type' 	=> 'checkbox',
			'label' => 'Show article excerpt',
			'short_tag' => 'excerpt'
		),
		array(
			'name' 	=> 'newslist_show_thumbnail_image',
			'value' => 1,
			'type' 	=> 'checkbox',
			'label' => 'Show image thumbnail',
			'short_tag' => 'thumbnail'
		),
		//Article View Settings
		array(
			'name' => 'article_date_format',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Date Format',
			'options' => array(
				'0' => 'January 21st, 2010',
				'1' => '01/21/10'
			),
			'short_tag' => 'date_format'
		),
		array(
			'name' => 'article_landing_page',
			'value' => -1,
			'type' => 'integer',
			'label' => 'Page',
			'short_tag' => 'page'
		),
		array(
			'name' => 'article_show_tags',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show tags'
		),
		array(
			'name' => 'article_show_date',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show date'
		),
		array(
			'name' => 'article_show_author',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show author'
		),
		array(
			'name' => 'article_image_display',
			'value' => 'thumbnail',
			'type' => 'radio',
			'options' => array(
				'none' => 'Never',
				'thumbnail' => 'Thumbnail',
				'full' => 'Full Size'
			),
			'label' => 'Show Image',
			'short_tag' => 'image_display'
		),
		
		//Event Title View Settings
		array(
			'name' 	=> 'eventTitle_filter_tags',
			'value' => '',
			'type' 	=> 'text',
			'label' => 'Show events tagged',
			'short_tag' => 'tagged'
		),
		array(
			'name' 	=> 'eventTitle_filter_number',
			'value' => 5,
			'type' 	=> 'integer',
			'size' => 3,
			'label' => 'Number of events to show',
			'short_tag' => 'top'
		),
		array(
			'name' => 'eventTitle_filter_date',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Show events with start dates later than',
			'options' => array(
				'0' => 'the current day',
				'7' => 'one week ago',
				'30' => 'one month ago',
				'180' => 'six months ago',
				'365' => 'one year ago'
			),
			'short_tag' => 'since'
		),
		array(
			'name' => 'eventTitle_date_format',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Date Format',
			'options' => array(
				'0' => 'January 21st, 2010',
				'1' => '01/21/10'
			),
			'short_tag' => 'date_format'
		),
		array(
			'name' => 'eventTitle_show_title',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Title',
			'short_tag' => 'title'
		),
		array(
			'name' => 'eventTitle_show_start_date',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Start Date',
			'short_tag' => 'start_date'
		),
		array(
			'name' => 'eventTitle_show_end_date',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show End Date',
			'short_tag' => 'end_date'
		),
		array(
			'name' => 'eventTitle_show_location',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Location',
			'short_tag' => 'location'
		),
		array(
			'name' => 'eventTitle_show_date_location_description',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Date Location Description',
			'short_tag' => 'date_location'
		),
		array(
			'name' => 'eventTitle_show_short_description',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Short Description',
			'short_tag' => 'short_description'
		),
		array(
			'name' => 'eventTitle_show_thumbnail_image',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Image Thumbnail',
			'short_tag' => 'thumbnail'
		),
		array(
			'name' => 'eventTitle_show_list_link',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show a Link to the Events List',
			'short_tag' => 'list_link'
		),
		
		//Event List View Settings
		array(
			'name' => 'eventList_date_format',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Date Format',
			'options' => array(
				'0' => 'January 21st, 2010',
				'1' => '01/21/10'
			),
			'short_tag' => 'date_format'
		),

		array(
			'name' 	=> 'eventList_items_per_page',
			'value' => 10,
			'type' 	=> 'integer',
			'label' => 'Items per page',
			'short_tag' => 'perpage'
		),
		array(
			'name' => 'eventList_show_title',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Title',
			'short_tag' => 'title'
		),
		array(
			'name' => 'eventList_show_start_date',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Start Date',
			'short_tag' => 'start_date'
		),
		array(
			'name' => 'eventList_show_end_date',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show End Date',
			'short_tag' => 'end_date'
		),
		array(
			'name' => 'eventList_show_location',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Location',
			'short_tag' => 'location'
		),
		array(
			'name' => 'eventList_show_date_location_description',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Date Location Description',
			'short_tag' => 'date_location'
		),
		array(
			'name' => 'eventList_show_short_description',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Short Description',
			'short_tag' => 'short_description'
		),
		array(
			'name' => 'eventList_show_long_description',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Long Description',
			'short_tag' => 'long_description'
		),
		array(
			'name' => 'eventList_show_tags',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Tags',
			'short_tag' => 'tags'
		),
		array(
			'name' => 'eventList_show_unpublished',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Unpublished Events',
			'short_tag' => 'unpublished'
		),
		array(
			'name' => 'eventList_show_thumbnail_image',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Image Thumbnail',
			'short_tag' => 'thumbnail'
		),
		
		//Event Detail View Settings
		array(
			'name' => 'eventDetail_date_format',
			'value' => 0,
			'type' => 'radio',
			'label' => 'Date Format',
			'options' => array(
				'0' => 'January 21st, 2010',
				'1' => '01/21/10'
			),
			'short_tag' => 'date_format'
		),
		array(
			'name' => 'eventDetail_landing_page',
			'value' => -1,
			'type' => 'integer',
			'label' => 'Page',
			'short_tag' => 'page'
		),
		array(
			'name' => 'eventDetail_show_start_date',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Start Date',
			'short_tag' => 'start_date'
		),
		array(
			'name' => 'eventDetail_show_end_date',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show End Date',
			'short_tag' => 'end_date'
		),
		array(
			'name' => 'eventDetail_show_location',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Location',
			'short_tag' => 'location'
		),
		array(
			'name' => 'eventDetail_show_date_location_description',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Date Location Description',
			'short_tag' => 'date_location'
		),
		array(
			'name' => 'eventDetail_show_short_description',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Short Description',
			'short_tag' => 'short_description'
		),
		array(
			'name' => 'eventDetail_show_long_description',
			'value' => 0,
			'type' => 'checkbox',
			'label' => 'Show Long Description',
			'short_tag' => 'long_description'
		),
		array(
			'name' => 'eventDetail_show_tags',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show Tags',
			'short_tag' => 'tags'
		),
		array(
			'name' => 'eventDetail_image_display',
			'value' => 'thumbnail',
			'type' => 'radio',
			'options' => array(
				'none' => 'Never',
				'thumbnail' => 'Thumbnail',
				'full' => 'Full Size'
			),
			'label' => 'Show Image',
			'short_tag' => 'image'
		),
		array(
			'name' => 'eventDetail_show_list_link',
			'value' => 1,
			'type' => 'checkbox',
			'label' => 'Show a link to the List View',
			'short_tag' => 'list_link'
		)
				
			);
	/*
	 * @brief Loads all options from the database table
	 */
	static function load() {
		if (empty(self::$options)) {
			self::$options = new ne_Options();
			self::$options->find();
		}
	}

	/*
	 * @brief Sets a single option given the name and value
	 * 
	 * Note that this ends up with more database calls than necessary,
	 * particularly if options weren't already loaded, but it isn't used that often
	 */
	static function set($name, $value) {
		$op = self::get($name);

		$op->value = $value;
		$op->save();
	}

	/*
	 * @brief Returns the value of the option named in the parameter
	 */
	static function get($name) {
		ne_Options::load();

		if (isset(self::$options)) {
			self::$options->move(0);
			while ($op = self::$options->next()) {
				if ($op->name == $name) {
					return $op;
				}
			}
		}

		foreach(self::$optionsDef as $option) {
			if($option['name'] == $name) {
				$op = new ne_Option();
				$op->data = $option;

				return $op;
			}
		}
		return null;
	}
	
	/*
	 * @brief A quick way to get the value for the named option
	 */
	static function value($name) {
		return self::get($name)->value;
	}

}
class ne_Tag extends ne_Model {
	function __construct() {
$this->fields = array('id','name');
$this->field_types = array('id' => 'int','name' => 'varchar');
$this->primary_field = 'id';
$this->primary_field_increment = '';

	}

	var $table = "ne_tags";
	var $modelName = "ne_Tag";

}
class ne_Tags extends ne_Models {
	var $model = "ne_Tag";

}
//newsandeventsHelper.php

function ne_newsAndEventsHelper_isInstalled() {
		$requiredTables = array('news', 'events', 'options', 'tags', 'tag_relationships');

		foreach($requiredTables as $table) {
			if(!ne_DBHelper_tableExists("ne_$table")) {
				return false;
			}
		}

		return true;

}


function ne_newsAndEventsHelper_fileArray($formpart, $name) {
		return array(
			'name' => $_FILES[$formpart]['name'][$name],
			'type' => $_FILES[$formpart]['type'][$name],
			'tmp_name' => $_FILES[$formpart]['tmp_name'][$name],
			'error' => $_FILES[$formpart]['error'][$name],
			'size' => $_FILES[$formpart]['size'][$name]
		);

}


function ne_newsAndEventsHelper_removeUnusedTags() {
		$unusedTags = new ne_Tags();
		$unusedTags->query("SELECT ne_tags.*
			FROM ne_tag_relationships
			RIGHT JOIN ne_tags ON ne_tags.id = ne_tag_relationships.tag_id
			WHERE ne_tag_relationships.id IS NULL");

		while($tag = $unusedTags->next()) {
			$tag->drop();
		}

}

//newsHelper.php

function ne_newsHelper_formatDate($date) {
		$date = strtotime($date);
		return date('n/j/Y', $date);

}


function ne_newsHelper_tag_str($news) {
		$tags = $news->tags();
		$str = '';

		foreach ($tags as $t) {
			$str .= "{$t->name}, ";
		}

		$str = trim($str, ', ');

		return $str;

}


function ne_newsHelper_tag_links($news) {
		$tags = $news->tags();
		
		$links = array();
		foreach($tags as $t) {
			$link = ne_urlHelper_publicURL(array(
				'action' => 'bytag',
				'ktarguments' => "t={$t->name}"
			));

			$links[] =  "<a href='$link'>{$t->name}</a>";
		}

		return implode(', ', $links);

}


function ne_newsHelper_detail_link($id, $name) {
		$detailLink = ne_urlHelper_publicURL(array(
			'action' => 'article',
			'ktarguments' => 'id=' . $id
		));

		if(ne_Options::value('article_landing_page') != -1) {
			$prefix = get_permalink(ne_Options::value('article_landing_page'));

			if(substr($prefix, -1) != '/') $prefix .= '/';
			return "<a href='{$prefix}{$detailLink}'>{$name}</a>";
		}
		else {
			return "<a href='$detailLink'>{$name}</a>";
		}

}


function ne_newsHelper_text_with_more_link($text, $link) {
		if ('</p>' == substr($text, -4, 4)) {
			return substr_replace($text, $link . '</p>', -4);
		} else {
			return $text . '</p>';
		}

}


function ne_newsHelper_nextPage($pageNumber) {
		return "<a href='" . ne_newsHelper_formulatePageLink($pageNumber + 1) . "'>next page</a>";

}


function ne_newsHelper_previousPage($pageNumber) {
		return "<a href='" . ne_newsHelper_formulatePageLink($pageNumber - 1) . "'>previous page</a>";

}


function ne_newsHelper_formulatePageLink($number) {
		//$link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
		
		$link = get_permalink();
		
		//Find out if there is already a query as part of the link
		if (strpos($link, '?') === false ) {
			$separator = '?';
		} else {
			$separator = '&';
		}
		
		//Look through any GET parameters and make sure the 'page' parameter is there
			//and correctly valued
		if(count($_GET) > 0) {
			$gets = array();
			$setPage = false;
			foreach($_GET as $name => $value) {
				if($name == 'page') {
					$setPage = true;
					$value = $number;
				}
				if ('page_id' !== $name) {
					$gets[] = $name . "=" . $value;
				}
			}

			if(!$setPage) {
				$gets[] = "page=$number";
			}
			
			//Add the Gets back in
			$link .= $separator . implode('&', $gets);
		}
		else {
			//Add the page parameter
			$link .= $separator . 'page=' . $number;
		}

		return $link;

}

//eventsHelper.php

function ne_eventsHelper_formatDate($date) {
		$date = strtotime($date);
		return date('n/j/Y', $date);

}


function ne_eventsHelper_tag_links($event) {
		$tags = $event->tags();
		
		$links = array();
		foreach($tags as $t) {
			$link = ne_urlHelper_publicURL(array(
				'action' => 'bytag',
				'ktarguments' => "tag={$t->name}"
			));

			$links[] =  "<a href='$link'>{$t->name}</a>";
		}

		return implode(', ', $links);

}


function ne_eventsHelper_detail_link($id, $name) {
		$detailLink = ne_urlHelper_publicURL(array(
			'action' => 'detail',
			'ktarguments' => 'id=' . $id
		));

		return "<a href='{$detailLink}'>{$name}</a>";

}


function ne_eventsHelper_nextPage($pageNumber) {
		return "<span class='next_page'><a href='" . ne_eventsHelper_formulatePageLink($pageNumber + 1) . "'>next page</a></span>";

}


function ne_eventsHelper_previousPage($pageNumber) {
		return "<span class='next_page'><a href='" . ne_eventsHelper_formulatePageLink($pageNumber - 1) . "'>previous page</a></span>";

}


function ne_eventsHelper_formulatePageLink($number) {
		//$link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
		
		$link = get_permalink();
		
		//Find out if there is already a query as part of the link
		if (strpos($link, '?') === false ) {
			$separator = '?';
		} else {
			$separator = '&';
		}
		
		//Look through any GET parameters and make sure the 'page' parameter is there
			//and correctly valued
		if(count($_GET) > 0) {
			$gets = array();
			$setPage = false;
			foreach($_GET as $name => $value) {
				if($name == 'page') {
					$setPage = true;
					$value = $number;
				}
				if ('page_id' !== $name) {
					$gets[] = $name . "=" . $value;
				}
			}

			if(!$setPage) {
				$gets[] = "page=$number";
			}
			
			//Add the Gets back in
			$link .= $separator . implode('&', $gets);
		}
		else {
			//Add the page parameter
			$link .= $separator . 'page=' . $number;
		}

		return $link;

}

//optionsHelper.php

function ne_optionsHelper_get($name) {
		return ne_Options::value($name);

}


function ne_optionsHelper_show($view, $name) {
		return ne_optionsHelper_is_on($view . '_show_' . $name);

}


function ne_optionsHelper_is_on($name) {
		$op = ne_Options::get($name);

		return (empty($op)) ? false : $op->is_on();

}


function ne_optionsHelper_showDropDown($optionName, $label = '') {
		?>
		<select id='<?php echo "$optionName" ?>'>
			<option value='show'>Show</option>
			<option value='hide'>Hide</option>
			<option value='default' selected='selected'>Use Default</option>
		</select>
		<?php 
		if(strlen($label) > 0) {
			echo $label;
		}
		else {
			echo ucwords(str_replace("_", " ", $option));
		}

}


function ne_optionsHelper_useDefaultCheckbox($id) {
		echo "<input type='checkbox' id='{$id}_useDefault' class='useDefault' checked='checked'> Use Default";

}


function ne_optionsHelper_mergeOptions($ktf_args, $view) {
		//New array to hold the results
		$merged = array();

		$options = ne_optionsHelper_filterOptions($view);

		//Loop through the overrides
		foreach($options as $option) {
			$default = $option['name'];
			$override = $option['short_tag'];

			$setting = ((ne_optionsHelper_get($default) != null) && $ktf_args[$override] >= 0 ? $ktf_args[$override] : ne_optionsHelper_get($default) );

			//Allow an override from the short tags only if one was expected in
			//the override array and there is an override
			if(isset($ktf_args[$override]) && $ktf_args[$override] >= 0) {
				$setting = $ktf_args[$override];
			} else {
				$setting = ne_Options::value($default);
			}
			
			//Convert the 'show' options to true and false
			if(false !== strpos($default, 'show')) {
				$setting = ($setting == 'true' ? true : ($setting == 1 ? true : false));
			}
			
			//Add the setting to the results
			$merged[$default] = $setting;
		}

		return $merged;
		

}


function ne_optionsHelper_filterOptions($view) {
		$return = array();
		foreach(ne_Options::$optionsDef as $op) {
			list($v) = explode('_', $op['name']);

			if($view == $v) {
				$return[] = $op;
			}
		}

		return $return;

}


function ne_optionsHelper_buildShowArrayJavascript($options) {
		$names = array();
		foreach($options as $option) {
			if(false !== strstr($option['name'], 'show')) { //Look only for options that control showing fields in views
				//Use the specified short tag if available, otherwise make an educated guess
				if(isset($option['short_tag'])) {
					$short_tag = $option['short_tag'];
				}
				else {
					//Guesses at a value for the short tag.
					//Example: "newslist_show_thumbnail_image" becomes "thumbnail_image"
					//Example: "article_show_author" becomes "author"
					$short_tag = ne_optionsHelper_guessShortTag($option['name']);
				}
				

				$ops[$short_tag] = $option['name'];
			}
		}

		//This code puts together the javascript object. Keys are the short tag name, value is the option name.
		$str = "{\n\t\t\t";

		$i = 0;
		$count = count($ops);
		foreach($ops as $tag => $name) {
			$str .= "$tag: '$name'";

			if($i != ($count - 1)) $str .= ',';

			$str .= "\n\t\t\t";

			$i++;
		}

		$str .= '}';

		return $str;

}


function ne_optionsHelper_guessShortTag($optionName) {
		return implode('_', array_slice(explode('_', $optionName), 2));

}

//adminHelper.php

function ne_adminHelper_checkFileForErrors($filename) {

		$errorChecker = ne_adminHelper_errorCheckerPath($filename);
		$errors = file_get_contents( $errorChecker );

		return $errors;

}


function ne_adminHelper_errorCheckerPath($filename) {
		$pattern = "/^" . str_replace("/", "\/", KTF_ROOTPATH ) . "\/([a-zA-Z0-9]+)\/([a-zA-Z0-9]+)\/([a-zA-Z.0-9]+)$/";
		preg_match( $pattern, $filename, $matches );

		list(, $domain, $folder, $fileName ) = $matches;

		$errorChecker = "http://" . $_SERVER['SERVER_NAME'] . "/" . substr(str_replace($_SERVER['DOCUMENT_ROOT'], "", ABSPATH ), 0, -1) . str_replace(ABSPATH, "", KTF_FWPATH) . "/errorchecker.php?file=$domain/$folder/$fileName";
		
		return $errorChecker;

}


function ne_adminHelper_errorIFrame($filename) {
		return "<iframe src='" . ne_adminHelper_errorCheckerPath($filename) . "'></iframe>";

}

//templateHelper.php

function ne_templateHelper_loadTemplate($path, $tags) {
		$template = file_get_contents($path);
		if(is_array($tags)) {
			foreach($tags as $tag => $value) {
				$template = str_replace('{' . $tag . '}', $value, $template);
			}
		}

		return $template;

}


class ne_tableHelper {
        private $inTable = false;
        private $inRow = false;
	private $inColumn = false;
	private $inHeaderColumn = false;
        private $alternating = false;
        private $numRows = 0;
        
	private function tagFromParams($params) {
		return (isset($params['id']) ? "id='{$params['id']}' " : '') . (isset($params['class']) ? "class='{$params['class']}' " : '') . (isset($params['style']) ? "style='{$params['style']}'" : '');
	}

        /**
         * @brief Opens a <table> tag.
         */
        function startTable($params = array()) {
                if(!$this->inTable) {
			echo "<table " . $this->tagFromParams($params) . ">";

                        $this->inTable = true;
                        $this->alternating = $params['alternating'];
                } else {
                        fwwarning('Tried to start a new table before ending the previous one.');
                }
        }
        
        /**
         * @briefs Opens a <table> tag with an alternating table.
         */
        function startAlternatingTable() {
                $this->startTable(true);
        }
        
        /**
         * @brief Opens a <tr> tag.
         */
        function newTableRow($params = array()) {
                if(!$this->inTable) {
                        fwwarning('Tried to output a table row while not in a table.');
                        return;
                }
                
		if($this->inColumn) {
			echo "\n\t\t</td>\n";
			$this->inColumn = false;
		}

                if($this->inRow) {
                        echo "\n\t</tr>\n";
                }
                
                if($this->alternating && ($this->numRows % 2) == 0) {
                        echo "\n\t<tr class=\'alternate\' " . $this->tagFromParams($params) . ">\n";
                } else {
                        echo "\n\t<tr " . $this->tagFromParams($params) . ">\n";
                }
                
                $this->inRow = true;
                $this->numRows ++;
        }

	function endTableRow() {
		if($this->inColumn) {
			echo "\n\t\t</td>\n";
			$this->inColumn = false;
		}

		if($this->inRow) {
			echo "\n\t</tr>\n";
			$this->inRow = false;
		}
	}
        
        /**
         * @brief Spits out a <td> tag
         */
        function newTableColumn($params = array()) {
		if(!$this->inRow) {
			fwwarning('Tried to create a table column while not in a row.');
			return;
		}

		if($this->inColumn) {
			echo "\n\t\t</td>\n\t\t<td " . $this->tagFromParams($params) . ">\n";
		}
		else if($this->inHeaderColumn) {
			echo "\n\t\t</th>\n\t\t<td " . $this->tagFromParams($params) . ">\n";
			$this->inHeaderColumn = false;
		}
		else {
			echo "\n\t\t<td " . $this->tagFromParams($params) . ">\n";
		}
		$this->inColumn = true;
        }

	/**
	 * @brief Spits out a th tag
	 */
	function newHeaderColumn($params = array()) {
		if(!$this->inRow) {
			fwwarning('Tried to create a table column while not in a row.');
			return;
		}

		if($this->inColumn) {
			echo "\n\t\t</td>\n\t\t<th" . $this->tagFromParams($params) . ">\n";
			$this->inColumn = false;
		}
		else if($this->inHeaderColumn) {
			echo "\n\t\t</th>\n\t\t<th" . $this->tagFromParams($params) . ">\n";
		}
		else {
			echo "\n\t\t<th " . $this->tagFromParams($params) . ">\n";
		}
		$this->inHeaderColumn = true;
        }

        /**
         * @brief Closes a <table> tag.
         */
        function endTable() {
                if(!$this->inTable) {
                        fwwarning('Tried to end a table without starting one first.');
                        return;
                }
		
		if($this->inColumn) {
			echo "\n\t\t</td>\n";
		}

		if($this->inHeaderColumn) {
			echo "\n\t\t</th>\n";
		}

                if($this->inRow) {
                        echo "\n\t</tr>\n";
                }
                echo "</table>\n";
                $this->inTable = false;
                $this->inRow = false;
        }

}

function ne_Table() {
	static $instance;
	
	if(!isset($instance)) {
		$instance = new ne_tableHelper();
		return $instance;
	}
	else {
		return $instance;
	}
}

//directoryHelper.php

function ne_directoryHelper_scanDirectory( $directory, $foldersOnly = false ) {
		//Fix up $directory
		if( substr($directory, -1) != '/' ) $directory .= '/';

		$dh = opendir( $directory );
		while( ($filename = readdir($dh) ) !== false ) {
			if( $filename != "." and $filename != ".." ) {
				if( $foldersOnly and !is_dir($directory . $filename) ) {
					continue;
				}
				$files[] = $filename;
			}
		}

		return $files;

}

//dbHelper.php

function ne_DBHelper_tableExists($tableName) {
		global $wpdb;
		$results = $wpdb->query("SHOW TABLES LIKE '$tableName'");
		
		return ( $results == 0 ? false : true );

}


function ne_DBHelper_createTable(array $info, $return = false) {
		global $wpdb;
		
		if( !isset($info['name']) ) {
			return false;
		}

		$type = ( isset($info['type']) ? $info['type'] : 'myisam');

		$query = "CREATE TABLE `{$info['name']}` (";
		
		foreach( $info['fields'] as $fieldName => $fieldInfo ) {
			$query .= "`$fieldName` {$fieldInfo['type']}";
			if( $fieldInfo['length'] ) {
				$query .= "({$fieldInfo['length']})";
			}

			if($fieldInfo['auto_increment'] == true) {
				$query .= " AUTO_INCREMENT";
			}
			$query .= ", ";
		}

		if($info['index']) {
			$query .= "INDEX (`{$info['index']}`)";
		}
		
		if($info['primary_key']) {
			$query .= " PRIMARY KEY (`{$info['primary_key']}`) ";
		}

		$query .= ") ENGINE = {$type};";
		
		if($return == false) {
			$results = $wpdb->query($query);

			return ($results === false ? false : true );
		}
		else {
			return $query;
		}

}


function ne_DBHelper_dropTable($tableName) {
		global $wpdb;

		if ( ne_DBHelper_tableExists($tableName) ) {
			$query = "DROP TABLE $tableName";
			$wpdb->query($query);
			return true;
		}
		
		 return false;

}


class ne_formHelper {
        var $inForm = false;
        var $formPrefix = '';
        var $formPart = '';
       
        /**
         * @brief Capture POST'd data to an object.
         */
        function captureObjectFromPOST($formPart, $object, $prefix = '') {
                if(!is_object($object)) {
                        fwwarning('Object parameter is not an actual object!');
                        return;
                }
               
                $class = get_class($object);
               
                if(!isset(ktf_sotu()->models[$class])) {
                        fwwarning('Object parameter is not of a recognized model class.');
                        return;
                }
               
                if($prefix != '' && !isset($_POST[$prefix])) {
                        fwwarning('No form fields use the prefix ' . $prefix);
                        return;
                } elseif($prefix != '' && !isset($_POST[$prefix][$formPart])) {
                        fwwarning('I couldn\'t find the requested form data.');
                        return;
                } elseif($prefix == '' && !isset($_POST[$formPart])) {
                        fwwarning('I couldn\'t find the requested form data.');
                        return;
                }
               
                /**
                 * First, get the target class's ivars. We're only going to copy the form values that
                 * share field names with an ivar. We'll also do a little sanitization.
                 */
                $fields = ktf_sotu()->models[$class]['fields'];
                $fieldTypes = ktf_sotu()->models[$class]['fieldTypes'];
               
                foreach($fields as $var) {
                        if(substr($var, 0, 3) == 'fw_')
                                continue;

                        $posted = $this->getPOST($prefix, $formPart, $var);
                       
                        // TODO: We should sanitize integers and such here
                        $object->$var = $posted;
                }
        }
       
        /**
         * @brief Capture POST'd data to an array.
         */
        function captureArrayFromPOST($formPart, $prefix = '') {
                if($prefix != '' && !isset($_POST[$prefix])) {
                        fwwarning('No form fields use the prefix ' . $prefix);
                        return Array();
                } elseif($prefix != '' && !isset($_POST[$prefix][$formPart])) {
                        fwwarning('I couldn\'t find the requested form data.');
                        return Array();
                } elseif($prefix == '' && !isset($_POST[$formPart])) {
                        fwwarning('I couldn\'t find the requested form data.');
                        return Array();
                }
               
                if($prefix != '') {
                        return $_POST[$prefix][$formPart];
                } else {
                        return $_POST[$formPart];
                }
        }
       
        /**
         * @brief Starts a new form.
         */
	function startForm($action, $page = '', $prefix = '', $otherHTML = '') {
                if($this->inForm) {
                        fwwarning('Tried to start a new form before ending the previous one.');
                        return;
                }
               
                $this->formPrefix = $prefix;
                $this->formPart = '';
               
		if(substr($page, 0, 4) == 'ktf_') $page = substr($page, 4, strlen($page) - 4);

                echo '<form method="post" enctype="multipart/form-data" action="' . basename($_SERVER['SCRIPT_NAME']) . '?page=' . $page . "_" . $action . ".php" . '"';
                if($otherHTML != '') {
                        echo ' ' . $otherHTML;
                }
                echo '>';
               
                $this->inForm = true;
        }

       
        /**
         * @brief Sets the form part.
         */
        function setFormPart($part) {
                $this->formPart = $part;
        }

        /**
         * @brief Ends the current form.
         */
        function endForm() {
                if(!$this->inForm) {
                        fwwarning('Tried to end a form that was never started.');
                        return;
                }
                echo '</form>';
               
                $this->inForm = false;
        }

        /**
         * @brief Outputs a label for a form field.
         */
        function newLabel($text, $forField) {
                if(!$this->inForm) {
                        fwwarning('Tried to add a label to a nonexistent form.');
                        return;
                }
                echo '<label for="' . $this->fieldID($forField) . '">' . $text . '</label>';
        }

	/**
	 * @brief Outputs a number input field
	 */
	function newIntegerField($field, $value) {
		if(!$this->inForm) {
			fwwarning('Tried to add an integer field to a nonexistent form.');
			return;
		}
		//For now, it just generates a basic text field
		Form()->newTextField($field, $value);
	}

        /**
         * @brief Outputs a text field.
         */
        function newTextField($field, $value = '', $size = 40, $disabled = false ) {
                if(!$this->inForm) {
                        fwwarning('Tried to add a text field to a nonexistent form.');
                        return;
                }
                if( $disabled == true ) {
                        $disabled = "disabled";
                }
                else {
                        $disabled = "";
                }
		
		$value = htmlspecialchars($value);

                echo '<input type="text" size="' . $size . '" name="' . $this->fieldName($field) . '" id="' . $this->fieldID($field) . '" value="' . $value . '" ' . $disabled . ' />';
        }

	/**
	 * @brief Outputs short instructions on how to use a form element
	 */
        function instructions( $instructions ) {
                if( !$this->inForm ) {
                        fwwarning('Tried to add instructions to a nonexistent form.');
                }
                echo " <span style='vertical-align: top' class='description'>$instructions</span>";
        }
	
        /**
         * @brief Outputs a text area. Be sure to set the correct path in wordpress/wp-includes/js/openwysiwyg/wysiwyg.js!
         */
        function newTextArea($field, $cols = 60, $rows = 10,  $value = '', $richtext = true, $smallrtf = false) {
                if(!$this->inForm) {
                        fwwarning('Tried to add a text area to a nonexistent form.');
                        return;
                }
		
		$classes = '';
                if( $richtext  ) {
                        echo "<script type='text/javascript'>";
						
			$text_area_editor = get_option('text_area_editor');							
			if($text_area_editor == null || $text_area_editor=='tinymce') {
				// activate this line if the user chooses tinyMCE on the settings page
				echo 'tinyMCE.execCommand("mceAddControl", true, "' . $this->fieldID( $field ) . '");';
			} elseif($text_area_editor=='wysiwyg') {
				// activate this line if the user chooses openwysiwyg on the settings page or by default
				echo "WYSIWYG.attach('" . $this->fieldID( $field ) . "', " . ($smallrtf == true ? 'small' : 'full') . ");";
			} else {
			    fwwarning('No text area editor selected.');
			}
						
                        echo "</script>";
                }

                echo "<textarea name='{$this->fieldName($field)}' id='{$this->fieldID($field)}' cols='{$cols}' rows='{$rows}' class='{$classes}'>$value</textarea>";
        }

        /**
         * @brief Outputs a file field.
         */
        function newFileField($field,  $value = '' )    {
                if(!$this->inForm) {
                        fwwarning('Tried to add a file field to a nonexistent form.');
                        return;
                }
                echo '<input type="file" name="' . $this->fieldName($field) . '" id="' . $this->fieldID($field) . '" value="' . $value . '" />';
        }      

        /**
         * @brief Takes data from $_FILES and uploads it to user defined directory
        */

        function uploadFile( $prefix, $name, $directory = null ) {
                if( !empty($_FILES[$prefix]['name'][$name] ) ) {
                        $target = ABSPATH . ( is_null( $directory ) ? KTF_UPLOAD_DIR : $directory );
                        $target .= basename( str_replace(" ", "-", $_FILES[ $prefix ][ 'name' ][ $name ]) );
                        move_uploaded_file( $_FILES[ $prefix ][ 'tmp_name' ][ $name ], $target );
                        return $target;
                }
                return "";
        }

        /**
         * @brief Outputs a submit button.
         */
        function newSubmitButton($value = 'submit') {
                if(!$this->inForm) {
                        fwwarning('Tried to add a submit button to a nonexistent form.');
                        return;
                }
                echo '<input class="button-primary" type="submit" value="' . $value . '" />';
        }
       
        /**
         * @brief Outputs a select list from a dabase table.
         */
        function db_select( $formPart, $field, $recordset, $fkidfield, $fklabelfield, $value = '', $multiple = false, $size = "" ) {
                $outset = '<select  name="' . $formPart . '[' . $field . ']' . ( $multiple ? '[]' : '' ) . '" id="' . $formPart . '_' . $field . '" ' . ( $multiple ? 'multiple' : '' ) . ' size="' . $size . '" ' . ( $multiple ? "style='height:" . 2 * $size . "em'" : "" ) . '>'."\n";
                $outset .= '<option value="-1"> -- choose -- </option>'."\n";
                
                //Check to see of the recordset parameter is an array
                if (is_array($recordset)) {
                	//Assume that the array is an associative array with 
                		//the database id as key and display value as value
                	
                	foreach($recordset as $option_key => $option_value) {
                		//By default, this option is not selected
                		$selected = '';
                		
                		//Check to see if the value for the field is the same as the key of
                			//this element of the array
                		if( $option_key == $value) {
                			$selected = ' selected="selected" ';
                		}
                		
                		//create the html for the option
                		$outset .= '<option value="'. $option_key .'"'. $selected .'>'. $option_value .'</option>'."\n";
                	}
                } else {
	                while(($row = $recordset->next()) !== null) {
	                        $selected = '';
	                        if( is_string( $value ) ) {
	                                if ( $row->$fkidfield == $value && $value != '' ) {
	                                        $selected = ' selected="selected" ';
	                                }
	                        }
	                        elseif( is_array( $value ) ) {
	                                if( in_array( $row->$fkidfield, $value ) ) {
	                                        $selected = ' selected="selected" ';
	                                }
	                        }
	                        $outset .= '<option value="'. $row->$fkidfield .'"'. $selected .'>'. $row->$fklabelfield .'</option>'."\n";
	                }            	
                }
                $outset .= '</select>';
                echo $outset;
        }

	/**
	 * @brief Starts a select box
	 */
	function startSelect($field) {
		if(!$this->inForm) {
			fwwarning('Tried to start a select box to a nonexistent form.');
			return;
		}
		echo '<select name="' . $this->fieldName($field) . '">';
	}

	function addOption($value, $text, $selected = false) {
		if(!$this->inForm) {
			fwwarning('Tried to start a select box to a nonexistent form.');
			return;
		}
		
		echo '<option value="' . $value . '" ' . ($selected == true ? 'selected="selected"' : '') . '>' . $text . '</option>';
	}

       	/**
	 * @brief Ends a select box
	 */
	function endSelect() {
		if(!$this->inForm) {
			fwwarning('Tried to end a select box in a nonexistent form.');
			return;
		}
		echo '</select>';
	}

        /**
         * @brief Outputs a check box.
         */
        function newCheckBox($field, $checked = false) {
                if(!$this->inForm) {
                        fwwarning('Tried to add a check box to a nonexistent form.');
                        return;
                }

		if($checked == 'on') $checked = true;

                echo '<input type="checkbox" name="' . $this->fieldName($field) . '" id="' . $this->fieldID($field) . '"' . ($checked ? ' checked' : '') . ' />';
        }

        /**
        * @brief Ouputs a radiobutton
        */
        function newRadioButton($field, $value, $checked = false) {
                if(!$this->inForm) {
                        fwwarning('Tried to add a radiobutton to a nonexistent form.');
                        return;
                }
                echo '<input type="radio" name="' . $this->fieldName($field) . '" id="' . $this->fieldId($field) . '" value="' . $value . '"' . ( $checked ? ' checked="checked"' : '' ) . ' />';
        }
       
        /**
         * @brief Outputs a hidden form element.
         */
        function newHidden($formPart, $field, $value = '') {
                if(!$this->inForm) {
                        fwwarning('Tried to add a hidden element to a nonexistent form.');
                        return;
                }
                echo '<input type="hidden" name="' . $this->fieldName($field) . '" id="' . $this->fieldID($field) . '" value="' . $value . '" />';
        }

	function newDatePicker($field, $showDate = true, $showTime = false, $vYear = null, $vMonth = null, $vDay = null, $vHour = null, $vMinute = null) {
		if($vYear == null) $vYear = date("Y");
		if($vMonth == null) $vMonth = date("m");
		if($vDay == null) $vDay = date("d");
		if($vHour == null) $vHour = date("g");
		if($vMinute == null) $vMinute = date("i");
		if($startYear == null) $startYear = $vYear - 90;
		if($endYear == null) $endYear = $vYear + 15;

		if(!$this->inForm) {
			fwwarning('Tried to add a date picker to a nonexisten form.');
			return;
		}
		
		echo '<select name="' . $this->fieldName($field) . '[month]" id="' . $this->fieldID($field) . '_month">';
		for($m = 1; $m < 13; $m++) {
			echo "<option value='$m' " . ($m == $vMonth ? 'selected="selected"' : '') . ">" . date("F", mktime(0, 0, 0, $m, 1, 0)) . "</option>";
		}
		echo '</select>';

		Form()->newTextField($field . '][day', $vDay, 2);
		echo ",";
		Form()->newTextField($field . '][year', $vYear, 4);
		echo " at ";
		Form()->newTextField($field . '][hour', $vHour, 2);
		Form()->newTextField($field . '][minute', $vMinute, 2);

		echo '<select name="' . $this->fieldName($field) . '[pm]" id="' . $this->fieldID($field) . '_pm">';
			echo "<option value='true' " . (date("A") == 'PM' ? 'selected="selected"' : "") . ">PM</option>";
			echo "<option value='false' " . (date("A") == 'AM' ? 'selected="selected"' : "") . ">AM</option>";
		echo '</select>';

	}

	/**
	 * @brief Outputs a hidden field with a nonce (see more at http://markjaquith.wordpress.com/2006/06/02/wordpress-203-nonces/)
	 */
	function newNonce() {
		if(function_exists('wp_nonce_field')) {
			wp_nonce_field($this->formPart);
		}
		else {
			fwwarning("Nonce's are supported in this version of Wordpress.");
		}
	}
       
        /**
         * @brief Gets a single POSTed value.
         */
        function getPOST($prefix, $formPart, $field) {
                if($prefix != '') {
                        if(isset($_POST[$prefix]) && isset($_POST[$prefix][$formPart])) {
                                return $_POST[$prefix][$formPart][$field];
                        } else {
                                return '';
                        }
                } else {
                        if(isset($_POST[$formPart])) {
                                return $_POST[$formPart][$field];
                        } else {
                                return '';
                        }
                }
        }
       
        /**
         * @brief Writes a field name.
         */
        function fieldName($field) {
                if($this->prefix != '') {
                        return $this->formPrefix . '[' . $this->formPart . '][' . $field . ']';
                } else {
                        return $this->formPrefix . $this->formPart . '[' . $field . ']';
                }
        }
       
        /**
         * @brief Writes a field ID.
         */
        function fieldID($field) {
                if($this->formPrefix != '') {
                        return $this->formPrefix . '_' . $this->formPart . '_' . $field;
                } else {
                        return $this->formPart . '_' . $field;
                }
        }
	
	function getID($formPart) {
		return $_POST[$formPart]['id'];
	}
	
	/**
	 * @brief Outputs an error message
	 */
	function error( $errorMessage ) {
		if( !$this->inForm ) {
			fwwarning('Tried to add instructions to a nonexistent form.');
		}
		echo " <span style='color: red'>$errorMessage</span>";
	}


}

function ne_Form() {
	static $instance;
	
	if(!isset($instance)) {
		$instance = new ne_formHelper();
		return $instance;
	}
	else {
		return $instance;
	}
}

//urlHelper.php
function ne_urlHelper_ajaxURL($args) {
	return ne_urlHelper_adminURL($args);
}

function ne_urlHelper_adminURL($args) {
	global $ne_folder_name;
	$defaults = array(
		'pluglet' => $ne_folder_name,
		'controller' => ne_Request::$controller,
		'action' => ne_Request::$action
	);

	$args = array_merge($defaults, $args);
	return get_option('siteurl') . '/wp-admin/admin.php?page=' . $args['pluglet'] . '/pages/' . $args['controller'] . "_" . $args['action'] . '.php' . (isset($args['ktarguments']) ? '&' . $args['ktarguments'] : '') . (isset($args['arguments']) ? '&' . $args['arguments'] : '');
}
function ne_urlHelper_publicURL($args) {
		$defaults = array(
			'pluglet' => ne_Request::$namespace,
			'controller' => ne_Request::$controller,
			'action' => ne_Request::$action
		);

		$args = array_merge($defaults, $args);
		
		return 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/?ktf=ne_' . $args['controller'] . '_' . $args['action'] . (isset($args['ktarguments']) ? "&{$args['ktarguments']}" : "") . (isset($args['arguments']) ? "&{$args['arguments']}" : '');
	}

//sessionHelper.php

function ne_sessionHelper_formHasErrors() {
		session_start();
		return isset($_SESSION['formErrors']);

}


function ne_sessionHelper_getError($field) {
		session_start();
		if(ne_sessionHelper_formHasErrors()) {
			return $_SESSION['formErrors'][$field];
		}

}


function ne_sessionHelper_clearErrors() {
		unset($_SESSION['formErrors']);
		unset($_SESSION['savedForms']);

}


function ne_sessionHelper_setError($field, $message) {
		session_start();
		if(!ne_sessionHelper_formHasErrors()) {
			$_SESSION['formErrors'] = array($field => $message);
		}
		else {
			$_SESSION['formErrors'][$field] = $message;
		}

}


function ne_sessionHelper_saveForm($formPart, $model) {
		session_start();
		$_SESSION['savedForms'][$formPart]['saved'] = $_POST[$formPart];

		return true;

}


function ne_sessionHelper_savedValue($formPart, $field) {
		session_start();
		return $_SESSION['savedForms'][$formPart]['saved'][$field];

}



?>
