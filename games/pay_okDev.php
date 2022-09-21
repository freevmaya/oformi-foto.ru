<?php
    header('Content-type: application/xml');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
        
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine2.php');
    include_once($homePath.'/pj_ok.inc');
    include_once($homePath.'/secrects.inc');
    
    $FDBGLogFile = LOGPATH.'pay_ok.log';
    $request = new Request();
    
    
    function errorResult($error_code, $error_msg) {
        header('invocation-error: '.$error_code);
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?\>
                    <ns2:error_response xmlns:ns2="http://api.forticom.com/1.0/">
                    <error_code>'.$error_code.'</error_code>
                    <error_msg>'.$error_msg.'</error_msg>
                </ns2:error_response>';
    }
    
    trace($_SERVER['QUERY_STRING']);
    if (isset($secrets[$request->getVar('application_key', false)])) {
        $table = $request->getVar('extra_attributes', 'pjok_transaction');
        trace($table);
        if ($transaction = query_line('SELECT * FROM '.$table.' WHERE transaction_id = '.$request->getVar('transaction_id', 0))) {
            errorResult('1001', 'Payment is invalid and can not be processed');
        } else {    
            $price = $request->getVar('amount', 0);
            $query = "INSERT INTO $table (`transaction_id`, `time`, `user_id`, `service_id`, `price`) 
                        VALUES (".$request->getVar('transaction_id').", NOW()".
                        ", '".$request->getVar('uid')."', ".$request->getVar('product_code').
                        ", $price)";
            if (sql_query($query))
                echo '<?xml version="1.0" encoding="UTF-8"?\>
<callbacks_payment_response xmlns="http://api.forticom.com/1.0/">
true
</callbacks_payment_response>';
            else errorResult('9999', 'Critical system failure, which can not be recovered');
        }
    } else errorResult('1002', 'invalid application key');

    if ($db) mysql_close($db);
?>