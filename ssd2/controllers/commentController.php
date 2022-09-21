<?
include_once(SSPATH.'/helpers/comments.php');
include_once(CONTROLLERS_PATH.'baseAjax.php');

class commentController extends baseAjax {
    var $comments;
    function __construct($a_request) {
        parent::__construct($a_request);   
        $this->comments = new comments($this->getVar('content_id', 0), $this->getVar('url', ''));
    }
    
    protected function getList() {
        return $this->comments->getComments($this->getVar('page', 0), $this->getVar('all', 0));
    }
    
    protected function send() {                   
        return $this->comments->addComment($this->getVar('text', ''), $this->getVar('answer_to', 0), $this->getSenderUser());
    }
    
    protected function addLike() {
        return $this->comments->addLike($this->getVar('comment_id'), $this->getSenderUser());
    }
}    
?>