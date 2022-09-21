<?
class payController extends controller {
    function __construct($a_request) {
        parent::__construct($a_request);
        if (isset(ss::$task[2])) ss::setTemplate(ss::$task[2].'.html');
    }
    
    public function view() {
        require(TEMPLATES_PATH.'pay_form.html');
    }
    
    public function after() {
        GLOBAl $_GET;
        require(TEMPLATES_PATH.'pay_after.html');
    }    
}

?>