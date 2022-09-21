<?
	define('PREVIEW_PATH', DATA_PATH.'pjm/preview/');
	define('PREVIEW_URL', DATA_URL.'pjm/preview/');
	define('QSTPREVIEW_PATH', DATA_PATH.'pjm/preview/question/');
	define('QSTPREVIEW_URL', DATA_URL.'pjm/preview/question/');
        
    class pjm extends g_model {
        function getUserData($params) {
            if (!$userData = query_line("SELECT * FROM pjm_users WHERE uid={$params[0]}")) {
                $userData = array('uid'=>$params[0], 'createDate'=>date('Y-m-d H:i'));
                sql_query("INSERT INTO pjm_users (uid, createDate) VALUES ({$userData['uid']}, '{$userData['createDate']}')");
            }
            
            $userData['serverTime'] = microtime();
            return $userData;
        }
        
        function renameHistory($params) {
            return array('result'=>sql_query("UPDATE pjm_history SET name='{$params[1]}', description='{$params[2]}' WHERE history_id = {$params[0]}"));
        }
        
        function saveHistory($params) {
            if (!$params[1]) {
                $params[1] = query_insert('pjm_history', array('uid'=>$params[0], 'name'=>$params[2], 'description'=>$params[3], 'body'=>$params[4], 'createTime'=>date('Y-m-d H:i:s'), 'attr'=>$params[5]), 'insert', 'history_id');
            } else {
                sql_query("UPDATE pjm_history SET name='{$params[2]}', description='{$params[3]}', body='{$params[4]}', attr='{$params[5]}' WHERE history_id = {$params[1]}");
            }
            
            $previewURL = $this->createPreview($params[1], ($params[5]=='QST')?QSTPREVIEW_PATH:PREVIEW_PATH);
            return array('history_id'=>$params[1], 'image'=>$previewURL);
        }
        
        function getHistoryList($params) {
            if ($params[0]) $query = "SELECT history_id, name, description, DATE_FORMAT(createTime, '%d.%m.%Y %H:%i') AS createTime
                                        , DATE_FORMAT(modifyTime, '%d.%m.%Y %H:%i') AS modifyTime
                                        FROM pjm_history WHERE uid={$params[0]}".($params[1]?(" AND attr='{$params[1]}'"):" AND attr='W'");
            else $query = "SELECT history_id, name, description FROM pjm_history WHERE attr='{$params[1]}'";
            
            $result = query_array($query);
            foreach ($result as $key=>$item) {
                $result[$key]['preview'] = PREVIEW_URL.$item['history_id'].'.jpg';
            } 
            return $result;
        }
        
        function getTemplates($params) {
            $query = "SELECT history_id, name, description, DATE_FORMAT(createTime, '%d.%m.%Y %H:%i') AS createTime
                                        , DATE_FORMAT(modifyTime, '%d.%m.%Y %H:%i') AS modifyTime
                                        FROM pjm_templates WHERE group_id={$params[0]} AND attr='CAT'";
                                        
            $result = query_array($query);
            foreach ($result as $key=>$item) {
                $result[$key]['preview'] = PREVIEW_URL.$item['history_id'].'.jpg';
            } 
            return $result;
        }
    
        function getTemplate($params) {
            $query = "SELECT * FROM pjm_templates WHERE history_id={$params[0]}";
            return query_line($query);
        }
        
        function getHistory($params) {
            $query = "SELECT * FROM pjm_history WHERE history_id={$params[0]}";
            return query_line($query);
        }
        
        function deleteHistory($params) {
            $query = "DELETE FROM pjm_history WHERE history_id={$params[0]}";
            $previewPath = PREVIEW_PATH.$params[0].'.jpg';
            if (file_exists($previewPath)) 
                unlink($previewPath);
            else {
                $previewPath = QSTPREVIEW_PATH.$params[0].'.jpg';
                if (file_exists($previewPath)) unlink($previewPath);
            }
            return array('result'=>sql_query($query));
        }
    
        function getBalance($params) {
            $query = "SELECT transaction_id, DATE_FORMAT(time, '%d.%m.%Y') as `date`, service_id, params, sms_price + other_price AS price 
                      FROM pjm_transaction 
                      WHERE uid='{$params[0]}'";
            $balance = query_array($query);
            $curTime = date('d.m.Y');
            $isFromDay = 0;
            foreach ($balance as $key=>$t) {
                if (($t['service_id'] == 4) && 
                    ($t['date'] == $curTime)) $isFromDay = 1;
                $balance[$key]['price'] = ceil($t['price'] / 100 / 11);
            }
            $result = array('balance'=>$balance, 'time'=>microtime(), 'isFromDay'=>$isFromDay);
			return $result;
        }

        function createPreview($fileName, $path) {
            GLOBAL $request;
            $result = false;
            $data = explode('|', $request->getVar('Filedata'));
            if (count($data) == 2) {
                $size = explode('x', $data[0]);
                $data = $data[1];
                $len = strlen($data);
                
                if ($image = imagecreatetruecolor($size[0], $size[1])) {
                    $index = 0;
                    for ($y=0;$y<$size[1];$y++)
                        for ($x=0;$x<$size[0];$x++) {
                            if ($index + 6 < $len) {
                                $code = '$color = 0x'.substr($data, $index, 6).';';
                                eval($code);
                                imagesetpixel($image, $x, $y, $color);
                                $index += 6;
                            }
                        }
                }
                
                $pathFile = $path.$fileName.'.jpg';
                if (file_exists($pathFile)) unlink($pathFile);
                
                $result = imagejpeg($image, $pathFile, 70);
                $result = imagedestroy($image) && $result;
            }
            return $result?($path.$fileName):'';
        }
        
        function setTransaction($params) {
            $result = sql_query("INSERT INTO pjm_transaction (`user_id`, `service_id`, `mailiki_price`, `time`, `params`) 
                        VALUES ('{$params[0]}', {$params[1]}, {$params[2]}, '".date('Y-m-d H:i:s')."', '{$params[3]}')");
            return array('result'=>$result, 'price'=>$params[2], 'uid'=>$params[0]);
        }
        
        function getTransactions($params) {
            $query = "SELECT * FROM pjm_transaction WHERE `user_id`={$params[0]} AND `time`>='".date('Y-m-d H:i:s', strtotime('-1 day'))."'";
            return query_array($query);
        }
    }
?>