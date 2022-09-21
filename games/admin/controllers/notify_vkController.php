<?

class notify_vkController extends controller {
    
    public function requireNotify($date, $limit=20) {
        $list = query_array("SELECT * FROM clt_vkusers WHERE notifyDate < '{$date}' AND isNotify != 0
                                LIMIT 0, $limit"); 
        return $list;
    }
    
    public function notifier($app_id, $users, $text, $field='uid') {
        GLOBAL $secret_key;
        $n_users = array();
        $where = '';
        $uids = '';
        $VK = new vkapi($app_id, $secret_key);
        
        $uids = '';        

        foreach ($users as $user) {
            $uids   .= ($uids?',':'').$user[$field];
            $where  .= ($where?' OR ':'')."{$field}={$user[$field]}"; // Для отметки что уведомление отправлено
        }
        
        $resp = $VK->api('secure.sendNotification', array(
            'timestamp'=>time(),
            'random'=>rand(1,100000),
            'uids'=>$uids,
            'message'=>$text
        ));

        if (isset($resp['response']) && $resp['response']) {

            $date = date('Y-m-d h:i:s');
            $query = "UPDATE clt_vkusers SET notifyDate='{$date}' WHERE $where";
            
            sql_query($query); // Отмечаем что уведомления отправлены
            
            $sends = explode(',', $resp['response']);
            $resp['sendPercent']    = round(count($sends)/count($users) * 100);
            //$resp['query']  = $query;
        }
        return $resp;
    }
}

?>