<?
include_once(dirname(__FILE__).'/appController.php');

class pjjsController extends controller {
    private $tid;
    private $user;
    function __construct($a_request) {
        if (isset(ss::$task[2]))  {
            $a = explode('-', ss::$task[2]);
            if (count($a) > 1) {
                $this->$a[0] = $a[1]; 
            } else $this->tid = ss::$task[2];
            unset(ss::$task[2]); 
        }
        parent::__construct($a_request);
    }
    
    function view() {
        if (ss::$isPhone) require(MOBILETEMPLATEPATH.'pjjs.html');
        else require(TEMPLATES_PATH.'pjjs.html');
    }
}    
?>