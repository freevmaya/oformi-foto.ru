<?php
class message extends g_model {
    public function server($params) {
        if (isset($params[1]) && ($params[1] == 'open'))
            return array('connect'=>'complette', 'data'=>array('connectResult'=>1));
            
            
        set_time_limit(0);
        $data = array();
        for ($i = 0; $i<50; $i++) { // Ожидаем появление данных максимум 10 секунд
            $rec_count = 0;
            while ($record = query_line("SELECT * FROM msg_stack WHERE client_to_id='{$params[0]}' OR client_id='{$params[0]}'")) {
                $data[] = $record;
                sql_query('DELETE FROM msg_stack WHERE stack_id='.$record['stack_id']);
                $rec_count++;
            }
            if ($rec_count) break;
            usleep(100000);
/*            session_write_close();  // Закрываем сессию
            session_start();        // Обновляем сессию*/
        }
        $result = array('connect'=>'complette');
        if ($rec_count > 0) $result['data'] = $data;
        return $result;
    }
    
    public function send($param) {
        GLOBAL $request;
        tables_lock('msg_stack');
        sql_query("INSERT INTO msg_stack (`client_id`, `client_to_id`, `text`) VALUES ('{$param[0]}', '{$param[1]}', '".$request->getVar('text')."')");
        $id = query_one("SELECT MAX(`stack_id`) FROM msg_stack", 0);
        tables_unlock();
        return array('result'=>1);
/*        return array('connect'=>'complette', 
                    'data'=>array('sendResult'=>query_line('SELECT client_id, client_to_id, stack_id, time FROM msg_stack WHERE stack_id='.$id)));
*/
    }
}
?>