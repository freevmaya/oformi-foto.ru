<?php
    header('Content-type: application/xml');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
        
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine2.php');
    include_once($homePath.'/ok.inc');
    include_once($homePath.'/secrects.inc');
    
    GLOBAL $dbname;
    $dbname = '_clothing';
    $FDBGLogFile = LOGPATH.'pay_ok_cl.log';
        
    $request = new Request();
    trace($request->values);
    
    $app_keys = array('CBAOMQFBABABABABA', 'CBAIKDKCABABABABA', "CBACPKADABABABABA");
    
    function errorResult($error_code, $error_msg) {
        header('invocation-error: 1001');
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?\>
                    <ns2:error_response xmlns:ns2="http://api.forticom.com/1.0/">
                    <error_code>'.$error_code.'</error_code>
                    <error_msg>'.$error_msg.'</error_msg>
                </ns2:error_response>';
                
        trace('PAYMENT ERROR: '.$error_code.' '.$error_msg);                
    }
    
    if (in_array($request->getVar('application_key', false), $app_keys)) {
        if ($transaction = query_line('SELECT * FROM clt_oktransaction WHERE transaction_id = '.$request->getVar('transaction_id', 0))) {
            errorResult('1001', 'Payment is invalid and can not be processed');
        } else {    
            $price = $request->getVar('amount', 0);
            if ($attr = json_decode($request->getVar('extra_attributes', null))) {
                $params = $attr->params?$attr->params:'';
                $param_num = $attr->param_num?$attr->param_num:0;
            } else {
                $params = '';
                $param_num = 0;
            }            
            
            
            $query = "INSERT INTO clt_oktransaction (`transaction_id`, `time`, `user_id`, `service_id`, `other_price`, `params`, `param_num`) 
                        VALUES (".$request->getVar('transaction_id').", '".$request->getVar('transaction_time')."'".
                        ", '".$request->getVar('uid')."', ".$request->getVar('product_code').
                        ", $price, '$params', $param_num)";
            if (sql_query($query))
                echo '<?xml version="1.0" encoding="UTF-8"?\>
                            <callbacks_payment_response xmlns="http://api.forticom.com/1.0/">
                            true
                            </callbacks_payment_response>';
            else errorResult('9999', 'Critical system failure, which can not be recovered');
        }
    } else errorResult('104', 'application_key');
    
    

    if ($db) mysql_close($db);
?>