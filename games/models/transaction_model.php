<?php
    class transaction extends g_model {
        function getBalance($params) {
            $query = "SELECT SUM(sms_price + other_price) / 100  AS balance
                        FROM g_transaction 
                        WHERE user_id='{$params[0]}'";
			return query_line($query);
        }
        
        function setTransaction($params) {
            $price = $params[2] * 100;
            $result = sql_query("INSERT INTO g_transaction (`user_id`, `service_id`, `other_price`, `time`, `params`) 
                        VALUES ('{$params[0]}', {$params[1]}, {$price}, '".date('Y-m-d H:i:s')."', '{$params[3]}')");
            return array('result'=>$result);
        }

        function getPrepaid($params) {
            $curDate = date('Y-m-d H:i:s');
            $date = date('Y-m-d H:i:s', strtotime("-{$params[1]} hour"));
            $where = "`user_id`='{$params[0]}' AND ((`time` >= '$date' AND `time` <= '$curDate') OR (service_id = 2002)) AND debug=0";
            if (isset($params[2])) {
                $where .= " AND {$params[2]}";
            }
            $query = "SELECT `time`, SUM(sms_price + other_price) / 100  AS price, service_id
                        FROM g_transaction 
                        WHERE $where
                        GROUP BY service_id";
			return query_array($query);
        }
    }
?>