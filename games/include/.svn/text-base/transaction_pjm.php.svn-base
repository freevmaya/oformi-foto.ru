<?

class g_transaction {
    function __construct() {
    }
    
    function begin($request) {
        if ($transaction = query_line('SELECT * FROM pjm_transaction WHERE transaction_id = '.$request->getVar('transaction_id', 0)))
            return '{"status":"2", "error_code":"703"}';
        else {
            $query = "INSERT INTO pjm_transaction (`transaction_id`, `time`, `user_id`, `service_id`, `mailiki_price`, `profit`, `debug`) 
                        VALUES (".$request->getVar('transaction_id').",'".date('Y-m-d H:i:s')."',".$request->getVar('uid').",".$request->getVar('service_id', 0).
                                ",".$request->getVar('mailiki_price', 0).",".$request->getVar('profit', 0).",".$request->getVar('debug', 0).")";
            if (sql_query($query))
                return '{"status":"1"}';
            else return '{"status":"2", "error_code":"703"}';
        }
    }
}

?>