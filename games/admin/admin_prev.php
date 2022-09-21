<?

include_once(CONTROLLERS_PATH.'controller.php');
include_once(ADMINPATH.'menu.php');

$root = null;

function rusDate($mysqlDate) {
    return date('<b>d.m.Y</b> H:i:s', strtotime($mysqlDate));
}

class Admin extends controller {
    protected $user;
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
            'optimize'=>'utils',
            'demand'=>'send',
            'demand20'=>'send',
            'demandUser'=>'send'
        );
    }
    
    public  function resetUser() {
        if (isset($_SESSION['user'])) $this->user = $_SESSION['user'];
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
        return array('sys', 'sysInfo');
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