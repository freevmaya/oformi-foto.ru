<?

class db {
    static function query($query, $args=null) { 
        // $args - массив где первый эелемент это запрос, а последующие это аргументы, например 'SELECT * FROM table WHERE ID=%d', [123]  
        GLOBAL $db;
        $result = null;
        if ($args && (count($args) > 0)) $query  = vsprintf($query, $args);
        if (!$db) connect_mysql();
        $result=mysql_query($query) or sql_error('mysql_error='.mysql_error().' $query='.$query);
        return $result;
    }
    
    static function line($query, $args=null) {
        $result = db::query($query, $args);
    	if (mysql_num_rows($result) < 1) {
    		mysql_free_result($result);
    		return false;
    	} else {
    		$row=mysql_fetch_array($result, $type);
    		mysql_free_result($result);
    		return $row;
    	}
    }
    
    static function array($query, $args=null) {
        $result = db::query($query, $args);
    	$ret=array();
    	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) $ret[]=$row;
    	mysql_free_result($result);
    	return $ret;
    }     
}
?>