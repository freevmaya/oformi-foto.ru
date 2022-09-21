<?
define('CM_ROWCOUNT', 5);
define('CM_ALLROWCOUNT', 5000);
define('DEFCOMTABLE', DBPREF.'comments');

include_once(SSPATH.'helpers/notify_classes/mailNotifier.php'); 

class comments {
    protected $content_id;
    protected $content_str;
    protected $comTable;
    function __construct($a_content, $a_url='', $a_comTable = DEFCOMTABLE) {
        $this->content_id = 0;
        $this->comTable = $a_comTable;
        if (is_string($a_content)) {
            $this->content_id = comments::contentId(addslashes($a_content), addslashes($a_url), true);
        } else $this->content_id = $a_content;
    }
    
    protected function commquery($isCalcRows=false) {
        return "SELECT ".($isCalcRows?'SQL_CALC_FOUND_ROWS':'')." c.*, CONCAT_WS(' ', DATE_FORMAT(c.date, '%d.%m.%Y'), DATE_FORMAT(c.time, '%h:%i')) AS msgTime, c.likes, u.nick, u.first_name, u.last_name, c.user_source AS source ".
                "FROM {$this->comTable} c LEFT JOIN ".DBPREF."users u ON u.uid = c.uid AND u.source = c.user_source";
    }
    
    protected function parseList($list) {
        GLOBAL $locale;
        foreach ($list as $i=>$item) {
            $src = $item['user_source'];
            $nav = $src=='none';
            $list[$i]['avatar'] = $src.'/'.($nav?'default':$item['uid']);
            $list[$i]['user_url'] = ss::userLink($item);
            if ($nav) $list[$i]['nick'] = $locale['ANONYM'];
            if ($item['answer_count'] > 0) 
                $list[$i]['childs'] = $this->parseList(DB::asArray($this->commquery()." WHERE answer_to={$item['comment_id']} AND state='active' ORDER BY comment_id DESC"));
                
        }
        return $list;    
    }
    
    public function getComments($page = 0, $all=0) {
        $result = array('count'=>0);
        if ($this->content_id) {
            $start = $page * CM_ROWCOUNT;
            $query = $this->commquery(true)." WHERE content_id = {$this->content_id} AND answer_to = 0 AND state='active' ORDER BY comment_id DESC LIMIT $start, ".($all?CM_ALLROWCOUNT:CM_ROWCOUNT);
            $tlist = DB::asArray($query);
            $result['total'] = DB::one('SELECT FOUND_ROWS()'); 
            $result['list'] = $this->parseList($tlist);
            $result['next_page'] = $all?-1:($page + 1);  
        }
        return $result;
    }
    
    public static function contentId($str_content, $a_url, $create=false) {
        GLOBAL $mysqli;
        if ($line = DB::line("SELECT * FROM ".DBPREF."content WHERE indent='$str_content'")) return $line['content_id'];
        else if ($create) {
            $str_content = $mysqli->real_escape_string($str_content);
            DB::query("INSERT INTO ".DBPREF."content (indent, url) VALUES ('{$str_content}', '{$a_url}')");
            return DB::lastID();
        } else return 0;                
    }
    
    public function addLike($comment_id, $user) {
        $res = 0;
        if (is_numeric($comment_id)) {                            
            $com = DB::line('SELECT * FROM '.DBPREF."comments WHERE comment_id={$comment_id}");        
            if ($com && DB::query("UPDATE ".DBPREF."comments SET likes = likes + 1 WHERE comment_id={$comment_id}")) {
                $res = DB::one("SELECT likes FROM ".DBPREF."comments WHERE comment_id={$comment_id}");
                //Event::fireEvent('CM-LIKE', $user['uid'], $user['source'], $com['uid'], $comment_id, $com['user_source']);              
            }
        }
        return $res;
    }
    
    public function addComment($text, $answer_to=0, $user=null) {
        GLOBAL $mysqli, $root, $sheme;
        $user = $user?$user:$root->getSenderUser();
                                                                                  
        if ($user) {
            $uid = $user['uid'];
            $source = $user['source'];
        } else {
            $uid = ANONIMUID;
            $source = 'none';
        }
        
        $state = ($source=='none')?'wait':'active';
        
        $text = $mysqli->real_escape_string($text);     
               
        $query = "INSERT INTO ".DBPREF."comments (content_id, answer_to, uid, `user_source`, `date`, `time`, `text`, `state`) ".
                    "VALUES ({$this->content_id}, {$answer_to}, {$uid}, '{$source}', NOW(), NOW(), '{$text}', '{$state}')";
        
        if (DB::query($query)) {
            $com_id = DB::lastID();
            if ($answer_to > 0) DB::query("UPDATE ".DBPREF."comments SET `answer_count` = `answer_count` + 1 WHERE comment_id={$answer_to}");
            $query = $this->commquery()." WHERE c.comment_id=".$com_id;
            $item = DB::line($query);
            $item['avatar'] = $source.'/'.($source=='none'?'default':$item['uid']);
            
            mailNotifier::adminNotify('<h3>Новый комментарий на сайте</h3><p>'.$text.'</p><a href="'.ss::currentURL().'">'.$com_id.'</a>'); 
            
            //Event::fireEvent('COMM', $user['uid'], $user['source'], $this->content_id, $com_id);
            return $item;
        }
        return 0;    
    }
}
?>