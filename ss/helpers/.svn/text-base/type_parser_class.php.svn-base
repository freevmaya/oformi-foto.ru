<?

function rt($tmpl, $index) {
    preg_match_all("/(\[([А-Яа-я]+),([А-Яа-я]+)\])/u", $tmpl, $result);
    
    $index = min($index, 1);
    if ($result) {
        foreach ($result[0] as $i=>$v) {
            $tmpl = str_replace($result[0][$i], $result[2 + $index][$i], $tmpl);
        }
    }
    return $tmpl;
}

function mt($text, $one, $more, $count) {
    return $text.(($count == 1)?$one:(($count > 1)?$more:''));
}

function event_ids($event) {
    return $event->getVal('event_id');
}
    
function event_ids_a($event) {
    return $event['event_id'];
}
    
class type_parser {
    protected $user;
    protected $notevents;
    protected $options;
    function __construct($a_user) {
        if ($this->user = $a_user) {
            /*
            $query = "SELECT e.event_id FROM ".DBPREF."notify_events e, ".
                    DBPREF."notify n ".
                    "WHERE n.notify_id=e.notify_id AND n.state != 'remove' AND n.uid = {$this->user['uid']} AND n.user_source ='{$this->user['source']}'";
            $this->notevents = implode(',', array_map('event_ids_a', DB::asArray($query)));
            */
            $this->options = DB::line("SELECT * FROM ".DBPREF."user_options WHERE uid={$this->user['uid']} AND source='{$this->user['source']}'");                    
        }
    }
    
    protected function stdGMParcer($type, $pat) {
        $result = null;
        $whereNE = "e.time > '{$this->options['last_notify']}' AND";//$this->notevents?("e.event_id NOT IN ({$this->notevents}) AND"):''; 
        
        $query = "SELECT g.game_id, g.name, e.event_id, e.uid, e.source, u.nick, u.first_name, u.last_name FROM ".DBPREF."events e ".
                "LEFT JOIN ".DBPREF."users u ON u.uid=e.uid AND u.source=e.source ".
                "INNER JOIN ".DBPREF."game g ON g.game_id=e.var_int2 ".
                "WHERE $whereNE `type` = '{$type}' AND e.var_int1={$this->user['uid']} AND e.var_source='{$this->user['source']}' AND e.uid != {$this->user['uid']} AND e.source!='{$this->user['source']}' ".
                "ORDER BY e.var_int2, e.source DESC";
            
        //echo $query."\n";
        $events = eventItem::getList($query);
        $count = count($events);
        $games = array();
        $users = array();
        if ($count > 0) {
            //$users = $this->union($events, 'getUserName');
            foreach ($events as $event)
                $games[$event->getVal('game_id')] = $event->getVal('name'); 
            foreach ($events as $event)
                $users[$event->getVal('source').'-'.$event->getVal('uid')] = $event->userName(); 
            $result = array(
                'callback'=>link::c('game', 'view', $event->getVal('game_id')),
                'text'=>rt($pat, $count>1?1:0),
                'events'=>array_map('event_ids', $events),
                'games'=>$games,
                'users'=>$users
            );
        }
        
        return $result;
    }  
    
    public function ADDVOTE() {
        return $this->stdGMParcer('ADDVOTE', '{users} проголосова[л,ли] за ваш[у,и] работ[у,ы] {games}');
    }
     
    public function GM_COMM() {
        return $this->stdGMParcer('GM-COMM', '{users} прокомментирова[л,ли] ваш[у,и] работ[у,ы] {games}'); 
    }
    
    public function GA_COMM() {
        return $this->stdGMParcer('GA-COMM', '{users} ответи[л,ли] на ваш комментарий к работ[е,ам] {games}'); 
    }
     
    public function GCM_LIKE() {
        return $this->stdGMParcer('GCM-LIKE', '{users} лайкну[л,ли] ваш комментарий к работ[е,ам] {games}'); 
    }
     
    public function PCM_LIKE() {
        return $this->stdGMParcer('PCM-LIKE', '{users} лайкну[л,ли] ваш комментарий на странице {user_page}'); 
    }
     
    public function PG_COMM() {
    
        $result = null;
        $whereNE = "e.time > '{$this->options['last_notify']}' AND";//$whereNE = $this->notevents?("e.event_id NOT IN ({$this->notevents}) AND"):'';
         
        $query = "SELECT e.event_id, e.uid, e.source, u.nick, u.first_name, u.last_name FROM ".DBPREF."events e ".
                "LEFT JOIN ".DBPREF."users u ON u.uid=e.uid AND u.source=e.source ".
                "WHERE $whereNE e.`type` = 'PG-COMM' AND e.var_int1={$this->user['uid']} AND e.var_source='{$this->user['source']}' AND e.uid != {$this->user['uid']} AND e.source!='{$this->user['source']}' ".
                "ORDER BY e.var_int2, e.source DESC";
                
        $events = eventItem::getList($query);
        
        $count = count($events);
        $users = array();
        if ($count > 0) {
            foreach ($events as $event) {
                $users[$event->getVal('source').'-'.$event->getVal('uid')] = $event->userName();
            } 
            $result = array(
                'callback'=>link::c('user', 'page', $this->user['source'].'-'.$this->user['uid']),
                'text'=>rt('{users} остави[л,ли] комментарий на вашей странице {user_page}', $count>1?1:0),
                'events'=>array_map('event_ids', $events),
                'users'=>$users,
                'user_page'=>$this->user['source'].'-'.$this->user['uid']
            );
        }
        return $result;  
    }
    
    public function PA_COMM() {
        $result = null;
        $whereNE = "e.time > '{$this->options['last_notify']}' AND";//$whereNE = $this->notevents?("e.event_id NOT IN ({$this->notevents}) AND"):'';
        $query = "SELECT e.event_id, e.uid, e.source, u.nick, u.first_name, u.last_name, e.var_str1 AS p_uid, e.var_str2 AS p_source FROM ".DBPREF."events e ".
                "LEFT JOIN ".DBPREF."users u ON u.uid=e.uid AND u.source=e.source ".
                "WHERE $whereNE `type` = 'PA-COMM' AND e.var_int1={$this->user['uid']} AND e.var_source='{$this->user['source']}' AND e.uid != {$this->user['uid']} AND e.source!='{$this->user['source']}' ".
                "ORDER BY e.var_int2, e.source DESC";
                
        $events = eventItem::getList($query);
        $count = count($events);
        $users = array();
        if ($count > 0) {
            foreach ($events as $event)
                $users[$event->getVal('source').'-'.$event->getVal('uid')] = $event->userName(); 
            $result = array(
                'callback'=>link::c('user', 'page', $this->user['source'].'-'.$this->user['uid']),
                'text'=>rt('{users} ответи[л,ли] на ваш комментарий на странице {user_page}', $count>1?1:0),
                'events'=>array_map('event_ids', $events),
                'users'=>$users,
                'user_page'=>$event->getVal('p_source').'-'.$event->getVal('p_uid')
            );
        }
        return $result;  
    }
    
    public function VIN() {
        $result = null;
        $whereNE = "e.time > '{$this->options['last_notify']}' AND";//$whereNE = $this->notevents?("e.event_id NOT IN ({$this->notevents}) AND"):'';
        $query = "SELECT e.event_id, e.uid, e.source, u.nick, u.first_name, u.last_name, e.var_int1 AS game_id, g.name as game_name FROM ".DBPREF."events e ".
                "INNER JOIN ".DBPREF."game g ON g.game_id=e.var_int1 ".
                "LEFT JOIN ".DBPREF."users u ON u.uid=e.uid AND u.source=e.source ".
                "WHERE $whereNE `type` = 'VIN' AND e.uid = {$this->user['uid']} AND e.source = '{$this->user['source']}' ".
                "ORDER BY e.var_int1 DESC";
                
        $events = eventItem::getList($query);
        $count = count($events);
        $games = array();
        if ($count > 0) {
            foreach ($events as $event)
                $games[$event->getVal('game_id')] = $event->getVal('game_name'); 
            $result = array(
                'callback'=>link::c('game', 'view', $events[0]->getVal('game_id')),
                'text'=>rt('Поздравляем! Ва[ш,ши] колла[ж,жи] {games} победи[л,ли] в конкурсе.', $count>1?1:0),
                'events'=>array_map('event_ids', $events),
                'games'=>$games
            );
        }
        return $result;  
    }
     
    public function TOGAME() {
    } 
}
?>