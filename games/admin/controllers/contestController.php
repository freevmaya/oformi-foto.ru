<?
include_once(INCLUDE_PATH.'/_edbu2.php');
include(TEMPLATES_PATH.'/paginator.php');

define('COUNTPERPAGE', 20);
define('DATEFORMAT', '%d.%m.%Y %h:%i');
define('PUSHALLKEY', 'fcc95ff04c1cfb96922f1eb48ecf1326');
define('PUSHALLID', '3072');


class contestController extends controller {
    protected function sendText($subs, $title, $text, $url='') {
        $res = false;
        $params = array(
            "type" => "unicast",
            "id" => PUSHALLID,
            "key" => PUSHALLKEY,
            "text" => $text,
            "title" => $title,
            "uid" => $subs['subs_id']
        );
        
        if ($url) $params['url'] = $url;
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
        CURLOPT_URL => "https://pushall.ru/api.php",
        CURLOPT_POSTFIELDS => $params,
          CURLOPT_RETURNTRANSFER => true
        ));
        $return=json_decode(curl_exec($ch), true); //получить ответ или ошибку
        curl_close($ch);
        
        $state = $return?'sent':'error';
        
//        $data = array('title'=>$title, 'text'=>$text, 'url'=>$url);
        
        DB::query("INSERT INTO of_notify (`uid`, `user_source`, `type`, `create_time`, `sending_time`, `state`, `сallback`, `data`) ".
                "VALUES ({$subs['uid']}, '{$subs['source']}', 'push', NOW(), NOW(), '{$state}', '{$url}', '$text')");
        
        return $return != null;
    }
    
    public function subs_item() {
        if ($subs_id = $this->request->getVar('subs_id', false)) {
            $query = "SELECT s.*, u.*, DATE_FORMAT(u.last_time, '".DATEFORMAT."') AS last_time, ".
                    "COUNT(g.game_id) AS game_count, SUM(IF(g.state='wait',1,0)) AS game_wait, SUM(IF(g.state='active',1,0)) AS game_active, SUM(IF(g.state='victory',1,0)) AS game_victory ".
                    "FROM `of_subs_pushall` s LEFT JOIN of_users u ON s.uid=u.uid AND s.source=u.source, of_game g ".
                    "WHERE s.subs_id={$subs_id} AND g.uid=u.uid AND g.user_source=u.source";
            if ($subs = DB::line($query)) {
                
                $url = $this->request->getVar('url', '');
                $title = $this->request->getVar('title', 'Сообщение');
                 
                if ($text = $this->request->getVar('text', false)) {
                    $isSent = $this->sendText($subs, $title, $text, $url);
                }
                
                $query = "SELECT DATE_FORMAT(g.time, '".DATEFORMAT."') AS `date`, g.*, (SELECT MAX(gm.votes) FROM of_game gm WHERE gm.state='active' AND gm.group_id=g.group_id) AS max_votes ".
                        "FROM of_game g ".
                        "WHERE g.uid={$subs['uid']} AND g.user_source='{$subs['source']}' AND (state='active' OR state='victory') ORDER BY g.votes DESC";
                $games = DB::asArray($query);
                foreach ($games as $i=>$g) {
                    $link = "game/view/{$g['game_id']}.html";
                    $indent = md5($link);
                    
                    $games[$i]['comment_count'] = query_one("SELECT COUNT(comment_id) FROM of_comments c INNER JOIN of_content cn ON cn.content_id = c.content_id ".
                                            "WHERE cn.indent='$indent'");
                    
                    $games[$i]['url'] = MAINURL.'/'.$link;
                }
                
                $query = "SELECT DATE_FORMAT(n.sending_time, '".DATEFORMAT."') AS `sent_time`, n.* FROM of_notify n WHERE n.uid={$subs['uid']} AND n.user_source='{$subs['source']}' AND n.state='sent'";
                $sents = DB::asArray($query);
                
                
                include($this->templatePath);
            }
        }
    }
    
    public function subs() {
        $page = max($this->request->getVar('subs-page', 1), 1);
        
        $limit = (max($page - 1, 0) * COUNTPERPAGE).','.COUNTPERPAGE;
         
        $query = "SELECT SQL_CALC_FOUND_ROWS p.*, u.*, g.game_id, (SELECT MAX(g.votes) FROM of_game g WHERE g.uid=u.uid AND g.user_source=u.source AND g.state='active') AS max_votes ".
                "FROM `of_subs_pushall` p LEFT JOIN `of_game` g ON g.uid=p.uid AND g.user_source=p.source AND g.state='active' INNER JOIN `of_users` u ON p.uid=u.uid AND p.source=u.source ".
                "GROUP BY p.subs_id ORDER BY g.game_id DESC, max_votes DESC, u.last_time DESC ".
                "LIMIT $limit";
        
        $items = DB::asArray($query);
        $count = query_one('SELECT FOUND_ROWS()');
        
        foreach ($items as $i=>$item) {
            $query = "SELECT COUNT(g.game_id) AS game_count, SUM(IF(g.state='wait',1,0)) AS game_wait, SUM(IF(g.state='active',1,0)) AS game_active, SUM(IF(g.state='victory',1,0)) AS game_victory ".
                    "FROM of_game g WHERE g.uid={$item['uid']} AND g.user_source='{$item['source']}'";
            $items[$i] = array_merge($item, DB::line($query));
        }

        $link = '?task=contest,subs'; 
        include($this->templatePath);
    }
    
    public function comments() {
    
        if (($waitOnly = $this->svar('waitOnly', false)) && ($this->request->getVar('allRecs', false))) {
            $waitOnly = false;
            $this->setSession('waitOnly', false);
        }
        
        $state      = $this->request->getVar('state', false);
        
        $ids = $this->request->getVar('ids', false);
        
        if ($state && $ids) {
            $where = 'comment_id IN ('.implode(',', $ids).')';
            $query = "UPDATE of_comments SET `state`='{$state}' WHERE $where";
            DB::query($query);
        }
        
        $page = max($this->request->getVar('com-page', 1), 1);        
        $limit = (max($page - 1, 0) * COUNTPERPAGE).','.COUNTPERPAGE;
        
        $where = "state";
        if ($waitOnly) $where = "state='wait'";
        
        $query = "SELECT SQL_CALC_FOUND_ROWS IF(u.source='none',u.nauid, u.first_name) AS first_name, u.*, c.*, cn.url ".
                "FROM of_comments c LEFT JOIN of_users u ON c.uid=u.uid AND c.user_source=u.source LEFT JOIN of_content cn ON cn.content_id=c.content_id ".
                "WHERE $where ".
                "ORDER BY comment_id DESC ".
                "LIMIT $limit";
        
        $items = DB::asArray($query);
        $count = query_one('SELECT FOUND_ROWS()');
        $link = '?task=contest,comments'; 
        
        include($this->templatePath);
    }
    
    public function pushall() {
        echo '<iframe src="https://pushall.ru/easypost.php?subid=3072" style="width: 800px; height: 800px;">';
    }     
}
?>