<?

    include_once(dirname(__FILE__).'/clt_model/config_mm.php');
    include_once(INCLUDE_PATH.'/statistic.inc');
    include_once(INCLUDE_PATH.'/_edbu2.php');
    
    define('VICSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('PAYSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('VIPSTARTDATE', 'NOW() - INTERVAL 1 MONTH');
    
    define('DEVUID', '8062938299454250872');
    define('GAMESLIMITDEFAULT', 20);
    
    GLOBAL $dbname;
    $dbname = '_clothing_mm';
    
    function addWhere($where, $a_where) {
        $where .= ($where?' AND ':'').$a_where;
        return $where;
    }
        
    class clt28 extends g_model {

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
                $result = DB::query("REPLACE INTO `clt_templates` (`id`, `type`, `group`, `ws`, `ears`, `corr`, `autor`, `other`) VALUES({$params[0]}, '{$params[1]}', {$params[2]}, {$params[3]}, '{$params[4]}', '{$params[5]}', {$params[6]}, '{$params[7]}')");
                return array('result'=>$result);
            } else {
                $result = DB::query("INSERT INTO `clt_templates` (`type`, `group`, `ws`, `ears`, `corr`, `autor`, `other`) VALUES('{$params[1]}', {$params[2]}, {$params[3]}, '{$params[4]}', '{$params[5]}', {$params[6]}, '{$params[7]}')");
                return array('result'=>$result, 'id'=>DB::lastID());
            }
        }

        public function getTemplates($params) {
            return DB::asArray('SELECT * FROM `clt_templates` WHERE `checked`=0');
        }
        
        public function getGroups($params) {
            return DB::asArray('SELECT * FROM `clt_group` WHERE `disable`=0');
        }
        
        public function applyGroup($params) {
            return DB::query('REPLACE INTO `clt_tmplGroup` (`group_id`, `tmpl_id`) VALUES (%s, %s)', $params);
        }
        
        public function getGroupTmpls($params) {
            return DB::asArray('SELECT * FROM `clt_tmplGroup` WHERE `group_id`=%s', $params);
        }
        
        public function clearGroupTmpls($params) {
            return DB::query('DELETE FROM `clt_tmplGroup` WHERE `tmpl_id`=%s', $params);
        }
        
        public function inUser($params) {
            $date = date('Y-m-d H:i:s');
            if ($params[0]) {
                if (!$rec = DB::line("SELECT * FROM `clt_users` WHERE `uid`={$params[0]}")) {
                    $rec = array(
                        'uid'       =>$params[0],
                        'createDate'=>$date,
                        'visitDate' =>$date,
                        'banType'   =>0
                    );
                    
                    $rec['create'] = DB::query("INSERT INTO `clt_users` (`uid`, `createDate`, `visitDate`) VALUES ({$rec['uid']}, '{$rec['createDate']}', '{$rec['visitDate']}')");
                } else {
                    $rec['visitDate'] = $date;
                    $set = "`visitDate`='{$rec['visitDate']}'";
                    if ($rec['banType'] == 2) {
                        if (strtotime($rec['banDate'])<=strtotime('NOW')) {
                            $rec['banType'] = 0;
                            $set .= ", `banType`=0";
                        } else {
                            $rec['banComRec'] = DB::line("SELECT * FROM `clt_comments` WHERE `comment_id`=%s", array($rec['banContent']));
                        }
                    }
                    DB::query("UPDATE `clt_users` SET $set WHERE `uid`={$rec['uid']}");
                }
            
                return array('user'=>$rec, 
                            'pays'=>DB::asArray("SELECT * FROM `clt_transaction` WHERE `user_id` = %s AND (`time`>=".PAYSTARTDATE." OR `service_id` = 5 OR (`service_id` = 6 AND `time`>=".VIPSTARTDATE."))", $params),
                            'checkVictory'=>$this->checkVictory($params[0]),
                            'myVic'=>DB::asArray("SELECT * FROM `clt_gameVictory` WHERE `uid`=%s", $params),
                            'victory'=>DB::asArray("SELECT *, DATE_FORMAT(`time`, '%d.%m') as `ftime` FROM `clt_gameVictory` ORDER BY `time` DESC LIMIT 0,11"),
                            'isMyGame'=>DB::line("SELECT COUNT(id) as `count` FROM `clt_game` WHERE `uid`=%s", $params)
                            );
            } else return null; 
        }
        
        protected function checkVictory($uid) {
            $list       = DB::asArray("SELECT *, (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY) AS votes
                                        FROM `clt_game` g WHERE g.`uid`={$uid} AND g.noVictory=0 AND g.`time`<=NOW()-INTERVAL 1 DAY");
            $nullIds    = '';
            $noVic      = '';
            $victory    = array();
            
            foreach ($list as $key=>$item) {
                if (($item['rate'] == 0) || ($item['votes'] == 0)) { // Отсеиваем все с нулевым рейтингом
                    $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                } else {
                    // Выбераем другой коллаж с максимальным количеством голосов за период с создания проверяемого коллажа по конец действия коллажа (сутки от создания проверяемого коллажа)
                       
                    $endTime = date('Y-m-d H:i:s', strtotime($item['time'].'+1 day'));
                    $query = "SELECT *,
                                (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                            FROM `clt_game` g 
                            WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                            ORDER BY votes DESC
                            LIMIT 0,1";
                    $vic = DB::line($query);
                    if ($vic['votes'] > $item['votes']) { // Это не победа
                        $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                    } else {
                        DB::query("UPDATE `clt_game` SET noVictory=2 WHERE id={$item['id']}");
                        DB::query("INSERT `clt_gameVictory` VALUES ({$item['id']}, $uid, '{$item['time']}', {$item['votes']})");
                        array_push($victory, $item);
                    }
                }
            }
            
            if ($nullIds) DB::query("UPDATE `clt_game` SET noVictory=1 WHERE $nullIds");
            return $victory;
        }
        
        public function getHeads($params) {
            return array();//DB::asArray("SELECT * FROM `clt_heads` WHERE `uid`=%s", $params);
        }
        
        public function addHead($params) {
            DB::query("INSERT INTO `clt_heads` (`uid`,`image`,`maskID`,`mpos`) VALUES (%s, '%s', %s, '%s')", $params);
            return DB::lastID();
        }
        
        public function removeHead($params) {
            return DB::query("DELETE FROM `clt_heads` WHERE `id`=%s", $params);
        }
        
        public function gameList($params) {
            $where = '';
            if (!isset($params[2])) $params[2] = GAMESLIMITDEFAULT;
            if (!isset($params[3])) 
                $where = addWhere($where, '(g.`time`>=NOW()-INTERVAL 1 DAY)');
            else $where = addWhere($where, 'g.`uid`='.$params[3]);
            if ($params[0] != DEVUID) $where = addWhere($where, '(g.`debug`=0)');
            
            $query = "SELECT g.id as id, g.name as name, g.uid as uid, g.rate as rate, g.`time` as `time`, g.`noVictory` as noVictory, v.votes as isMyVote, g.`debug` as `debug`,
                                    (SELECT COUNT(transaction_id) as `count` FROM clt_transaction WHERE service_id=g.id AND `time`>=".VICSTARTDATE.") as dv_count, g.`stiker` as stiker
                                FROM `clt_game` g LEFT JOIN `clt_votes` v ON g.id=v.game_id AND v.uid=%s
                                ".($where?"WHERE {$where}":'')."
                                ORDER BY g.`%s` DESC
                                LIMIT 0, %s";
            
             return DB::asArray($query, $params);
        }
        
        public function banRequest($params) {
            $date = date('Y-m-d');
            $query = "UPDATE `clt_users` SET banType={$params[2]}, banDate='{$date}', banContent={$params[1]} WHERE uid={$params[0]}";
            return array('result'=>DB::query($query), 'query'=>$query);
        }
        
        public function getGame($params) {
            return DB::line('SELECT g.id as id, g.name as name, g.uid as uid, g.rate as rate, g.`time` as `time`, g.`noVictory` as noVictory, v.votes as isMyVote,
                                (SELECT COUNT(transaction_id) as `count` FROM clt_transaction WHERE service_id=g.id AND `time`>='.VICSTARTDATE.') as dv_count, g.`stiker` as stiker
                            FROM `clt_game` g LEFT JOIN `clt_votes` v ON g.id=v.game_id AND v.uid=%s
                            WHERE id=%s', $params);
        }
        
        public function renameGame($params) {
            return array('result'=>DB::query("UPDATE `clt_game` SET name='".mysql_escape_string(urldecode($params[1]))."', `stiker`={$params[2]} WHERE id={$params[0]}"));
        } 

        public function removeGame($params) {
            $delResult = DB::query("DELETE FROM `clt_game` WHERE uid={$params[0]} AND id={$params[1]}");
            if (!$delResult) $delResult = DB::query("DELETE FROM `clt_gameVictory` WHERE uid={$params[0]} AND id={$params[1]}");
            if ($delResult) {
                unlink(GAMEPATH.$params[1].'.jpg');
                unlink(GAMEPREVIEWPATH.$params[1].'.jpg');
            }
            DB::query("DELETE FROM `clt_votes` WHERE game_id={$params[0]}");
            return array('deleteResult'=>$delResult);
        } 

        public function uploadFromGame($params) {
            
            GLOBAL $GLOBALS;
            set_time_limit(100);
            
            $debug = 0;
            if ($params[0] == DEVUID) $debug = 1;
            
            $result     = DB::query("INSERT INTO `clt_game` (uid, rate, `name`, `stiker`, time, debug) VALUES ({$params[0]}, 0, '".mysql_escape_string(urldecode($params[1]))."', {$params[2]}, '".date('Y-m-d H:i:s')."', $debug)");
            $id         = DB::lastID();
            $file_name  = $id.'.jpg'; 
            $jpg       =  $GLOBALS["HTTP_RAW_POST_DATA"];
            
            if (file_exists(GAMEPATH.$file_name)) unlink(GAMEPATH.$file_name);
            $file = fopen(GAMEPATH.$file_name, 'w+');
            fwrite($file, $jpg);
            fclose($file);
            
            if (file_exists(GAMEPREVIEWPATH.$file_name)) unlink(GAMEPREVIEWPATH.$file_name);
            $img = imagecreatefromjpeg(GAMEPATH.$file_name);
            $dest = imagecreatetruecolor(60, 60);
            imagecopyresampled($dest, $img, 0, 0, 0, 0, imagesx($dest), imagesy($dest), imagesx($img), imagesx($img));
            imagejpeg($dest, GAMEPREVIEWPATH.$file_name, 80);
            
            return array('file'=>GAMEURL.$file_name, 'id'=>$id);
        }
        
        public function addComment($params) {
            $params[2] = mysql_escape_string(urldecode($params[2]));
            return array('result'=>DB::query("INSERT INTO `clt_comments` (uid, content_type, content_id, comment) VALUES (%s, 1, %s, '%s')", $params));
        }
        
        public function deleteComment($params) {
            return array('result'=>DB::query("DELETE FROM `clt_comments` WHERE comment_id=%s", $params));
        }
        
        public function listComment($params) {
            return query_array("SELECT DATE_FORMAT(c.`time`, '%d.%m.%y %H:%i') AS `time`, c.uid, c.comment, c.comment_id
                                FROM `clt_comments` c LEFT JOIN `clt_users` u ON c.uid=u.uid
                                WHERE c.content_type=1 AND c.content_id={$params[0]} AND (u.banType<2 OR u.banType=4)
                                ORDER BY c.`time`");
        }
        
        public function addVotes($params) {
            $result = DB::query("REPLACE `clt_votes` (game_id, uid, votes) VALUES (%s, %s,  %s)", $params);
            $votes  = DB::line('SELECT SUM(votes) AS votes FROM clt_votes WHERE game_id = %s', $params);
            $result = $result && DB::query("UPDATE `clt_game` SET rate=%s WHERE id=%s", array($votes['votes'], $params[0]));
            return array('result'=>$result);
        }
        
        public function getVotes($params) {
            return DB::asArray("SELECT * FROM `clt_votes` WHERE `game_id`=%s ORDER BY `time` DESC LIMIT 0,50", $params);
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
            
            return array('file'=>JPGURL.$file_name, 'time'=>time());
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
    }
?>