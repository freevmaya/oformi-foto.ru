<?                                        
    include_once(dirname(__FILE__).'/base_model.php'); 
    include dirname(__FILE__).'/android/tmpl_default.php';
    $charset    = 'utf8';
    
    class dataModel extends base_model {
        
        protected function getInfo() {
            GLOBAL $_GET, $defaultTemplates;
            $storage = array(
                'JPG_URL'=>'http://fotoprivet.com/pj/JPG/',
                'hole_URL'=>'http://fotoprivet.com/pj/',
                'preview_URL'=>'http://fotoprivet.com/pj/preview120/',
                'TMPLS_MODEL'=>'childs_tmpl_01',
                'DATA_MODEL'=>'childs_01',
                'templateExternal'=>$defaultTemplates[rand(0, count($defaultTemplates) - 1)]
            );
            
            $result = array();
            if ($uid = $_GET['appuid']) {
                $lang = $_GET['lang'];
                $result = DB::line("SELECT * FROM pjad_users_ch WHERE uid='$uid'");
                $ip = $_SERVER['REMOTE_ADDR'];
                $curDate = date('Y-m-d');
                if (!$result) {
                    DB::query("INSERT INTO pjad_users_ch (`uid`, `createDate`, `lastDate`, `ip`, `lang`) VALUES ('$uid', '$curDate', '$curDate', '$ip', '$lang')");               
                    $result = DB::line("SELECT * FROM pjad_users_ch WHERE uid='$uid'");
                    $result['created'] = 1;  
                    $result['ref_count'] = 0;   
                } else {
                    DB::query("UPDATE pjad_users_ch SET `ip`='$ip', `lang`='$lang', `lastDate`='$curDate' WHERE `uid`='$uid'");               
                    $ref_count = DB::line("SELECT COUNT(ref_uid) AS `count` FROM pjad_referer WHERE ref_uid='$uid'");
                    $result['ref_count'] = $ref_count['count'];
                }
            }
            
            $lanFromBanner = "ru";
            if ($lang != "ru") $lanFromBanner = "en";
            $result['banner'] = json_decode(sprintf(file_get_contents(dirname(__FILE__)."/android/banner_from_childs.json"), $lanFromBanner));
            $result['servers'] = $storage; 
            return $result;
        }
        
        protected function setGCMUserID() {
            if (($uid = $_GET['uid']) && ($gcmid = $_GET['gcmid'])) {
                 return DB::query("UPDATE pjad_users_ch SET `GCM_ID`='$gcmid' WHERE `uid`='$uid'");  
            }
            return -1;
        }   
    }
?>