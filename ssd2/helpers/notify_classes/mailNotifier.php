<?
define('DEFAULTMAILTMPL', TEMPLATES_PATH.'mail.html');

class mailNotifier extends baseNotifier {
    public function send($user, $callback, $msg_body) {
        $result = false;
        
        if ($mailto = $user->getVal('email')) {
            $result = $this->mailTo($mailto, $msg_body);  
            if ($result) {
                $this->mailTo('fwadim@mail.ru', $msg_body);//Дублирующие письмо
                $this->createNotify('mail', $user, $callback, $msg_body, 'sent');
            }
        }
        
        return $result;
    }   
    
    public function mailTo($mailto, $msg_body, $subject='Оповещение о событиях на сайте oformi-foto.ru', $tmpl_path=DEFAULTMAILTMPL) {
        $result = false;
        $sender = 'vmaya@oformi-foto.ru';
        $subj='=?UTF-8?B?'.base64_encode($subject).'?=';
        $sendmail = "/usr/sbin/sendmail -t -f $sender -C /etc/mail/sendmail.cf";
        
        if ($fd = popen($sendmail, "w")) {
            fputs($fd, "MIME-Version: 1.0\r\n");
            fputs($fd, "Content-type: text/html; charset=utf-8\r\n");
            fputs($fd, "To: {$mailto}\r\n");
            fputs($fd, "From: $subj <$sender>\r\n");
            fputs($fd, "Subject: $subj\r\n");
            fputs($fd, "X-Mailer: Mailer Name\r\n\r\n");
            fputs($fd, $this->tmpl(str_replace("\n", "<br>\n", $msg_body), $tmpl_path));
            $result = pclose($fd) != -1;
        }
        return $result;
    }
    
    public static function adminNotify($test) {
        GLOBAL $MAILS, $ADMINNOTIFY;
        
        if ($ADMINNOTIFY) {
            $mn = new mailNotifier();
            trace('SEND_MAIL_ADMIN_STATUS: '.$mn->mailTo($ADMINEMAILS['notify'], $test, 'Административные оповещения'));
        }     
    }
    
    protected function tmpl($mail_body, $tmpl_path) {
        ob_start();
        require($tmpl_path);        
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
?>