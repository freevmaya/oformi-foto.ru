<?
    GLOBAL $charset, $dbname;
    $dbname = '_clothing';
    
    define('DEVUID', 351762715688);
    define("LISTLIMITDEFAULT", 30);
    define("CONCURENTLIMIT", 30);
    define("MCNVTIME", strtotime('2015-02-26 13:40:00'));
    define('VICSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('GAMESLIMITDEFAULT', 20);
    
    define('TBPREFIX', 'clt_ok');
    define('VOTESSUMQUERY', "(IFNULL((SELECT SUM(votes) FROM ".TBPREFIX."votes WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR), 0) + IFNULL((SELECT SUM(votes) FROM ".
                                TBPREFIX."votes_other WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR), 0))");
    
    function addWhere($where, $a_where) {
        $where .= ($where?' AND ':'').$a_where;
        return $where;
    }
        
    class dataModel extends base_model {
        protected $TABLEPREF = TBPREFIX;
        
        protected function getContent() {
            GLOBAL $_GET;
            
            $endDate = isset($_GET['date'])?$_GET['date']:date('Y-m-d H:i:s');
                        
            $query = "SELECT * FROM {$this->TABLEPREF}game g WHERE id={$_GET['id']}";
            if ($content = DB::line($query)) {
                
                //Собираем нормальные голоса (до 5 баллов за голос) 
                $query      = "SELECT SUM(votes) AS votes, COUNT(votes) AS vote_count FROM {$this->TABLEPREF}votes WHERE game_id={$_GET['id']} AND `time`<='{$endDate}' AND votes <= 5";
                $content    = array_merge($content, DB::line($query));
                
                //Собираем удвоеные голоса
                $query      = "SELECT SUM(votes) AS votes, COUNT(votes) AS vote_count FROM {$this->TABLEPREF}votes WHERE game_id={$_GET['id']} AND `time`<='{$endDate}' AND votes > 5";
                $votes      = DB::line($query);
                $content['votes'] += $votes['votes'];
                $content['vote_count'] += $votes['vote_count'] * 2;
    
                //Собираем голоса с сайта
                $query      = "SELECT SUM(votes) AS votes, COUNT(votes) AS vote_count FROM {$this->TABLEPREF}votes_other WHERE game_id={$_GET['id']} AND `time`<='{$endDate}'";
                $votes      = DB::line($query);
                $content['votes'] += $votes['votes'];
                $content['vote_count'] += $votes['vote_count'];
                
                $endTime = date('Y-m-d H:i:s', strtotime($content['time'].' +1 day'));
                $query = "SELECT *, ".VOTESSUMQUERY." AS votes 
                            FROM `{$this->TABLEPREF}game` g 
                            WHERE g.`id`<>{$content['id']} AND g.`time`>='{$content['time']}' AND g.`time`<='$endTime' 
                            ORDER BY votes DESC
                            LIMIT 0, ".CONCURENTLIMIT;
                
                $content['endDate'] = $endDate;
                $content['concuren'] = DB::asArray($query);
    //            $content['duscus'] = $this->aconv($this->discusList($_GET['id']), array('comment'));
            }
            return $content;
        }
        
        protected function discusList($content_id) {
            $query = "SELECT comment_id, uid, comment, time FROM {$this->TABLEPREF}comments WHERE content_id=$content_id AND content_type=1";  
            return DB::asArray($query);
        }
        
        protected function getLeaders() {
            return $this->gameList('g.rate', '(g.`time`>=NOW()-INTERVAL 1 DAY) AND g.`group`=0');           
        }
        
        protected function getBests() {
            return $this->gameList('g.time', 'g.best=1');           
        }                                  
        
        protected function getWinners() {
            $result = array();
            $orderField = $this->getVar('orderField', 'id');
            $limit = $this->getVar('limit', GAMESLIMITDEFAULT);
            $a_page =  $this->getVar('page', 0) * $limit;
            
            $query = "SELECT SQL_CALC_FOUND_ROWS g.id as id, g.name as name, g.uid as uid, ".VOTESSUMQUERY." as rate, g.`time` as `time`, g.`noVictory` as noVictory, g.`debug` as `debug`, g.`group` as `group`,
                                    g.`stiker` as stiker, g.mlp as mlp, g.options as options,
                                    cu.cur_state as `cur_state`, g.best as best
                                FROM `".TBPREFIX."game` g INNER JOIN `".TBPREFIX."users` cu ON cu.uid=g.uid
                                WHERE g.`noVictory`=2
                                ORDER BY g.`$orderField` DESC
                                LIMIT $a_page, $limit";

            $result['list'] = DB::asArray($query);            
            $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
            $result['count']  = $count['count'];            
            return $result;            
        }
        
        protected function gameList($order, $where='') {
            $limit = $this->getVar('limit', GAMESLIMITDEFAULT);
            $uid = $this->getVar('uid', '0');
            $result = array();
            if ($uid != DEVUID) $where = addWhere($where, '(g.`debug`=0)');
            
//            $where = addWhere($where, '(g.`time`>=NOW()-INTERVAL 1 DAY) AND g.`group`=0');
            
            $a_page =  $this->getVar('page', 0) * $limit;            
            
            $query = "SELECT SQL_CALC_FOUND_ROWS g.id as id, g.name as name, g.uid as uid, ".VOTESSUMQUERY." as rate, g.`time` as `time`, g.`noVictory` as noVictory, v.votes as isMyVote, g.`debug` as `debug`, g.`group` as `group`,
                                    (SELECT COUNT(transaction_id) as `count` FROM clt_oktransaction WHERE service_id=g.id AND `time`>=".VICSTARTDATE.") as dv_count, g.`stiker` as stiker, g.mlp as mlp, g.options as options,
                                    cu.cur_state as `cur_state`, g.best as best
                                FROM `".TBPREFIX."game` g LEFT JOIN `".TBPREFIX."votes` v ON g.id=v.game_id AND v.uid=$uid INNER JOIN `clt_okusers` cu ON cu.uid=g.uid
                                ".($where?"WHERE {$where}":'')."
                                ORDER BY $order DESC
                                LIMIT $a_page, $limit";

            $result['list'] = DB::asArray($query);
            $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
            $result['count']  = $count['count'];          
            return $result;        
        }
        
        protected function getIP() {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ip = explode('.', $ip);
            return implode('', $ip);
        }
        
        protected function addVote() {
            $gid = $this->getVar('id', 0);  
            $result = false;
            $addVotes = 0;
            if ($gid) {
                $uid = $this->getVar('uid', 0);
                $votes = $this->getVar('votes', 0);
                $source = $this->getVar('source', 'other');
                
                $addVotes = $votes;
                
                $prevVotes = DB::line("SELECT * FROM `{$this->TABLEPREF}votes_other` WHERE game_id=$gid AND source='$source' AND uid=$uid");
                if ($prevVotes) $addVotes -= $prevVotes['votes'];
                
                $result = DB::query("REPLACE `{$this->TABLEPREF}votes_other` (game_id, source, uid, votes, ip) VALUES ($gid, '".$source."', ".$uid.", ".$votes.", ".$this->getIP().")");
                if ($addVotes != 0) {
                    $query = "UPDATE `clt_okgame` SET rate=rate+".$addVotes." WHERE id=$gid";
                    DB::query($query);
                }                            
            }                
            return array('addVotes'=>$addVotes, 'result'=>$result);
        }
        
        function setBest() {
            $gid = $this->getVar('id', 0);
            $query = "UPDATE `{$this->TABLEPREF}game` SET `best`={$_GET['value']} WHERE id={$_GET['id']}";
            $result = DB::query($query); 
            return array('result'=>$result, 'query'=>$query);
        }
    }
?>    