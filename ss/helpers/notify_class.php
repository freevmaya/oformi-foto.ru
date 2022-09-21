<?
define('NF_MAXGAMECOUNT', 4);
define('NF_MAXUSERSCOUNT', 5);

class record {
    protected $data;
    function __construct($a_data) {
        $this->assign($a_data);
    }
    
    public function assign($a_data) {
        $this->data = $a_data;
    }
    
    public function getVal($name) {
        return $this->data[$name];
    }
    
    public function setVal($name, $val) {
        $this->data[$name] = $val;
    }
    
    public static function getList($query) {
        $notifys = DB::asArray($query);
        $list = array();
        foreach ($notifys as $ndata) $list[] = new record($ndata);
        return $list;
    }
}

function nf_getUserName($user) {
    GLOBAL $locale;
    $result = '';
    if (is_numeric($user)) $result = $user;
    else if ($user['source'] == 'none') {
        if ($user['uid']) $result = $locale['ANONYM'].' '.$user['uid'];
        else $result = $locale['ANONYM'];
    } else if ($user['first_name'] || $user['last_name']) $result = $user['first_name'].' '.$user['last_name'];
    else $result = $user['nick']?$user['nick']:$locale['ANONYM'];
    return $result;
}

class recordUser extends record {
    public function userName() {
        return nf_getUserName($this->data);
    }
}

class eventItem extends recordUser {
    public function getGameLink() {
        return "<a href=\"".MAINURL."{$this->data['game_id']}\">{$this->data['game_name']}</a>";   
    }
      
    public static function getList($query) {
        $notifys = DB::asArray($query);
        $list = array();
        foreach ($notifys as $ndata) $list[] = new eventItem($ndata);
        return $list;
    }
}

class notifyItem extends record {
    public static function getList($query) {
        $notifys = DB::asArray($query);
        $list = array();
        foreach ($notifys as $ndata) $list[] = new notifyItem($ndata);
        return $list;
    }
} 

class baseNotifier {
    protected $url;
    function __construct($a_url=MAINURL) {
        $this->url = $a_url;
    }
        
    function nfUserStr($indent, $user) {
        return "<a href=\"{$this->url}user/page/{$indent}.html\">$user</a>";
    }
    function nfGameStr($indent, $name) {
        return "<a href=\"{$this->url}game/view/{$indent}.html\">$name</a>";
    }
    
    public function union($items, $getValue, $limit=3) {
        GLOBAL $locale;
        $result = '';
        $count = min(count($items), $limit + 1);
        $i=0;
        foreach ($items as $inx=>$item) {
            $sep = ($i==0)?'':(($i==$count - 1)?" {$locale['AND']} ":', ');
            $result .= $sep.(($i==$limit)?$locale['OTHER']:$this->$getValue($inx, $item));
            $i++;
            if ($i == $count) break;
        }
        return $result;
    }

    public function prepare($user, $message) {
       preg_match_all("/\{([\w-_]+)\}/u", $message['text'], $result);
        if ($result) {
            $text = $message['text'];
            foreach ($result[0] as $i=>$item) {
                $windex = $result[1][$i];
                $text = str_replace($item, $this->$windex($message[$windex]), $text);                
            }
        }
        return $text;
    }
    
    public function send($user, $callback, $msg_body) {
    }
    
    protected function users($users) {
        return $this->union($users, 'nfUserStr', NF_MAXUSERSCOUNT);
    }
    
    protected function user_page($page) {
        return "<a href=\"{$this->url}user/page/{$page}.html\">{$page}</a>";
    }
    
    protected function games($games) {
        return $this->union($games, 'nfGameStr', NF_MAXGAMECOUNT);
    }
    
    public function createNotify($type, /*$events, */$user, $сallbackURL, $data, $state='wait') {
        GLOBAL $mysqli;
        $result = false;
        $data = mysqli_real_escape_string($mysqli, $data);
        $ndate = date('Y-m-d H:i:s');
        if (($uid = $user->getVal('uid')) && ($source = $user->getVal('source'))) {
            $query = "INSERT INTO ".DBPREF."notify (uid, user_source, create_time, type, state, сallback, data) VALUES ({$uid}, '{$source}', '$ndate', '{$type}', '{$state}', '$сallbackURL', '{$data}')";
            if ($result = DB::query($query)) {
                $nid = DB::lastID();
                
                $query = "UPDATE ".DBPREF."user_options SET last_notify='{$ndate}', last_nid={$nid} WHERE uid={$uid} AND source='{$source}'";
                $result = $result && DB::query($query);
            }
        }
        
        return $result;                
    }
    
    public function deleteNotify($nid) {
        $result = DB::query("DELETE ".DBPREF."notify_events WHERE notify_id=$nid");
        return $result && DB::query("DELETE ".DBPREF."notify WHERE notify_id=$nid");
    }
} 

class siteNotifierA extends baseNotifier {
    public function send($user, $callback, $message) {
        preg_match_all("/\{([\w-_]+)\}/u", $message['text'], $result);
        if ($result) {
            $text = $message['text'];
            foreach ($result[0] as $i=>$item) {
                $windex = $result[1][$i];
                $text = str_replace($item, $this->$windex($message[$windex]), $text);                
            }
        }
        return $text;
    }
}  
?>