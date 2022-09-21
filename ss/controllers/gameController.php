<?

include_once(CONTROLLERS_PATH.'/gameBaseController.php');
include_once(INCLUDE_PATH.'/image.php');

class gameController extends gameBaseController {

    protected $page = 1;
    protected $group;
    protected $contest;
    
    function __construct($a_request) {
        parent::__construct($a_request);
        $this->page = $this->getVar('page', 1);
        $this->group = $this->getVar('group', false);
        $this->contest = $this->getVar('contest', 1);
    }
    
    protected function sendToGame() {
        GLOBAL $_SESSION;
        $res        = array('result'=>0, 'data'=>array());
        if ($user = $this->getSenderUser()) {
            $image      = $this->getVar('image');
            $name       = $this->getSafeVar('name', '');
            $isComments = $this->getVar('isComments', 1);
            $group_id   = $this->getVar('group_id');
            $imageType  = $this->getVar('imageType');
            
            $group_path = GAMEPATH.$group_id;
            $group_path_preview = GAMEPATH.$group_id.'/'.CLOTH_PREVIEWWIDTH;
            
            if ($user['uid'] == 0) $user['uid'] = $_SESSION['NAUID'];
            
            if (!file_exists($group_path)) {
                mkdir($group_path);
                chmod($group_path, 0755);
                mkdir($group_path_preview);
                chmod($group_path_preview, 0755);
            }
            
            if ($image = base64_decode($image)) {                                                                
                $options = ($isComments?1:0);
                
                $res['result'] = DB::query("INSERT INTO ".DBPREF."game (`name`, `group_id`, `uid`, `user_source`, `time`, `options`) VALUES ('{$name}', {$group_id}, {$user['uid']}, '{$user['source']}', NOW(), {$options})")?1:0;
                $game_id = DB::lastID();
            
                $file_path = $group_path.'/'.$game_id.'.'.$imageType; 
                $res['result'] = file_put_contents($file_path, $image)?1:0;
                $res['data']['game_id'] = $game_id;
                
                $ig = new Image();
                $ig->Resize($file_path, CLOTH_PREVIEWWIDTH, CLOTH_PREVIEWHEIGHT, $group_path_preview.'/'.$game_id.'.jpg');
                
                if ($user['source'] == 'none') $swhere = "nauid={$user['uid']}";
                else $swhere = "uid={$user['uid']} AND source='{$user['source']}'";
                
                $res['data']['subs_state'] = DB::one("SELECT count(subs_id) FROM ".DBPREF."subs_pushall WHERE $swhere");
                Event::fireEvent('TOGAME', $user['uid'], $user['source'], $game_id, $group_id);                
            }
        }
        return $res;
    }
    
    protected function items() {
        include_once($this->templatePath);
    }
    
    protected function gamelist() {
        GLOBAL $locale;
        //include_once(TEMPLATES_PATH.'service.html'); return; //SERVICE
        
        $result = $this->_gameList($this->group, $this->page);
        $this->title = $locale['GAMELISTTITLE'].($result['group']?$result['group']['name']:'');
        $pageLink = link::c('game', 'gamelist', $this->group, '%s');    
        include_once($this->templatePath);
    }
    
    protected function winners() {
        GLOBAL $locale;
        //include_once(TEMPLATES_PATH.'service.html'); return; //SERVICE

        $result = $this->_gameList($this->group, $this->page, GC_ITEMPERPAGE, "g.state='victory'", 'g.time DESC, u.uid DESC');
        $this->title = $locale['WINNERSTITLE'].($result['group']?$result['group']['name']:'');
        $pageLink = link::c('game', 'winners', $this->group, '%s');    
        include_once($this->templatePath);
    }                                     
    
    protected function scenePictureURL($game) {
        if ($game['app'] == 'clothing')
            return GAMEURL.$game['group_id'].'/'.$game['game_id'].'.png'; 
        return false;
    }
    
    protected function view() {
        //include_once(TEMPLATES_PATH.'service.html'); return; //SERVICE
        
        if ($game_id = ss::getTask(2)) {
            $game = DB::line("SELECT g.*, g.uid AS uid, u.nick, u.first_name, u.last_name, g.user_source AS source, gp.name AS gName, gp.app AS app, g.votes/g.count_votes AS rate, t.text AS rulesText, d.text as descText, ".
                "(SELECT SUM(votes) FROM ".DBPREF."votes WHERE game_id=g.game_id) AS all_votes ".
                "FROM ".DBPREF."game g LEFT JOIN ".DBPREF."users u ON g.uid = u.uid AND g.user_source=u.source INNER JOIN ".DBPREF."gamegroups gp ON g.group_id=gp.group_id ".
                "LEFT JOIN gpj_texts t ON gp.rules = t.text_id LEFT JOIN gpj_texts d ON gp.desc = d.text_id ".
                "WHERE g.game_id=$game_id");
                
            $gamegroup = DB::line("SELECT * FROM ".DBPREF."gamegroups WHERE group_id={$game['group_id']}"); 
            include_once($this->templatePath);
        }           
    }
    
    protected function arhive() {
        GLOBAL $locale;
        //include_once(TEMPLATES_PATH.'service.html'); return; //SERVICE
        
        
        $where = "state='inactive'";
        if ($this->group) $where .= " AND group_id=$this->group";
        if ($this->contest) $where .= " AND contest_id=$this->contest";
        
        $arhives = DB::asArray("SELECT * FROM ".DBPREF."contest WHERE $where ORDER BY `date` DESC");
        if (count($arhives) > 0) {
            $arhive = $arhives[0];
            $result = $this->_gameList($arhives[0]['group_id'], $this->page, GC_ITEMPERPAGE, "g.contest_id={$arhive['contest_id']} AND g.state != 'remove'");
            $method = 'arhive';
            $this->title = sprintf($locale['GAMEARHIVETITLE'], date('d.m.Y', strtotime($arhive['date']))).($result['group']?$result['group']['name']:'');
            $this->group = $arhive['contest_id']; 
        } else {
            $result = array('group'=>$this->getGroup($this->group));
            $this->title = $result['group']['name'];
        }        
                   
        $pageLink = link::c('game', 'arhive', "contest-{$this->contest}-page-%s");    
        include_once($this->templatePath);
    }
    
    protected function getGroup($group_id) {
        return DB::line("SELECT g.*, t.text AS rulesText, d.text as descText ".
        "FROM ".DBPREF."gamegroups g LEFT JOIN gpj_texts t ON g.rules = t.text_id LEFT JOIN gpj_texts d ON g.desc = d.text_id ".
        "WHERE g.group_id='{$group_id}'");
    }
    
    protected function _gameList($group_id, $page=1, $count=GC_ITEMPERPAGE, $other_where="g.state='active'", $order='g.votes DESC, u.uid DESC') {
        $result = array('items'=>array(), 'total'=>0, 'page'=>$page, 'countperpage'=>$count);
        $start = max(($page - 1) * $count, 0);
        if (!$group_id) $group_id = 1;
        
        
        $query = "SELECT SQL_CALC_FOUND_ROWS g.*, g.user_source as source, u.nick, u.first_name, u.last_name, votes/count_votes AS rate, ".
                "(SELECT SUM(votes) FROM ".DBPREF."votes WHERE game_id=g.game_id) AS all_votes ".
                "FROM ".DBPREF."game g LEFT JOIN ".DBPREF."users u ON g.uid = u.uid AND g.user_source=u.source ".
                "WHERE g.group_id=$group_id AND $other_where ORDER BY $order LIMIT $start, $count";
                
        if ($items = DB::asArray($query)) {
            $result['total'] = ceil(DB::one('SELECT FOUND_ROWS()') / $count);
        }
        
        $result['group'] = $this->getGroup($group_id);
        $result['items'] = $items;
        
        return $result;
    }
    
    protected function addVote() {
        GLOBAL $_SERVER;
        $res = array('result'=>0, 'votes'=>0);
        $user = $this->getSenderUser();
        if (($game_id = $this->getSafeVar('game_id', 0)) && $user && ($votes = $this->getSafeVar('votes', 0))) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ($g = DB::line("SELECT * FROM ".DBPREF."game WHERE game_id=$game_id")) {
                $res['votes'] = $g['votes'];
                
                $vWhere = "game_id=$game_id AND `state`='{$g['state']}'";
                
                if ($user['source'] == 'none') $vWhere .= " AND `ip`='$ip'";
                else $vWhere .= " AND uid={$user['uid']} AND user_source='{$user['source']}'";  
                
                $query = "SELECT * FROM ".DBPREF."votes WHERE $vWhere";
                if ($vote = DB::line($query)) {
                    if (($addVotes = $votes - $vote['votes']) != 0)
                        $res['result'] = DB::query("UPDATE ".DBPREF."votes SET `votes`=$votes, `ip`='$ip' WHERE $vWhere")?1:0;
                    else $res['result'] = true;
                } else {
                    $addVotes = $votes;
                    $query = "INSERT INTO ".DBPREF."votes (`state`, `game_id`, `uid`, `user_source`, `ip`, `votes`) VALUES ('{$g['state']}', $game_id, {$user['uid']}, '{$user['source']}', '$ip', $votes)";
                    $res['result'] = DB::query($query)?1:0;
                    if ($addVotes != 0) Event::fireEvent('ADDVOTE', $user['uid'], $user['source'], $g['uid'], $game_id, $g['user_source'], $addVotes);
                }
                
                if ($res['result'] && ($g['state'] == 'active')) {
                    $votes = DB::line("SELECT SUM(votes) AS votes, COUNT(votes) AS `count_votes` FROM ".DBPREF."votes WHERE game_id=$game_id AND state='active'");
                    $res['result'] = DB::query("UPDATE ".DBPREF."game SET votes={$votes['votes']}, count_votes={$votes['count_votes']} WHERE game_id=$game_id")?1:0;
                    $res['votes'] = $votes['votes'];
                }
            }
        }
        echo json_encode($res); 
    }
    
    protected function getCmdItems() {
        $group_id = $this->getSafeVar('group_id', 1);
        
        $res = array('result'=>0);
        
        $noids = json_decode($this->getVar('noids', '[]'));
        
        $noids_str = '';
        foreach ($noids as $nid) 
            if (is_numeric($nid)) $noids_str .= ($noids_str?',':'').$nid;
        
        $cmditem = array();
        $user = $this->getSenderUser();
        
        $query = "SELECT * FROM ".DBPREF."game g ".
                "WHERE g.group_id=$group_id AND g.state='active' ".($noids_str?("AND g.game_id NOT IN ({$noids_str})"):'').
                " AND (SELECT game_id FROM ".DBPREF."votes v WHERE v.game_id=g.game_id AND v.uid={$user['uid']} AND v.user_source='{$user['source']}' GROUP BY game_id) IS NULL ".
                "GROUP BY g.uid ORDER BY g.votes DESC LIMIT 0, 10";
                              
        if (($items = DB::asArray($query)) && (count($items) > 1)) {
            
            $i=0;
            while ($i < 2) {
                $index = rand(0, count($items) - 1);
                
                if ($item = $items[$index]) { 
                    $item['image_url'] = $this->gameImageURL($item);
                    $cmditem[] = $item;
                    $noids[] = $item['game_id'];
                } 
                array_splice($items, $index, 1);
                $i++;                
            } 
            
        }
        
        $res['data'] = $cmditem;
        $res['result'] = 1;
        return $res;         
    }
    
    protected function getActiveGroups() {
        $query = "SELECT g.group_id, (SELECT count(gm.group_id) FROM of_game gm WHERE gm.group_id=g.group_id AND gm.state='active') AS g_count FROM `of_gamegroups` g WHERE g.active=1";
    }
}
?>