<?

include_once(CONTROLLERS_PATH.'/gameBaseController.php');
include_once(CONTROLLERS_PATH.'/ajaxController.php');
include_once(SSPATH.'helpers/notify_class.php');
include_once(SSPATH.'helpers/notify_classes/mailNotifier.php');

class userController extends gameBaseController {
    public function login() {
        GLOBAL $_SESSION;
        controller::mobileToBase();
        $autor = $this->getVar('autor', false);
        if ($autor) {
            $_SESSION['autor-appeal'] = $autor;
        }
        ss::$noadv = true;
        require($this->templatePath);
    }

    public function lang() {
        GLOBAL $_SERVER;
        $lang = $this->svar('lang', DEFAULT_LANG);
        echo '{"result":1}';
        //echo '<script type="text/javascript">document.location.href="'.$_SERVER['HTTP_REFERER'].'"</script>';
    }
    
    public function appeal() {
        GLOBAL $root;
        $user = $root->getUser();
        $uid = $user['uid'];
        $tid = $this->getVar('id', 0);
        $uid_autor = $this->getSafeVar('uid_autor', 0);
        if ($tid && $uid && $uid_autor) {
            if (!$content = DB::line("SELECT * FROM ev_appeal WHERE `content`={$tid}"))
                DB::query("INSERT INTO ev_appeal (`date`, `name`, `content`, `refplace`) VALUES ('{$date}', '{$uid}', '{$tid}', '{$uid_autor}')");
            require(TEMPLATES_PATH.'/user_appeal.html');
        }
    }   
    
    public function page() {
        /*    
        if (!ss::$isAdmin) {
            include_once(TEMPLATES_PATH.'service.html'); 
            return; //SERVICE
        }
        */
        
        $cuser = ss::getUserAlternate(); 
        $curUser = null;
        $suid = explode('-', $this->escape(ss::getTask(2, $cuser['source'].'-'.$cuser['uid'])));
        $snone = $suid[0] == 'none';
        $collages = null;
        $isMainPage = false;
        
        if ((count($suid) > 1) && $suid[0]) {
            if (!$snone) {
                if ($user = DB::line("SELECT *, DATE_FORMAT(create_date, '%d.%m.%Y') AS cDate, DATE_FORMAT(birthday, '%d.%m.%Y') AS birthday FROM ".DBPREF."users WHERE source='{$suid[0]}' AND uid={$suid[1]}")) {
                    $cw = "g.uid={$user['uid']} AND g.user_source='{$user['source']}'";
                }
            } else {
                if ($user = DB::line("SELECT *, DATE_FORMAT(create_date, '%d.%m.%Y') AS cDate, DATE_FORMAT(birthday, '%d.%m.%Y') AS birthday ".
                            "FROM ".DBPREF."users WHERE source='none' AND uid=0")) {
                            
                    if ($suid[1]) {
                        $user['uid'] = $suid[1];
                        $user['nauid'] = $suid[1];
                    }
                    $cw = "g.uid={$user['uid']} AND g.user_source='none'";
                }
            }
            
            $query = "SELECT g.*, ".
                    "(SELECT SUM(votes) FROM ".DBPREF."votes WHERE game_id=g.game_id) AS all_votes ".
                    "FROM ".DBPREF."game g WHERE $cw";
            $collages = DB::asArray($query);
            
            function collageCMD($c1, $c2) {
                if ($c1['state'] == 'victory') return -1;
                else if ($c2['state'] == 'victory') return 1; 
                else if ($c1['all_votes'] < $c2['all_votes']) return 1;
                return 0;
            }
            
            usort($collages, 'collageCMD');
            
            $vics = array();
            foreach ($collages as $col) 
                if ($col['state'] == 'victory') $vics[] = $col['game_id'];
            
            if ($user) {
                $naUsers = null;
                $curUser = ss::getUser(); 
                $isMainPage = ss::itsMe($user) || ($curUser && ($curUser['nauid'] == $user['nauid']));
                                
                if (($user['nauid']> 0) && ($curUser && (($curUser['nauid'] == $user['nauid']) || ss::$isAdmin))) {
                    $naUsers = DB::asArray("SELECT * FROM ".DBPREF."users WHERE nauid={$user['nauid']} AND uid!={$user['uid']} LIMIT 0, 10");
                    if (DB::line("SELECT * FROM ".DBPREF."nauids WHERE uid={$user['nauid']}")) {
                        $naUsers[] = array('uid'=>$user['nauid'], 'nauid'=>$user['nauid'], 'source'=>'none');
                    }
                } 
                
                mailNotifier::adminNotify('TEST');
                
                require($this->templatePath);
            }
        } else $this->login();
    }
    
    public function ofin() {
        ss::$noadv = true;
        require($this->templatePath);
    }
    
    public function privacypolicy() {
        ss::$noadv = true;
        require($this->templatePath);
    }  
    
    public function register() {
        controller::mobileToBase();
        $nick = '';
        if ($email = $this->getVar('email', '')) {
            $a      = explode('@', $email);
            $nick   = $a[0];
        }
        ss::$noadv = true;
        require($this->templatePath);
    }
    
    public function options() { 
        if ($user = ss::getUser()) {
            $options = DB::line("SELECT u.*, o.* FROM ".DBPREF."users u INNER JOIN ".DBPREF."user_options o ON u.uid=o.uid AND u.source=o.source WHERE u.uid={$user['uid']} AND u.source='{$user['source']}'");
            require($this->templatePath);
        } else require(TEMPLATES_PATH.'default.html'); 
    }
    
    public function forgotpass() {
        GLOBAL $locale;
        if ($code = ss::getTask(2)) {
            if ($forgotpass = DB::line("SELECT * FROM ".DBPREF."forgotpass WHERE code='$code'")) {
                require(TEMPLATES_PATH.'user/forgotform.html');
            } else require(TEMPLATES_PATH.'default.html'); 
        } else {
            $send = 0;
            if ($email = trim($this->getSafeVar('email'))) {
                if ($user = DB::line("SELECT email FROM ".DBPREF."users WHERE email='$email'")) {
                    $code = md5(date('d.m.Y H:i:s'));
                    $notifier = new mailNotifier('');
                    if ($notifier->mailTo($email, $code, $locale['FORGOTPASS_MAIL_SUBJECT'], TEMPLATES_PATH.'forgotpass.html')) {
                        $send = 1;
                        DB::query("REPLACE ".DBPREF."forgotpass (email, date, code) VALUES ('$email', NOW(), '$code')");
                    } else $send = -1;
                } else $send = -1;                        
            }
            require($this->templatePath);
        }
    }
     
    public function oflog() {
        GLOBAL $_SESSION;
        $res = array();
        if (($pass = $this->getSafeVar('pass')) && ($email = $this->getSafeVar('email'))) {
            $query = "SELECT * FROM ".DBPREF."users u, ".DBPREF."user_options o WHERE u.uid=o.uid AND u.source=o.source AND o.pass='{$pass}' AND u.email='{$email}' AND u.source='of'";

            $res['query'] = $query;
            if ($result = DB::line($query)) {
                $_SESSION[SESUSER] = $result;
                $_SESSION['NAUID'] = $result['nauid'];
            }
            $res['is_user'] = $result?1:0;
        }
        echo json_encode($res);
    }
    
    public function newuser() {
        $res = array();
        $source = 'of';
        if (($name = trim($this->getSafeVar('name'))) &&
            ($pass = $this->getSafeVar('pass')) &&
            ($email = trim($this->getSafeVar('email')))) {
            
            if (DB::line("SELECT uid FROM ".DBPREF."users WHERE email='{$email}' AND source='{$source}'")) {
                $res['email_exists'] = 1;
            } else {
                if (!($uid = ss::nauid())) 
                    $uid = ajaxController::getNAUID_A(); 
                
                $ip = $_SERVER['REMOTE_ADDR'];
                $result = DB::query("INSERT INTO ".DBPREF."users (uid, nauid, first_name, last_name, avatar, birthday, url, source, nick, email, create_date, last_ip) VALUES ($uid, $uid, '', '', '', NOW(), '', '{$source}', '{$name}', '{$email}', NOW(), '{$ip}')");
                $result &= DB::query("INSERT INTO ".DBPREF."user_options (uid, source, pass) VALUES ({$uid}, '{$source}', '{$pass}')");
                
                $sPath = AVAPATH.$source; 
                if (!file_exists($sPath)) {
                    $result &= @mkdir($sPath);
                    $result &= @chmod($sPath, 0755); 
                }
                 
                $imagePath = $sPath.'/'.$uid;
                if (file_exists(AVAPATH.'default')) {
                    $result &= @copy(AVAPATH.'default', $imagePath);
                    $result &= @chmod($imagePath, 0744);
                }
                
                $res['create'] = $result?1:0;
            }
        }
        echo json_encode($res);
    }
    
    public function changeAvatar() {
        GLOBAL $_SESSION;
        $res = array('result'=>0);
        if (($image = $this->getVar('image')) && ($user = $this->getSenderUser())) {
            $bsp = strpos($image, 'base64');
            
            $filePath = AVAPATH.$user['source'].'/'.$user['uid'];
            
            if (file_exists($filePath)) unlink($filePath);
            
            $file = fopen($filePath, 'w+');
            $image = substr($image, strpos($image, ","));
            $filesize = fwrite($file, base64_decode($image));
            fclose($file);
            
            if ($filesize > 0) {
                $img = imagecreatefrompng($filePath);
                unlink($filePath);
                imagejpeg($img, $filePath, 80);
                
                $last_time = date('Y-m-d H:i:s');
                $res['result'] = DB::query("UPDATE ".DBPREF."users SET `last_time`='{$last_time}', `avatar`='CUSTOM' WHERE uid={$user['uid']} AND source='{$user['source']}'")?1:0;
                
                $curuser = ss::getUser();
                if ($curuser && ($curuser['uid'] == $user['uid']))
                    $_SESSION[SESUSER]['last_time'] = $last_time; 
            }
        }
        echo json_encode($res);
    }
    
    public function union() {
        $res = array('result'=>0);
        if (($nauid = $this->getVar('nauid')) && ($cur_user = ss::getUser()) && (($nauid == $cur_user['nauid']) || (ss::$isAdmin))) {
            $where = "`uid`={$nauid} AND `source`='none'";
            $sets = "uid={$cur_user['uid']}, source='{$cur_user['source']}'";
            
            $whereA = "`uid`={$nauid} AND `user_source`='none'";
            $setsA = "uid={$cur_user['uid']}, user_source='{$cur_user['source']}'";
            
            DB::setProfile(true);
            
            $result = DB::query("UPDATE `".DBPREF."gif` SET $sets WHERE $where"); 
            $result &= DB::query("UPDATE `".DBPREF."notify` SET $setsA WHERE $whereA"); 
            $result &= DB::query("UPDATE `".DBPREF."game` SET $setsA WHERE $whereA");
            $result &= DB::query("UPDATE `".DBPREF."comments` SET $setsA WHERE $whereA");
            
            //$result &= DB::query("UPDATE `".DBPREF."votes` SET $setsA WHERE $whereA");
            
            $result &= DB::query("DELETE FROM `".DBPREF."nauids` WHERE uid=$nauid");
            $res['result'] = $result;
        }
        echo json_encode($res);
    }
    
    public function update() {
        $res = array('result'=>0);
        
        if ($user = ss::getUser()) {
            $result = false;
            $sets = "`first_name`='".$this->getSafeVar('first_name')."'";
            $sets .= ", `last_name`='".$this->getSafeVar('last_name')."'"; 
            $sets .= ", `nick`='".$this->getSafeVar('nick')."'";
            $sets .= ", `email`='".$this->getSafeVar('email')."'";
            $sets .= ", `gender`='".$this->getSafeVar('gender')."'";
            if ($birthday = $this->getSafeVar('birthday')) 
                $sets .= ", `birthday`='".date('Y-m-d', strtotime($birthday))."'";
            
            if ($result = DB::query("UPDATE ".DBPREF."users SET $sets WHERE uid={$user['uid']} AND source='{$user['source']}'")) 
                if ($pass = $this->getSafeVar('pass')) 
                    $result = $result && DB::query("UPDATE ".DBPREF."user_options SET `pass`='".$this->getSafeVar('pass')."' WHERE uid={$user['uid']} AND source='{$user['source']}'");
                
            
            $res['result'] = $result?1:0;
        }
        
        echo json_encode($res);
    }
    
    public function forgot_success() {
        $res = array('result'=>0);
        if (($code = $this->getVar('code')) && ($pass =  $this->getVar('pass'))) {
            if ($item = DB::line("SELECT f.*, u.* FROM ".DBPREF."forgotpass f INNER JOIN ".DBPREF."users u ON f.email=u.email AND u.source='of' WHERE f.code='$code'")) {
                if ($result = DB::query("UPDATE ".DBPREF."user_options SET pass='{$pass}' WHERE uid={$item['uid']} AND source='{$item['source']}'")) {
                    $result = $result && DB::query("DELETE FROM ".DBPREF."forgotpass WHERE code='$code'");
                }
                $res['result'] = $result?1:0;
            }
        }
        echo json_encode($res);
    }
}

?>