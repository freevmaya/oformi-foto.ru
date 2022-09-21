<?
    define('APISHELLPATH', MODEL_PATH.'/gapi');
    
    include_once(INCLUDE_PATH.'/_edbu2.php');
    include_once(APISHELLPATH.'/baseApiShell.php');
    
    class gapi01 extends g_model {
        public function addAPI($params) {
            $apiShell = $this->getApiShell($params[0]);
        }
    
        protected function createNewUser() {
            global $_SERVER, $_COOKIE;
            
            $ip = $this->getPackIP($_SERVER['REMOTE_ADDR']);
            $date = date('Y-m-d H:i:s');
            DB::query("INSERT INTO gpj_profiles (ip, createTime, lastVisit) VALUES ($ip, '$date', '$date')");
            $uid = query_one("SELECT LAST_INSERT_ID()");
            
            return query_one("SELECT LAST_INSERT_ID()");
        }
        
        protected function checkUID($uid) {
            global $_SERVER, $_COOKIE;
            if ($uid == 0) {
                if (!$uid = $this->findUserCookie())
                    if (!$uid = $this->findUserIP())
                        $uid = $this->createNewUser();                                                    
            }
            setcookie('uid', $uid);
            return $uid;
        } 
        
        public function userIn($params) {
            $uid = $this->checkUID($params[0]);
            $user = DB::line("SELECT * FROM gpj_profiles WHERE uid=$uid"); 
            return array('user'=>$user);
        }

        public function userGetInfo($params) {
        }
        
        public function photosGetAlbums($params) {
        }  

        public function photosGet($params) {
        }
        
        public function getApiShell($apiName) {
            $apiShell = $this->getVar($params[0]);
            if (!$apiShell) {
                include_once(APISHELLPATH."/{$params[0]}.php");
                $apiShell = new $params[0]($params);
                $this->setVar($apiShell);
            }
            return $apiShell;
        } 
        
        public function getVar($varName, $default=null) {
            global $_SESSION;
            $className = get_class($this);
            if (!isset($_SESSION[$className][$varName])) return $default;
            else return $_SESSION[$className][$varName];
        }
        
        public function setVar($varName, $value) {
            global $_SESSION;
            $className = get_class($this);
            $_SESSION[$className][$varName] = $value;
        }
        
        protected function findUserCookie() {
            global $_COOKIE;
            $uid = isset($_COOKIE['uid'])?$_COOKIE['uid']:0;
            return $uid;
        }
        
        protected function findUserIP() {
            global $_SERVER;
            $ip = $this->getPackIP($_SERVER['REMOTE_ADDR']);
            $result = DB::line("SELECT uid FROM gpj_profiles WHERE ip=$ip");
            echo 'Find as IP: '.$result['uid'];
            return $result?$result['uid']:0;
        }
        
        protected function getPackIP($ip) {
            $aip = explode('.', $ip);
            return ($aip[0] << 24) | ($aip[1] << 16) | ($aip[2] << 8) | $aip[3];
        }
    }
?>