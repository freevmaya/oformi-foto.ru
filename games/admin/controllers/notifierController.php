<?

define('NOTIFYMESSAGE', "У вас есть не просмотренные открытки. Чтобы посмотреть их, зайдите в приложение и нажмите кнопку входящие открытки.");

class notifierController extends controller {
    public function in_notify() {
        
        $page = $this->request->getVar('page', 0);
        $numFromPage = 100;
        $date = date('Y-m-d', strtotime('-7 day'));
        $users = query_array("SELECT gs.*, IF(n.uid,1,0) AS notify, COUNT(gs.uid) AS `count` 
                             FROM gpj_send gs 
                                LEFT JOIN gpj_notifier n ON n.uid = gs.sendTo AND n.`time`>'$date'  
                             WHERE gs.received = 0 AND ISNULL(n.uid)
                             GROUP BY sendTo
                             LIMIT $page, $numFromPage");

		$users = array(array('setndTo'=>'8062938299454250872', 'notify'=>1));
                         
        $uids = '';
        $values = '';
        $n_users = array();
        foreach ($users as $user) {
            $result = MAILServer::request('441805', 'users.hasAppPermission', 
                        array('uid'=>$user['sendTo'],
                                'ext_perm'=>'notifications'));
            if (!isset($result['error']) && ($result['notifications'] == 1)) {
                // Если дано разраешение тогда добавляем в список
                $uids .= ($uids?',':'').$user['sendTo'];
                $user['notify'] = 1;
                $n_users[] = $user;
            }
            
            $values .= ($values?',':'')."('{$user['sendTo']}')"; // Для отметки что уведомление отправлено     
			usleep(300);
        }
        
        if ($uids) { 
            $result = MAILServer::request('441805', 'notifications.send', 
                        array('uids'=>$uids,
                                'text'=>NOTIFYMESSAGE));
            if (!$result['error']) {
            } else print_r($result);
        }

        sql_query('REPLACE gpj_notifier (uid) VALUES '.$values); // Отмечаем что уведомления отправлены
        
        require TEMPLATES_PATH.'notifyList.html';
    }
    
    public function requireNotify($date, $limit=20) {
        $list = query_array("SELECT o.uid, n.time FROM `gpj_options` o 
                                LEFT JOIN gpj_notifier n ON o.uid = n.uid 
                                WHERE (n.uid AND n.time < '{$date}') OR isNULL(n.uid) 
                                LIMIT 0, $limit"); 
        return $list;
    }
    
    public function notifier($users, $text, $field='uid') {
        $n_users = array();
        $values = '';
        $uids = '';

        foreach ($users as $user) {
            $user['notify'] = 0;
            $result = @MAILServer::request('441805', 'users.hasAppPermission', 
                        array('uid'=>$user[$field],
                                'ext_perm'=>'notifications'));
            if (!$result) {                     
                $user['notify'] = -1;
            } else if (!isset($result['error']) && ($result['notifications'] == 1)) {
                // Если дано разраешение тогда добавляем в список
                $uids .= ($uids?',':'').$user[$field];
                $user['notify'] = 1;
            }
            $n_users[] = $user;
            
            $values .= ($values?',':'')."('{$user[$field]}')"; // Для отметки что уведомление отправлено     
			usleep(300);
        }
        
        if ($uids) { 
            $result = MAILServer::request('441805', 'notifications.send', 
                        array('uids'=>$uids,
                                'text'=>$text));
            if (isset($result['error'])) {
                print_r($result);
                return null;
            }
        }

        sql_query('REPLACE gpj_notifier (uid) VALUES '.$values); // Отмечаем что уведомления отправлены
        return $n_users;
    }
    
    public function userNotify() {
        $msg = trim($this->request->getVar('notifyText', 0));
        $backTime = trim($this->request->getVar('time', 0));
        if ($msg && $backTime) {

            if ($this->request->getVar('test', 0)) $users = array(array('uid'=>'8062938299454250872'), array('uid'=>'1731353195984349210'), array('uid'=>'3476324718619473216'));
            else $users = $this->requireNotify($backTime, 20);
            
            $n_users = $this->notifier($users, iconv('windows-1251', 'UTF-8', $msg));
        }
        require TEMPLATES_PATH.'notify.html';
    }
    
    public function userClear() {
        $list = query_array('SELECT * 
                                FROM `gpj_send` s LEFT JOIN `gpj_options` o ON s.sendTo=o.uid
                                WHERE isNULL(o.uid)
                                ORDER BY s.uid
                                LIMIT 0, 200');
        $where = '';
        foreach ($list as $item) {
            $where .= ($where?' OR ':'')."send_id={$item['send_id']}";
        }
        sql_query("DELETE FROM `gpj_send` WHERE $where");
        echo 'Удалено '.count($list).' элементов<br>';
        echo 'Следующий запуск подчистки через 10 сек.';
        echo "
<script type=\"text/javascript\">
    setTimeout('document.location.href = document.location.href', 10000);
</script>        
        ";
    }
}

?>