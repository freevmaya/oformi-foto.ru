<?
    GLOBAL $dbname, $charset;
    
    class dataModelBase {
        private  $request;
        function __construct($request) {
            $this->request = $request;
        }
        
        protected function getTree() {
            $id = $this->getVar('id', 0);
            return $id?$this->getRod($id):null;
        }
        
        protected function getFirstRod() {
            $res = array('rod'=>0, 'tree'=>0);
            if ($uid = $this->getVar('uid', false)) {
                $query = "SELECT * FROM t_rod WHERE uid=$uid";
                if ($res['rod'] = DB::line($query)) {
                    $res['tree'] = $this->getRod($res['rod']['rod_id']);
                }
                
            }
            return $res;
        }
        
        protected function getRod($rod) {
            GLOBAL $_SESSION;
            $uid = @$_SESSION['uid'];
            $aid = explode('-', $rod);
            
            $query  = "SELECT people_id AS id, link_uid, name, family, father, DATE_FORMAT(bday, '%d.%m.%Y') AS bday, UNIX_TIMESTAMP(modified) AS modified, gender, rod_id, haveAvatar FROM t_peoples WHERE rod_id = {$aid[0]}";
            $result = DB::asArray($query);
            $ids    = array();
            $count  = count($result);      
            
            if ($count) {
                if (query_one("SELECT count(rod_id) FROM t_wins_tree WHERE uid={$uid} AND rod_id={$aid[0]}") == 0) {
                    $rod_head = $this->getRodHead($aid[0], $uid);
                    if ($rod_head['uid'] != $uid) {
                        if (($wcount = round($count * WINPERCENT)) > 0) { 
                            while ($wcount > 0) {
                                $i = rand(0, $count - 1); 
                                $result[$i]['win'] = 1;
                                $wcount--;
                            }
                        }
                    }
                }
                
                for ($i=0; $i<$count; $i++) {
                    $ids[] = $result[$i]['id'];
                }
                
                $ids_str = implode(',', $ids);
                $query = "SELECT parent_id AS pid, child_id as cid FROM t_childs WHERE parent_id IN ({$ids_str})";
                $childs = DB::asArray($query);
            } else $childs = array();
            
            return array($result, $childs, $this->getRodHead($aid[0], $uid));
        }
        
        protected function applyPeopleData() {
            GLOBAL $mysqli;
            
            $id     = $this->getVar('id', 0);
            $gender = $this->getVar('gender', 0);
            $link_uid     = $this->getVar('link_uid', 0);
            $name   = $mysqli->real_escape_string($this->getVar('name', ''));
            $family = $mysqli->real_escape_string($this->getVar('family', ''));
            $father = $mysqli->real_escape_string($this->getVar('father', ''));
            $bday   = $mysqli->real_escape_string($this->getVar('bday', ''));
             
            $haveAvatar = $mysqli->real_escape_string($this->getVar('haveAvatar', '0'));
            $rod_id     = $this->getVar('rod_id', 0);
            
            $bday = date('Y-m-d', strtotime($bday));
            
            $result = array('id'=>$id, 'result'=>false);
            if ($id) {
                $query = "UPDATE t_peoples SET name='{$name}', link_uid={$link_uid}, family='{$family}', father ='{$father}', bday='{$bday}', rod_id={$rod_id}, gender={$gender}, modified=NOW(), haveAvatar={$haveAvatar} WHERE people_id={$id}";
                $result['result'] = DB::query($query);
            } else {
                $query = "INSERT INTO t_peoples (name, link_uid, family, father, bday, rod_id, gender, modified, haveAvatar) VALUES ('{$name}', {$link_uid}, '{$family}', '{$father}', '{$bday}', {$rod_id}, {$gender}, NOW(), '{$haveAvatar}')";
                $result['result'] = DB::query($query);
                $result['id'] = $id = DB::lastID();
                $result['is_new'] = 1; 
                if ($this->getVar('set_main_id', false)) {
                    $query = "UPDATE t_rod SET main_id={$id} WHERE rod_id={$rod_id}";
                    $result['result'] = $result['result'] && DB::query($query);
                }
            }
            
            $img = $_POST['img'];
            $im = false;
            if (strpos($img, 'data') !== false) {
                $data = substr($img, strpos($img, ',') + 1);
                $data = base64_decode($data);
                $im = imagecreatefromstring($data);
            } else if (strpos($img, 'http') !== false) {
                $im = imagecreatefromjpeg($img);
            }
            
            if ($im !== false) {
                $image_file = $id.'.jpg'; 
                $image_file_path = PEOPLEIMAGEPATH.$image_file;
                if (file_exists($image_file_path)) unlink($image_file_path);
                
                imagejpeg($im, $image_file_path, JPGQUALITY);
                imagedestroy($im);
                chmod($image_file_path, IMAGEMODE);
                
                $result['image'] = $image_file.'?v='.strtotime('NOW');
            }
            
            $result['result'] = $result['result']?1:0;
            
            return $result;
        }
        
        protected function shareTree() {
            $res = array('result'=>0);
            
            if (($img = $_POST['image']) && ($id = $_POST['id'])) {
                $data = substr($img, strpos($img, ',') + 1);
                $data = base64_decode($data);
                $im = imagecreatefromstring($data);
                if ($im !== false) {
                    $image_file = $id.'.jpg'; 
                    $image_file_path = SHAREIMAGEPATH.$image_file;
                    if (file_exists($image_file_path)) unlink($image_file_path);
                    
                    imagejpeg($im, $image_file_path, JPGQUALITY);
                    imagedestroy($im);
                    chmod($image_file_path, IMAGEMODE);
                    
                    $res['image'] = $image_file.'?v='.strtotime('NOW');
                }
            }
            return $res;
        }
        
        protected function getShareTreeFriends() {
            $res = array('result'=>0);
            if ($id = $_POST['id']) {         
                $res['result'] = count($res['users'] = DB::asArray("SELECT uid, level AS access FROM t_access WHERE rod_id={$id}")) > 0;
            }
            return $res; 
        }
        
        protected function shareTreeFriends() {
            $res = array('result'=>0);
            if (($id = $this->getVar('id', DEMORODID)) && ($uids = $this->getVar('uids', 0))) {
                $uids = explode(',', $uids);
                $values = '';
                $access = $this->getVar('access', 'edit');
                $noclear = $this->getVar('noclear', 0);
                 
                foreach ($uids as $uid) $values .= ($values?',':'')."({$id}, {$uid}, '{$access}')";
                
                tables_lock('t_access');
                if ($noclear == 0) DB::query("DELETE FROM t_access WHERE rod_id={$id}");
                
                $res['result'] = DB::query("REPLACE INTO t_access (`rod_id`, `uid`, `level`) VALUES {$values}")?1:0;
                $res['rod_id'] = $id;
                tables_unlock();
            }
            return $res; 
        }
        
        protected function deleteRod() {
            $res = array('result'=>false);
            if ($rod_id = $this->getVar('rod_id', false)) {
                $query = "DELETE FROM t_rod WHERE rod_id={$rod_id}";
                $res['result'] = DB::query($query)?1:0;
                
                $pls = DB::asArray("SELECT * FROM t_peoples WHERE rod_id={$rod_id}");
                $delwhere = '';
                foreach ($pls as $pitem) {
                    $imgFile = PEOPLEIMAGEPATH.$pitem['people_id'].'.jpg';
                    if (file_exists($imgFile))
                        unlink($imgFile);
                    $delwhere .= ($delwhere?' OR ':'').'parent_id='.$pitem['people_id'];
                }
                
                if (count($pls) > 0) DB::query('DELETE FROM t_childs WHERE '.$delwhere);
                DB::query("DELETE FROM t_peoples WHERE rod_id={$rod_id}");
                DB::query("UPDATE t_users SET def_rod=0 WHERE def_rod={$rod_id}");
            }
            return $res;
        }
        
        protected function updateTree() {
            $resp = array('result'=>0);
            $db_result = false;
            if ($strlist = $this->getVar('list', false)) {
                $list = json_decode($strlist, true);
                $del_where = '';
                $values = '';
                foreach ($list as $id=>$item) {
                    $del_where .= ($del_where?' OR ':'')."parent_id={$id}";
                    if ($item)
                        foreach ($item as $cid)       
                            $values .= ($values?',':'')."({$id}, {$cid})";
                } 
                
                tables_lock('t_childs');
                if ($del_where) {
                    $del_query = "DELETE FROM t_childs WHERE $del_where";
                    $db_result = DB::query($del_query);                    
                }
                if ($values) {
                    $ins_query = "INSERT INTO t_childs (parent_id, child_id) VALUES $values";
                    $db_result = $db_result && DB::query($ins_query);                    
                }
                tables_unlock();
            }
            
            $resp['result'] = $db_result?1:0;
            
            return $resp;
        }
        
        protected function getRodsA($uid) {
            $list = DB::asArray("SELECT main_id, rod_id, rod_id AS id, uid, name, options, 'main' AS access FROM t_rod WHERE uid={$uid}");
             
            $assess_list = DB::asArray("SELECT r.main_id, r.rod_id, r.rod_id AS id, r.uid, r.name, r.options, a.level AS access ".
                                    "FROM t_access a INNER JOIN t_rod r ON r.rod_id = a.rod_id WHERE a.uid={$uid}"); 
            return array_merge($list, $assess_list);
        }
        
        protected function getRods() {
            if ($uid = $this->getVar('uid', 0)) {
                return $this->getRodsA($uid);
            }
        }
        
        protected function newRod() {
            $res = array('result'=>0);
            if (($name = $this->getVar('name', false)) && ($uid = $this->getVar('uid', 0))) {
                $options = $this->getVar('options', 0);
                $query = "INSERT INTO t_rod (`uid`, `name`, `options`) VALUES ({$uid}, '{$name}', {$options})";
                if ($res['result'] = DB::query($query)) {
                    $res['rod_id'] = DB::lastID();
                }
            }
            return $res;
        }
        
        protected function updateRod() {
            $res = array('result'=>0);
            if (($name = $this->getVar('name', false)) && ($rod_id = $this->getVar('rod_id', 0))) {
                $options = $this->getVar('options', 0);
                $query = "UPDATE t_rod SET `name`='{$name}', `options`={$options} WHERE rod_id={$rod_id}";
                $res['result'] = DB::query($query);
            }
            return $res;
        }
        
        protected function getRodHead($id, $uid) {
            $aid = explode('-', $id);
            if ($rod = DB::line("SELECT r.*, a.level AS access FROM t_rod r LEFT JOIN t_access a ON r.rod_id=a.rod_id AND a.uid={$uid} WHERE r.rod_id={$aid[0]}")) {
                if (!isset($aid[1])) {
                    $start_id = query_one("SELECT start_id FROM t_rod_state WHERE rod_id={$aid[0]} AND uid={$uid}");
                    $rod['start_id'] = $start_id?$start_id:$id;
                } else $rod['start_id'] = $aid[1]; 
            } 
            return $rod;
        }
        
        protected function setRodDefault() {
            if (($uid = $this->getVar('uid', false)) && ($rod_id = $this->getVar('rod_id', false))) {
                return DB::query("UPDATE `t_users` SET `def_rod`={$rod_id} WHERE `uid`={$uid}")?1:0; 
            }
        }
        
        protected function createDefRod($uid) {
            $query = "INSERT INTO t_rod (`uid`, `name`) VALUES ({$uid}, '".RODNAMEDEFAULT."')";
            DB::query($query);
            return DB::lastID();
        }
        
        protected function inUser() {
            GLOBAL $_SESSION;
            
            $_SESSION['uid'] = $uid = $this->getVar('uid', 1);            
            
            $res = array();
            $id = $this->getVar('id', 0); 
            
            if ($user = DB::line("SELECT * FROM t_users WHERE uid={$uid}")) {
                if (!$id) $id = $user['def_rod'];
                if (!($rod = $this->getRodHead($id, $uid))) {
                    $id = $this->createDefRod($uid);
                    $rod = $this->getRodHead($id, $uid);
                }
                $res = array('user'=>$user, 'tree'=>$this->getRod($id), 'rod'=>$rod, 'trees'=>$this->getRodsA($uid), 'balance'=>$this->getBalance($uid));
            } else {
                tables_lock('t_users,t_rod');
                $rod_id = $this->createDefRod($uid);
                
                $user = array('uid'=>$uid, 'def_rod'=>$rod_id);
                $query = "INSERT INTO t_users (`uid`, `def_rod`) VALUES ({$uid}, {$rod_id})";
                DB::query($query);
                tables_unlock();
                
                $this->addTransactionA($uid, 103);
                
                if (!$id) $id = $rod_id;
                $rod = $this->getRodHead($id, $uid);
                $res = array('user'=>$user, 'tree'=>$this->getRod($id), 'rod'=>$rod, 'trees'=>$this->getRodsA($uid), 'created'=>1, 'balance'=>$this->getBalance($uid));
            }
            
            $res['prices'] = $this->getPrices();
            $res['wins'] = $this->getWins();
            return $res;
        }
        
        protected function setTopID() {
            $res = array('result'=>0);                           
            if (($rod_id = $this->getVar('rod_id', 0)) && ($uid = $this->getVar('uid', 0)) && ($id = $this->getVar('id', 0))) {
                $res['result'] = DB::query("REPLACE t_rod_state (`rod_id`, `uid`, `start_id`) VALUES ({$rod_id}, {$uid}, {$id})");
            }
            return $res;
        }
        
        protected function removeItem() {
            $res = array('result'=>0);
            if ($id = $this->getVar('id', 0)) {
                $res['result'] = DB::query("DELETE FROM t_peoples WHERE people_id = {$id}");                
            }
            return $res;
        }
        
        protected function addTransactionA($uid, $service, $amount=0, $param_int=0) {
            if ($amount == 0)
                $amount = query_one("SELECT `price` FROM t_wins WHERE id={$service}");
                
            $query = "INSERT INTO t_transaction (uid, amount, service, param_int) VALUES ({$uid}, {$amount}, {$service}, {$param_int})";
            return DB::query($query);
        }
        
        protected function addTransaction() {
            $res = null;
            if (($uid = $this->getVar('uid', 0)) &&
                ($service = $this->getVar('service', 0)) &&
                ($amount = $this->getVar('amount', 0))) {
                $param_int = $this->getVar('param_int', 0);
                
                $res = $this->addTransactionA($uid, $service, $amount, $param_int)?1:0;
                
                if ($extend = $this->getVar('extend', 0)) $this->$extend();
            }
            return $res;
        }
        
        protected function winTook() {
            if (($uid = $this->getVar('uid', 0)) &&
                ($rod_id = $this->getVar('rod_id', 0)) &&
                ($people_id = $this->getVar('people_id', 0))) {
                DB::query("REPLACE t_wins_tree (`uid`, `rod_id`, `people_id`) VALUES ({$uid}, {$rod_id}, {$people_id})");
            }
        }
        
        protected function getPrices() {
            $prices = DB::asArray("SELECT `id`, `aliase`, `desc`, `price` FROM `t_services`");
            $result = array(); 
            foreach ($prices as $pitem) {
                $aliase = $pitem['aliase'];
                unset($pitem['aliase']);
                $result[$aliase] = $pitem;
            }
            return $result;
        } 
        
        protected function getWins() {
            $prices = DB::asArray("SELECT `id`, `aliase`, `desc`, `price` FROM `t_wins`");
            $result = array(); 
            foreach ($prices as $pitem) {
                $aliase = $pitem['aliase'];
                unset($pitem['aliase']);
                $result[$aliase] = $pitem;
            }
            return $result;
        }
        
        protected function getBalance($uid) {
            $balance = query_one("SELECT SUM(amount) FROM `t_transaction` WHERE uid={$uid}");
            return $balance?$balance:0;
        }
        
        protected function getBalanceValue() {
            if ($uid = $this->getVar('uid', 0))
                return array('balance'=>$this->getBalance($uid));
        }
        
        protected function import() {
            GLOBAL $mysqli;
            
            $res = array('rod_id'=>0);
            $rod_id = 0;
            $result = true;
            $nids = array();
            if (($uid = $this->getVar('uid', 0)) && ($data = $this->getVar('data', 0))) {
                $lines = explode("\n", $data);
                $count = count($lines);
                if ($count > 0) {
                    $info = explode(';', $lines[0]);
                    $options = $this->getVar('options', 0);
                    $query = "INSERT INTO t_rod (`uid`, `name`, `options`) VALUES ({$uid}, '{$info[1]}', {$options})";
                    if (DB::query($query)) {
                        $rod_id = DB::lastID();
                        $childs = array();
                        for ($i=1; $i<$count; $i++) {
                            $ldata = explode(';', $mysqli->real_escape_string(trim($lines[$i])));
                            if (count($ldata) > 5) {
                                
                                $bday = date('Y-m-d', strtotime($ldata[4]));
                                
                                $query = 'INSERT INTO t_peoples (rod_id, name, family, father, bday, gender, link_uid, haveAvatar) '.
                                        "VALUES ({$rod_id}, '{$ldata[1]}', '{$ldata[2]}', '{$ldata[3]}', '{$bday}', {$ldata[5]}, {$ldata[8]}, {$ldata[6]})";
                                if ($result = $result && DB::query($query)) {
                                    $itm_id = DB::lastID();
                                    $nids[$ldata[0]] = $itm_id;
                                    $childs[$itm_id] = explode(',', $ldata[9]); 
                                    $result = $result && @copy('http:'.$ldata[7], PEOPLEIMAGEPATH.$itm_id.'.jpg');
                                }
                            }
                        }
                        
                        foreach ($childs as $new_id=>$ichs) {
                            $values = '';
                            foreach ($ichs as $iid) {
                                
                                if ($iid && ($iid > 0)) {
                                    if (isset($nids[$iid]))
                                        $values .= ($values?',':'')."({$new_id}, {$nids[$iid]})";
                                }
                            }
                            if ($values) {
                                $result = $result && DB::query("INSERT INTO t_childs VALUES {$values}");
                            }
                        }
                    }
                    $res['rod_id'] = $result?$rod_id:0;   
                }
            }
            
            return $res;
        }
        
        protected function addJSError() {
            GLOBAL $mysqli;
            $res = 0;
            if (($line = $this->getVar('line', 0)) && 
                ($browser = $this->getVar('browser', '')) &&
                ($uid = $this->getVar('uid', ''))) {
                
                $line = $mysqli->real_escape_string($line); 
                $browser = $mysqli->real_escape_string($browser); 
                $res = DB::query("INSERT INTO t_errors (`uid`, `error`, `browser`) VALUES ({$uid}, '{$line}', '{$browser}')");
            }
            return $res;
        }
        
        protected function aconv($array, $fields, $sourceCharset, $descCharset) {
            foreach ($array as $key=>$item) {
                foreach ($fields as $field)
                    $array[$key][$field] = iconv($sourceCharset, $descCharset, $item[$field]); 
            }
            
            return $array;
        }
        
        protected function rodTreeData($id) {
            $rodHead = DB::line("SELECT * FROM t_rod WHERE rod_id={$id}");
            $rodTree = $this->getRod($id);
            
            return array('head'=>$rodHead, 'tree'=>$rodTree);
        }
        
        protected function getVar($varName, $default) {
            GLOBAL $_POST;
            return isset($this->request[$varName])?$this->request[$varName]:
                (isset($_POST[$varName])?$_POST[$varName]:$default);
        }
        
        public function result() {
            $method = $this->getVar('method', 'getList');
            return $this->$method();
        }     
    }
?>