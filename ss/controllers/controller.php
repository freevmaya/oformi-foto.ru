<?

class controller {
    public $request;
    public $noindex;
    public $headers;

    protected $templatePath;
    protected $title;
    protected $description;
    protected $keywords;
    protected $banners; 
    protected $fileStyle;
    protected $styles;
    protected $scripts;
    protected $groupID;
    protected $meta;      
    protected $og;      
    protected $mobile_pages;
    protected $page_id;
    protected $canonical;
        
    
    function __construct($a_request) {
        $className = get_class($this);
        if (!isset($_SESSION[$className])) $_SESSION[$className] = $this->initSession();
        $this->noindex = false;
        $this->request = $a_request;
        $this->groupID = '50829404668032';
        $this->styles = array();
        $this->scripts = array();     
        $this->mobile_pages = array();            
        $this->checkSocialRef();
        $this->meta = $this->createMeta();  
        $this->headers = array();
        $this->og = $this->createOpenGraph(); 
        $this->page_id = $this->pageID();       
    }
    
    protected function copy($source) {
        $this->noindex  = $source->noindex;
        $this->request  = $source->request;
        $this->groupID  = $source->groupID;
        $this->styles   = $source->styles;
        $this->scripts  = $source->scripts;
        $this->meta     = $source->meta;  
        $this->headers  = $source->headers;  
        $this->og       = $source->og;  
        $this->title        = $source->title;
        $this->description  = $source->description;
        $this->keywords     = $source->keywords;
        $this->banners      = $source->banners; 
        $this->fileStyle    = $source->fileStyle;
    } 
    
    protected function pageID() {
        return $this->aliase();
    }
    
    protected function checkSocialRef() {
        global $_SESSION;
        $user = @$_SESSION[SESUSER];
        if ($user && $user['source'] && ($user['source'] == 'fb')) {
            $_SESSION['social_referer'] = 'facebook';
        } else if ($ref = $_SERVER['HTTP_REFERER']) {
            if (stripos($ref, 'facebook.com')) $_SESSION['social_referer'] = 'facebook';
            else if (stripos($ref, 'vk.com')) $_SESSION['social_referer'] = 'vk';
        }
    } 
    
    public function socialRef() {
        return isset($_SESSION['social_referer'])?$_SESSION['social_referer']:null;
    }
    
    protected function createOpenGraph() {
        GLOBAL  $_SERVER;
        return array(
            'type'=>'website',
            'site_name'=>'Прикольное оформление ваших фотографий',
            'url'=>$this->getLink()
        ); 
    }
    
    public function og() {
        return $this->og;        
    }
    
    protected function user() {
        global $_SESSION;
        return @$_SESSION[SESUSER];    
    }
    
    protected function socialImagePath() {
        $social = $this->socialSource();
        return MAINURL.'/images/'.($social?($social.'/'):'');
    }
    
    protected function socialSource() {
        if ($user = $this->user())
            $social = isset($user['source'])?$user['source']:'';
        else $social = '';
        return $social;     
    }
    
    protected function createMeta() {
        return array('page-image'=>MAINURL.'/images/share/general.jpg', 'url'=>ss::currentURL()); 
    }
    
    public function getMeta($metaName) {
        return $this->meta[$metaName];
    }
    
    public function addMeta($metaName, $value) {
        $this->meta[$metaName] = $value;
    }
    
    public function setPageImage($imageURL) {
        if ($imageURL) $this->addMeta('page-image', $imageURL);
    }
    
    public function meta() {
        return $this->meta;
    }
    
    public function getMobilePages() {
        return array_unique($this->mobile_pages);
    }
    
    function createController($controller) {
        $controllerName = $controller.'Controller';
        $controlPath = CONTROLLERS_PATH.$controllerName.'.php';
        if (!file_exists($controlPath)) {
            $controllerName = 'defaultController';
            $controlPath = CONTROLLERS_PATH.$controllerName.'.php';
        }
        require_once $controlPath;
        return new $controllerName($this->request);
    } 
    
    function redirect($a_controller, $task='display') {
        GLOBAL $controller;
        if (method_exists($a_controller, $task)) {
            $controller = $a_controller;     
            $controller->copy($this);   
            $classNameOrg = get_class($controller);
            $a_controller->templatePath = TEMPLATES_PATH.substr($classNameOrg, 0, strlen($classNameOrg) - 10).DS.$task.'.html';
            $a_controller->$task();
        } else include TEMPLATES_PATH.(ss::$isPhone?'mobile/':'').'default.html';
        return $a_controller;
    }
    
    function stripWords($text, $wordCount) {
        preg_match_all("/([^ \n\t]+)/i", $text, $list);
        $result = '';
        $count = 0;
        foreach ($list[1] as $item) {
            $result .= ($result?' ':'').$item;
            $count++;
            if ($count >= $wordCount) break;
        }
        return $result;        
    }
    
    function getTitle($default='', $limitWords=20) {
        GLOBAL $locale;
        if (!$default) $default = $locale['DEFAULT_TITLE'];
        return $this->title?limitWords(strip_all_tags($this->title), $limitWords):$default;
    }
    
    function getDescription($default='', $limitWords=40) {
        if (!$default) $default = $locale['DEFAULT_DESC'];
        return $this->description?limitWords(strip_all_tags($this->description), $limitWords):$default;
    }
    
    function getKeywords($default='') {
        if (!$default) $default = $locale['DEFAULT_KEYS'];
        return $this->keywords?$this->keywords:$default;
    }
    
    function getFileStyle($default='') {
        return $this->fileStyle?$this->fileStyle:$default;
    }
    
    public function getStyles() {
        return array_unique($this->styles);
    }
    
    public function addScript($scriptUrl, $firts=false) {
        if (!in_array($scriptUrl, $this->scripts)) {
            if ($firts) $this->scripts = array_merge(array($scriptUrl), $this->scripts);
            else $this->scripts[] = $scriptUrl;
        }
    }
    
    public function getScripts() {
        return array_unique($this->scripts);
    }
    
    function getBanners($default) {
        return $this->banners?$this->banners:$default;
    }
    
    public function display() {
        require($this->templatePath);
    } 
    
    protected function initSession() {
        return array();
    }
    
    public function getSession($varName) {
        global $_SESSION;
        $className = get_class($this);
        if (!isset($_SESSION[$className][$varName])) return false;
        else return $_SESSION[$className][$varName];
    }
    
    public function setSession($varName, $value) {
        global $_SESSION;
        $className = get_class($this);
        $_SESSION[$className][$varName] = $value;
    }
    
    public function svar($varName, $default=0) {
        global $_SESSION;
        $className = get_class($this);
        $_SESSION[$className][$varName] = $this->request->getVar($varName, 
                                            isset($_SESSION[$className][$varName])?$_SESSION[$className][$varName]:$default);
        return $_SESSION[$className][$varName];
    }
    
    public function getVar($varName, $default=0) {
        return $this->request->getVar($varName, $default);
    }
    
    public function getSafeVar($varName, $default=0) {
        return $this->escape($this->getVar($varName, $default));
    }
    
    public function escape($value) {
        GLOBAL $mysqli;
        return $mysqli->real_escape_string($value);
    }    
    
    public function html_uidInput($defaultUid = 0) {
        global $_SESSION;
        if (!isset($_SESSION['uids'])) $_SESSION['uids'] = array();
        $uid = $this->svar('uid', $defaultUid);
        $_SESSION['uids'][$uid] = $uid;
        $uids = '<option value="0">---</option>\n';
        foreach ($_SESSION['uids'] as $uid) {
           $uids .= "<option value=\"{$uid}\">{$uid}</option>\n"; 
        }
        return '<input name="uid" value="'.$uid.'" size="20" id="uid">
                <select name="uids" style="width:200px" onchange="document.getElementById(\'uid\').value = this.value;">
                    '.$uids.'
                </select>';
    }
    
    public function cacheKey() {
        GLOBAL $_GET, $_POST;
        $keys = array_merge($_GET, $_POST);
        $skey = MAINURL.get_class($this).ss::lang().(ss::$isPhone?'mobile':'');

        foreach ($keys as $key=>$value)
            $skey .= '&'.$key.'='.$value;

        return md5($skey);
    }
    
    public function isCached() {
        return false;
    }  
    
    public function cacheContent($cacheKey, $contentFunc) {
        $result = null;                      
        if (!$result = ss::getCache($cacheKey)) {
            $result = $contentFunc();
            ss::setCache($cacheKey, $result, $this->cacheExpire());
        } 
        return $result;   
    }   
    
    public function cacheExpire() {
        return 60;        
    }                 
    
    public function getBaseURL() {
        GLOBAL $_SERVER, $sheme;
        return $sheme.$_SERVER['SERVER_NAME'];
    }                  
    
    public function getLink() {
        GLOBAL $_SERVER;
        return $this->getBaseURL().$_SERVER['REQUEST_URI'];
    }
    
    protected function aliase() {
        return 'controller';
    }
    
    public function isDev() {
        return $this->getVar('dev', false); 
    }
    
    public function target() {
        GLOBAL $_GET;
        return isset($_GET['target'])?$_GET['target']:''; 
    }
    
    public static function translit($string, $revers=false) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'yo',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'j',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'\'',
            'э' => 'e\'',   'ю' => 'yu',  'я' => 'ya',
            
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'Yo',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'J',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '-',  'Ы' => 'Y',   'Ъ' => '-',
            'Э' => 'E\'',   'Ю' => 'Yu',  'Я' => 'Ya'
        );
        
        $string = strtr($string, $revers?array_flip($converter):$converter);
        if (!$revers)
            $string = preg_replace('~[^-A-z0-9\'_\+]+~u', '', $string);
        return $string;
    }

    protected function getSenderUser() {
        if ($user = $this->getVar('user', false)) 
            return json_decode($user, true);
        
        return ss::getUserAlternate(); 
    }

    public function pushAll($user) {
        GLOBAL $_SERVER, $_SESSION;
        
        $res = array();
        if (!$user && !($user = ss::getUser())) $user = array('uid'=>0, 'source'=>'none', 'nauid'=>isset($_SESSION['NAUID'])?$_SESSION['NAUID']:0);
        $nauid = $user['nauid'];
        
        if ($nauid || $user['uid']) {
            if ($id = $this->getSafeVar('pushalluserid', false)) {
                $ip = $_SERVER['REMOTE_ADDR'];
                $time = $this->getVar('time', time());
                $calcSign = md5(PUSHALLKEY.$id.$time.$ip);
                
                if (($calcSign == $this->getVar('sign')) && (time() - 10 < $time)) {
                    $res = array();
                    $query = "REPLACE ".DBPREF."subs_pushall (subs_id, nauid, uid, source) VALUES ($id, {$nauid}, {$user['uid']}, '{$user['source']}')";
                    $res['add_status'] = DB::query($query);
                }
            }
            
            if ($nauid) $where = "nauid=$nauid";
            else $where = "uid={$user['uid']} AND source='{$user['source']}'";
            
            $res['state'] = DB::line("SELECT * FROM ".DBPREF."subs_pushall WHERE $where");
        }        
        return $res;
    }
    
    public function service() {
        require(TEMPLATES_PATH.'service.html');
    }                        
    
    public function capbot() {
        require(TEMPLATES_PATH.'capbot.html');
    }

    public static function mobileToBase() {
        if (ss::$isPhone) {
            header("Location: ".MAINURL);
            die();
        }
    }
}

?>
