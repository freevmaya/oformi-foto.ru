<?

include_once(INCLUDE_PATH.'/statistic.inc');
define('RESULTTYPE', MYSQL_ASSOC);

class DB {
    static private $PROFILE;
    static public function query($a_query, $args=null) { 
        // например 'SELECT * FROM table WHERE ID=%d', [123]  
        GLOBAL $db;
        $result = null;
        if ($args && (count($args) > 0)) $query = vsprintf($a_query, $args);
        else $query = $a_query;
        if (!$db) connect_mysql();
        if (DB::$PROFILE) $startTime = fdbg::time();
        $result=mysql_query($query) or sql_error('mysql_error='.mysql_error().' $query='.$query);
        if (DB::$PROFILE) {
            //$a_query = mysql_escape_string($a_query);
            trace((fdbg::time() - $startTime).", '{$a_query}'");
//            trace($a_query);
        }
        return $result;
    }
    
    static public function setProfile($a_val) {
        DB::$PROFILE = $a_val;
    }
    
    static public function lastID() {
        return query_one("SELECT LAST_INSERT_ID()");  
    }
    
    static public function line($query, $args=null) {
        $result = DB::query($query, $args);
    	if (mysql_num_rows($result) < 1) {
    		mysql_free_result($result);
    		return false;
    	} else {
    		$row=mysql_fetch_array($result, RESULTTYPE);
    		mysql_free_result($result);
    		return $row;
    	}
    }
    
    static public function asArray($query, $args=null) {
        $result = DB::query($query, $args);
    	$ret=array();
    	while ($row=mysql_fetch_array($result, RESULTTYPE)) $ret[]=$row;
    	mysql_free_result($result);
    	return $ret;
    }
    
    static public function close() {
        GLOBAL $db;
        if ($db) mysql_close($db);
    }     
}
?>