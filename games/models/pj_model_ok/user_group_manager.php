<?
    define('QUERY_TYPE_COUNT', 1);
    define('QUERY_TYPE_LIST', 2);
    
    include_once(INCLUDE_PATH.'/Memcache.php');
    
    class UGManager {
        private $groups;
        function __construct($a_groups) {
            $this->groups = $a_groups;
        }
        
        public function query_count($group_index) {
            $group = $this->groups[$group_index];
            $where = ($group['where']?'WHERE ':'').$group['where'];
            return "SELECT COUNT(`uid`) AS `count` FROM {$group['table']} $where";
        } 
        
        public function query_list($group_index) {
            $group = $this->groups[$group_index];
            $where = ($group['where']?'WHERE ':'').$group['where'];
            return "SELECT * FROM {$group['table']} $where";
        } 
        
        public function query_move($group_index) {
            $group = $this->groups[$group_index];
            if (isset($group['move_table'])) {
                $where = ($group['where']?'WHERE ':'').$group['where'];
                            
                return "REPLACE `{$group['move_table']}` (SELECT `uid` FROM {$group['table']} $where)";
            } else return '';
        } 
        
        public function query_to_alert($query, $distanceTable, $alert_id) {
            return "REPLACE `{$distanceTable}` (`uid`, `alert_id`) SELECT `uid`, {$alert_id} FROM ({$query}) AS ict";
        }
        
        public function prepareMove($group_index) {
            $group = $this->groups[$group_index];
            if (isset($group['move_table'])) {
                $query = "CREATE TABLE IF NOT EXISTS `{$group['move_table']}` (`uid` bigint(20) unsigned NOT NULL, PRIMARY KEY  (`uid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
                DB::query($query); 
                $query = "TRUNCATE TABLE `{$group['move_table']}`";
                DB::query($query);
            }
        }
        
        public function queryFromType($group_index, $type, $distanceTable='', $alert_id=1) {
            $query = '';
            switch ($type) {
                case 1: $query = $this->query_count($group_index);
                        break;
                case 2: $query = $this->query_list($group_index);
                        break;
                case 3: $query = $this->query_move($group_index);
                        break;
            }
            return $query;
        } 
        
        public function checkDistanceTable($group_index) {
            $group = $this->groups[$group_index];
            if (isset($group['move_table'])) {
                
            };
        }
        
        /*
        public function execute($group_index, $type, $query, $analize=false) {
            $result = '';
            $query = ($analize?'EXPLAIN ':'').$query;
            if ($analize) return DB::line($query);
            else { 
                if ($type == 2) {
                    //echo($query); 
                    $result = DB::query($query);
                } else {
                    $key = md5($query);
                    if (!$result = MCache::get($key)) {
                        $result = DB::line($query);
                        MCache::set($key, $result, 60 * 60 * 24);
                    }
                }   
            }
            return $result;
        }
        */
    }
?>