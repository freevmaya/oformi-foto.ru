<?

GLOBAL $__cache_key, $mysql_cache_expired;

include_once(INCLUDE_PATH.'/statistic.inc');
include_once(INCLUDE_PATH.'/Memcache.php');

define('RESULTTYPE', PDO::FETCH_ASSOC);

class DB {
    static private $PROFILE;
    static public function query($a_query, $args=null) { 
        GLOBAL $db, $host, $dbname, $user, $password, $charset;
        $result = null;


        if (!$db) {
        	$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
		    $opt = [
		        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		        PDO::ATTR_EMULATE_PREPARES   => false,
		    ];
		    $db = new PDO($dsn, $user, $password, $opt);
        }

        if (DB::$PROFILE) $startTime = fdbg::time();

//trace($args == null ? "null": ("value: ".print_r($args, true)), 'document', 3);

        if ($args && isset($args[0])) {
            $a_query = vsprintf($a_query, $args);
            $args = null;
        }

        $stmt = $db->prepare($a_query);
        $result = $stmt->execute($args);

        if (DB::$PROFILE)
            trace((fdbg::time() - $startTime).", '{$a_query}'");
        return $stmt;
    }
    
    static public function setProfile($a_val) {
        DB::$PROFILE = $a_val;
    }

    static public function one($query, $args=null) {
    	$stmt = DB::query($query, $args);
    	return $stmt?$stmt->fetchColumn():null;
    }
    
    static public function lastID() {
        return DB::one("SELECT LAST_INSERT_ID()");  
    }
    
    static public function line($query, $args=null, $resType = RESULTTYPE, $cacheKey=false) {
        if (!$cacheKey || (($row = DB::getCache($query, $cacheKey)) === false)) { 

            	$stmt = DB::query($query, $args);
        	if ($stmt->rowCount() < 1)
        		return false;
        	else {
        		$row = $stmt->fetch($resType);
                if ($cacheKey) DB::setCache($cacheKey, $row);
        	}
        }
        return $row;
    }   
    
    static public function asArray($query, $args=null, $cacheKey=false) {
        if (!$cacheKey || (($ret = DB::getCache($query, $cacheKey)) === false)) { 
            $stmt = DB::query($query, $args);
        	$ret=array();
        	foreach ($stmt as $row) $ret[]=$row;
            
            if ($cacheKey) DB::setCache($cacheKey, $ret);
        }
    	return $ret;
    }
    
    static public function close() {
        GLOBAL $db;
        if ($db) $db = null;
    }
    
    static private function setCache($cacheKey, $value) {
        GLOBAL $mysql_cache_expired;
        if ($mysql_cache_expired > 0) MCache::set($cacheKey, $value, $mysql_cache_expired);
    }
    
    static private function getCache($query, &$cacheKey) {
        GLOBAL $__cache_key, $mysql_cache_expired;
         
        $cache_data = false;   
        $is_cached  = ($mysql_cache_expired > 0) && (strtoupper(substr($query, 0, 3)) == 'SEL');
    
        if ($is_cached) {
            if (strpos(strtoupper($query), 'FOUND_ROWS') !== false) {
                $__cache_key = md5($query.$__cache_key);                
            } else $__cache_key = md5($query);
            
            $cacheKey = $__cache_key;
                                         
            $cache_data = MCache::get($__cache_key);               
            //trace('CACHE: '.$__cache_key.', CACHE_DATA_ON: '.(($cache_data !== false)?1:0).', QUERY: '.$query);
            //trace($cache_data);
        } 
        
        return $cache_data;
    
    }  

    static public function startTransaction() {
        return DB::query('START TRANSACTION');
    }

    static public function commitTransaction() {
        return DB::query('COMMIT');
    }

    static public function rollbackTransaction() {
        return DB::query('ROLLBACK');
    }   
}
?>
