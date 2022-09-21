<?
include_once(SSPATH.'/helpers/comments.php');

class baseAjax extends controller {
    function __construct($a_request) {
        parent::__construct($a_request);
    }
    
    protected function defaultMethod() {
    }
    
    protected function ajax() {
        $method = $this->getVar('method', 'defaultMethod');
        echo json_encode($this->$method());
    }
}    
?>