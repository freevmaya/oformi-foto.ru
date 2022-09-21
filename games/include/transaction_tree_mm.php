<?

class g_transaction {
    function __construct() {
    }
    
    function begin($request) {
        if ($service_id = $request->getVar('service_id', 0)) {
            if ($transaction = query_line('SELECT * FROM t_transaction  WHERE pay_id = '.$request->getVar('transaction_id', 0)))
                return '{"status":"2", "error_code":"703"}';
            else {    
                $sms_prices = array(
                    0=>0,
                    1=>50,
                    3=>70,
                    5=>100
                );
                
                $mailiki_price = 0;
                $other_price = 0;
                if (!($sms_price = $sms_prices[$request->getVar('sms_price', 0)]))
                    if (!($other_price = $request->getVar('other_price', 0)))
                        $mailiki_price = $request->getVar('mailiki_price', 0);
                
                $price = $sms_price + round($other_price / 100) + $mailiki_price;
                
                $tp = json_decode(PAYVARS_PHP, true);
                foreach ($tp as $t_price=>$s_price)
                    if ($s_price == $price) {
                        $price = $t_price;
                        break;
                    }
                
                $query = "INSERT INTO t_transaction (`pay_id`, `uid`, `service`, `amount`, `param_int`) 
                            VALUES (".$request->getVar('transaction_id').", '".$request->getVar('uid')."', ".$service_id.
                            ", ".$price.", ".$request->getVar('debug', 0).")";
                if (sql_query($query))
                    return '{"status":"1"}';
                else return '{"status":"2", "error_code":"703"}';
            }
        } else return '{"status":"0", "error_code":"702"}';
    }
}

?>