<?

    include_once(dirname(__FILE__).'/pj_model_ok/config.php');
    include_once(INCLUDE_PATH.'/statistic.inc');
    include_once(INCLUDE_PATH.'/_edbu2.php');
    
    define('TIMELDAYCOUNT', 5);
    define('TIMELIMITMIN', 400);
    define('TIMELIMITMAX', 2000);
    define('TIMELIMITPRICE', 30);
    define('PRICEFROMINVITE', 10);
    define('NULLDATE', '0000-00-00');
    define('NULLTIME', '0000-00-00 00:00:00');
    define('TEMPLATESLIMIT', 6000);
    define('APPKEY', 'CBAOKEABABABABABA');
    
    
	define('COLLECTFILETIMELIMITSET', 'В течение суток вы можете <b>быстро</b> сохранять неограниченное количество открыток. Стоимость этой услуги '.TIMELIMITPRICE.' ок.\n<b>Вы действительно хотите воспользоваться услугой?</b>');
	define('COLLECTFILENOACCESS', '<b>Вы исчерпали лимит бесплатных сохранений в сутки (%s открыток).</b>\n'.COLLECTFILETIMELIMITSET);
    define('ADDVOTENOTIFY', 'За ваш коллаж проголосовали, текущее количество голосов: %s');
	define('SENDNOTIFY', 'К вам пришла открытка от %s');
	define('MAILSUBJECT', 'К вам пришла открытка');
    
    define('VICSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('PAYSTARTDATE', 'NOW() - INTERVAL 1 DAY');
    define('VIPSTARTDATE', 'NOW() - INTERVAL 1 MONTH');
    define('DEVUID', '351762715688');
    define('GAMESLIMITDEFAULT', 40);
    
	define('GAMETMP', HOMEPATH.'/tmp/');
    
    define('CLODOGAMEPATH', 'pj_game/collages/');
    define('CLODOPREVIEWPATH', 'pj_game/preview/');
    define('CLODOGAMEURL', 'http://storage-27811-2.cs.clodoserver.ru/pj_game/collages/');
    define('CLODOPREVIEWURL', 'http://storage-27811-2.cs.clodoserver.ru/pj_game/preview/');
    
    GLOBAL $charset;
    $charset = 'utf8';
    
//    ERROR---        

    function addWhere($where, $a_where) {
        $where .= ($where?' AND ':'').$a_where;
        return $where;
    }
        
    class pj_ok45 extends g_model {
        
        private $intervals  = array(2001=>"-24 hour", 2008=>"-1 week", 2009=>"-1 month");
        
        private function getStartAlert() {
            include_once(dirname(__FILE__).'/pj_ok_start_alert.php');
            return $start_alert;
        }
        
        public function sendCard($params) {
            $cardInfo = explode('~', $params[2]);
            $send_result = array();
            $send_result['state'] = sql_query("INSERT INTO pjok_send (`uid`, `sendTo`, `params`, `time`, `date`, `card_id`) 
                                                VALUES ('{$params[0]}', '{$params[1]}', '{$params[2]}', '".date('Y-m-d H:i:s')."', '".date('Y-m-d')."', {$cardInfo[0]})");
            $send_result['send_id'] = query_one("SELECT LAST_INSERT_ID()");                                  
            
            if (!$sendTo = query_line("SELECT `inCount` FROM pjok_options WHERE `uid`={$params[1]}")) $this->createNewUser($params[1], false);
            if ($params[4]) // Отмечаем что есть исходящие открытки
                sql_query("UPDATE pjok_options SET isOut = 1 WHERE `uid`={$params[0]}");
            
            // Отмечаем что есть входящая открытка
            sql_query("UPDATE pjok_options SET `inCount` = `inCount` + 1 WHERE `uid`={$params[1]}");
            
            // Отправляет уведомление адресату от приложения
            $send_result['notifyResult'] = $this->sendNotify($params[1], sprintf(SENDNOTIFY, $params[3]), 'sid,'.$send_result['send_id']); 
            
            return $send_result;
        }

        private function sendNotify($uids, $message, $mark='') {
            include_once(INCLUDE_PATH.'/OKServer.php');
            $uids   = explode(',', $uids);
            $result = 0;
            foreach ($uids as $uid) {
                $notifyResult = OKServer::request(APPKEY, 'notifications/sendSimple', array(
                    'uid'=>$uid,
                    'text'=>$message,
                    'mark'=>$mark
                ));
                if ($notifyResult && !isset($notifyResult->error_code)) $result++;
                else return $notifyResult;
            }
            
            return $result;
        }
        
        public function notifyUser($params) {
            return $this->sendNotify($params[0], $params[1]);
        }
        
        public function getCard($params) {
            $card = DB::line('SELECT * FROM pjok_send WHERE send_id=%s', array($params[1]));
            if ($card['sendTo'] == $params[0]) 
                $card = array_merge($card, $this->receiveCardViewedAsUserA($params[1], $params[0]));
            return $card;
        }
        
        protected function receiveCardViewedAsUserA($send_id, $userID) {
            return array('result1'=>DB::query('UPDATE pjok_send SET `received`=1 WHERE `send_id`=%s', array($send_id)), 
                         'result2'=>DB::query('UPDATE pjok_options SET `receiveCount`=`receiveCount`+1 WHERE `uid`=%s', array($userID)));
        }
        
        public function createNewUser($uid, $createDateSet=true){
            $date = date('Y-m-d');
            $createTime = $createDateSet?date('H:i:s'):NULLDATE;
            $createDate = $createDateSet?$date:NULLDATE;
            $result = array('uid'=>$uid, 'notifyEnable'=>1, 'createDate'=>$createDate, 'visitDate'=>$createDate, 'visitTime'=>$createTime, 'inCount'=>0, 'receivedCount'=>0, 'isOut'=>0, 'vidgetDate'=>$createDate, 'create'=>1);
            sql_query("REPLACE pjok_options VALUES ({$uid}, 1, '{$createDate}', '{$createDate}', '{$createTime}', 0, 0, 0, '{$createDate}')");
            sql_query("REPLACE `pjok_refplace` (`uid`, `date`, `refplace`) VALUES ({$uid}, '{$createDate}', -1)");
            
            return $result;
        }
        
        private function utime() { 
            list($usec, $sec) = explode(" ", microtime()); 
            return ((float)$usec + (float)$sec); 
        }
        
        public function getInfo($params) {      
            $refplaces = array(//'catalog'=>1, 
                            'app_notification'=>2, 'friend_invitation'=>3, 'friend_feed'=>4, 
                            'friend_notification'=>5, 'app_search_apps'=>6);        
        
            $utime = $this->utime();
            $date = date('Y-m-d');
            
//            $query = "SELECT o.*, i.birthday FROM `pjok_options` o LEFT JOIN `pjok_info` i ON i.uid={$params[0]} WHERE o.uid={$params[0]}";
            $query = "SELECT * FROM pjok_options WHERE `uid`={$params[0]}";
            $result = query_line($query);
            if (!$result) {                                                     // Создаем запись нового пользователя
                $result = $this->createNewUser($params[0], true);
            } else if ($result['createDate'] == NULLDATE) {                     // Если уже есть запись, но не активированна
                $result['create']       = 1;
                $result['createDate']   = $date;
                $result['visitDate']    = $date;
                $result['vidgetDate']   = $date;
                sql_query("UPDATE pjok_options SET createDate=CURDATE(), vidgetDate=CURDATE(), visitDate=CURDATE(), visitTime=CURTIME() WHERE `uid`={$params[0]}");
            } else {
                if (strtotime($result['vidgetDate']) < strtotime(date('Y-m-d'))) {
                    sql_query("UPDATE pjok_options SET vidgetDate=CURDATE(), visitDate=CURDATE(), visitTime=CURTIME() WHERE `uid`={$params[0]}");
                } else sql_query("UPDATE pjok_options SET visitTime=CURTIME(), visitDate=CURDATE() WHERE `uid`={$params[0]}");
                
                $saveRec = query_line("SELECT `count` FROM pjok_save WHERE `uid`={$params[0]} AND `date`='$date'"); 
                $result['saveCount'] = $saveRec['count'];
                $result['alert'] = query_line("SELECT a.alert_id, t.* FROM `pjok_alerts` a, `gpj_alertTemplates` t WHERE a.uid={$params[0]} AND a.alert_id=t.id AND t.type<100");
            }
            
            if (isset($params[1]) && $params[1]) {
                if (isset($refplaces[$params[1]]))
                    sql_query("REPLACE `pjok_refplace` (`uid`, `date`, `refplace`) VALUES ({$params[0]}, '$date', {$refplaces[$params[1]]})");
            }
            
            if (!$result['alert']) $result['alert'] = $this->getStartAlert();  
            
            $this->userInFromInvite($params[0]);
            
            $result['utime'] = $utime;
            return $result;
        }
        
        public function removeAlert($params) {
            return DB::query("DELETE FROM `pjok_alerts` WHERE `uid`={$params[0]} AND `alert_id`={$params[1]}"); 
        }
        
        protected function userInFromInvite($user_id) {
            $acceptInvites  = DB::asArray("SELECT * FROM `pjok_invite` WHERE `inviteUser`={$user_id}"); // Выбираем активированные приглашения
            if (count($acceptInvites)) {
                foreach ($acceptInvites as $invite) {
                    $query = "INSERT INTO pjok_transaction (`user_id`, `service_id`, `price`, `time`, `params`) VALUES ({$invite['user_id']}, 1, ".PRICEFROMINVITE.", NOW(), '{$user_id}')";
                    DB::query($query);
                }
                DB::query("DELETE FROM pjok_invite WHERE `inviteUser`={$user_id}");
            }
        }

        public function getInBox($params) {
            $query = "SELECT send_id, uid, sendTo, DATE_FORMAT(time, '%d.%m.%Y %r') as time, UNIX_TIMESTAMP(time) as utime,
                               params, received FROM pjok_send WHERE `sendTo`='{$params[0]}'";
            $result = query_array($query);
            $receiveCount = 0;
            foreach ($result as $item)
                if ($item['received'] == 1) $receiveCount++;
            sql_query("UPDATE pjok_options SET `inCount`=".(count($result)).", `receiveCount`=".$receiveCount." WHERE `uid`={$params[0]}");
            return $result;
        }

        public function getOutBox($params) {
            $query = "SELECT send_id, uid, sendTo, DATE_FORMAT(time, '%d.%m.%Y %r') as time, UNIX_TIMESTAMP(time) as utime,
                               params, received FROM pjok_send WHERE `uid`='{$params[0]}' AND `received`=0";
            $result = query_array($query);
            if (count($result) > 0)
                sql_query("UPDATE pjok_options SET `isOut`=1 WHERE `uid`={$params[0]}");
            return query_array($query);
        }

        public function getInBoxCount($params) {
            $query = "SELECT COUNT(send_id) as `count` FROM pjok_send WHERE `sendTo`='{$params[0]}'";
            return query_line($query);
        }

        public function getOutBoxCount($params) {
            $query = "SELECT COUNT(send_id) as `count` FROM pjok_send WHERE `uid`='{$params[0]}'";
            return query_line($query);
        }
        
/*        public function receiveCardViewed($params) {
            return array('result1'=>sql_query("UPDATE pjok_send SET `received`=1 WHERE `send_id`={$params[0]}"), 
                         'result2'=>sql_query("UPDATE pjok_options SET `receiveCount`=`receiveCount`+1 WHERE `uid`={$params[0]}"));
        }*/

        public function receiveCardViewedAsUser($params) {
            return array('result1'=>sql_query("UPDATE pjok_send SET `received`=1 WHERE `send_id`={$params[1]}"), 
                         'result2'=>sql_query("UPDATE pjok_options SET `receiveCount`=`receiveCount`+1 WHERE `uid`={$params[0]}"));
        }
        
        public function getTime() {
            return array('time'=>strtotime('+1 month'), 'utime'=>strtotime('now')); 
        }
        
        public function deleteCard($params) {
            $query = "DELETE FROM pjok_send WHERE `send_id`={$params[0]}";
            return array('result'=>sql_query($query));
        }
        
        protected function prepaidWhereString() {
            $where      = '';
            
            foreach ($this->intervals as $key=>$item) {
                $curDate    = date('Y-m-d H:i:s');
                $date       = date('Y-m-d H:i:s', strtotime($item));
                $where      .= ($where?' OR ':'')."(gt.`service_id`={$key} AND gt.`debug`=0 AND gt.`time` >= '$date' AND gt.`time` <= '$curDate')";
            }
            return $where;
       }
        
        public function isPrepaidCollector($uid) {
            $result = array();
            foreach ($this->intervals as $key=>$item) {
            
                $curDate    = date('Y-m-d H:i:s');
                $date       = date('Y-m-d H:i:s', strtotime($item));
                $where      = "(gt.`service_id`={$key} AND gt.`debug`=0 AND gt.`time` >= '$date' AND gt.`time` <= '$curDate')";
                
                $query = "SELECT gs.`count` AS `noPayCount`, gt.price AS Pay, gs.lastTime as lastTime 
                          FROM pjok_save gs LEFT JOIN pjok_transaction gt ON (gt.`user_id`=$uid AND $where)
                          WHERE gs.uid = $uid AND gs.`date`='".date('Y-m-d')."'";
                $pp = query_line($query);
                if ($pp['Pay']) return $pp;
            }
            
        	return $pp;
        }
       
        public function noPayCollector($uid, $count) {
            return array('result'=>sql_query("REPLACE pjok_save (uid, `date`, `count`) VALUES ('$uid', '".date('Y-m-d')."', $count)"));        
        }
        
        public function addNoPayCollector($params) {
            statistic::add($params[1], $params[0], 'f');
            $pp = $this->isPrepaidCollector($params[0]);
            return $this->noPayCollector($params[0], $pp['Pay']?1:($pp['noPayCount'] + 1));        
        }
        
        public function statistic($params) {
            statistic::add($params[0], $params[1], $params[2]);
        }
            
        public function collectorImage($params) {
            parse_str($params[1], $p);
            if (!isset($p['cardClass'])) $p['cardClass'] = 'pi_pngcard';
            return $this->$p['cardClass']($params, $p);
        }
        
        public function checkPrepaid($params) {
            $pc = $this->isPrepaidCollector($params[0]);
            
            if (!isset($pc['Pay']) || !$pc['Pay']) {    // Если услуга не проплачена
                if (isset($pc['noPayCount']) && ($pc['lastTime'] != NULLTIME)) {    // Есть безлимитные сохранения
                
                    $limitCount = TIMELDAYCOUNT + $params[1];
                    
                    if ($pc['noPayCount'] >= $limitCount) {   // Вышел лимит бесплатных сохранений
                        return array('timeNoAccess'=>-1, 
                                    'timeNoDescr'=>sprintf(COLLECTFILENOACCESS, $limitCount),
                                    'timeNoUnset'=>COLLECTFILETIMELIMITSET,
                                    'price'=>TIMELIMITPRICE);
                    } else {
/*                    
                        $timeCount = round(TIMELIMITMIN + (TIMELIMITMAX - TIMELIMITMIN) / TIMELDAYCOUNT * $pc['noPayCount']);
                        $timeWait = $timeCount - (time() - strtotime($pc['lastTime']));
                        if ($timeWait > 0) {
                            return array('timeNoAccess'=>$timeWait,
                                        'timeNoDescr'=>sprintf(COLLECTFILETIMELIMITDESC, TIMELDAYCOUNT - $pc['noPayCount'], '%s'),
                                        'timeNoUnset'=>COLLECTFILETIMELIMITSET,
                                        'price'=>TIMELIMITPRICE);
                        }
*/                         
                    }
                }
                $pc['time'] = time();
            }
            return $pc;
        }
        
        protected function callExtFunc($ext, $callType, $params) {
            if ($ext) {
                foreach ($ext as $ext_info) {
                    if (($ext_info[0] != 'COR') && class_exists($ext_info[0])) 
                        eval("{$ext_info[0]}::{$callType}(\$params, \$ext_info);");
                }
            }
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
        
        protected function pi_pngcard2($params, $p) {
            include_once ('include/image.php');
//            return array('result'=>'');
            $hash = md5($params[1]);
            if (!isset($p['unlimit'])) $r_path = 'images/';
            else $r_path = 'question/';
            
            $file_name = $r_path.'i'.$p['id'].'_'.$params[0].'_'.$hash.'.jpg';
            if (!file_exists(DATA_PATH.$file_name)) {
                
//                if (isset($p['unlimit'])) trace('QUESTION BOOK RECORD '.$file_name.' SOURCE: '.$p['img']); // Лог, наблюдение за отправкой в гостевые
                
                if (!isset($p['unlimit'])) {
                    $pp = $this->checkPrepaid($params);
                    if (isset($pp['timeNoAccess'])) return $pp;
                    $this->noPayCollector($params[0], isset($pp['noPayCount'])?($pp['noPayCount'] + 1):1);
                }
                
                $tid        = $p['tid'];
                $ts         = explode(',', $p['ts']);
                $colors     = explode(',', $p['colors']);
                $inpos      = explode(',', $p['pos']);
                $flips      = explode(',', $p['flips']);
                $pos        = explode(',', $p['photo_pos2']);
                $scale      = round($p['scale'] * 100) / 100;
                $rotation   = $p['rotation'];
                
                $photo_url  = 'http://'.$p['img'];
                $mask_url   = 'http://'.$p['url'];
                $mask_file  = DATA_PATH."images/cards/i{$tid}.png"; // Сначало проверяем есть ли PNG файл
                
                if (!file_exists($mask_file)) {
                    $mask_file  = DATA_PATH.'images/cards/'.basename($mask_url); // Если нет тогда копируем по URL шаблона
                    if (!file_exists($mask_file)) copy($mask_url, $mask_file);
                }
                
                $ext        = array();
                if (isset($p['ext'])) {
                    $ext = explode('~', $p['ext']);
                    array_splice($ext, 0, 1);
                    foreach ($ext as $key=>$ext_info) {
                        $ext[$key] = explode(',', $ext_info);
                        $file_ext = dirname(__FILE__).'/pj_model/ext/'.$ext[$key][0].'.php';
                        if (file_exists($file_ext)) include_once($file_ext);
                    }
                }
                
                $image  = new Image();
                
                $himg   = $image->CreateImageFromFile($photo_url);
                $mimg   = $image->CreateImageFromFile($mask_file);
                $tsize  = array(imagesx($mimg), imagesy($mimg));
                // Корректируем позицию фото и скалинг по разнице размеров шаблонов в клиенте и на сервере
                
                $pcor   = array($tsize[0]/$ts[0], $tsize[1]/$ts[1]);
/*                $pos[0] = $pos[0] * $pcor[0];
                $pos[1] = $pos[1] * $pcor[1];*/
                $inpos[0] = $inpos[0] * $pcor[0];
                $inpos[1] = $inpos[1] * $pcor[1];
                $inpos[2] = $inpos[2] * $pcor[0];
                $inpos[3] = $inpos[3] * $pcor[1];
                $scale  = $scale * $pcor[0];
                
                $result = imagecreatetruecolor($tsize[0], $tsize[1]);
                
                $size = array(imagesx($himg), imagesy($himg));
                if ($p['gray']) imagefilter($himg, IMG_FILTER_GRAYSCALE); 
    
                $pos_image = imagecreatetruecolor($inpos[2], $inpos[3]);
                $spos = array('x'=>$pos[0] - $inpos[2] / 2 / $scale, 'y'=>$pos[1] - $inpos[3] / 2 / $scale);
                
                if ($scale == 1) imagecopy($pos_image, $himg, 0, 0, $spos['x'], $spos['y'], 
                                            $size[0] + $inpos[0], $size[1] + $inpos[1]);
                else imagecopyresampled($pos_image, $himg, 
                                    0, 0, $spos['x'], $spos['y'], 
                                    $size[0] * $scale, $size[1] * $scale, $size[0], $size[1]);

                if (($colors[0] != 1) || ($colors[1] != 1) || ($colors[2] != 1) ||
                    ($colors[4] != 0) || ($colors[5] != 0) || ($colors[6] != 0)) {
                    function calcColor($matrix, $rgb, $index) {
                        $result = $rgb[$index] * $matrix[$index] + $matrix[$index + 4];
                        if ($result < 0) $result = 0;
                        else if ($result > 255) $result = 255;
                        return $result; 
                    }                                    
                    for ($y=0; $y<$inpos[3]; $y++)
                        for ($x=0; $x<$inpos[2]; $x++) {
                            $color = @imagecolorat($pos_image, $x, $y);
                            $rgb = array(($color & 0xFF0000) >> 16, ($color & 0x00FF00) >> 8, $color & 0x0000FF);
                            $rgb[0] = calcColor($colors, $rgb, 0);
                            $rgb[1] = calcColor($colors, $rgb, 1);
                            $rgb[2] = calcColor($colors, $rgb, 2);
                            $color = imagecolorallocate($pos_image, $rgb[0], $rgb[1], $rgb[2]);
                            imagesetpixel($pos_image, $x, $y, $color);
                        }
                }
                

                if ((int)$flips[0] < 0) {
                    $pos_image = $image->flip($pos_image, true);
                    //if ((int)$flips[1] >= 0) $rotation += 180;
                }
                if ((int)$flips[1] < 0) {
                    $pos_image = $image->flip($pos_image, true);
                }
                
                if ($rotation) $pos_image = imagerotate($pos_image, -$rotation, 0);
                
                $nsize = array(imagesx($pos_image), imagesy($pos_image));
                
                imagecopy($result, $pos_image, $inpos[0] + $inpos[2] / 2 - $nsize[0] / 2, $inpos[1] + $inpos[3] / 2 - $nsize[1] / 2, 0, 0, $nsize[0], $nsize[1]);
                
                imagecopy($result, $mimg, 0, 0, 0, 0, imagesx($mimg), imagesy($mimg));

                $this->callExtFunc($ext, 'resultProc', $result);
                
                if (isset($p['extends']) && ($p['extends'] == 'mobil')) {
                    $mr_size    = array(imagesx($result), imagesy($result));
                    $m_size     = explode(',', $p['m_size']);
                    $m_offset   = explode(',', $p['m_offset']);
                    $m_ascale   = array($m_size[0]/$mr_size[0], $m_size[1]/$mr_size[1]);
                    $m_scale    = (($m_ascale[0] > $m_ascale[1])?$m_ascale[0]:$m_ascale[1]) * $pcor[0];
                    $o_size     = array($m_size[0] / $m_scale, $m_size[1] / $m_scale);
                    
                    $tmp = imagecreatetruecolor($m_size[0], $m_size[1]);
                    imagecopyresampled($tmp, $result, 0, 0, 
                                        - $m_offset[0] / $m_scale, 
                                        - $m_offset[1] / $m_scale, 
                                        $m_size[0], $m_size[1], 
                                        $o_size[0], $o_size[1]);
                    $result = $tmp;
                }
                                                        
                $image->Save(DATA_PATH.$file_name, $result);
            }
            
            return array('file'=>DATA_URL.$file_name, 'time'=>time());
        }
        
        protected function pi_pngcard($params, $p) {
            if (isset($p['photo_pos2'])) return $this->pi_pngcard2($params, $p);
            
            include_once ('include/image.php');
//            return array('result'=>'');
            
            $hash = md5($params[1]);
            $file_name = 'images/i'.$p['id'].'_'.$params[0].'_'.$hash.'.jpg';
            if (!file_exists(DATA_PATH.$file_name)) {
                
                $pp = $this->checkPrepaid($params);
                if (isset($pp['timeNoAccess'])) return $pp;
                $this->noPayCollector($params[0], isset($pp['noPayCount'])?($pp['noPayCount'] + 1):1);
                
                $photo_url  = 'http://'.$p['img'];
                $mask_url   = 'http://'.$p['url'];
                $mask_file  = DATA_PATH.'images/cards/'.basename($mask_url);
                
                if (!file_exists($mask_file)) copy($mask_url, $mask_file);
                 
                $colors     = explode(',', $p['colors']);
                $pos        = explode(',', $p['photo_pos']);
                $scale      = round($p['scale'] * 100) / 100;
                $rotation   = $p['rotation'];
                
                $image = new Image();
                
                $himg = $image->CreateImageFromFile($photo_url);
                $mimg = $image->CreateImageFromFile($mask_file);
                $result = imagecreatetruecolor(imagesx($mimg), imagesy($mimg));
                
                $size = array(imagesx($himg), imagesy($himg));
                if ($p['gray']) imagefilter($himg, IMG_FILTER_GRAYSCALE); 
                
                imagefilter($himg, IMG_FILTER_COLORIZE, 80 * $colors[0] - 80 + $colors[4], 
                                                        80 * $colors[1] - 80 + $colors[5], 
                                                        80 * $colors[2] - 80 + $colors[6], 0);
                                                        
    
                if ($rotation) {
                    $rollimg = imagecreatetruecolor($size[0] * 2, $size[1] * 2);
                    imagecopy($rollimg, $himg, $size[0], $size[1], 0, 0, $size[0], $size[1]);
                    $rollimg = imagerotate($rollimg, -$rotation, 0);
                    
                    $size = array(imagesx($rollimg), imagesy($rollimg));
                    $pos[0] -= $size[0] / 2 * $scale;
                    $pos[1] -= $size[1] / 2 * $scale;
                } else $rollimg = $himg;
                
                if ($scale == 1) 
                    imagecopy($result, $rollimg, $pos[0], $pos[1], 0, 0, $size[0], $size[1]);
                else imagecopyresampled($result, $rollimg, $pos[0], $pos[1], 0, 0, $size[0] * $scale, $size[1] * $scale, $size[0], $size[1]);
                
                imagecopy($result, $mimg, 0, 0, 0, 0, imagesx($mimg), imagesy($mimg));
                                                        
                $image->Save(DATA_PATH.$file_name, $result);
            }
            
            return array('file'=>DATA_URL.$file_name, 'time'=>time());
        }
        
        protected function pi_pngcardswf($params, $p) {
            return $this->pi_pjc($params, $p); 
        }
        
        protected function pi_pjc($params, $p) {

            include_once(INCLUDE_PATH.'/zip.php');
            $hash = md5($params[1]);
            $pth = DATA_PATH.'swf/swf'.$p['id'].'_'.$params[0].'_'.$hash;
            $zip_fileURL    = DATA_URL.'swf/swf'.$p['id'].'_'.$params[0].'_'.$hash.'.zip';
            
            $zip_fileName   = $pth.'.zip';
            $path = $pth.'/';
            $datapath = $path.'data/';
            if (!file_exists($zip_fileName)) {
                $pp = $this->checkPrepaid($params);
                if (isset($pp['timeNoAccess'])) return $pp;
                $this->noPayCollector($params[0], isset($pp['noPayCount'])?($pp['noPayCount'] + 1):1);
                
                mkdir($path);
                mkdir($datapath);
                chmod ($path, 0755);
                chmod ($datapath, 0755);
                
                $photo_url  = 'http://'.$p['img'];
                $mask_url   = 'http://'.$p['url'];
                $dvig       = 'models/templates/pj.swf';
                
                $d_mask_file  = DATA_PATH.'images/cards/'.basename($mask_url);
                if (!file_exists($d_mask_file)) copy($mask_url, $d_mask_file);
                
                $mask_file  = $datapath.basename($mask_url);
                $image_file = $datapath.basename($photo_url);
                $dvig_file  = $datapath.basename($dvig);
                    
                copy($d_mask_file, $mask_file);
                copy($photo_url, $image_file);
                copy($dvig, $dvig_file);
   
                ob_start();
                
                
                $p['img'] = 'data/'.basename($photo_url);
                $p['url'] = 'data/'.basename($mask_url);
                $flashvars = '';
                foreach ($p as $key=>$value) 
                    $flashvars .= ($flashvars?'&':'').$key.'='.$value ;
                require_once('models/templates/index.html');
                $content = ob_get_contents();
                ob_end_clean();
    
                $file = fopen($path.'index.html', 'w+');
                fwrite($file, $content);
                fclose($file);
                
                $zipfile = new zipfile();
                $zipfile->packToFile($path, $zip_fileName, false);
                $zipfile->_readdir($path, $files);
                foreach ($files as $file) unlink($path.$file);
                
                
                chmod ($zip_fileName, 0775);
                rmdir ($datapath);
                rmdir ($path);
            }
            
            return array('file'=>$zip_fileURL, 'time'=>time());
        }
        
        public function uploadImage($params) {
            GLOBAL $_FILES;
            include_once ('include/image.php');
          
            $uid = $params[0];
            $filePath = USERDATAPATH.$params[1];
            if (file_exists($filePath)) unlink($filePath);
            
            $image = new Image();
            $result = $image->Upload($_FILES['Filedata'], $filePath);
            if (is_array($result)) {
                $image->Resize($result['fileName'], 800, 800, '', 90);
                return array('file_url'=>USERDATAURL.$params[1]);
            } else return array('error'=>$result);
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
        
        public function removeTemplateGroups($params) {
            $result     = 1;
            $tmpl_id    = $params[0];
            $list       = explode(',', $params[1]);
            $where     = '';
            $tmpl_values     = '';
            foreach ($list as $group_id) {
                $where .= ($where?' OR ':'').'group_id='.$group_id;
            }
            
//            $result = $result && DB::query("REPLACE gpj_groups (`group_id`) VALUES %s", $values);
            $result = $result && DB::query("DELETE FROM gpj_templates WHERE (%s) AND tmpl_id=%s", array($where, $tmpl_id));
            
            return array('result'=>$result);
        }
        
        public function applyGroupTemplate($params) {
        
            $result     = 1;
            $tmpl_id    = $params[0];
            $list       = explode(',', $params[1]);
            $values     = '';
            $tmpl_values     = '';
            foreach ($list as $group_id) {
                $values .= ($values?',(':'(').$group_id.')';
                $tmpl_values .= ($tmpl_values?',(':'(').$group_id.','.$tmpl_id.')';
            }
            
//            $result = $result && DB::query("REPLACE gpj_groups (`group_id`) VALUES %s", $values);
            $result = $result && DB::query("REPLACE gpj_templates (`group_id`, `tmpl_id`) VALUES %s", $tmpl_values);
            
            return array('result'=>$result);
        }

        public function getTemplateGroups($params) {
            $query = "SELECT `group_id` 
                      FROM `gpj_templates` 
                      WHERE tmpl_id=%s";
            $list = DB::asArray($query, $params[0]);
            $result = array();
            foreach ($list as $item)
                $result[] = $item['group_id'];
            return $result;
        }
        
        public function getGroupTemplates($params) {
            $where      = '';
            $groups     = explode(',', $params[0]);
            $count      = count($groups);
            foreach ($groups as $group_id)
                $where .= ($where?' OR ':'')."group_id=$group_id";
                
            $notWhere = '';
            if (isset($params[1])) {
                $groups     = explode(',', $params[1]);
                foreach ($groups as $group_id)
                    $notWhere .= " OR group_id=$group_id";
            }
            
            $query = " SELECT *
                FROM (
                    SELECT tmpl_id, SUM(IF($where,1,0)) AS `acount`, COUNT( tmpl_id ) AS `count`
                    FROM `gpj_templates`
                    WHERE ($where $notWhere)
                    GROUP BY tmpl_id
                ) tmpls
                WHERE tmpls.acount=$count AND tmpls.count=$count
                ORDER BY tmpls.`tmpl_id` DESC
                LIMIT 0,".TEMPLATESLIMIT;                   
            
            //echo $query;
            $list = DB::asArray($query);
            $result = array();
            foreach ($list as $item) $result[] = $item['tmpl_id'];
            return $result;
        }
        
        public function addInvite($params) {
            $count  = 0;
            $result = 0;
            $users  = explode(',', $params[1]);
            $date = date('Y-m-d');
            if (count($users)) {
                $where = '`inviteUser`='.str_replace(',', ' OR `inviteUser`=', $params[1]);
                
// Исключаем уже приглашенных                
                $query = "SELECT * FROM `pjok_invite` WHERE `user_id`={$params[0]} AND ($where)";
                $invites = DB::asArray($query);
                foreach ($invites as $invite) {
                    $index = array_search($invite['inviteUser'], $users); 
                    if ($index !== false) array_splice($users, $index, 1);
                }
                 
// Исключаем тех кто уже установил приложение и был познее трех дней назад                  
                $where = '`uid`='.str_replace(',', ' OR `uid`=', $params[1]);
                $query = "SELECT * FROM `pjok_options` WHERE ($where) AND (`visitDate`>=NOW() - INTERVAL 3 DAY)";
                $backusers = DB::asArray($query);
                foreach ($backusers as $user) {
                    $index = array_search($user['uid'], $users); 
                    if ($index !== false) array_splice($users, $index, 1);
                }
                 
                $count  = count($users);
                if ($count) {
                    foreach ($users as $user) {
                        $query = "REPLACE INTO pjok_invite (`user_id`, `inviteUser`, `date`) VALUES ({$params[0]}, $user, '$date')";
                        $result += DB::query($query)?1:0;
                    }
                }
            }
            return array('result'=>$result, 'count'=>$count);
        }
//Transaction

        function getBalance($params) {
			return DB::line("SELECT SUM(`price`) as `balance` FROM pjok_transaction WHERE `service_id`=1 AND `user_id`={$params[0]}") ;
        }
        
        public function payMoney($params) {
            $result = DB::query("INSERT INTO pjok_transaction (`user_id`, `service_id`, `price`, `time`) VALUES ({$params[0]}, 1, -{$params[1]}, NOW())");
            $result = $result && DB::query("INSERT INTO pjok_transaction (`user_id`, `service_id`, `price`, `time`) VALUES ({$params[0]}, {$params[2]}, {$params[1]}, NOW())");
            return array('result'=>$result);
        }
        
        function setTransaction($params) {
            $price = ($params[1]==1)?$params[2]:$params[2] * 100;
            $result = DB::query("INSERT INTO pjok_transaction (`user_id`, `service_id`, `price`, `time`, `params`) 
                        VALUES (%s, %s, %s, '%s', '%s')",
                        array($params[0], $params[1], $price, date('Y-m-d H:i:s'), $params[3]));
            return array('result'=>$result);
        }

        function getPrepaid($params) {
/*            $curDate = date('Y-m-d H:i:s');
            $date   = date('Y-m-d H:i:s', strtotime("-{$params[1]} hour"));
            $where  = '`user_id`=%s AND ((`time` >= \'%s\' AND `time` <= \'%s\') OR (service_id = 2002)) AND debug=0';
            
            $args   = array($params[0], $date, $curDate);*/
            $where = $this->prepaidWhereString();
/*            
            if (isset($params[2])) {
                $where .= ' AND %s';
                $args[] = $params[2];
            }
*/            
            $query = "SELECT `time`, price, service_id
                        FROM pjok_transaction gt
                        WHERE gt.`user_id`={$params[0]} AND ($where)
                        GROUP BY service_id";
//            trace($query);
			return DB::asArray($query);
        }
        
//GAME COLLAGES IMPLEMENTATION
        
        public function inUserFromGame($params) {
            return array(
                'checkVictory'=>$this->checkVictory($params[0]),
                'bans'=>$this->checkBans($params[0]),
                'victoryList'=>DB::asArray("SELECT *, DATE_FORMAT(`time`, '%d.%m') as `ftime` FROM `pjok_gameVictory` WHERE `uid`={$params[0]} ORDER BY `time` DESC"),
                
            );
        }
        
        protected function checkBans($uid) {
            $bans = array();
            return $bans;
        }
        
        protected function checkVictory($uid) {
            $list       = DB::asArray("SELECT *, (SELECT SUM(votes) FROM pjok_votes WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY) AS votes
                                        FROM `pjok_game` g WHERE g.`uid`={$uid} AND g.noVictory=0 AND g.`time`<=NOW()-INTERVAL 1 DAY");
            $nullIds    = '';
            $noVic      = '';
            $victory    = array();
            
            foreach ($list as $key=>$item) {
                if (($item['rate'] == 0) || ($item['votes'] == 0)) { // Отсеиваем все с нулевым рейтингом
                    $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                } else {
                    $endTime = date('Y-m-d H:i:s', strtotime($item['time'].'+1 day'));
                    $query = "SELECT *,
                                (SELECT SUM(votes) FROM pjok_votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                            FROM `pjok_game` g 
                            WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                            ORDER BY votes DESC
                            LIMIT 0,1";
                    $vic = DB::line($query);
                    if ($vic['votes'] > $item['votes']) { // Это не победа
                        $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                    } else {
                        DB::query("UPDATE `pjok_game` SET noVictory=2 WHERE id={$item['id']}");
                        DB::query("INSERT `pjok_gameVictory` VALUES ({$item['id']}, '{$item['name']}', $uid, '{$item['time']}', {$item['stiker']}, {$item['votes']}, 0)");
                        array_push($victory, $item);
                    }
                }
            }
            
            if ($nullIds) DB::query("UPDATE `pjok_game` SET noVictory=1 WHERE $nullIds");
            return $victory;
        }
        
        public function gameList($params) {
            $where = '';
            if (!isset($params[2])) $params[2] = GAMESLIMITDEFAULT;
            if (!isset($params[3])) 
                $where = addWhere($where, '(g.`time`>=NOW()-INTERVAL 1 DAY)');
            else $where = addWhere($where, 'g.`uid`='.$params[3]);
            if ($params[0] != DEVUID) $where = addWhere($where, '(g.`debug`=0)');
            
//            $where = addWhere($where, '(b.banType is NULL)');
            $query = "SELECT g.id as id, g.name as name, g.uid as uid, g.rate as rate, g.`time` as `time`, g.`noVictory` as noVictory, v.votes as isMyVote, g.`debug` as `debug`, g.`stiker` as stiker,
                            (SELECT COUNT(content_id) AS comment_count FROM pjok_comments WHERE content_id=g.id) as comment_count
                                FROM `pjok_game` g LEFT JOIN `pjok_votes` v ON g.id=v.game_id AND v.uid=%s
                                ".($where?"WHERE {$where}":'')."                                    
                                GROUP BY g.id                                                                                                  
                                ORDER BY g.`%s` DESC
                                LIMIT 0, %s";
            $games = DB::asArray($query, $params);

            return array('GAMEURL'=>CLODOGAMEURL, 'GAMEPREVIEWURL'=>CLODOPREVIEWURL, 'list'=>$games);
        }
        
        public function friendGameList($params) {
            $users = explode(',', $params[0]);
            $where = '';
            foreach ($users as $uid) {
                $where .= ($where?' OR ':'').'g.uid='.$uid;
            }
            
            $query = "SELECT g.id as id, g.name as name, g.uid as uid, g.rate as rate, g.`time` as `time`, g.`noVictory` as noVictory, g.`debug` as `debug`, g.`stiker` as stiker,
                            (SELECT COUNT(content_id) AS comment_count FROM pjok_comments WHERE content_id=g.id) as comment_count, v.votes as isMyVote
                                FROM `pjok_game` g LEFT JOIN `pjok_votes` v ON g.id=v.game_id AND v.uid={$params[1]} LEFT JOIN `pjok_ban` b ON b.banContent=g.id
                                WHERE $where                                    
                                GROUP BY g.id                                                                                                  
                                ORDER BY g.rate DESC";
            $games = DB::asArray($query);            
            return array('GAMEURL'=>CLODOGAMEURL, 'GAMEPREVIEWURL'=>CLODOPREVIEWURL, 'list'=>$games);
        }
        
        public function getVicList($params) {
            return  array('GAMEURL'=>CLODOGAMEURL, 'GAMEPREVIEWURL'=>CLODOPREVIEWURL, 
                            'list'=>DB::asArray("SELECT *, DATE_FORMAT(`time`, '%d.%m.%y') as `ftime` FROM `pjok_gameVictory` ORDER BY `time` DESC LIMIT 0, 10"));
        }
        
        public function banRequest($params) {
            $query = "REPLACE `pjok_ban` (uid, banContent, banType) VALUES ({$params[2]}, {$params[0]}, {$params[1]})";
            return array('result'=>DB::query($query));
        }
        
        public function getGame($params) {
            $line = DB::line('SELECT g.id as id, g.name as name, g.uid as uid, g.rate as rate, g.`time`, g.`noVictory` as noVictory, v.votes as isMyVote, g.`stiker` as stiker,
                            (SELECT COUNT(content_id) AS comment_count FROM pjok_comments WHERE content_id=g.id) as comment_count
                            FROM `pjok_game` g LEFT JOIN `pjok_votes` v ON (g.id=v.game_id AND v.uid='.$params[0].')
                            WHERE id='.$params[1]);
            if ($line && $line['id']) {
                $line['GAMEURL']        = CLODOGAMEURL;
                $line['GAMEPREVIEWURL'] = CLODOPREVIEWURL;
            }                            
            return $line;                            
        }

        public function renameGame($params) {
            return array('result'=>DB::query("UPDATE `pjok_game` SET name='".mysql_escape_string(urldecode($params[1]))."', `stiker`={$params[2]} WHERE id={$params[0]}"));
        } 

        public function removeGame($params) {
            $delResult = DB::query("DELETE FROM `pjok_game` WHERE uid={$params[0]} AND id={$params[1]}");
            if (!$delResult) $delResult = DB::query("DELETE FROM `pjok_gameVictory` WHERE uid={$params[0]} AND id={$params[1]}");
            if ($delResult) {
//              Удалить файлы
            }
            DB::query("DELETE FROM `pjok_votes` WHERE game_id={$params[0]}");
            return array('deleteResult'=>$delResult);
        } 

        public function addGameCollage($params) {
            
            GLOBAL $GLOBALS;
            
            if (!$params[3]) {
                $todayDate = date('Y-m-d 00:00:00');
                $query = "SELECT COUNT(id) as gameCount FROM pjok_game WHERE uid={$params[0]} AND `time`>='{$todayDate}'"; 
                $today = DB::line($query);
                if ($today['gameCount'] > 0) {
                    return array('todayCount'=>$today['gameCount']);
                }
            }

            $debug = 0;
            if ($params[0] == DEVUID) $debug = 1;
            
            $result     = DB::query("INSERT INTO `pjok_game` (uid, `name`, rate, `stiker`, time, debug) VALUES ({$params[0]}, '{$params[1]}|{$params[2]}', 0, {$params[4]}, '".date('Y-m-d H:i:s')."', $debug)");
            return array('id'=>DB::lastID());
        }
        
        public function addComment($params) {
            $params[2] = mysql_escape_string(urldecode($params[2]));
            return array('result'=>DB::query("INSERT INTO `pjok_comments` (uid, content_type, content_id, comment) VALUES (%s, 1, %s, '%s')", $params));
        }
        
        public function deleteComment($params) {
            return array('result'=>DB::query("DELETE FROM `pjok_comments` WHERE comment_id=%s", $params));
        }
        
        public function listComment($params) {
           return query_array("SELECT DATE_FORMAT(c.`time`, '%d.%m.%y %H:%i') AS `time`, c.uid, c.comment, c.comment_id
                                FROM `pjok_comments` c
                                WHERE c.content_type=1 AND c.content_id={$params[0]}
                                ORDER BY c.`time` DESC");
        }
        
        public function addVotes($params) {
            $game   = DB::line('SELECT * FROM pjok_game WHERE id=%s', $params); 
            $result = DB::query("REPLACE `pjok_votes` (game_id, uid, votes) VALUES (%s, %s,  %s)", $params);
            $votes  = DB::line('SELECT SUM(votes) AS votes FROM pjok_votes WHERE game_id = %s', $params);
            $result = $result && DB::query("UPDATE `pjok_game` SET rate=%s WHERE id=%s", array($votes['votes'], $params[0]));
            
            $this->sendNotify($game['uid'], sprintf(ADDVOTENOTIFY, $votes['votes']));
            return array('result'=>$result);
        }
        
        public function getVotes($params) {
            return DB::asArray("SELECT * FROM `pjok_votes` WHERE `game_id`=%s ORDER BY `time` DESC LIMIT 0,50", $params);
        }
        
        public function socialInfo($params) {
            $info = json_decode($params[0]);
            return array('result'=>DB::query("REPLACE `pjok_info` (uid, name, sex, age, birthday, bday, city) VALUES ({$info->uid}, '{$info->name}', {$info->sex}, {$info->age}, '{$info->birthday}', '{$info->bday}', '{$info->city}')")?1:0);
        }
    }
?>