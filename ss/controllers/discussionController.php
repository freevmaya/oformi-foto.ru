<?

GLOBAL $dbname;

include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(SSPATH.'helpers/OKServer.php');

define('SOURCE_OK', 'ok');
define('SOURCE_MM', 'mm');
define('SOURCE_VK', 'vk');
define('OKAPIKEY', 'CBAODNHIABABABABA');
define('NOTICESFROMPAGE', 100);
define('DEFAULTUSERIMAGE', MAINURL.'/images/pj60x60.png');
define('SUBJECTTYPE_IMAGE', 1);
define('CACHEEXPIRED', 15 * 60); // 15 минут
define('USEREXPIRE', 60 * 60 * 24 * 30); // месяц
define('NOTICETIMEINTERVAL', 30);
define('NTI_KEY', 'nti_key');

$SOURCES = array(1=>'ok', 2=>'mm', 3=>'vk');
$SEXTOGENTER = array(0=>'male', 1=>'female');

ini_set("memory_limit", "128M");

class discussionController extends controller {
    private $content_image_url;
    private $vmayaModel;
    function __construct($a_request) {
        parent::__construct($a_request);
        
        //$this->disDBConnect();
        $this->vmayaModel = 'discus_model';
        if ($this->request->getVar('target', false)) $this->vmayaModel = 'discus_dev_model';
    }
    
    public function getMeta($metaName) {
        return (($metaName == 'page-image') && $this->content_image_url)?$this->content_image_url:$this->meta[$metaName];
    }
    
/*    
    private function disDBConnect() {
        GLOBAL $dbname;
        $dbname = '_discussion';
        connect_mysql();
    }
*/    
    function view() {
        $param = ss::$task[2];
        if (is_numeric($param))
            $this->okcollage($param);
        else $this->$param();
//        require(TEMPLATES_PATH.'viewDiscus.html');
    }
    
    protected function fconv(&$item, $fields) {
        foreach ($fields as $field) $item->$field = mb_convert_encoding($item->$field, 'CP1251', 'UTF-8');
    }
    
    protected function naconv(&$item, $fields) {
        foreach ($fields as $field) $item[$field] = mb_convert_encoding($item[$field], 'CP1251', 'UTF-8');
    }
    
    protected function aconv(&$array, $fields) {
        $count = count($array);
        for ($i=0;$i<$count;$i++) $this->naconv($array[$i], $fields);
    }
      
    protected function getVmayaData($url) {
        return file_get_contents($url.'&format=asis');
    }
    
    protected function getVmayaCollage($id) {  
        $cache = $this->data_getCache($id);
        if ($cache) {
            $result = $cache['value'];
        } else { 
            $result = $this->getVmayaData('https://oformi-foto.ru/games/data/?model='.$this->vmayaModel.'&method=getContent&id='.$id);
            $this->data_setCache($id, $result, CACHEEXPIRED);
        }
//        $result = $this->getVmayaData('http://oformi-foto.ru/games/data/?model=discus_model&method=getContent&id='.$id);
        return json_decode($result, true);
    }
    
    protected function OKApiCall($method, $params) {
        $user = ss::getUser();
        $result = null;
        if ($user && ($user['source'] == 'ok')) {
            if ($session = $user['session']) {
                $session = (is_string($session))?json_decode($session):$session;
                $result = OKServer::request(OKAPIKEY, $method, $params, $session->token);
                ss::trace(OKAPIKEY.' '.$method);
                ss::trace($params);             
                ss::trace($result);
            }
        }
        return $result;
    }
    
    private function data_getSubject($source_id, $source) {
        return DB::line("SELECT * FROM ds_subject WHERE source_id=$source_id AND source='$source'");
    }
    
    private function data_getDisputeFromSubject($source_id, $source) {
        $query = "SELECT * FROM ds_subject s INNER JOIN ds_dispute d ON s.dispute_id=d.id WHERE s.source_id=$source_id AND s.source='$source'";
        return DB::line($query);
    } 
    
    private function data_addDisputeFromSubject($uid, $title, $desc, $source_id, $source) {
        $query = "INSERT INTO ds_dispute (`uid`, `userSource`, `title`, `desc`, `modifyTime`, `createDate`) VALUES ($uid, '$source', '$title', '$desc', NOW(), NOW())";
        DB::query($query);
        $disputeID = DB::lastID();
        
        $query = "INSERT INTO ds_subject (source_id, source, type, dispute_id, url) VALUES ($source_id, '$source', ".SUBJECTTYPE_IMAGE.", $disputeID, '".$this->collageURL($source_id)."')"; 
        DB::query($query);
    }
    
    private function data_getNotices($disputeId, $source_id, $startLimit) {                                  
        $query = "SELECT *, DATE_FORMAT(`time`, '%d.%m.%Y %H:%i:%s') AS `time` FROM (SELECT * FROM ds_notices n ".
                "WHERE n.dispute_id=$disputeId) n ORDER BY n.`id` DESC LIMIT $startLimit, ".NOTICESFROMPAGE;
        return DB::asArray($query);
    }
    
    private function data_addNotices($disputeId, $user, $message) {
        GLOBAL $mysqli, $_SESSION;
        
        if (!isset($user['uid'])) return 0;
        
        $cur_time = time();
        if ($_SESSION[NTI_KEY] && ($_SESSION[NTI_KEY] > $cur_time)) {
            return 0;
        } 
        $_SESSION[NTI_KEY] = $cur_time + NOTICETIMEINTERVAL;
        if ($message = mysqli_real_escape_string($mysqli, strip_tags(trim($message)))) { 
            $uid = 0;
            $userSource = 0;
            if ($user) {
                $uid = $user['uid'];
                $userSource = $user['source'];
                $this->data_replaceUser($user, $userSource);
            }
            $query = "INSERT INTO ds_notices (`dispute_id`, `userSource`, `uid`, `text`) VALUES ($disputeId, '$userSource', $uid, '$message')";
            return DB::query($query);
        } else return 0;
    }
    
    public function getUserName($user) {
        $result = '';
        if ($user) {
            if (isset($user['name'])) $result = $user['name'];
            else if (isset($user['nick'])) $result = $user['nick'];
            else if (isset($user['screen_name'])) $result = $user['screen_name']; 
            else {
                if (isset($user['first_name'])) $result += $user['first_name'];
                if (isset($user['last_name'])) $result += ' ' + $user['last_name'];
            }
        } 
//        if (!$result) $result = 'Аноним';
        return $result;
    }
    
    public function getPic($user) {
        if ($user) {
            if (isset($user['pic'])) return $user['pic'];
            else if (isset($user['pic_2'])) return $user['pic_2'];
            else if (isset($user['pic_small'])) return $user['pic_small'];
            else if (isset($user['photo_100'])) return $user['photo_100'];
            else if (isset($user['pic128x128'])) return $user['pic128x128'];
            else return '';
        }
        return '';
    }
    
    public function getPicBig($user) {
        if ($user) {
            if (isset($user['pic_4'])) return $user['pic_4'];
            else if (isset($user['photo_max_orig'])) return $user['photo_max_orig'];
            else return '';
        }
        return '';
    }
    
    private function data_getCache($content_id) {
        $query = "SELECT * FROM ds_content_cache WHERE content_id = {$content_id}";
        $cache = DB::line($query);
        if (!$cache || ($cache['expired'] < time())) return null;
        return $cache;
    }
    
    private function data_setCache($content_id, $value, $longExpireSec=10) {
        $time = time() + $longExpireSec;
        //$value = mysql_real_escape_string($value);
        return DB::query("REPLACE ds_content_cache VALUES ($content_id, $time, :value)", [
        	'value'=>$value
        ]);
    }
    
    private function data_replaceUser($user, $source) {
        GLOBAL $SEXTOGENTER;
        if (isset($user['uid'])) {
            $curDate = date('Y-m-d H:i:s');
            if (!isset($user['gender'])) {
                $user['gender'] = 'other';
                if (isset($user['sex'])) {
                   $user['gender'] = $SEXTOGENTER[$user['sex']]; 
                }
            }
            
            if (!isset($user['name'])) {
                if (isset($user['screen_name'])) {
                   $user['name'] = $user['screen_name']; 
                }
            }
            
            if (!isset($user['birthday'])) {
                if (isset($user['bdate'])) $user['birthday'] = $user['bdate'];
            }
            
            $user['birthday']   = date('Y-m-d', strtotime($user['birthday']));
            $user['pic']        = $this->getPic($user);
            $user['pic_big']    = $this->getPicBig($user);
            $user['gender']     = $user['gender'] ? $user['gender'] : 'none';
            $expire = time() + USEREXPIRE;
            //trace($user);
            $query = "REPLACE ds_users VALUE ({$user['uid']}, {$expire}, '{$source}', '{$user['name']}', ".
                    "'{$user['first_name']}', '{$user['last_name']}', '{$curDate}', '{$user['pic']}', ".
                    "'{$user['pic_big']}', '{$user['gender']}', '{$user['birthday']}', '{$user['url']}')";
                    
            //ss::trace($query); 
            return DB::query($query);
        } else return false;
    }
    
    private function data_getUser($uid, $source) {
        $time = time();
        //ss::trace($time);
        if ($user = DB::line("SELECT * FROM ds_users WHERE (uid={$uid} AND userSource='$source')"))
            $user['refresh'] = $user['expire'] > $time; 
         
        return $user;            
    }
    
    protected function collageURL($collage_id) {
        GLOBAL $sheme;
        return $sheme."oformi-foto.ru/games/data/clothing/game/{$collage_id}.jpg";
    }
    
    protected function collagePreview($collage_id) {
        GLOBAL $sheme;
        return $sheme."oformi-foto.ru/games/data/clothing/game/preview/{$collage_id}.jpg";
    }
    
    protected function collageLink($collage) {
        return link::c('discussion', $collage['id']);
    }
    
    protected function getOkUser($uid) {
        $users = $this->getOkUsers($uid);
        return ($users && (count($users) > 0))?$users[0]:null;
    }
        
    protected function getOkUsers($uids) {
        //ss::trace($uids);
        $params = array('uids'=>$uids, 'fields'=>'uid,name,first_name,last_name,gender,birthday,pic128x128,pic640x480,url_profile');
        $users = $this->OKApiCall('users/getInfo', $params);
        if ($users && (count($users) > 0)) {
            for ($i=0; $i<count($users); $i++) if ($users[$i]) {
//                $this->naconv($users[$i], array('name', 'first_name', 'last_name'));
                $users[$i]['pic']       = $users[$i]['pic128x128']; 
                $users[$i]['pic_big']   = $users[$i]['pic640x480'];
                $users[$i]['url']       = $users[$i]['url_profile'];
                
                //ss::trace($users);
                $this->data_replaceUser($users[$i], SOURCE_OK);
            }
        }
        
        return $users;
    }
    
    protected function assignUserInfo(&$list, &$infos) {
        $idxs = array();            
        foreach ($infos as $i=>$info) $idxs[$info['uid']] = $i;
                             
        foreach ($list as $key=>$item) { 
            if (isset($idxs[$item['uid']])) {
                $list[$key]['user'] = $infos[$idxs[$item['uid']]];
            }                        
        }                        
        
        return $list;
    }
    
    protected function api_UsersInfo_ok(&$list, $checkSource=true) {
        $uids = array();
        foreach ($list as $item)
            if ((!isset($item['user']) || (!$item['user']['pic'])/* || (!@$item['user']['refresh'])*/) && 
                (!$checkSource || ($item['userSource'] == SOURCE_OK)) && 
                (!in_array($item['uid'], $uids))) {
                $uids[] = $item['uid'];
            }
            
        if (count($uids) > 0) {
            if ($infos = $this->getOkUsers(implode(',', $uids))) {
                foreach ($list as $key=>$item)
                    foreach ($infos as $info) {
                        if ($item['uid'] == $info['uid']) {
                            $list[$key]['user'] = $info;
                        }
                    }
            }
        }
        
        return $list;
    }
    
    protected function getUsersInfo_db(&$list, $defUserSource='') {
        $where = '';
        foreach ($list as $item) {
            $userSource = isset($item['userSource'])?$item['userSource']:$defUserSource; 
            if ($item['uid'])
                $where .= ($where?' OR ':'')."(u.uid={$item['uid']} AND u.userSource='{$userSource}')";
        }
               
        $time = time();
        $query = "SELECT * FROM ds_users u WHERE ($where)"; // Получаем всех пользователей из базы для кого время кеша еще не окончено
        if ($where) {
            if ($users = DB::asArray($query)) {
                foreach ($users as $i=>$user) {
                    if ($user['userSource'] != SOURCE_OK) {
                        $users[$i]['pic'] = AVAURL.$user['userSource'].'/'.$user['uid'];
                    }
                    $users[$i]['refresh'] = $user['expire'] > $time;
                }
                $list = $this->assignUserInfo($list, $users);
            }
        }
        
        return $list;
    }
    
    public function getUsersInfo(&$users, $defUserSource='') {
//        return $this->api_UsersInfo_ok($users);
        $this->getUsersInfo_db($users, $defUserSource);
        $this->api_UsersInfo_ok($users, $defUserSource != SOURCE_OK);
        
        return $users;
    }
    
    protected function userName($user) {
        if ($user)
            return $user['first_name'].' '.$user['last_name'];
        else return '';
    }
    
    protected function getUser($uid, $source='ok') {
        if (!($user = $this->data_getUser($uid, $source))/* || (@$user['refresh'])*/) {
            if ($source == 'ok') $user = $this->getOkUser($uid, $source);
        }
        return $user;
    }
    
    protected function okcollage($collage_id) {
        GLOBAL $_SESSION;
        
        $sendMesage = $this->getVar('message');
        $userSource = SOURCE_OK; 
        
        $this->noindex = true;
        ss::$noadv = true;
        
        if ($sendMesage) {
            $user = json_decode(urldecode($this->getVar('user')), true);
            $result = $this->data_addNotices($this->getVar('id'), $user, $sendMesage);
            $ckey = $this->createCacheKey($collage_id);
            $this->clearCache($ckey);
            echo '{"result": '.($result ? 1 : 0).', "time": "'.date('d.m.Y H:i:s').'", "ckey": "'.$ckey.'"}';
        } else {
            if ($collage = $this->getVmayaCollage($collage_id)) {
                $disput = $this->data_getDisputeFromSubject($collage_id, $userSource);            
                if (!$disput) {
                    if ($collage) {
                        $autor = $this->getUser($collage['uid']);
                        $this->data_addDisputeFromSubject($collage['uid'], $collage['name'], '', $collage_id, $userSource);
                        $disput = $this->data_getDisputeFromSubject($collage_id, $userSource);
                    } else exit;
                } else {
                    $autor = $this->getUser($disput['uid'], $userSource);
                }
                
                $disput['url'] = str_replace('vmaya.ru', 'oformi-foto.ru', $disput['url']);
                
                $this->content_image_url = $disput['url'];
                //$this->data_replaceUser($autor, $userSource);
                
                $disput = array_merge($disput, $collage);
                $notices = $this->data_getNotices($collage_id, $userSource, 0);
                $this->getUsersInfo($collage['concuren'], $userSource);
                $this->getUsersInfo($notices);
                $this->title = 'Фото-коллаж: '.($collage['name']?$collage['name']:' пользователя '.$this->userName($autor));
                $this->description = 'Коллаж, фоторамка: '.($collage['name']?($collage['name'].', автор: '):' пользователя соц-сети одноклассники.ru ').$this->userName($autor);
                
//                $this->api_UsersInfo_ok($notices);
                require(TEMPLATES_PATH.'discussion/viewDispute.html');
            } else require(TEMPLATES_PATH.'discussion/nofoundDispute.html');
        }
    }
                    
    function to_link($string) {
        return preg_replace("~(http|https|ftp|ftps)://(.*?)(\s|\n|[,.?!](\s|\n)|$)~", '<a href="$1://$2">$1://$2</a>$3', $string);
    }    
    
    protected function leaders() {
        GLOBAL $locale;
        $this->title = $locale['DISC-LEADERS'];
        $this->description = 'Список лидирующих коллажей в конкурсе Лучший коллаж дня, созданных пользователями соц-сети Одноклассники.ru'; 
        $this->addMeta('page-image', MAINURL.'/images/promo/anim01.jpg');
        $this->getGameList('Leaders');
    }
    
    protected function winners() {
        GLOBAL $locale;
        $this->title = $locale['DISC-WINNERS'];
        $this->description = 'Список коллажей-победителей в конкурсе Лучший коллаж дня, за все время существования конкурса в соц-сети Одноклассники.ru'; 
        $this->addMeta('page-image', MAINURL.'/images/promo/anim01.jpg');
        $this->getGameList('Winners');
    }
    
    protected function bests() {
        GLOBAL $locale;
        $this->title = $locale['DISC-SUCCESSFUL'];
        $this->description = 'Список наиболее удачных коллажей по мнению администрации сайта в конкурсе на Лучший коллаж дня'; 
        $this->addMeta('page-image', MAINURL.'/images/promo/anim01.jpg');
        $this->getGameList('Bests');
    }
    
    public function getGameListObject($method, $page=1, $limit=20) {
    
        $uid = ss::getUserID();
        $url = 'https://oformi-foto.ru/games/data/?model='.$this->vmayaModel.'&method=get'.$method.'&uid='.$uid.'&page='.max(0, $page - 1).'&limit='.$limit;
        
        //echo $url;
        $result = json_decode($this->getVmayaData($url), true);
        
        if (isset($result['list'])) { 
            $this->getUsersInfo($result['list'], SOURCE_OK);
        }
        return $result;
    }
    
    protected function getGameList($method) {
        $limit = 20;
        $page = isset(ss::$task[3])?ss::$task[3]:1;
        $result = $this->getGameListObject($method, $page, $limit);
            
        $totalPages = isset($result['count'])?ceil($result['count']/$limit):0; 
    
        if ($contentType == 'json')
            $templatePath = MOBILETEMPLATEPATH.'catalog_json.html';
        else $templatePath =  ss::$isPhone?(MOBILETEMPLATEPATH.'discussion.html'):TEMPLATES_PATH.'discussion/discusGameList.html';
        require($templatePath);        
    }
    
    protected function deleteNotice() {
        $ids = explode('_', $this->getVar('id', 0));
        $ckey = $this->createCacheKey($ids[0]);
        $this->clearCache($ckey);
        $query = "DELETE FROM ds_notices WHERE id=".$ids[1];
        echo '{"result": '.(DB::query($query) ? 1 : 0).'}';
    } 
    
    protected function clearCache($cacheKey) {
        ss::clearCache($cacheKey);
        $query = "REPLACE _cache_list VALUES ('{$cacheKey}', 0)";
        DB::query($query);
    }
    
    protected function collageStartTime($item) {
        $st = strtotime($item['time']);                  
        if ($st >= strtotime('-1 DAY')) $format = 'd.m H:i:s';
        else $format = 'd.m.y'; 
        return date($format, $st);
    }
    
    protected function collageInfo($item) {
        $createTimeMLS = strtotime($item['time']);
        $endTime = date('d.m.Y H:i:s', strtotime("+1 day", $createTimeMLS));
        
        $countSec = strtotime($endTime) - strtotime('NOW');
        $collageInfo = "Время окончания:<br><b>$endTime</b>";
        if ($countSec >= 0) {
            $min = floor($countSec / 60);
            $hour = floor($min / 60);
            $dmin =  $min % 60;
            $collageInfo .= "<br>До окончания конкурса осталось:<br><b>$hour ч. $dmin мин.</b>";
        } else $collageInfo = '<br>Конкурс окончен.';
        
        return $collageInfo.'<br>Текущее серверное время:<br><b>'.date("d.m.Y H:i:s").'</b>';    
    }
    
    protected function createCacheKey($content_id) {
        if (is_numeric($content_id))
            return md5('dis_'.MAINURL.'_'.$content_id.'_'.ss::lang());
        else return ss::cacheKeyDefault(); 
    }
    
    public function cacheKey() {
        return $this->createCacheKey(ss::$task[2]);
    }
    
    public function isCached() {
        GLOBAL $contentType;
        return ($contentType == 'html') && ($this->getVar('type', '') != 'json');// && !ss::$isAdmin;// && (!$this->getVar('target', false));
    }   
    
    public function cacheExpire() {
        return CACHEEXPIRED * ((ss::getTask(2)=='bests')?20:2);        
    }
}
?>    