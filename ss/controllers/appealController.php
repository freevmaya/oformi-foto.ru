<?
define('ARTICLESPATH', SSPATH.'articles/');
define('ARTICLESURL', SSURL.'articles/');
define('APPEALIMAGES', MAINPATH.'/images/appeal-docs/');
define('APPEALIMAGEEXT', '.jpg');

include_once(INCLUDE_PATH.'/util.php');

class appealController extends controller {
    private function checkValues() {
        GLOBAL $_FILES;
        $error = '';
        if (!filter_var($this->request->getVar('email', ''), FILTER_VALIDATE_EMAIL)) $error = 'email указан неверно!';        
        if (!$this->request->getVar('name', null)) $error .= ($error?'<br>':'').'имя указано неверно!';
        
        if (!$url = $this->request->getVar('url', null)) {
            if ((!$file = $_FILES['image']) || (!$file['name'])) $error .= ($error?'<br>':'').'отсутствует ссылка или документ подтверждающий право!';
        }        
        return $error;
    }
    
    private function appendAppeal() {
        GLOBAL $_FILES;
        $name = $this->request->getVar('name', null);
        $email = $this->request->getVar('email', null);
        $url = $this->request->getVar('url', null);
        $comment = $this->request->getVar('comment', null);
        $refplace = $this->svar('refplace', '');
        $content = $this->svar('content', 0);
        
        $date = date('Y-m-d');
        $time = date('H:i:00');
        
        DB::query("INSERT INTO `ev_appeal` (`name`, `date`, `time`, `email`, `content`, `url`, `comment`, `refplace`) VALUES ('$name', '$date', '$time', '$email', '$content', '$url', '$comment', '$refplace')");
        
        $record_id = DB::lastID();
        
        if (!$url) {
            $fileName = APPEALIMAGES.$record_id.APPEALIMAGEEXT;
            $completeLoaded = util::imageResponse($fileName, array(1024, 1024));
            chmod($fileName, 0444);
        }
            
        return $record_id > 0;
    }
    
    private function checkAlready() {
        $email = $this->request->getVar('email', null);
        $date = date('Y-m-d');
        
        $line = DB::line("SELECT COUNT(id) as `count` FROM `ev_appeal` WHERE `date`='$date' AND `email`='$email'");
        return $line['count'] > 0;         
    }

    public function view() {
        GLOBAL $_SERVER;
        ss::$noadv = true;
        ss::$nomenu = true;
        
        $refplace = $_SERVER['HTTP_REFERER'];
        $myserver = $_SERVER['SERVER_NAME'];
        if (strpos($refplace, $myserver) === false)        
            $this->svar('refplace', $refplace);
            
        if (isset(ss::$task[2]))
            $this->svar('content', ss::$task[2]);
        
        if ($this->request->getVar('send', null)) {
            if (!($error = $this->checkValues())) {
                if (!$this->checkAlready())
                    $complete = $this->appendAppeal();
                else $error = 'Сегодня от вас обращение уже принято';
            }                        
        }
        require(TEMPLATES_PATH.'appeal.html');
    }
}    
?>