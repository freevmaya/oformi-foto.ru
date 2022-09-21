<?                                        
    include_once(dirname(__FILE__).'/base_model.php');
    $charset    = 'utf8';
    
    class dataModel extends base_model {
        
        protected function getInfo() {
            GLOBAL $_GET;
            $result = array();
            if ($uid = $_GET['appuid']) {
                $result = DB::line("SELECT * FROM pjad_users WHERE uid='$uid'");
                $ip = $_SERVER['REMOTE_ADDR'];
                if (!$result) {
                    $createDate = date('Y-m-d');
                    DB::query("INSERT INTO pjad_users (`uid`, `createDate`, `ip`) VALUES ('$uid', '$createDate', '$ip')");               
                    $result = DB::line("SELECT * FROM pjad_users WHERE uid='$uid'");
                    $result['created'] = 1;  
                    $result['ref_count'] = 0;   
                } else {
                    DB::query("UPDATE pjad_users SET `ip`='$ip' WHERE `uid`='$uid'");               
                    $ref_count = DB::line("SELECT COUNT(ref_uid) AS `count` FROM pjad_referer WHERE ref_uid='$uid'");
                    $result['ref_count'] = $ref_count['count'];
                }
            }
            return $result;
        }
        
        protected function setGCMUserID() {
            if (($uid = $_GET['uid']) && ($gcmid = $_GET['gcmid'])) {
                 DB::query("UPDATE pjad_users SET `GCM_ID`='$gcmid' WHERE `uid`='$uid'");  
            }
        }      
    }
?>