<?

    define('MAILSUBJECT', 'К вам пришла открытка от ');
    class pj06 extends g_model {

        protected function userMailNotify($mail, $sid, $userName, $subject, $description) {
            GLOBAL $_SERVER;
            ob_start();
            require(MAINPATH.'games/templates/pj/mail_fromMM.php');
            $body = ob_get_contents();
            ob_end_clean();
            
            $from = "noreply@{$_SERVER['SERVER_NAME']}";
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=windows-1251\r\n";
            $headers .= "From:<$from>\r\n";
            $headers .= "To:<$mail>\r\n";
            $headers .= "Date:".date('r')."\r\n";
            
            return mail($mail, iconv('utf-8', 'windows-1251', $subject), 
                                iconv('utf-8', 'windows-1251', $body), $headers, $from);
        }
        
        public function sendCardEmail($params) {
            $cardInfo = explode('~', $params[2]);
            $send_result = array();
            $send_result['state'] = sql_query("INSERT INTO gpj_send (`uid`, `sendTo`, `params`, `time`, `card_id`) 
                                                VALUES ('{$params[0]}', '{$params[1]}', '{$params[2]}', '".date('Y-m-d H:i:s')."', {$cardInfo[0]})");
            $send_result['send_id'] = query_one("SELECT LAST_INSERT_ID()");
            $send_result['send_result'] = $this->userMailNotify(App::decode($params[3]), $send_result['send_id'], $params[4], MAILSUBJECT.$params[4], App::decode($params[5])); 
            return $send_result;
      }        
    }
?>