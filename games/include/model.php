<?php

function __autoload($class_name) {	// Автоматическое подключение файла класса
	$file = MODEL_PATH.$class_name.'_model.php';
	if (file_exists($file)) require_once $file;
	else die("file $file not found");
}

class g_model {
    protected $app;
    function __construct($app) {
        $this->app = $app;
    }

	protected function getValue($params, $strs, $i) {
		$value = $params[$i];
		if (!$strs[$i]) {
			if (!is_numeric($value)) $value = 0;
		} else {
			if (strpos($value, 'undefined') !== false) $value = '';
			$value = "'".mysql_escape_string($value)."'";
		}
		return $value;
	}

    protected function updateRecord($tableName, $params, $fields, $strs, $where='') {
        if ($where && (!($record = query_line("SELECT * FROM $tableName WHERE $where")))) {
            $res_fields = '';
            $res_values = '';
            for ($i=0;$i<count($params); $i++) {
                if ($i >= count($fields)) break;

                $res_fields .= (($i > 0)?',':'').$fields[$i]; 
                $res_values .= (($i > 0)?',':'').$this->getValue($params, $strs, $i); 
            }
            $query = 'REPLACE '.$tableName.' ('.$res_fields.')  VALUES ('.$res_values.')';
        } else {
            $update_str = '';
            for ($i=0;$i<count($params); $i++) {
                if ($i >= count($fields)) break;

                $value = $this->getValue($params, $strs, $i);
                
                $update_str .= (($i > 0)?',':'')."`{$fields[$i]}`=$value"; 
            }
            $query = 'UPDATE '.$tableName.' SET '.$update_str.' WHERE '.$where;
        }

		$query = str_replace('NaN', '0', $query);
        sql_query($query);        
    }
 }

?>