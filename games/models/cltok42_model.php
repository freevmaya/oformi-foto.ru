<?
    GLOBAL $dbname;
    $dbname = '_clothing';

    include_once(dirname(__FILE__).'/clt_model/config.php');
    include_once(INCLUDE_PATH.'/statistic.inc');
    include_once(INCLUDE_PATH.'/_edbu2.php');

    define('VICSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('PAYSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('VIPSTARTDATE', 'NOW() - INTERVAL 1 MONTH');
    
    define('MESGCOUNT_ACTIVEUSER', 10);
    define('VOTESCOUNT_ACTIVEUSER', 20);
    define('VOTES_GAMEUSER', 100);
    
    define('STATE_ACTIVEUSER', 1);
    define('STATE_GAMEUSER', 2);
    define('STATE_VICTORYUSER', 3);
    
    define('DEVUID', 351762715688);
    define('GAMESLIMITDEFAULT', 20);
    define('TBPREFIX', 'clt_ok');
    
    define('VOTESSUMQUERY', "(IFNULL((SELECT SUM(votes) FROM ".TBPREFIX."votes WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR), 0) + IFNULL((SELECT SUM(votes) FROM ".
                                TBPREFIX."votes_other WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR), 0))");
    
    define('COLLAGESPATH', 'collages/');
    
    define('ADDEDCOMMENTNOTIFY', 'К вашему коллажу оставили комментарий, в приложении "Прикольное перевоплощение"');
    
    function addWhere($where, $a_where) {
        $where .= ($where?' AND ':'').$a_where;
        return $where;
    }
        
    class cltok42 extends g_model {

        public function uploadJPGTemplate($params) {
            
            GLOBAL $GLOBALS;

            $path       = CLT_TEMPLATES_PATH.$params[0];
            $url        = CLT_TEMPLATES_URL.$params[0];
            
            $jpg        =  $GLOBALS["HTTP_RAW_POST_DATA"];
            
            if (file_exists($path)) {
                if ($params[1] == 1) unlink($path);
                else return array('result'=>'FILEEXISTS', 'file'=>$url);
            }
            $file = fopen($path, 'w+');
            fwrite($file, $jpg);
            fclose($file);
            return array('result'=>'OK', 'file'=>$url);
        }
        
        public function deleteTemplate($params) {
            $query = "DELETE FROM `clt_templates` WHERE `type`='{$params[0]}' AND `id`={$params[1]}";
            return array('result'=>DB::query($query));
        }
        
        public function updateTemplate($params) {
            if ($params[0]) {
                $result = DB::query("REPLACE INTO `clt_templates` (`id`, `type`, `group`, `ws`, `ears`, `corr`, `autor`) VALUES({$params[0]}, '{$params[1]}', {$params[2]}, {$params[3]}, '{$params[4]}', '{$params[5]}', {$params[6]})");
                return array('result'=>$result);
            } else {
                $result = DB::query("INSERT INTO `clt_templates` (`type`, `group`, `ws`, `ears`, `corr`, `autor`) VALUES('{$params[1]}', {$params[2]}, {$params[3]}, '{$params[4]}', '{$params[5]}', {$params[6]})");
                return array('result'=>$result, 'id'=>DB::lastID());
            }
        }

        public function getTemplates($params) {
            return DB::asArray('SELECT * FROM `clt_templates` WHERE `checked`=0');
        }
        
        public function inUser($params) {
            $date = date('Y-m-d H:i:s');
            $a = explode(',', $params[0]);
            $uid = $a[0];
//            $url = $a[1];
            if (!$rec = DB::line("SELECT * FROM `clt_okusers` WHERE `uid`={$uid}")) {
                $rec = array(
                    'uid'=>$uid,
                    'createDate'=>$date,
                    'visitDate'=>$date,
                    'banType'   =>0
                );
                
                $rec['create'] = DB::query("INSERT INTO `clt_okusers` (`uid`, `createDate`, `visitDate`) VALUES ({$rec['uid']}, '{$rec['createDate']}', '{$rec['visitDate']}')");
            } else {
                $rec['visitDate'] = $date;
                $set = "`visitDate`='{$rec['visitDate']}'";
                if ($rec['banType'] == 2) {
                    if (strtotime($rec['banDate'])<=strtotime('NOW')) {
                        $rec['banType'] = 0;
                        $set .= ", `banType`=0";
                    } else {
                        $rec['banComRec'] = DB::line("SELECT * FROM `clt_okcomments` WHERE `comment_id`=%s", array($rec['banContent']));
                    }
                }
                DB::query("UPDATE `clt_okusers` SET $set WHERE `uid`={$rec['uid']}");
            }
            

            $params[0] = $uid;
            return array('user'=>$rec, 
                        'pays'=>DB::asArray("SELECT * FROM `clt_oktransaction` WHERE `user_id` = {$uid} AND (`time`>=".PAYSTARTDATE." OR `service_id` = 5 OR (`service_id` = 6 AND `time`>=".VIPSTARTDATE."))"),
                        'save_error'=>DB::line("SELECT COUNT(`service_id`) AS `count` FROM `clt_oktransaction` WHERE `user_id` = %s AND `service_id`=7 AND param_num>0", $params),
                        'checkVictory'=>$this->checkVictory($uid),
                        'myVic'=>DB::asArray("SELECT * FROM `clt_okgameVictory` WHERE `uid`={$uid}"),
                        'isMyGame'=>DB::line("SELECT COUNT(id) as `count` FROM `clt_okgame` WHERE `uid`={$uid}"),
                        'message'=>'',
                        'plugins'=>array(
                            'streamPublic'=>'stream_public.swf,10'
                        )
                    );
        } 
        
        protected function checkVictory($uid) {
            $query = "SELECT *, (IFNULL((SELECT SUM(votes) FROM clt_okvotes_other WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY), 0) + 
                                                  IFNULL((SELECT SUM(votes) FROM clt_okvotes WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY), 0)) AS votes, g.mlp AS mlp
                                        FROM `clt_okgame` g WHERE g.`uid`={$uid} AND g.noVictory=0 AND (g.`time`<=NOW()-INTERVAL 1 DAY) AND g.`group`=0";
            $list       = DB::asArray($query);
            $nullIds    = '';
            $noVic      = '';
            $victory    = array();
            
            foreach ($list as $key=>$item) {
                if (($item['rate'] == 0) || ($item['votes'] == 0)) { // Отсеиваем все с нулевым рейтингом
                    $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                } else {
                    $endTime = date('Y-m-d H:i:s', strtotime($item['time'].'+1 day'));
                    $query = "SELECT *,
                                (IFNULL((SELECT SUM(votes) FROM clt_okvotes_other WHERE game_id=g.id AND `time`<='{$endTime}'), 0) + 
                                 IFNULL((SELECT SUM(votes) FROM clt_okvotes WHERE game_id=g.id AND `time`<='{$endTime}'), 0)) AS votes 
                            FROM `clt_okgame` g 
                            WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                            ORDER BY votes DESC
                            LIMIT 0,1";
                    $vic = DB::line($query);
                    if ($vic['votes'] > $item['votes']) { // Это не победа
                        $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                    } else {
                        DB::query("UPDATE `clt_okgame` SET noVictory=2 WHERE id={$item['id']}");
                        DB::query("INSERT `clt_okgameVictory` VALUES ({$item['id']}, $uid, '{$item['name']}', '{$item['time']}', {$item['votes']}, {$item['mlp']})");
                        array_push($victory, $item);
                        
                        $this->setStatus(array($uid, STATE_VICTORYUSER));
                    }
                }
            }
            
            if ($nullIds) DB::query("UPDATE `clt_okgame` SET noVictory=1 WHERE $nullIds");
            return $victory;
        }
        
        public function addSaved($params) {
            $trans = DB::asArray("SELECT * FROM `clt_oktransaction` WHERE `user_id` = {$params[0]} AND `service_id`=7 AND param_num>0", $params);
            if (count($trans) > 0) {
                DB::query("UPDATE `clt_oktransaction` SET `param_num`=0 WHERE transaction_id={$trans[0]['transaction_id']}");
            } 
        }
        
        public function getAllCountPayments($params) {
            return DB::line("SELECT COUNT(`transaction_id`) AS `count` FROM `clt_oktransaction` WHERE `user_id`={$params[0]}");
        }
        
        public function getGroupTmpls($params) {
            return DB::asArray('SELECT * FROM `clt_tmplGroup` WHERE `group_id`=%s GROUP BY tmpl_id', $params);;
        }
        
        public function getHeads($params) {
            return array();//DB::asArray("SELECT * FROM `clt_okheads` WHERE `uid`=%s", $params);
        }
        
        public function addHead($params) {
            DB::query("INSERT INTO `clt_okheads` (`uid`,`image`,`maskID`,`mpos`) VALUES (%s, '%s', %s, '%s')", $params);
            return DB::lastID();
        }
        
        public function removeHead($params) {
            return DB::query("DELETE FROM `clt_okheads` WHERE `id`=%s", $params);
        }
        
        public function getVictory($params) {
            return array('result'=>DB::asArray("SELECT *, DATE_FORMAT(`time`, '%d.%m') as `ftime` FROM `clt_okgameVictory` WHERE `time`>NOW() - INTERVAL 5 DAY ORDER BY `time` DESC LIMIT 0, {$params[0]}"));
        }
        
        public function gameList($params) {
            $where  = '';
            $result = array();
            if (!isset($params[2])) $params[2] = GAMESLIMITDEFAULT;
            if (!isset($params[3])) 
                $where = addWhere($where, '(g.`time`>=NOW()-INTERVAL 1 DAY) AND g.`group`=0');
            else {
                $where = addWhere($where, 'g.`uid`='.$params[3]);
            }
            if ($params[0] != DEVUID) $where = addWhere($where, '(g.`debug`=0)');
            
            $query = "SELECT g.id as id, g.name as name, g.uid as uid, ".VOTESSUMQUERY." as rate, g.`time` as `time`, g.`noVictory` as noVictory, v.votes as isMyVote, g.`debug` as `debug`, g.`group` as `group`,
                                    (SELECT COUNT(transaction_id) as `count` FROM clt_oktransaction WHERE service_id=g.id AND `time`>=".VICSTARTDATE.") as dv_count, g.`stiker` as stiker, g.mlp as mlp, g.options as options,
                                    cu.cur_state as `cur_state`
                                FROM `clt_okgame` g LEFT JOIN `clt_okvotes` v ON g.id=v.game_id AND v.uid=%s INNER JOIN `clt_okusers` cu ON cu.uid=g.uid
                                ".($where?"WHERE {$where}":'')."
                                ORDER BY `%s` DESC
                                LIMIT 0, %s";

            $result['list'] = DB::asArray($query, $params);          
            return $result;
        }
        
        public function banRequest($params) {
            $date = date('Y-m-d');
            $query = "UPDATE `clt_okusers` SET banType={$params[2]}, banDate='{$date}', banContent={$params[1]} WHERE uid={$params[0]}";
            return array('result'=>DB::query($query));
        }
        
        public function getGame($params) {
            return DB::line('SELECT g.id as id, g.name as name, g.uid as uid, '.VOTESSUMQUERY.' as rate, 
                                    g.`time` as `time`,g.`noVictory` as noVictory, v.votes as isMyVote, g.`group` as `group`,
                                (SELECT COUNT(transaction_id) as `count` FROM clt_oktransaction WHERE service_id=g.id AND `time`>=NOW() - INTERVAL 1 DAY) as dv_coun, g.`stiker` as stiker, g.mlp as mlp, g.options as options
                            FROM `clt_okgame` g LEFT JOIN `clt_okvotes` v ON g.id=v.game_id AND v.uid=%s
                            WHERE id=%s', $params);
        }

        public function renameGame($params) {
            GLOBAL $mysqli;
            return array('result'=>DB::query("UPDATE `clt_okgame` SET name='".mysqli_escape_string($mysqli, urldecode($params[1]))."', `stiker`={$params[2]} WHERE id={$params[0]}"));
        } 

        public function removeGame($params) {
            $delResult = DB::query("DELETE FROM `clt_okgame` WHERE uid={$params[0]} AND id={$params[1]}");
            if (!$delResult) $delResult = DB::query("DELETE FROM `clt_okgameVictory` WHERE uid={$params[0]} AND id={$params[1]}");
            if ($delResult) {
                @unlink(GAMEPATH.$params[1].'.jpg');
                @unlink(GAMEPREVIEWPATH.$params[1].'.jpg');
                @unlink(GAMESHAREPATH.$params[1].'.jpg');
            }
            DB::query("DELETE FROM `clt_okvotes` WHERE game_id={$params[0]}");
            DB::query("DELETE FROM `clt_okvotes_other` WHERE game_id={$params[0]}");
            return array('deleteResult'=>$delResult);
        } 

        public function uploadFromGame($params) {
            
            GLOBAL $GLOBALS, $mysqli;
            set_time_limit(100);

            $debug = 0;
            $group = 0;
            if ($params[0] == DEVUID) $debug = 1;
            if ($params[4]) $group = 1;
            
            $result     = DB::query("INSERT INTO `clt_okgame` (uid, `group`, rate, `name`, `stiker`, time, mlp, options, debug) VALUES ({$params[0]}, {$group}, 0, '".mysqli_escape_string($mysqli, urldecode($params[1]))."', {$params[2]}, '".date('Y-m-d H:i:s')."', {$params[3]}, {$params[5]}, $debug)");
            $id         = DB::lastID();
//            $this->setStatus(array($params[0], STATE_GAMEUSER));
            
            if ($params[3] == 0) return $this->uploadAsJPG(file_get_contents('php://input')/*$GLOBALS["HTTP_RAW_POST_DATA"]*/, $id);
            else return $this->uploadAsMLP(file_get_contents('php://input')/*$GLOBALS["HTTP_RAW_POST_DATA"]*/, $id);
        }
        
        protected function uploadAsJPG($jpg, $id) {
            $file_name  = $id.'.jpg';
             
            if (file_exists(GAMEPATH.$file_name)) unlink(GAMEPATH.$file_name);
            $file = fopen(GAMEPATH.$file_name, 'w+');
            fwrite($file, $jpg);
            fclose($file);
            
            if (file_exists(GAMEPREVIEWPATH.$file_name)) unlink(GAMEPREVIEWPATH.$file_name);
            $img  = imagecreatefromjpeg(GAMEPATH.$file_name);
            $dest = imagecreatetruecolor(60, 60);
            imagecopyresampled($dest, $img, 0, 0, 0, 0, imagesx($dest), imagesy($dest), imagesx($img), imagesy($img));
            imagejpeg($dest, GAMEPREVIEWPATH.$file_name, 80);
            
            if (file_exists(GAMESHAREPATH.$file_name)) unlink(GAMESHAREPATH.$file_name);
            $shpos = explode('x', SHAREPOS);
            $img  = imagecreatefromjpeg(GAMEPATH.$file_name);
            $dest = imagecreatefrompng(CLT_TEMPLATES_PATH.'images/stream_tmpl.png');
            
            $scale = $shpos[2] / imagesx($img);
            
            imagecopyresampled($dest, $img, $shpos[0], $shpos[1], 0, 0, $shpos[2], $shpos[3], imagesx($img), imagesy($img));
            imagejpeg($dest, GAMESHAREPATH.$file_name, 80);
            
            return array('file'=>GAMEURL.$file_name, 'id'=>$id);
        }
        
        protected function uploadAsMLP($mlp, $id) {
            $file_name      = $id.'.jpg';
            $preview_name   = GAMEPREVIEWPATH.$id.'.jpg';
            $len            = (ord($mlp[0]) << 32) | (ord($mlp[1]) << 16) | (ord($mlp[2]) << 8) | ord($mlp[3]);
            $preview = substr($mlp, 4, $len);
            
            if (file_exists($preview_name)) unlink($preview_name);
            $file = fopen($preview_name, 'w+');
            fwrite($file, $preview);
            fclose($file);
            
            $image = substr($mlp, 4 + $len);
            
            if (file_exists(GAMEPATH.$file_name)) unlink(GAMEPATH.$file_name);
            $file = fopen(GAMEPATH.$file_name, 'w+');
            fwrite($file, $image);
            fclose($file);
            
            //if (file_exists(GAMEPATH.$file_name)) unlink(GAMEPATH.$file_name);
            
            return array('file'=>GAMEURL.$file_name, 'id'=>$id);
        }
        
        public function addComment($params) {
            GLOBAL $mysqli;
            $params[2] = urldecode($params[2]);  
            $content_id = $params[1];
            if ($game = DB::line("SELECT * FROM `clt_okgame` WHERE id=$content_id"))
                $this->sendNotifyA($game['uid'], ADDEDCOMMENTNOTIFY);
             
            $result = array('result'=>DB::query("INSERT INTO `clt_okcomments` (uid, content_type, content_id, comment) VALUES (%s, 1, %s, '%s')", $params));
                
            $cc = DB::line("SELECT COUNT(comment_id) as `count` FROM `clt_okcomments` WHERE `uid`={$params[0]}");
            if ($cc['count'] >= MESGCOUNT_ACTIVEUSER) {
                $result['set_state'] = $this->setStatus(array($params[0], STATE_ACTIVEUSER));
            }
            return $result;
        }
        
        public function setStatus($params) {
            $user = DB::line("SELECT cur_state FROM `clt_okusers` WHERE uid={$params[0]}");
            if ($user['cur_state'] < $params[1]) {
                $new_state = $params[1];
                DB::query("UPDATE `clt_okusers` SET `cur_state`={$new_state}, `new_state`={$new_state} WHERE `uid`={$params[0]}");
                return $params[1];
            } else return 0;
        }
        
        public function deleteComment($params) {
            return array('result'=>DB::query("DELETE FROM `clt_okcomments` WHERE comment_id=%s", $params));
        }
        
        public function listComment($params) {
           return DB::asArray("SELECT DATE_FORMAT(c.`time`, '%d.%m.%y %H:%i') AS `time`, c.uid, c.comment, c.comment_id, u.cur_state
                                FROM `clt_okcomments` c LEFT JOIN `clt_okusers` u ON c.uid=u.uid
                                WHERE c.content_type=1 AND c.content_id={$params[0]} AND (u.banType<2 OR u.banType=4)
                                ORDER BY c.`time`");
        }
        
        protected function getIP() {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ip = explode('.', $ip);
            return implode('', $ip);
        }
        
/* 03.12.2014 отменил голосование только с одного IP                
        public function addVotes($params) {
            $params[3] = $this->getIP();
            
            $voteItem = DB::line("SELECT COUNT(game_id) AS `count` FROM `clt_okvotes` WHERE game_id={$params[0]} AND ip={$params[3]}");
            if ($voteItem['count'] == 0) {
                $result = DB::query("REPLACE `clt_okvotes` (game_id, uid, votes, ip) VALUES (%s, %s,  %s, %s)", $params);
                $votes  = DB::line('SELECT SUM(votes) AS votes FROM clt_okvotes WHERE game_id = %s', $params);
                $result = $result && DB::query("UPDATE `clt_okgame` SET rate=%s WHERE id=%s", array($votes['votes'], $params[0]));
                return array('result'=>$result);
            } else {
                return array('result'=>-1);
            }
        }
*/        
        public function addVotes($params) {
            $result = DB::query("REPLACE `clt_okvotes` (game_id, uid, votes, ip) VALUES (%s, %s,  %s, %s)", $params);
            $uid = $params[1];
            
            $lcount = DB::line("SELECT count(game_id) as `count` FROM `clt_okvotes` WHERE uid={$uid}");
            if ($lcount['count'] > VOTESCOUNT_ACTIVEUSER) $this->setStatus(array($uid, STATE_ACTIVEUSER));
            
            $votes  = DB::line('SELECT SUM(votes) AS votes FROM clt_okvotes WHERE game_id = %s', $params);
            $votes  = $votes['votes'];
            
            if ($votes > VOTES_GAMEUSER) {
                $autor = DB::line("SELECT u.uid AS `uid`, u.cur_state AS `cur_state` FROM `clt_okgame` g INNER JOIN `clt_okusers` u ON g.uid=u.uid WHERE g.id={$params[0]}");
                if ($autor['cur_state'] < STATE_GAMEUSER)
                    $this->setStatus(array($autor['uid'], STATE_GAMEUSER)); 
            }
            
            $result = $result && DB::query("UPDATE `clt_okgame` SET rate=%s WHERE id=%s", array($votes, $params[0]));
            return array('result'=>$result, 'vcount'=>$lcount['count']);
        }
        
        public function getVotes($params) {
            return DB::asArray("SELECT cv.*, cu.cur_state FROM `clt_okvotes` cv INNER JOIN `clt_okusers` cu ON cv.uid=cu.uid  WHERE cv.`game_id`=%s ORDER BY cv.`time` DESC LIMIT 0,50", $params);
        }
        

        public function uploadJPGCard($params) {
            
            GLOBAL $GLOBALS;

            $path   = explode('/', $params[1]);
            if (count($path) == 1) $file_name = $path[0].'/i'.$params[0].'_'.md5(time()).'.jpg';
            else $file_name = $params[1];
            
            $jpg       =  $GLOBALS["HTTP_RAW_POST_DATA"];
            
            if (file_exists(JPGPATH.$file_name)) unlink(JPGPATH.$file_name);
            $file = fopen(JPGPATH.$file_name, 'w+');
            fwrite($file, $jpg);
            fclose($file);
            
            return array('file'=>$file_name, 'time'=>time());
        }
        
        public function removeImage($params) {
            $result = 0;
            $afile = explode('/', $params[0]);
            $fileName = $afile[count($afile) - 1];
            array_splice($afile, count($afile) - 1, 1);
            array_splice($afile, 0, 3);

            $fileRelativePath = implode('/', $afile);
            $result = @unlink(MAINPATH.$fileRelativePath.'/'.$fileName);
            
            return array('result'=>$result);
        }
        
        public function voteDoubleRemove($params) {
            return array('result'=>DB::query("DELETE FROM `clt_oktransaction` WHERE `service_id`={$params[0]}"));
        }
        
        protected function sendNotifyA($suids, $text) {
            include_once(INCLUDE_PATH.'/OKServer.php');
            $uids = explode(',', $suids);
            $result = 0;
            $count = 0;
            foreach ($uids as $uid) {                       
                if (OKServer::request('CBAIKDKCABABABABA', 'notifications/sendSimple', array(
                    'uid'=>$uid,
                    'text'=>$text
                ))) $result++;
                
                $count++;
                if ($count >= 6) break;
            }
            return $result;// == count($uids);
        }
        
        public function sendNotify($params) {
            $backDate = date('Y-m-d', strtotime("-2 DAY"));
            $where = ' uid='.str_replace(',', ' OR uid=', $params[0]);
            $users = DB::asArray("SELECT * FROM clt_okusers WHERE ($where) AND `visitDate`<='$backDate'");
            $usersStr = '';
            $sendCount = 0;
            if (count($users) > 0) {
                foreach ($users as $user)
                    $usersStr .= ($usersStr?',':'').$user['uid'];
                $sendCount = $this->sendNotifyA($usersStr, $params[1]);
            }
            return array('sendCount'=>$sendCount);
        }
        
        public function setTransaction($params) {
            $result = DB::query("INSERT INTO clt_oktransaction (`user_id`, `service_id`, `other_price`, `time`, `params`) VALUES (%s, %s, %s, '%s', '%s')",
                        array($params[0], $params[1], $params[2], date('Y-m-d H:i:s'), $params[3]));
            return array('result'=>$result);
        }
    }
?>