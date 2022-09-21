<?php
    header('Content-type: application/xml');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
        
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine2.php');
    include_once($homePath.'/okfw.inc');
    include_once($homePath.'/secrects.inc');
    
    $request = new Request();
    
    trace($request->values);
    
    function errorResult($error_code, $error_msg) {
        header('invocation-error: 1001');
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?\>
                    <ns2:error_response xmlns:ns2="http://api.forticom.com/1.0/">
                    <error_code>'.$error_code.'</error_code>
                    <error_msg>'.$error_msg.'</error_msg>
                </ns2:error_response>';
    }
    
    if ($request->getVar('application_key', false) == $application_key) {
        if ($transaction = query_line('SELECT * FROM fw_transaction WHERE transaction_id = '.$request->getVar('transaction_id', 0))) {
            errorResult('1001', 'Payment is invalid and can not be processed');
        } else {    
            $price = round($request->getVar('amount', 0) / 10);
            $query = "INSERT INTO fw_transaction (`transaction_id`, `time`, `user_id`, `service_id`, `other_price`) 
                        VALUES (".$request->getVar('transaction_id').", '".$request->getVar('transaction_time')."'".
                        ", '".$request->getVar('uid')."', ".$request->getVar('product_code').
                        ", $price)";
            if (sql_query($query))
                echo '<?xml version="1.0" encoding="UTF-8"?\>
                            <callbacks_payment_response xmlns="http://api.forticom.com/1.0/">
                            true
                            </callbacks_payment_response>';
            else {
                errorResult('9999', 'Critical system failure, which can not be recovered');
                trace('error query: ' + $query);
            }
        }
    }
    

    if ($db) mysql_close($db);
?>