<?

include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(CONTROLLERS_PATH.'controller.php');
include_once(INCLUDE_PATH.'/Memcache.php');
include_once(INCLUDE_PATH.'/mobile_detect/Mobile_Detect.php');
include_once(HOMEPATH.'/domains.inc');

include_once(SSPATH.'ssconfig.php');
include_once(SSPATH.'ssutils.php'); 
include_once(SSPATH.'helpers/Event.php');
include_once(SSPATH.'helpers/link.php');

define('DEFAULTTEMPLATE', 'index.html');
define('MOBILETEMPLATEPATH', TEMPLATES_PATH.'mobile/');
define('FIRSTPNGID', 24286);
define('SESUSER', 'of-user');
define('DBPREF', 'of_');

$root = null;
$ver = isset($_GET['ver'])?$_GET['ver']:VER;
$target = null;
$locale = array();
$controller = null;
$menuList=array();

$finders = array('yandex.ru'=>'/text=([^&0-9]+)/i',
                'www.yandex.ru'=>'/text=([^&0-9]+)/i',  
                'google.ru'=>'/q=([^&]+)/i',  
                'www.google.ru'=>'/q=([^&]+)/i', 
                'nova.rambler.ru'=>'/query=([^&]+)/i', 
                'www.nova.rambler.ru'=>'/query=([^&]+)/i',  
                'go.mail.ru'=>'/q=([^&]+)/i', 
                'www.go.mail.ru'=>'/q=([^&]+)/i',  
                'bing.com'=>'/q=([^&]+)/i', 
                'www.bing.com'=>'/q=([^&]+)/i',  
                'ask.com'=>'/q=([^&]+)/i',  
                'www.ask.com'=>'/q=([^&]+)/i',
                'vmaya.ru'=>'/search=([^&]+)/i');                

function rusDate($mysqlDate) {
    GLOBAL $locale;
    $time = strtotime($mysqlDate);
    if (date('d.m.Y') == date('d.m.Y', $time))
        return "<b>{$locale['TODAY']}</b>";
    else {
        if (date('Y') == date('Y', $time))
            return date('<b>d.m</b> H:i', $time);
        else return date('<b>d.m.Y</b> H:i', $time);
    }
}

class ss extends controller {
    protected $user;
    protected $template;        
    static public $task;
    static public $controller_name;
    static public $isAdmin;
    static public $noadv;
    static public $nomenu;
    static public $nofooter;
    static public $socialfooter;
    static public $shared;
    static public $isPhone;
    static public $trace;
    static protected $css;
    
    function __construct($a_request, $template=DEFAULTTEMPLATE) {
        GLOBAL $_SERVER, $_SESSION, $_GET, $root, $locale, $LANGINSTALL;
        
        $root = $this;
        link::init($a_request);
        
        parent::__construct($a_request);
        //Устанавливаем язык по названию субдомена
        if ($languri = ss::getLangSubdomain()) {
            $this->request->values['lang'] = $languri;
            $this->setSession('lang', $languri);
        } else if ($reqstr = $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
             
            $reqlan = preg_split('/,/', $reqstr);
            $rlangs = array();
            foreach ($reqlan as $rlan) {
                $rlan = explode(';', $rlan);
                if (isset($rlan[1])) {
                    preg_match ('/([\d\.]+)/', $rlan[1], $out);
                    $priory = isset($out[0])?$out[0]:1; 
                } else $priory = 1;
                $rlangs[] = array($rlan[0], $priory);
            } 
            
            function smd_lang($t1, $t2) {
                return ($t1[1] < $t2[1])?1:-1;
            }
            
            usort($rlangs, 'smd_lang');
            $reqlan = $rlangs[0][0];
            
            if (in_array($reqlan, $LANGINSTALL)) {
                $this->request->values['lang'] = $reqlan;
                $this->setSession('lang', $reqlan);
            }
        }
        
        include_once(SSPATH.'language/'.ss::lang().'.php');
        
        $agent = $_SERVER['HTTP_USER_AGENT'];
        
        ss::$noadv = false;
        ss::$nomenu = false;
        ss::$shared = 1;
        ss::$socialfooter = $template == DEFAULTTEMPLATE;
        ss::$isPhone = $this->checkPhone(); 
        ss::$css = array();
        ss::$trace = array();
        $this->template = $template;
        if (isset($_SESSION[SESUSER])) $this->setUser($_SESSION[SESUSER]);
        
        if (COLLECT_QUESTION) $this->collectQuestion();
    }
    
    public static function getLangSubdomain() {
        GLOBAL $_SERVER, $LANGINSTALL;
        $uria = explode('.', $_SERVER['HTTP_HOST']);
        if (in_array($uria[0], $LANGINSTALL)) return $uria[0];
        else return DEFAULT_LANG;
    }
    
    public static function isDefaultLang() {
        return ss::lang() == DEFAULT_LANG;
    }
    
/*    
    protected function checkPhone() {
        GLOBAL $_SERVER; 
        //http://detectmobilebrowsers.com/
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
    }
*/

    public static function addCSS($class, $cssText) {
        ss::$css[$class] = $cssText;
    }
    
    public static function getTask($index, $default=false) {
        return isset(ss::$task[$index])?ss::$task[$index]:$default;
    }
    
    public static function getAllCSS() {
        $result = '';
        foreach (ss::$css as $class=>$css) {
            $result .=  $class.' {'.$css."}\n";
        }
        
        if ($result) $result = "<style type=\"text/css\">$result</style>";
        return $result;
    }
    
    public static function injectLang($file) {
        $filePath = SSPATH.'language/'.ss::lang().'/'.$file;
        if (file_exists($filePath)) include($filePath);
        else echo "<div>file $file no found, for <b>".ss::lang()."</b> language</div>";
    } 
    
    protected function checkPhone() {
        if ($this->getVar('mobile', false)) $this->setSession('mobile', true);
        if ($this->getSession('mobile')) return true;
        
        $detect = new Mobile_Detect();
        return $detect->isMobile();
    }
    
    public static function relReferer() {
        GLOBAL $domains, $_SERVER;
        
        function checkDomain($domain) {
            GLOBAL $domains;
            foreach ($domains as $cdom) {
                if (preg_match("/$cdom/", $domain) == 1) return true;
            }
            return false;
        }
           
        if ($ref = @$_SERVER['HTTP_REFERER']) {
            $result = explode('/', $ref);
            if (checkDomain($result[2]) !== false) return true;
        };
        //echo "alert('Domain {$result[2]} is not allowed');";
        return false;
    }    
    
    protected function collectQuestion() {
        GLOBAL $_SERVER, $finders, $mysqli;
        
        $query = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($query['host']) && isset($query['query'])) {
            if (isset($finders[$query['host']])) $pattern = $finders[$query['host']];
            else $pattern = '/q=([^&]+)/i';
            
            preg_match_all($pattern, $query['query'], $list);
            if (isset($list[1])) {
                if ($word = mysqli_escape_string($mysqli, trim(urldecode($list[1][0])))) {
                    echo('<div style="display:none;">'.trim(urldecode($list[1][0])).'</div>'); 
                    if (mb_strlen($word, 'UTF-8') > 8) { 
                        DB::query("REPLACE `search_questions` VALUES ('{$query['host']}', '{$word}', `count` + 1, 1)");
                    }
                }
            }
        }
    }
    
    public static function lang() {
        GLOBAL $_SESSION, $root, $LANGINSTALL;
        
        if (!($rlang = $root->svar('lang', false))) $rlang = DEFAULT_LANG;

        //if (!in_array($rlang, $LANGINSTALL)) $rlang = DEFAULT_LANG;
        
        return $rlang;
        //return isset($_SESSION['lang'])?$_SESSION['lang']:DEFAULT_LANG;
    }
    
    protected function setUser($a_user) {
        $this->user = $a_user;
        ss::$isAdmin = $this->checkIsAdmin();
    }
    
    public static function getUser() {
        GLOBAL $root;
        return $root->user;
    }
    
    public static function anonymUser() {
        $nauid = ss::nauid();
        return array(
            'source'=>'none',
            'uid'=>$nauid,
            'nauid'=>$nauid
        );
    }
    
    public static function getUserAlternate() {
        GLOBAL $root;
        if (!($user = $root->user))
            $user = ss::anonymUser();
        return $user;
    }
    
    public static function itsMe($a_user) {
        GLOBAL $_SESSION;
        if ($a_user && ($a_user['source'] != 'none') && ($my = ss::getUser())) return ($my['uid'] == $a_user['uid']) && ($my['source'] == $a_user['source']);
        else return $_SESSION['NAUID'] == $a_user['nauid'];        
    }
    
    public static function nauid() {
        GLOBAL $root, $_SERVER, $_SESSION;
        $nauid = 0;
        $user = $root->user;
        if ($user && $user['nauid']) $nauid = $user['nauid'];
        else $nauid = (isset($_SESSION['NAUID']) && $_SESSION['NAUID'])?$_SESSION['NAUID']:(DB::one('SELECT uid FROM '.DBPREF."nauids WHERE ip='{$_SERVER['REMOTE_ADDR']}'"));
        return $nauid;
    }
    
    public static function getUserID() {
        GLOBAL $root;
        if ($root->user) return $root->user['uid'];
        else return 0;  
    }
    
    public static function userName($user=null) {
        GLOBAL $locale, $root, $result;
        if (!$user) $user = $root->user;
        
        $result = '';
        if (is_numeric($user)) $result = $user;
        else if ($user['source'] == 'none') {
            if ($user['uid']) $result = $locale['ANONYM'].' '.$user['uid'];
            else $result = $locale['ANONYM'];
        } else if ($user['first_name'] || $user['last_name']) $result = $user['first_name'].' '.$user['last_name'];
        else $result = $user['nick']?$user['nick']:$locale['ANONYM'];
        return $result;
    }    
    
    public function getSocialSource() {
        return isset($this->user['source'])?$this->user['source']:'';
    } 
    
    public function uid() {
        return isset($this->user['uid'])?$this->user['uid']:0;
    }
    
    public static function avatar($user) {
        $rel = $user['source'].'/'.(($user['uid'] && ($user['source'] != 'none'))?$user['uid']:'default');
        $img_url = AVAURL.$rel;
        $img_path = AVAPATH.$rel;
        if (!file_exists($img_path)) {
            copy(AVAPATH.'default', $img_path);
            chmod($img_path, 0744);
        }
        if (isset($user['last_time'])) $img_url .= '?v='.strtotime($user['last_time']);
        return $img_url;
    }
    
    public static function userLink($user) {
        if (!$user['source'] || ($user['source'] == 'none')) {
            return link::c('user', 'page', 'none-'.($user['uid']?$user['uid']:'0'));
        }
        return link::c('user', 'page', $user['source'].'-'.$user['uid']);
    }
    
    public static function extUserLink ($user) {
        GLOBAL $sheme;
        if ($user['url']) return $user['url'];
        else if ($user['source'] == 'ok') return $sheme.'ok.ru/profile/'.$user['uid'];
        else if ($user['source'] == 'vk') return $sheme.'vk.com/id'.$user['uid'];
        return null;
    }
    
    public function checkIsAdmin() {
        GLOBAL $ADMINS;
        $result = false; 
        if ($user = $this->getUser()) {
            foreach ($ADMINS as $admin) 
                if (($admin['source'] == $user['source']) && ($admin['uid'] == $user['uid'])) return true;            
        }
        return false;//array_search($this->uid(), array('8062938299454250872', '1731353195984349210', '12700092426321616713', '14095186048222604222')) !== false;
    }
    
    public static function getInstance() {
        GLOBAL $root;
        return $root;
    }
    
    public static function getTaskLink() {
        GLOBAL $_GET;             
        $result = '?task='.ss::$task[0].','.ss::$task[1];//.($_GET['target']?('&target='.$_GET['target']):'').($_GET['dev']?('&dev='.$_GET['dev']):'');
        if (isset($_GET['target'])) $result .= "&target={$_GET['target']}"; 
        return $result;
    }
    
    public static function getCurrentProtocol() {
        GLOBAL $_SERVER;
        return isset($_SERVER['HTTP_HTTPS'])?'https://':'http://';
    }
    
    public static function getCurrentHost() {
//        GLOBAL $_SERVER;
        return MAINURL;
    }
    
    public static function getImages() {
        return ss::getCurrentHost().'/images/';
    }
    
    public static function getCurrentParams() {
        GLOBAL $root;
        $values = $root->request->values;
        $result = '';
        foreach ($values as $key=>$value)
            $result .= ($result?'&':'').$key.'='.$value;
        return ($result?'?':'').$result;        
    }
    
    public static function isCurrentTask($task) {
        $atask = explode(',', $task);
        $check = true;
        foreach (ss::$task as $key=>$elem)
            $check = $check && (isset($atask[$key]) && (ss::$task[$key] == $atask[$key]));
        return $check;
    }
    
    public static function setTemplate($templateName) {
        GLOBAL $root;
        $root->template = $templateName;
    }
    
    public static function cacheKeyDefault() {
        GLOBAL $_SERVER, $root;
        $skey = MAINURL;//isset($_SERVER['HTTP_HTTPS'])?'https':'';
        foreach (ss::$task as $task) $skey .= ($skey?'&':'').$task;
        $skey .= '&'.(ss::$isPhone).'&'.VER.'&'.ss::lang();
        if ($target = $root->request->getVar('target', false)) $skey .= '&'.$target;
        //echo 'CACHEKEY: '.$skey;
        return md5($skey); 
    }
    
    public static function getCache($cacheKey, $defValue=null) {
        $val = MCache::get($cacheKey);
        //echo $cacheKey.'='.strval($val)."\n"; 
        return ($val===false)?$defValue:$val;
    } 
    
    public static function setCache($cacheKey, $value, $expire) {
        MCache::set($cacheKey, $value, $expire);
        /*
        $time = time() + $expire;
        $query = "REPLACE _cache_list VALUES ('{$cacheKey}', {$time})";
        DB::query($query);
        */
    }
    
    public static function clearCache($cacheKey) {
        return MCache::delete($cacheKey);
    } 
    
    public static function isHavePNG($tmplId) {
        return $tmplId >= FIRSTPNGID;
    } 
    
    public static function currentURL() {
        GLOBAl $_SERVER;
        return MAINURL.$_SERVER['REQUEST_URI'];
    }
/*    
    public static function isSEOLink() {
        GLOBAL $root;
        return !ss::isTarget() || ($root->getVar('seo', false) != false);
    }
    
    public static function isTarget() {
        GLOBAL $root;
        return $root->getVar('target', false) != false;
    }
*/    
    public static function realControllerName($aliase) {
        GLOBAL $ALIASES;
        return isset($ALIASES[$aliase])?$ALIASES[$aliase]:$aliase;
    }
    
    public function isCached() {
        return !($this->getVar('reset-cache', false) || $this->getVar('nocached', false));
    }
    
    protected function restoreCache($controller) {
        $cacheKey = $controller->cacheKey();
        $cacheKey_adv = $cacheKey.'_adv';
        $cacheKey_share = $cacheKey.'_share';
                                   
        if ($controller->isCached() && ss::getInstance()->isCached()) {
            $content = ss::getCache($cacheKey);
            ss::$noadv = ss::getCache($cacheKey_adv, ss::$noadv);  // Восстанавливаем флаг показа рекламы из кэша
            ss::$shared = ss::getCache($cacheKey_share, ss::$shared); 
        } else $content = null;
        
        return $content;
    } 
    
    protected function toCache($controller, $content, $cacheKey=false) {
        $cacheKey = $cacheKey?$cacheKey:$controller->cacheKey();
        $cacheKey_adv = $cacheKey.'_adv';
        $cacheKey_share = $cacheKey.'_share';
        
        if ($controller->isCached()) {
            ss::setCache($cacheKey, $content, $controller->cacheExpire());
            ss::setCache($cacheKey_adv, ss::$noadv, $controller->cacheExpire()); // Запоминаем флаг показа рекламы  
            ss::setCache($cacheKey_share, ss::$shared, $controller->cacheExpire());
        }
    }
    
    public static function trace($item) {
        GLOBAL $root;
        if ($root->isDev()) {
            $stack = GetStack();
            
            $index = 1;
            $pif = pathinfo($stack[$index]['file']);
            $fileName = $pif['filename'];
            
            /* 
            print_r($stack);
            while ($index < count($stack)) {
                if (strpos($stack[$index]['file'], 'ss') !== false) break;
                $index++;
            }
            */
            
            $idx = "{$fileName}=>{$stack[$index]['line']}";
        
            ss::$trace[$idx] = $item;
        }
    } 
    
    public function langPath($fileName) {
        return SSPATH.'language/'.$this->lang().'/'.$fileName;    
    }
    
    public function page() {     
        GLOBAL $_GET, $_SERVER, $controller, $locale, $contentType;
        if ($contentType == 'html')
            include_once($this->langPath('menu.php'));
        
        $content = null;
        if (preg_match("/(login)\W/i", $_SERVER['QUERY_STRING']) > 0) {
            $task = "user,login";
        } else $task = $this->request->getVar('task', 'article,viewArticle,home');
        
        
        ss::$task = explode(',', $task);
        ss::$controller_name = ss::realControllerName(ss::$task[0]);
                            
        if ($controller = $this->createController(ss::$controller_name)) {
            $isdev      = $this->isDev();
            $cacheKey   = $controller->cacheKey();          
            $content    = $this->restoreCache($controller);
            $devpanel   = '';
            $cache_dev  = '';
            $is_cache_content   = $content > ''; 
            
            if (!$is_cache_content) {
                ob_start();
                parent::redirect($controller, (isset(ss::$task[1])?ss::$task[1]:ss::$task[0]));
                
                $content = ob_get_contents();
                ob_end_clean();
                
                $this->toCache($controller, $content, $cacheKey);
            } else $cache_dev = 'CACHE_KEY: "<a onclick="clearCache(\''.$cacheKey.'\')">'.$cacheKey.'</a>"';
            
            if (($contentType == 'html') && $isdev) {
                $devpanel = '<div class="dev_panel">'.$cache_dev.
                            ($isdev?('<pre>'.print_r(json_encode(ss::$trace), true).'</pre>'):'').
                            '</div>';
            } 
            
            if (is_string($content)) $content .= $devpanel;
            
            require TEMPLATES_PATH.$this->template;
        }
    }
}

?>