<?

    include_once(dirname(__FILE__).'/pj_model/config.php');
    include_once(INCLUDE_PATH.'/statistic.inc');
    
    define('TIMELDAYCOUNT', 20);
    define('TIMELIMITMIN', 0);
    define('TIMELIMITMAX', 1200);
    define('TIMELIMITPRICE', 20);
    define('NULLDATE', '0000-00-00');
    define('NULLTIME', '0000-00-00 00:00:00');
    
	define('COLLECTFILETIMELIMITDESC', 'Вы можете сегодня бесплатно сохранить еще %s открыток. Следующее, бесплатное сохранение открытки возможно через \n<b><font color="#888800">%s</font></b>\n<font color="#00AA00"><b>ВНИМАНИЕ:</b></font> Пригласите двух друзей и получите 20 рублей! Кстати, ровно столько стоит услуга сохранения открыток без ограничений.\n<b>Для немедленного сохранения открытки нажмите «Сохранить сейчас!»</b>');
	define('COLLECTFILETIMELIMITSET', 'В течение суток вы можете <b>быстро</b> сохранять неограниченное количество открыток. Стоимость этой услуги '.TIMELIMITPRICE.' руб.\n<b>Вы действительно хотите воспользоваться услугой?</b>');
	define('COLLECTFILENOACCESS', '<b>Вы исчерпали лимит бесплатных сохранений в сутки ('.TIMELDAYCOUNT.' открыток).</b>\n'.COLLECTFILETIMELIMITSET);
	define('SENDNOTIFY', 'К вам пришла открытка от %s!');
	define('MAILSUBJECT', 'К вам пришла открытка');

    class pjf01 extends g_model {
        
        protected function userMailNotify($mail, $sid) {
            GLOBAL $_SERVER;
            ob_start();
            require(MAINPATH.'games/templates/pj/mail_tmpl.php');
            $body = ob_get_contents();
            ob_end_clean();
            
            $from = "noreply@{$_SERVER['SERVER_NAME']}";
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=windows-1251\r\n";
            $headers .= "From:<$from>\r\n";
            $headers .= "To:<$mail>\r\n";
            $headers .= "Date:".date('r')."\r\n";
            
            return array('result'=>mail($mail, iconv('utf-8', 'windows-1251', MAILSUBJECT), 
                                iconv('utf-8', 'windows-1251', $body), $headers, $from), 'mail'=>$mail);            
        }
        
        public function unsubscribe($params) {
            $query = "UPDATE gpj_options SET notifyEnable = 0 WHERE `uid`={$params[0]}";
            sql_query($query);
            echo 'К вам больше не будут приходить уведомления об открытках!<br>';
        }
        
        public function getUserCreate($params) {
            if (!$user = query_line("SELECT * FROM gpj_users WHERE email='{$params[0]}'")) {
                sql_query("INSERT INTO gpj_users (`email`) VALUE ('{$params[0]}')");
                $user = array('uid'=>query_one("SELECT LAST_INSERT_ID()"), 'email'=>$params[0]);
            }
            
            return $user;
        }
            
        public function sendCard($params) {
            $mail           = $params[1];
            $a_mail         = explode('@', $mail);
            $send_result    = array();
            if (count($a_mail) == 2) {
                $cardInfo       = explode('~', $params[2]);
                $user           = $this->getUserCreate(array($mail));
                $send_result['state'] = sql_query("INSERT INTO gpj_send (`uid`, `sendTo`, `params`, `time`, `card_id`) 
                                                    VALUES ('{$params[0]}', '{$user['uid']}', '{$params[2]}', '".date('Y-m-d H:i:s')."', {$cardInfo[0]})");
                $send_result['send_id'] = query_one("SELECT LAST_INSERT_ID()");
                $send_result['send_result'] = $this->userMailNotify($mail, $send_result['send_id']); 
            } else $send_result['error'] = "Неверный формат e-mail: $mail";
            return $send_result;
        }
        
        public function getCard($params) {
            return query_line("SELECT * FROM gpj_send WHERE send_id=".@intval($params[0]));
        }
        
        public function createNewUser($uid, $createDateSet=true){
            $date = date('Y-m-d');
            $createTime = $createDateSet?date('H:i:s'):NULLDATE;
            $createDate = $createDateSet?$date:NULLDATE;
            $result = array('uid'=>$uid, 'notifyEnable'=>1, 'createDate'=>$createDate, 'visitDate'=>$createDate, 'visitTime'=>$createTime, 'inCount'=>0, 'receivedCount'=>0, 'isOut'=>0, 'vidgetDate'=>$createDate, 'create'=>1);
            sql_query("REPLACE gpj_options VALUES ({$uid}, 1, '{$createDate}', '{$createDate}', '{$createTime}', 0, 0, 0, '{$createDate}')");
            return $result;
        }
        
        private function utime() { 
            list($usec, $sec) = explode(" ", microtime()); 
            return ((float)$usec + (float)$sec); 
        }
        
        public function getInfo($params) {
            $utime = $this->utime();
            $query = "SELECT * FROM gpj_options WHERE `uid`={$params[0]}";
            $result = query_line($query);
            if (!$result) {                                                     // Создаем запись нового пользователя
                $result = $this->createNewUser($params[0], true);
                $result['setVidget'] = 1;
            } else if ($result['createDate'] == NULLDATE) {                     // Если уже есть запись, но не активированна
                $date = date('Y-m-d');
                $result['create']       = 1;
                $result['createDate']   = $date;
                $result['visitDate']    = $date;
                $result['vidgetDate']   = $date;
                sql_query("UPDATE gpj_options SET createDate=CURDATE(), vidgetDate=CURDATE(), visitDate=CURDATE(), visitTime=CURTIME() WHERE `uid`={$params[0]}");
                $result['setVidget'] = 1;
//                $this->setVidget($params);
            } else {
                if (strtotime($result['vidgetDate']) < strtotime(date('Y-m-d'))) {
//                    $this->setVidget($params);
                    $result['setVidget'] = 1;
                    sql_query("UPDATE gpj_options SET vidgetDate=CURDATE(), visitDate=CURDATE(), visitTime=CURTIME() WHERE `uid`={$params[0]}");
                } else sql_query("UPDATE gpj_options SET visitTime=CURTIME(), visitDate=CURDATE() WHERE `uid`={$params[0]}");
            }
            
            $result['utime'] = $utime; 
            return $result;
        }

        public function getInBox($params) {
            $query = "SELECT send_id, uid, sendTo, DATE_FORMAT(time, '%d.%m.%Y %r') as time, UNIX_TIMESTAMP(time) as utime,
                               params, received FROM gpj_send WHERE `sendTo`='{$params[0]}'";
            $result = query_array($query);
            $receiveCount = 0;
            foreach ($result as $item)
                if ($item['received'] == 1) $receiveCount++;
            sql_query("UPDATE gpj_options SET `inCount`=".(count($result)).", `receiveCount`=".$receiveCount." WHERE `uid`={$params[0]}");
            return $result;
        }

        public function getOutBox($params) {
            $query = "SELECT send_id, uid, sendTo, DATE_FORMAT(time, '%d.%m.%Y %r') as time, UNIX_TIMESTAMP(time) as utime,
                               params, received FROM gpj_send WHERE `uid`='{$params[0]}'";
            $result = query_array($query);
            if (count($result) > 0)
                sql_query("UPDATE gpj_options SET `isOut`=1 WHERE `uid`={$params[0]}");
            return query_array($query);
        }

        public function getInBoxCount($params) {
            $query = "SELECT COUNT(send_id) as `count` FROM gpj_send WHERE `sendTo`='{$params[0]}'";
            return query_line($query);
        }

        public function getOutBoxCount($params) {
            $query = "SELECT COUNT(send_id) as `count` FROM gpj_send WHERE `uid`='{$params[0]}'";
            return query_line($query);
        }
        
/*        public function receiveCardViewed($params) {
            return array('result1'=>sql_query("UPDATE gpj_send SET `received`=1 WHERE `send_id`={$params[0]}"), 
                         'result2'=>sql_query("UPDATE gpj_options SET `receiveCount`=`receiveCount`+1 WHERE `uid`={$params[0]}"));
        }*/

        public function receiveCardViewedAsUser($params) {
            return array('result1'=>sql_query("UPDATE gpj_send SET `received`=1 WHERE `send_id`={$params[1]}"), 
                         'result2'=>sql_query("UPDATE gpj_options SET `receiveCount`=`receiveCount`+1 WHERE `uid`={$params[0]}"));
        }
        
        public function getTime() {
            return array('time'=>strtotime('+1 month'), 'utime'=>strtotime('now')); 
        }
        
        public function deleteCard($params) {
            $query = "DELETE FROM gpj_send WHERE `send_id`={$params[0]}";
            return array('result'=>sql_query($query));
        }
        
        public function isPrepaidCollector($uid) {
            $curDate = date('Y-m-d H:i:s');
            $date = date('Y-m-d H:i:s', strtotime("-24 hour"));
            $query = "SELECT gs.`count` AS `noPayCount`, (gt.sms_price + gt.other_price) AS Pay, gs.lastTime as lastTime 
                      FROM gpj_save gs LEFT JOIN g_transaction gt ON (gt.`user_id`='$uid' AND gt.`time` >= '$date' AND gt.`time` <= '$curDate' AND gt.debug=0
                                AND gt.service_id=2001 AND gt.sms_price + gt.other_price < 0)
                      WHERE gs.uid = '$uid' AND gs.`date`='".date('Y-m-d')."'";
            $pc = query_line($query);
        	return $pc;
        }
       
        public function noPayCollector($uid, $count) {
            return array('result'=>sql_query("REPLACE gpj_save (uid, `date`, `count`) VALUES ('$uid', '".date('Y-m-d')."', $count)"));        
        }
        
        public function addNoPayCollector($params) {
            statistic::add($params[1], $params[0], 'f');
            $pp = $this->isPrepaidCollector($params[0]);
            return $this->noPayCollector($params[0], isset($pp['noPayCount'])?($pp['noPayCount'] + 1):1);        
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

                    if ($pc['noPayCount'] >= TIMELDAYCOUNT) {   // Вышел лимит бесплатных сохранений
                        return array('timeNoAccess'=>-1, 
                                    'timeNoDescr'=>COLLECTFILENOACCESS,
                                    'timeNoUnset'=>COLLECTFILETIMELIMITSET,
                                    'price'=>TIMELIMITPRICE);
                    } else {
                        $timeWait = round(TIMELIMITMIN + (TIMELIMITMAX - TIMELIMITMIN) / TIMELDAYCOUNT * $pc['noPayCount']) - (time() - strtotime($pc['lastTime']));
                        if ($timeWait > 0) {
                            return array('timeNoAccess'=>$timeWait,
                                        'timeNoDescr'=>sprintf(COLLECTFILETIMELIMITDESC, TIMELDAYCOUNT - $pc['noPayCount'], '%s'),
                                        'timeNoUnset'=>COLLECTFILETIMELIMITSET,
                                        'price'=>TIMELIMITPRICE);
                        } 
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
        
        public function setVidget($params) {
            $permission = MAILServer::request('441805', 'users.hasAppPermission', array('uid'=>$params[0], 'ext_perm'=>'widget'));
            if ($permission['widget']) {
                return MAILServer::request('441805', 'widget.set', array('uid'=>$params[0], 'html'=>
                    $this->getVidget($params[0])
                ));
            } else return array('set'=>0);
        }
        
        function getVidget($uid = 0) {
            
            include_once(dirname(__FILE__).'/pj_model/new_templates.php');
            $appURL = 'http://my.mail.ru/cgi-bin/my/app-canvas?appid=441805&ref=inst'.$uid;
            
            if ($uid) {
                include_once(MODEL_PATH.'pj_model/parser.php');
                $query = "SELECT card_id, send_id, uid, sendTo, DATE_FORMAT(time, '%d.%m.%y %H:%i') as time,
                                   params, received FROM gpj_send WHERE `sendTo`={$uid} AND `received`=0 ORDER BY `time` DESC LIMIT 0, 2";
                $inBox = query_array($query);
/*                $users = array();
                foreach ($inBox as $key=>$card) {
                    if (!isset($users[$card['uid']])) {
                        $user = MAILServer::request('441805', 'users.getInfo', array('uid'=>$uid, 'uids'=>$card['uid']));
                        $users[$card['uid']] = $user[0];
                    }
                    
                    $inBox[$key]['user'] = $users[$card['uid']];
                }*/
            }
            
            ob_start();
            require(MODEL_PATH.'/templates/vidget_pjv11.html');
            $content = str_replace(array("\n", "\r"), array('', ''), ob_get_contents());
            ob_end_clean();
            return $content;
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
        
        public function getGroupCards($params) {
            return file_get_contents(DATA_PATH.'pj_cards/'.$params[0].'.json');
        }        
    }
?>