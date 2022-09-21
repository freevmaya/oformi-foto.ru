<?                  
$db      = null;
$dbname  = _dbname_default;
$charset = 'cp1251';

function connect_mysql() {
    GLOBAL $db, $host, $user, $password, $dbname, $charset;
    $db = mysql_connect($host, $user, $password) or sql_error('Не могу прислюнявиться с мускулу: '.mysql_error());
    mysql_select_db($dbname);
    // русификация
    if (_sql_i18n) sql_query("set collation_connection={$charset}_general_ci,
    				collation_database={$charset}_general_ci,
    				character_set_client={$charset},
    				character_set_connection={$charset},
    				character_set_database={$charset},
    				character_set_results={$charset},
    				charset {$charset},
    				names {$charset}");
}

function val($val) {
   if (is_numeric($val)) return $val;
   else return "'$val'";
}

function GetStack() {
    $stack = debug_backtrace();
    foreach ($stack as $key=>$val) {
        if (!isset($stack[$key]['file'])) unset($stack[$key]);	// удаляем пустые записи
    }
    return $stack;
}

function log_errors($message, $required_function=__FUNCTION__, $file_log=_file_log) {
    GLOBAL $_SERVER;
	if (!isset($file_log)) {
		return "ERROR: NOT DEFINED \$file_log, '$message'";
	}
    
    $stack = GetStack();
    
    $index = 0;    
    while ($index < count($stack)) {
        if (strpos($stack[$index]['file'], 'dbu') === false) break;
        $index++;
    }
    
    $required_function = "{$stack[$index]['file']}=>{$stack[$index]['line']}";

	if ($handle=fopen($file_log,'a')) {
		$login=""; isset($_SESSION['login']) && $login=$_SESSION['login'];
		$userid=""; isset($_SESSION['userid']) && $userid=$_SESSION['userid'];
		$login_as=""; isset($_SESSION['login_as']) && $login_as=$_SESSION['login_as'];
		$message=date('Y-m-d H:i:s').': ip="'.@$_SERVER['REMOTE_ADDR']." function=\"$required_function\" message=\"$message\"\n";
		fwrite($handle, "$message");
		fclose($handle);
		return $message;
	} else {
		return "ERROR: unable to open log file \"$file_log\", '$message'\n";
	}
}

// базовая обвеска для кверей с обрабоктой ошибок.
function sql_query($query) {
    GLOBAL $db;
    if (!$db) connect_mysql();              
	$result=mysql_query($query) or sql_error('mysql_error='.mysql_error().' $query='.$query);
	return $result;
}

function sql_error($error='') {
    log_errors($error, __FUNCTION__);
	die();
}


// обвеска для кверей возвращщает одну линию в виде массива (к полям можно обращщаться как по имени так и по номеру).
// 
function query_line($query, $type=MYSQL_ASSOC) { 
	$result=sql_query($query);
	if (mysql_num_rows($result) < 1) {
		mysql_free_result($result);
		return false;
	} else {
		$row=mysql_fetch_array($result, $type);
		mysql_free_result($result);
		return $row;
	}
}


// обвеска для кверей возвращщает одну первую ячейку
//	column - номер или имя колонки
function query_one($query, $column=0) {
	$row=query_line($query, MYSQL_BOTH);
	if ($row===false) return false;
	return $row[$column];
}


// обвеска для кверей возвращщает одну колонку ввиде массива
//	column - номер или имя колонки
function query_column($query, $column=0) {
	$result=sql_query($query);
	$ret=array();
	while ($row=mysql_fetch_array($result)) $ret[]=$row[$column];
	mysql_free_result($result);
	return $ret;
}

// обвеска для кверей возвращщает результат ввиде массива
function query_array($query) {
	$result=sql_query($query);
	$ret=array();
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) $ret[]=$row;
	mysql_free_result($result);
	return $ret;
}

// обвеска для кверей проверяет наличие результатов у запроса query
//	например: "select * users where login='name' and pass='passwd'", при наличии хотябы одной строки вернет true иначе false
function query_find($query) {
	$result=sql_query($query);
	if (mysql_num_rows($result) < 1) {
		mysql_free_result($result);
		return false;
	}
	mysql_free_result($result);
	return true;
}


// обвеска для кверей проверяет наличие записи field со значением value в таблице table
//	
function record_exist($table, $field, $value) {
	return query_find("select * from $table where $field='$value'");
}


// обвеска для кверей, формирует и выполняет insert запрос из массива значений на вставку в таблицу\
//	table - таблица куда будет инсертится
//	data - массив данных
// массив может быть одномерным тогда будет вставленна только одна строка
// примеры запроса:
//	query_insert("table", array(array("column"=>"value","column2"=>"value2"),array(.....)));
//	query_insert("table", array("column"=>"value","column2"=>"value2"))
//////
function query_insert($table, $data, $insert_operator='insert', $key_field='') {
	if (!is_array($data)) die(log_errors('query_insert("table", array(array("column"=>"value","column2"=>"value2"),array(.....)))'));
	if (!is_array(reset($data))) $data=array($data);

	$first=true;
	
    
    $values = '';
    $names  = '';
	foreach ($data as $data_line) {
        $value = '';
		foreach ($data_line as $name=>$val) {
            $value .= ($value?',':'').val($val);

			if (!$first) continue;
            $names .= ($names?',':'')."`$name`";
		}
		$first=false; 
        $values .= ($values?',':'')."\n($value)";
	}
	
	$query = "$insert_operator into $table\n ($names) values $values";
    sql_query($query);
    if ($key_field) return query_one("SELECT MAX(`$key_field`) FROM $table", 0);
	else return 0;
}

// обвеска для кверей, формирует и выполняет update запрос из массива значений
//	table - таблица куда будет инсертится
//	data - массив данных
//	where - условие для обновления, если будет '' то обновится вся таблица
// массив должен быть одномерным
// примеры запроса:
//	query_update("table",array("column"=>"value","column2"=>"value2"),"where userid=12")
//////
function query_update($table, $data, $where='') {
	if (!is_array($data)) die(log_errors('query_update("table",array("column"=>"value","column2"=>"value2"),"where userid=12")'));

	foreach ($data as $name=>$val) {
		if (!isset($query)) {
			$query="$name='$val'";
		} else	$query.=", $name='$val'";
	}

	return sql_query("update $table\n set $query $where");
}


// обвеска для кверей, блокирует таблицы
//	$lock_write - массив или список разделенный запятой, таблицы для полной блокировки
//	$lock_read - массив или список разделенный запятой, таблицы для блокировки на запись
function tables_lock($lock_write, $lock_read='') {
	$query='lock tables ';
	$n=0;

	if (!empty($lock_write)) {
		if (!is_array($lock_write)) $lock_write=explode(',', $lock_write);
		$query.=current($lock_write).' write';
		while ($table=next($lock_write)) $query.=", $table write";
		$n=1;
	}

	if (!empty($lock_read)) {
		if ($n) $query.=', ';
		if (!is_array($lock_read)) $lock_read=explode(',', $lock_read);
		$query.=current($lock_read).' read';
		while ($table=next($lock_read)) $query.=", $table read";
	}

	$result=sql_query($query);
	return $result;
}

// разблокирует все заблокированые таблицы.
function tables_unlock() {
	$result=sql_query('unlock tables');
	return $result;
}

function startTransaction() {
	return sql_query('START TRANSACTION');
}

function commitTransaction() {
	return sql_query('COMMIT');
}

function rollbackTransaction() {
	return sql_query('ROLLBACK');
}

?>