<?
include(SSPATH.'helpers/notify_class.php');    
include(SSPATH.'helpers/type_parser_class.php');    

class Event {
    public static function fireEvent($type, $uid=0, $source='', $var1=0, $var2=0, $var_source='none', $str1='', $str2='') {
        $var1 = $var1?$var1:0;
        $var2 = $var1?$var2:0;
        $time = date('Y-m-d H:i:s');
        return DB::query("INSERT INTO ".DBPREF."events (type, time, uid, source, var_int1, var_source, var_int2, var_str1, var_str2) VALUES ('$type', '$time', $uid, '$source', $var1, '$var_source', $var2, '$str1', '$str2')");
    }
    
    public static function getLastEventsA($EVENT_TYPES, $users) {
        $result = array();
        
        foreach ($users as $user) {
            if ($eventTypes = explode(',', $user['event_types'])) {
                //$efq = '\''.implode("','", $eventTypes).'\'';
                
                $user_data = array('user'=>new recordUser($user), 'notify'=>array());
                
                foreach ($eventTypes as $type) {
                    if (in_array($type, $EVENT_TYPES)) {
                        $parser = new type_parser($user); 
                        $pm = str_replace('-', '_', $type);
                        if (method_exists($parser, $pm)) {
                            if ($message = $parser->$pm()) {
                                $user_data['notify'][] = $message;
                            }
                        }
                    }
                }
                
                if (count($user_data['notify']) > 0)
                    $result[] = $user_data; 
            }
        }    
        return $result;
    }
    
    public static function getLastEvents($EVENT_TYPES, $a_users=null, $query='') {
        $where = '';
        if ($a_users) {
            foreach ($a_users as $user) {
                $where .= ($where?' OR ':'')."(u.uid={$user['uid']} AND u.source='{$user['source']}')";
            }                     
            
            $where = ($where?" AND ($where)":'');           
        }

        if (!$query) $query = "SELECT u.*, o.* FROM ".DBPREF."users u INNER JOIN ".DBPREF."user_options o ON u.uid=o.uid AND u.source=o.source ".
                                "WHERE o.last_notify < NOW() - INTERVAL o.notify_interval_sec SECOND AND u.`last_time` > NOW() - INTERVAL 1 MONTH $where";
        return Event::getLastEventsA($EVENT_TYPES, DB::asArray($query));
    }
}    
?>