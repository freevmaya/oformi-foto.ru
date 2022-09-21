<?
include_once(CONTROLLERS_PATH.'controller.php');

$root = null;
$admin_menu = null;
$default_task = null;

function rusDate($mysqlDate) {
    return date('<b>d.m.Y</b> H:i:s', strtotime($mysqlDate));
}

class Admin extends controller {
    protected $user;
    protected $user_data;
    protected $request;
    protected $tasks;
    static public $task;
    
    function __construct($a_request) {
        GLOBAL $_SESSION, $root;
        
        parent::__construct($a_request);
        $root = $this;
        
        $this->resetUser();
        $this->tasks = array(
            'login'=>'user',
            'logout'=>'user',
            'stateTrace'=>'state',
            'optimize'=>'utils'
        );
    }

    public static function lang() {
         GLOBAL $_SESSION, $root;

        if ($lang = $root->request->getVar('lang', false)) {
            $_SESSION['admin_lang'] = $lang;
        } else $lang = $_SESSION['admin_lang'] ? $_SESSION['admin_lang'] : 'ru';

        return $lang;
    }
    
    public function resetUser() {
        GLOBAL $admin_menu, $default_task;
        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
            $this->user_data = $_SESSION['user-data'];
            include_once(ADMINPATH.'user-type-'.$this->user_data['type'].'.php');            
        }
    }
    
    public static function userData() {
        GLOBAL $_SESSION;
        return $_SESSION['user-data'];        
    }
    
    public static function sheme() {
        if (isset($_SESSION['HTTP_HTTPS'])) return 'https';
        else return 'http';
    }
    
    public static function getInstance() {
        GLOBAL $root;
        return $root;
    }
                                    
    public static function toDefault() {
        GLOBAL $_SESSION;
        if (isset($_SESSION['start_request'])) Admin::getInstance()->toStart();
        else {
            $def = Admin::taskDefault();
            Admin::getInstance()->redirect($def[0], $def[1]);
        }
    }
    
    protected static function taskDefault() {
        GLOBAL $default_task;
        return $default_task?$default_task:array('jpgCnv', 'jpgcnvForm2');
    }
    
    protected function toStart() {        
        GLOBAL $_SESSION;
        $this->request->values = $_SESSION['start_request'];

        unset($_SESSION['start_request']);
        $this->parseRequest();  
        $this->pageRedirect();
    }
    
    public static function getTaskLink() {
        return '?task='.Admin::$task[0].','.Admin::$task[1];
    }
    
    protected function pageRedirect() {
        //print_r(Admin::$task);
        Admin::redirect(Admin::$task[0], (isset(Admin::$task[1])?Admin::$task[1]:'display'));
    }
    
    protected function parseRequest() {
        GLOBAL $CTYPE, $_SESSION;
        if ($this->user || ($CTYPE == 'json')) {
            $task = $this->request->getVar('task', 'login');
            $atask = explode(',', $task);
            if (count($atask) == 2) {
                Admin::$task = $atask;
            } else {
                Admin::$task = array($this->tasks[$task], $task);
            }
        } else {
            if (!isset($_SESSION['start_request']))
                $_SESSION['start_request'] = $this->request->values;
            Admin::$task = array('user', 'login');
        }
    }
    
    public function page() {
        GLOBAL $CTYPE;
        $this->parseRequest();
        ob_start();
        $this->pageRedirect();
        $content = ob_get_contents();
        ob_end_clean();
        
        require TEMPLATES_PATH.$this->request->getVar('tmpl', 'index.'.$CTYPE);
    }
}

?>