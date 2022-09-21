<?                                        
    include_once(dirname(__FILE__).'/base_model.php');
    $charset    = 'utf8';
    
    class dataModel extends base_model {
        
        protected function getInfo() {
            GLOBAL $_GET;
            include_once(dirname(__FILE__)."/android/servers.php");
            
            $result = array();
            if ($uid = $_GET['appuid']) {
                $lang = $_GET['lang'];
                $result = DB::line("SELECT * FROM pjad_users WHERE uid='$uid'");
                $ip = $_SERVER['REMOTE_ADDR'];
                $curDate = date('Y-m-d');
                if (!$result) {
                    DB::query("INSERT INTO pjad_users (`uid`, `createDate`, `lastDate`, `ip`, `lang`) VALUES ('$uid', '$curDate', '$curDate', '$ip', '$lang')");               
                    $result = DB::line("SELECT * FROM pjad_users WHERE uid='$uid'");
                    $result['created'] = 1;  
                    $result['ref_count'] = 0;   
                } else {
                    DB::query("UPDATE pjad_users SET `ip`='$ip', `lang`='$lang', `lastDate`='$curDate' WHERE `uid`='$uid'");               
                    $ref_count = DB::line("SELECT COUNT(ref_uid) AS `count` FROM pjad_referer WHERE ref_uid='$uid'");
                    $result['ref_count'] = $ref_count['count'];
                }
            }
            
            $lanFromBanner = "ru";
            if ($lang != "ru") $lanFromBanner = "en";
            $result['banner'] = null;//json_decode(sprintf(file_get_contents(dirname(__FILE__)."/android/banner.json"), $lanFromBanner));
            $result['servers'] = $storage; 
            return $result;
        }
        
        protected function setGCMUserID() {
            if (($uid = $_GET['uid']) && ($gcmid = $_GET['gcmid'])) {
                 return DB::query("UPDATE pjad_users SET `GCM_ID`='$gcmid' WHERE `uid`='$uid'");  
            }
            return -1;
        }
        
        protected function addPurchase() {
            if (($orderId = $_GET['orderId']) && ($uid = $_GET['uid']) && ($data = $_GET['data'])) {
                 return DB::query("REPLACE pjad_purchases (`orderId`, `uid`, `data`) VALUES ('$orderId', '$uid', '$data')");  
            }
            return -1;
        }   
        
        private function inWords($cw, $check_id) {
            $result = false;
            foreach ($cw as $word_id)
                if ($word_id == $check_id) $result = true;
            return $result; 
        }
        
        protected function createWord() { 
            
            $uid = $_GET['uid'];
            if (!$curWord = DB::line("SELECT word_id, activated FROM pjad_curWords WHERE uid='{$uid}'")) {
                $minmax = DB::line("SELECT MAX(`word_id`) AS max_id, MIN(`word_id`) AS min_id FROM `pjad_words`");
                if ($cw = DB::asArray("SELECT word_id FROM pjad_curWords WHERE activated=0")) {
                    while ($this->inWords($cw, $next_id = rand($minmax['min_id'], $minmax['max_id']))) {
                    }
                } else $next_id = rand($minmax['min_id'], $minmax['max_id']);
                
                DB::query("INSERT INTO `pjad_curWords` (`word_id`, `uid`) VALUES ({$next_id}, '{$uid}')");
            } else $next_id = ($curWord['activated']==0)?$curWord['word_id']:0;
                        
            DB::query("DELETE FROM `pjad_curWords` WHERE `time` <= NOW() - INTERVAL 5 DAY AND activated=0");
            
            $word = null;
            if ($next_id)
                $word = DB::line("SELECT * FROM pjad_words WHERE word_id=$next_id");
            return $word;
        }   
    }
?>