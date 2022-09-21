<?php

    define('MSGFILE', dirname(__FILE__).'/message.txt');
    define('XMLFILE', dirname(__FILE__).'/../data/sokoban.xml');
    define('MSGFILE_ONLINE', dirname(__FILE__).'/message_from_online.txt');
    define('NEWUSER_BALANCE', 6000); // На шесть подсказок
    define('NEWUSER_BALANCE_SERVICE_ID', 50); // Номер сервиса для подъемного баланса нового игрока

    class sokoban extends g_model {
        
       /*
            0 - user_id
            1 - host
        */
        public function getUserData($params) {
            $curTime = strtotime('now');
            $query = "SELECT *, DATE_FORMAT(u.update_time, '%d %M %Y %r') as update_time,
                            (SELECT SUM(sms_price + other_price) / 100 
                                FROM g_transaction 
                                WHERE user_id='{$params[0]}') AS balance,
                            (SELECT COUNT(user_id) 
                                FROM g_favorites 
                                WHERE user_id='{$params[0]}') AS f,
                            (SELECT COUNT(user_id) 
                                FROM g_tournament t
                                WHERE t.user_id='{$params[0]}' AND
                                    t.TourEnd > 0 AND
									t.steps=(SELECT MIN(steps) 
											FROM g_tournament 
											WHERE level=t.level)) AS t
                        FROM g_users_data u 
                        WHERE u.user_id='{$params[0]}' AND u.host='{$params[1]}'";
            
			$record = query_line($query);
			if ($record) {
    			$time = strtotime('+1 day '.$record['update_time']);
    			$record['live_time'] = (!$record['update_time'])?0:$time - strtotime('now');
    			$record['time'] = $curTime;
//    			$record['next_time'] = strtotime('+2 hour now');
			} else $record = array('user_id'=>0, 'time'=>$curTime);

			if (file_exists(XMLFILE)) {
				$record['ver'] = filemtime(XMLFILE);
			}
			
			if (file_exists(MSGFILE)) { 
                $msgTime = filemtime(MSGFILE);
                if (!isset($record['last_dateMsg']) || !$record['last_dateMsg'] ||
                    ($msgTime > $record['last_dateMsg']))
                    $record['message'] = file_get_contents(MSGFILE);
            }
            unset($record['last_dateMsg']);
            return $record;
        }
        
        public function setUserData($params) {
            if (!$user = query_line("SELECT * FROM g_users_data WHERE user_id='$params[0]'")) { // Если нет такого юзверя, тогда накинем ему бабла
                sql_query("INSERT INTO g_transaction (`user_id`, `service_id`, `other_price`) 
                                    VALUES ('{$params[0]}', ".NEWUSER_BALANCE_SERVICE_ID.', '.NEWUSER_BALANCE.')');
            }
            $this->updateRecord('g_users_data', $params, 
                                    array('user_id', 'host', 'user_name', 'level', 'photo', 'sex', 'city', 'nickname', 'link'),
                                    array(true, true, true, false, true, false, false, true, true),
                                 "user_id='{$params[0]}' AND host='{$params[1]}'");
            return array('result'=>1);
        }
        
        /*
            0 - level
            1 - host
        */
        public function getFavorite($params) {
            $query = 'SELECT f.level, f.user_id, f.steps, f.time, u.user_name, 
                        u.photo, u.sex, u.city, u.nickname, u.link, 
                        DATE_FORMAT(f.update_time, \'%d %M %Y %r\') as update_time,
                        DATE_FORMAT(u.update_time, \'%d %M %Y %r\') as u_update_time 
                        FROM g_favorites f LEFT JOIN g_users_data u ON f.user_id = u.user_id AND f.host = u.host
                                WHERE f.level='.$params[0]." AND f.host='{$params[1]}'";
			$record = query_line($query);
			if ($record) {
    			$time = strtotime('+1 day '.$record['update_time']);
    			$record['live_time'] = (!$record['update_time'])?0:$time - strtotime('now');

    			$u_time = strtotime('+1 day '.$record['u_update_time']);
    			$record['ulive_time'] = (!$record['u_update_time'])?0:$u_time - strtotime('now');
			}
            return $record;
        }
        
        /*
            0 - level
            1 - host
            2 - user_id
            3 - user_name
            4 - steps
            5 - time
        */
        public function setFavorite($params) {
            $this->updateRecord('g_favorites', $params, 
                                    array('level', 'host', 'user_id', 'user_name', 'steps', 'time'),
                                    array(false, true, true, true, false, false),
                                 "level='{$params[0]}' AND host='{$params[1]}'");
            return array('result'=>1);
/*            
            sql_query('REPLACE g_favorites (level, host, user_id, user_name, steps, time) 
                       VALUES ('.$params[0].', \''.$params[1].'\', \''.$params[2].'\', \''.$params[3].'\', '.$params[4].', '.$params[5].')');*/
        }
        
        public function setInstall($params) {
            $this->updateRecord('g_users_data', $params, 
                                    array('user_id', 'host', 'install'),
                                    array(true, true, false),
                                 "user_id='{$params[0]}' AND host='{$params[1]}'");
            return array('result'=>1);
        }
        
        public function updateFriend($params) {
			if ($params[0] && $params[1]) {
	            $this->updateRecord('g_friends', $params, 
	                                    array('user_id', 'friend_id'),
	                                    array(true, true), "user_id='{$params[0]}' AND friend_id='{$params[1]}'");
	            return array('result'=>1);
			} else return array('result'=>0);
        }

        public function getFriends($params) {
            $query = "SELECT u.*, f.use_tip as use_tip,
                        (SELECT COUNT(user_id) 
                            FROM g_favorites 
                            WHERE user_id=f.friend_id) AS f,
                        (SELECT COUNT(user_id) 
                                FROM g_tournament t
                                WHERE t.user_id=f.friend_id AND
                                    t.TourEnd > 0 AND
									t.steps=(SELECT MIN(steps) 
											FROM g_tournament 
											WHERE level=t.level)) AS t 
                        FROM g_friends f LEFT JOIN g_users_data u ON u.user_id = f.friend_id
                        WHERE f.user_id='{$params[0]}'";
            $result = query_array($query);
            return $result;
        }
        
        public function useFriendTip($params) {
			if ($params[0] && $params[1]) {
                $this->updateRecord('g_friends', $params, 
                                        array('user_id', 'friend_id', 'use_tip'),
                                        array(true, true, false), "user_id='{$params[0]}' AND friend_id='{$params[1]}'");
            }
            return array('result'=>1);
        }
        
        public function getTip($params) {
            $query = "SELECT * 
                        FROM g_levels
                        WHERE level_id={$params[0]}";
            if (!$result = query_line($query)) {
                $query = "SELECT stepMap as tip, level as level_id 
                            FROM g_tournament
                            WHERE level={$params[0]} AND steps = (SELECT MAX(steps) FROM g_tournament WHERE level={$params[0]})";
                $result = query_line($query);
            }
            return $result;
        }
        
        public function setTip($params) {
            $this->updateRecord('g_levels', $params, 
                                    array('level_id', 'tip'),
                                    array(false, true), "level_id='{$params[0]}'");
            return array('result'=>1);
        }
        
        public function setDisableMsg($params) {
            $params[1] = strtotime('now');
            $this->updateRecord('g_users_data', $params, 
                                    array('user_id', 'last_dateMsg'),
                                    array(true, false),
                                 "user_id='{$params[0]}'");
            return array('result'=>1);
        }
        
        public function userTourParse($params) {
            if (isset($params[4]))
                $params[4] = $params[4]?strtotime('now'):0;
            else $params[4] = strtotime('now');
            
            $this->updateRecord('g_tournament', $params, 
                                    array('user_id', 'level', 'steps', 'stepMap', 'TourEnd'),
                                    array(true, false, false, true, false),
                                 "user_id='{$params[0]}' AND level={$params[1]}");
            return array('result'=>1);
        }
        
        public function command($params) {
        
			if (file_exists(MSGFILE_ONLINE)) {
                $msgTime = filemtime(MSGFILE_ONLINE);
                if ($msgTime > $params[3]) {
                    return array('Function'=>'oneMessage', 
                                'data'=>array('message'=>file_get_contents(MSGFILE_ONLINE), 'msgTime'=>$msgTime));
                }
            }
            
            return array();
        }

        public function getTourFavorite($params) {
            $query = "SELECT t.steps, t.level as levelTour, u.*
                        FROM (SELECT MIN(steps) AS min_steps FROM g_tournament WHERE level={$params[0]} AND TourEnd > 0) mt,
                            g_tournament t LEFT JOIN g_users_data u ON u.user_id = t.user_id   
                        WHERE t.level={$params[0]} AND t.steps=mt.min_steps AND t.TourEnd > 0
                        ORDER BY TourEnd";
			$record = query_line($query);
			return $record;
        }
        
        public function getBalance($params) {
            $query = "SELECT SUM(sms_price + other_price) / 100 AS balance FROM g_transaction WHERE user_id='{$params[0]}'";
			$record = query_line($query);
			return $record;
        }
        
        public function transaction($params) {
            $price = $params[2] * 100;
            $result = sql_query("INSERT INTO g_transaction (`user_id`, `service_id`, `other_price`) 
                        VALUES ('{$params[0]}', {$params[1]}, {$price})");
            return array('result'=>$result);
        }
    }
?>