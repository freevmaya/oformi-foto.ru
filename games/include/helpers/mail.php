<?
function sendMail($target_email, $from_mail, $subject, $body_mail, $charset='windows-1251', $main_charset='utf-8') {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=$charset\r\n";
    
    /* additional headers */
//            $headers .= "To:<$target_email>\r\n";
    $headers .= "From:<$from_mail>\r\n";
    $headers .= "Date:".date('r')."\r\n";
    
    if ($charset != $main_charset) {
        $subject = iconv($main_charset, $charset, $subject);
        $body_mail = iconv($main_charset, $charset, $body_mail);
    }
	return mail($target_email, $subject, $body_mail, $headers, 'sendmail');
}
?>