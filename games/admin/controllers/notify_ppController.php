<?

class notify_ppController extends controller {
    
    public function requireNotify($date, $limit=20) {
        $list = query_array("SELECT * FROM clt_users WHERE notifyDate < '{$date}'
                                LIMIT 0, $limit"); 
        return $list;
    }
    
    public function notifier($app_id, $users, $text, $field='uid') {
        $n_users = array();
        $values = '';
        $uids = '';

        foreach ($users as $user) {
            $user['notify'] = 0;
            $result = @MAILServer::request($app_id, 'users.hasAppPermission', 
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
            
            $values .= ($values?' OR ':'')."$field={$user[$field]}"; // Для отметки что уведомление отправлено     
			usleep(300);
        }
        
        if ($uids) { 
            $result = MAILServer::request($app_id, 'notifications.send', 
                        array('uids'=>$uids,
                                'text'=>$text));
            if (isset($result['error'])) {
                print_r($result);
                return null;
            }
        }
        
        $date = date('Y-m-d h:i:s');

        sql_query("UPDATE clt_users SET notifyDate='{$date}' WHERE $values"); // Отмечаем что уведомления отправлены
        return $n_users;
    }
}

?>