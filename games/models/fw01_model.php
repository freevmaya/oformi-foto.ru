<?
    include_once(dirname(__FILE__).'/fw_model/config.php');
    include_once(INCLUDE_PATH.'/_edbu2.php');
    
    class fw01 extends g_model {
        public function sendBouquet($params) {
            $result = DB::query("INSERT INTO `fw_bouquets` (`autor_uid`, `receiver_uid`, `pid`, `body`) VALUES ('{$params[0]}', {$params[1]}, {$params[2]}, '{$params[3]}')");
            return array('result'=>$result, 'id'=>DB::lastID());
        }

        public function getBouquet($params) {
            return  DB::line("SELECT * FROM `fw_bouquets` WHERE `bouquet_id`=%s", $params);
        }
        
        public function watchedBouquet($params) {
            return  DB::query("UPDATE `fw_bouquets` SET receive=1 WHERE `bouquet_id`=%s", $params);
        }
        
        public function inUser($params) {
            $date = date('Y-m-d H:i:s');
            if (!$rec = DB::line("SELECT * FROM `fw_users` WHERE `uid`={$params[0]}")) {
                $rec = array(
                    'uid'=>$params[0],
                    'createDate'=>$date,
                    'visitDate'=>$date,
                    'banType'   =>0
                );
                
                if (DB::query("INSERT INTO `fw_users` (`uid`, `createDate`, `visitDate`) VALUES ({$rec['uid']}, '{$rec['createDate']}', '{$rec['visitDate']}')")) {
                    $rec['serviceDesc'] = $this->addTransaction($params[0], SERVICEFROMNEW, PRICEFROMNEW);
                }
            } else {
                if (substr($rec['visitDate'], 0, 10) != date('Y-m-d'))
                    $rec['serviceDesc'] = $this->addTransaction($params[0], SERVICEEVERYDAY, PRICEFROMEVERYDAY);
                    
                $rec['visitDate'] = $date;
                $set = "`visitDate`='{$rec['visitDate']}'";
                DB::query("UPDATE `fw_users` SET $set WHERE `uid`={$rec['uid']}");
            }
            $balance = DB::line("SELECT SUM(`price`) as balance FROM `fw_transaction` WHERE `user_id`=%s", $params);
        
            return  array(
                'user'=>$rec,
                'bouquetList'=> DB::asArray("SELECT bouquet_id, autor_uid, receiver_uid, pid, receive, receive, DATE_FORMAT(`time`, '%d.%m.%Y %H:%i') as `btime` FROM `fw_bouquets` WHERE `receiver_uid`={$params[0]} AND `receive`=0 ORDER BY `time` DESC"),
                'balance'=> $balance['balance']
            );
        }
        
        protected function addTransaction($uid, $service_id, $price) {
            $servicesDesc = array(
                SERVICEFROMNEW=>'Поздравляем! вам начислено 10 денежек за первое посещение приложения.',
                SERVICEEVERYDAY=>'Поздравляем! вам начислено 2 денежки за сегодняшнее посещение',
                SERVICEFROMSEND=>true
            );
            DB::query("INSERT INTO `fw_transaction` (`time`, `user_id`, `service_id`, `price`) VALUES ('".date('Y-m-d H:i:s')."', {$uid}, {$service_id}, {$price})");
            return $servicesDesc[$service_id];
        }
        
        public function transaction($params) {
            return $this->addTransaction($params[0], $params[1], $params[2]);
        }
    }
?>