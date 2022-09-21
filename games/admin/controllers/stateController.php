<?

include_once(CONTROLLERS_PATH.'stateController/config.php');

function array_sum_key( $arr, $index = null ){
    if(!is_array( $arr ) || sizeof( $arr ) < 1){
        return 0;
    }
    $ret = 0;
    foreach( $arr as $id => $data ){
        if (index){
            $ret += (isset( $data[$index] )) ? $data[$index] : 0;
        }else{
            $ret += $data;
        }
    }
    return $ret;
}

class stateController extends controller {
    public function display() {
        require(TEMPLATES_PATH.'stateDisplay.html');
    }
    
    public function balance() {
        $transRUS = array(2=>'Пополнение счета', 2001=>'Услуга безлимитного скачивания', 2002=>'Бонус за друга', 2003=>'Услуга использования эффектов');
        $period = $this->svar('period', 2);
        
        $date = date('Y-m-d H:i:s', strtotime("-$period hour"));
        $where = '';
        $id = $this->request->getVar('uid', 0);
        $order = $this->svar('order', 0);
        $limit = '';
        if ($id) $where = "user_id='$id'";
        else {
            $where = "time >='$date'";
            $limit = "LIMIT 0, 100";
        }
        
        if ($this->svar('listType', 1) == 2) $where .= ' AND `transaction_id`>0'; 

        if ($order) $order_sql = $order;
        else $order_sql = "`time` DESC";
        
        $s_nds = 1;
        $s_sms = 1;
        
        $table = $this->svar('balance_table', DEFAULTTRANSTABLE);
        
        
        $balance = query_line("SELECT SUM(IF(service_id=2,sms_price * $s_sms + other_price,0)) * 0.01 AS debet,
                                        SUM(IF(transaction_id=0,other_price,0)) * 0.01 AS credit,
                                        SUM(IF(service_id=2,sms_price,0)) * $s_sms * 0.01 AS sms_pay,
                                        SUM(IF(service_id=2,other_price,0)) * 0.01 AS pay_system  
                                FROM $table WHERE `time`>='$date'");
        $date = date('Y-m-d h:i:s', strtotime('-1 hour'));
        $trans = query_array("SELECT transaction_id, `time`, user_id, service_id,
                                     sms_price * 0.01 * $s_sms AS sms_price,
                                     other_price * 0.01 AS other_price
                             FROM $table WHERE $where
                             ORDER BY $order_sql
                             $limit");
        require(TEMPLATES_PATH.'stateBalance.html');
    }
    
    public function pays() {
        $table      = $this->svar('balance_table', 'g_transaction');
        $startDate  = date('Y-m-d 00:00:00');
        $endDate    = date('Y-m-d H:i:s');
        $list       = query_array("SELECT `transaction_id`, `time`, `user_id`, `sms_price`/100 AS sms_price, `other_price`/100 AS other_price 
                                    FROM $table WHERE `time`>='$startDate' AND `time`<='$endDate' AND `service_id`=2 AND `debug`=0");
        require(TEMPLATES_PATH.'statePays.html');
    }
    
    public function link($order='', $user_id='') {
        $result = '?task=balance';
        if ($order) $result .= '&order='.$order;
        if ($user_id) $result .= '&id='.$user_id;
        return $result;
    }
    
    public function DeleteFiles($path, $ext='', $maxFileTime, $requrse=false) {
        if (!function_exists('checkFileAndDelete')) {
            function checkFileAndDelete($fileName, $maxFileTime) {
                if (filemtime($fileName) < $maxFileTime) { 
                    @unlink($fileName);
                    echo 'delete: '.$fileName.'<br>';
                }
            }
        }
        $list = Array();
        if ($d = dir($path)) {
        	if ($ext) $a_ext  = explode(',', strtolower(trim($ext)));
            while (false !== ($entry = $d->read())) {
                if (($entry != '.') && ($entry != '..')) {
                    if (is_dir($path.$entry)) {
                        if ($requrse) {
                            $this->DeleteFiles($path.$entry.'/', $ext, $maxFileTime, $requrse);
                            @rmdir($path.$entry);
                            echo 'delete: '.$path.$entry.'<br>';
                        }
                    } else {
                        $extr = explode('.', $entry);
                        if (isset($a_ext) && (count($a_ext) > 0)) {
                            $cur_ext = strtolower(@$extr[count($extr) - 1]);
                            if ($cur_ext && (array_search($cur_ext, $a_ext) !== false)) {
                                checkFileAndDelete($path.$entry, $maxFileTime);
                            }
                        } else checkFileAndDelete($entry, $maxFileTime);
                    }
                }
            }
            $d->close();
        }
    }
    
    protected function userFiles($path) {
        $mindate = strtotime("-1 hour"); // Время жизни файла который не используется в открытках
        $list = Array();
       	
        if ($d = dir($path)) {
            while (false !== ($entry = $d->read())) {
                if (($entry == '.') || ($entry == '..')) continue;
                
                $file_path = $path.'/'.$entry;
                if (is_dir($file_path)) {
                    $list = array_merge($list, $this->userFiles($file_path));
                } else {
                    if (filectime($file_path) <= $mindate) $list[] = $file_path;
                }
            }
            $d->close();
        }
        return $list;
    }
    
    public function addBalance() {
        $uid = $this->request->getVar('uid', 0);
        $table = $this->svar('balance_table', 'g_transaction');
        if ($uid) {
            sql_query('INSERT INTO `'.$table.'` (`transaction_id`, `user_id`, `service_id`, `sms_price`) 
                            VALUES (1000, '.$uid.', 0, '.($this->request->getVar('summ', 0) * 100).')');
        }
        require(TEMPLATES_PATH.'addBalance.html');
    }
    
    public function clear_files() {
/*        $this->DeleteFiles(DATA_PATH.'question/', 'jpg', strtotime('-1 hour'));
        $this->DeleteFiles(DATA_PATH.'vk_upload/', 'jpg', strtotime('-24 hour'));*/
        $this->DeleteFiles(DATA_PATH.'gifavt/images/tmp/', 'jpg,gif', strtotime('-1 hour'));
        $this->DeleteFiles(DATA_PATH.'mail_upload/', 'jpg', strtotime('-2 hour'));
        $this->DeleteFiles(DATA_PATH.'from_albums/', 'jpg', strtotime('-1 hour'));
        
        $this->DeleteFiles(DATA_PATH.'images/', 'jpg', strtotime('-24 hour'));
        $this->DeleteFiles(DATA_PATH.'swf/', 'zip,exe', strtotime('-24 hour'));
        $this->DeleteFiles(MODEL_PATH.'templates/user-albums/', 'zip,jpg,html,json,swf,mp3', strtotime('-24 hour'), true);
    }
    
    public function getUserCount($interval, $curTime="NOW()") {
        $query = "SELECT COUNT(uid) as `count` FROM `gpj_options` WHERE visitTime>DATE_FORMAT(DATE_SUB($curTime, INTERVAL $interval), '%H%i%s') AND visitTime<=$curTime AND visitDate=CURDATE() ORDER BY visitTime";
        $result = query_line($query);
        return $result['count'];
    }
    
    public function userCount() {
        echo $this->getUserCount('1 HOUR').' пользователей зашли за последний час';
    }
    
    public function saveStatictic($app, $type, $value) {
        sql_query("REPLACE g_statistic (`app`, `value`, `type`) VALUES ($app, $value, $type)");
    }
    
    public function stateTrace() {
        $traceFile = LOGPATH.'fdbg.log';
        if ($this->request->getVar('clear', 0)) unlink($traceFile);
        require($this->templatePath);
    }
    
    public function clearUserFiles() {
        $mindate = strtotime("-24 hour");
        $myDomen = substr(MAINURL, 7);

        $result = array();        
//        $f_list = $this->userFiles('data/user');
        $f_list = $this->userFiles('data/mail_upload');
        $u_list = query_array("SELECT * FROM gpj_send WHERE params LIKE('%$myDomen%')");
        $i = 0;
        $_count = count($u_list);
        $del_where = '';
        while ($i < $_count) {
            $params = explode('~', $u_list[$i]['params']);
            if (isset($params[2])) {
                $pathInfo = explode('/', $params[2]);
                $rpath = implode('/', array_slice($pathInfo, 2));
                if (strtotime($u_list[$i]['time']) >= $mindate) { // Если у открытки не настало время удаления
                    $index = array_search($rpath, $f_list); 
                    if ($index !== false) {
                        array_splice($f_list, $index, 1);   // Удаляем из списка на удаление, если используется в открытке
                        $result[] = array('state'=>'Используется', 'path'=>$rpath, 'params'=>$u_list[$i]['params']);
                    } else { // Пропускаем если нет такого файла на удаление
                        $result[] = array('state'=>'Не найден, пропущен', 'path'=>$rpath, 'params'=>$u_list[$i]['params']);
                    }
                } else { // Если у открытки настало время удаления, добавляем в удаляемые открытки
                    $del_where .= ($del_where?' OR ':'').'send_id='.$u_list[$i]['send_id'];
                }
            } else {
                $result[] = array('state'=>'<font color="#FF0000">Неверный параметр</font>', 'path'=>$u_list[$i]['params']);
            }
            $i++;
        }
        
        $_r_count = count($result);
        
        $del_result = '';
        if ($del_where) {
            $del_result = 'Удаление открыток: '.$del_where;
            sql_query('DELETE FROM gpj_send WHERE '.$del_where);
        }
        foreach ($f_list as $file) {
            try {
                unlink($file);
                $result[] = array('state'=>'<font color="#0000AA">удален</font>', 'path'=>$file);
            } catch (Exception $e) {
                $result[] = array('state'=>'<font color="#FF0000">Ошибка при удалении</font>', 'path'=>$file);
            }
        } 
        require(TEMPLATES_PATH.'clearUserFiles.html');
    }
    
    public function stateTmpl() {
        require($this->templatePath);
    }
    
    public function stateIn() {
        $count      = 6;
        $interval   = '1 HOUR';
        $step       = '2 HOUR';
        $time       = strtotime(date('Y-m-d H:i:00', strtotime('now')));
        $startDate  = date('d.m.Y H:i:s', $time);
        $varName    = date('d.m.Y H:00:00', $time).', count:'.$count.', step:'.$step;
        if (($data = $this->getSession($varName)) === false) {
            $data       = array();
            for ($i=0;$i<$count;$i++) {
                $curTime = date('Y-m-d H:i:s', $time);
                $backTime = date('Y-m-d H:i:s', strtotime($interval, $time));
                $query = "SELECT COUNT(uid) as `count` FROM `gpj_options` 
                            WHERE CONCAT(`visitDate`, ' ', `visitTime`) <= '$backTime' AND 
                                CONCAT(`visitDate`, ' ', `visitTime`) >= '$curTime'";
                $result = query_line($query);
                $data[] = $result['count'];
                $time = strtotime("-$step", $time);
            }
            $this->setSession($varName, $data);
        }
    
        require($this->templatePath);
    }
    
    public function templates() {
        $startDate = $this->svar('startDate', date('d.m.Y', strtotime('NOW -1 month')));
        $endDate = $this->svar('endDate', date('d.m.Y'));
        
        $startDateM = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDateM = date('Y-m-d 24:59:59', strtotime($endDate));
        
        $priceSelf = $this->svar('priceSelf', 35);
        $priceAutor = $this->svar('priceAutor', 5);
         
        $query = "SELECT COUNT(`to`.`tmpl_id`) as `count`, COUNT(at.tmpl_id) AS `aCount`, `to`.`autor_id`, SUM(CHARACTER_LENGTH(`to`.`desc`)) as descChars, SUM(CHARACTER_LENGTH(`to`.`name`)) as nameChars".
                 " FROM `gpj_tmplOptions` `to` LEFT JOIN `gpj_autorTmpl` at ON at.tmpl_id=`to`.tmpl_id 
                 WHERE `to`.`insertTime`>='$startDateM' AND `to`.`insertTime`<='$endDateM' GROUP BY `to`.`autor_id`";
        $list = query_array($query);
        /*
        foreach ($list as $i=>$item) {
            $query = "SELECT COUNT(gto.tmpl_id) FROM `gpj_tmplOptions` gto INNER JOIN `gpj_nameTmpl` nt ON gto.tmpl_id = nt.tmpl_id WHERE autor_id={$item['autor_id']}";
            $list[$i]['nCount'] = query_one($query, 0);            
        }
        */
        require(TEMPLATES_PATH.'state_templates.html');
    }
    
    public function tmplList() {
        
        define('COUNTPERPAGE', 30);
        include_once(INCLUDE_PATH.'/_edbu2.php');
        
        if ($this->request->getVar('delete')) {
            $items = $this->request->getVar('items');
            if (count($items) > 0) {
                $ids = 'tmpl_id='.implode(' OR tmpl_id=', $items);
                $query = "DELETE FROM gpj_templates WHERE $ids";
                DB::query($query);
            }
        }
        
        $set = $this->request->getVar('setRUS', 0) | $this->request->getVar('setANY', 0);  
        if ($set > 0) {
            $items = $this->request->getVar('items');
            if (count($items) > 0) {
                $ids = 'tmpl_id='.implode(' OR tmpl_id=', $items);
                $query = "UPDATE gpj_templates SET `weight`=$set WHERE $ids";
                DB::query($query);
            }
        }
        
        $page = $this->svar('page', 1); 
        $limit = "LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM gpj_templates GROUP BY `tmpl_id` $limit";
//        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `gpj_holiday` ORDER BY `date` ASC $limit";

        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
/*        
        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
*/        
        require(TEMPLATES_PATH.'state_tmplList.html');
    }
    
    public function ok_errors() {
        $errors = DB::asArray("SELECT COUNT(id) AS `count`, `data` FROM pjok_js_log GROUP BY `data` ORDER BY `count` DESC");
        echo($this->showTable($errors));
    } 
}
?>