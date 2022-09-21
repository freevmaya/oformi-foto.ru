<?php
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
        
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine2.php');
    include_once($homePath.'/secrects.inc'); 
    
    GLOBAL $dbname;
    $dbname = '_clothing';
    
    $app = new App('utf-8');
    $request = new Request();
    $check_sig = false;
    
    if ($dbname = $request->getVar('dbname', '_request')) unset($request->values['dbname']);
        
    if ($request->getVar('sig', false) && $request->getVar('app_id', false)) {
        $check_sig = (Request::genSig($request->values, $secrets) == $request->getVar('sig')) || 
                    (Request::genSig($request->values, $sKeys) == $request->getVar('sig'));
    }
    
    if ($request->getVar('transaction_id', false)) { 
        if ($check_sig) {
            if ($transaction = query_line('SELECT * FROM clt_transaction WHERE transaction_id = '.$request->getVar('transaction_id', 0)))
                return '{"status":"2", "error_code":"703"}';
            else {    
                $price = $request->getVar('mailiki_price', 0) * 100;
                if (!$price) $price = $request->getVar('other_price', 0);
                $query = "INSERT INTO clt_transaction (`transaction_id`, `time`, `user_id`, `service_id`, `other_price`, `debug`) 
                            VALUES (".$request->getVar('transaction_id').", '".date('Y-m-d H:i:s')."'".
                            ", '".$request->getVar('uid')."', ".$request->getVar('service_id').
                            ", $price".
                            ", ".$request->getVar('debug', 0).")";
                if (sql_query($query))
                    $result = '{"status":"1"}';
                else $result = '{"status":"2", "error_code":"703"}';
            }        
        } else $result = '{"status":"0", "check_sig_result":"'.($check_sig?'true':'false').'"}';
        echo $result;
    }

    if ($db) mysql_close($db);
?>