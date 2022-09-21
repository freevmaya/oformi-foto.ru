<?
class sender {

    protected static function codeUTF8($text) {
        return '=?UTF-8?B?'.base64_encode($text).'?=';
    }

    public static function sendMail($mail, $subject, $body) {
        GLOBAL $_SERVER;
        
/*        $from = "noreply@{$_SERVER['SERVER_NAME']}";
        
        $sendmail = "/usr/sbin/sendmail -t -f $from -C /etc/mail/sendmail.cf";
        $fd = popen($sendmail, "w");
        fputs($fd, "MIME-Version: 1.0\r\n");
        fputs($fd, "Content-type: text/html; charset=utf-8\r\n");
        fputs($fd, "To: $mail\r\n");
        fputs($fd, "From: $from <$from>\r\n");
        fputs($fd, "Subject: ".sender::codeUTF8($subject)."\r\n");
        fputs($fd, "X-Mailer: Mailer Name\r\n\r\n");
        fputs($fd, $body);
        pclose($fd);*/
        
        $sender = 'vmaya@oformi-foto.ru';
        $body = '<b>Это текст письма</b>';
        
        $subj='=?UTF-8?B?'.base64_encode('Разработчики писем').'?=';
        
        $sendmail = "/usr/sbin/sendmail -t -f $sender -C /etc/mail/sendmail.cf";
        $fd = popen($sendmail, "w");
        fputs($fd, "MIME-Version: 1.0\r\n");
        fputs($fd, "Content-type: text/html; charset=utf-8\r\n");
        fputs($fd, "To: fwadim@mail.ru\r\n");
        fputs($fd, "From: $subj <$sender>\r\n");
        fputs($fd, "Subject: $subj\r\n");
        fputs($fd, "X-Mailer: Mailer Name\r\n\r\n");
        fputs($fd, $body);
        pclose($fd);        
    }
    
    public static function socketSendMail($to, $subject, $message, $from='noreply@oformi-foto.ru', $fromName='noreply') {
        $server = 'localhost';
        $subject = sender::codeUTF8($subject);
        $fromName = sender::codeUTF8($fromName);
        
        $connect = fsockopen ($server, 25, $errno, $errstr, 30);
        fputs($connect, "HELO localhost\r\n");
        fputs($connect, "MAIL FROM: $from\n");
        fputs($connect, "RCPT TO: $to\n");
        fputs($connect, "DATA\r\n");
        fputs($connect, "Content-Type: text/html; charset=UTF-8\n");
        fputs($connect, "To: $to\n");
        fputs($connect, "From: $fromName <$from>\r\n");
        fputs($connect, "Subject: $subject\n");
        fputs($connect, "\n\n");
        fputs($connect, stripslashes($message)." \r\n");
        fputs($connect, ".\r\n");
        fputs($connect, "RSET\r\n");
        fclose($connect);
    }
}
?>