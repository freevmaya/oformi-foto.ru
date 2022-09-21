<?

GLOBAL $sheme;
include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(CONTROLLERS_PATH.'utilsController/config.php');

define('COUNTPERPAGE', 60);
define('SENDSERVICE', 2003);

class transactionController extends controller {
    var $services = array(
            '100'=>'Поощрение за группу',
			'106'=>'Поощрение за установку Android приложения',
			'105'=>'Пятидневное поощрение (3,6,9,12,18)',
            '2002'=>'Сохранение',
            SENDSERVICE=>'Отправка открытки',
            '2005'=>'Свертка',
            '1'=>'Пополнение'
        );
    public function tlist() {
        $uid = $this->request->getVar('uid', '351762715688');
        $page_field = 'page_'.$uid;
        $page = $this->svar($page_field, 1);
        $apage = (($page - 1) * COUNTPERPAGE);        
        
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `pjok_transaction` WHERE `user_id`={$uid} ORDER BY `time` DESC LIMIT {$apage}, ".COUNTPERPAGE;
        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
        
        $ssv = '';
        foreach ($this->services as $sid=>$service) $ssv .= ($ssv?',':'').$sid;
        foreach ($list as $id=>$item) {
            $sid = $item['service_id'];
            $sname = isset($this->services[$sid])?$this->services[$sid]:'---';
            if ($sid == SENDSERVICE) {
                $t_time = date('Y-m-d', strtotime($item['time']));
                $send = DB::line("SELECT * FROM pjok_send WHERE `uid`={$uid} AND `date`='$t_time'");
                //$link = "?task=transaction,send_info&send_id={$send['send_id']}";
                $link = "https://ok.ru/app/oformifoto?sid,{$send['send_id']}"; 
                $sname = "<a href=\"{$link}\" target=\"_blank\">{$sname}</a>"; //https://ok.ru/app/oformifoto?sid,{$send['send_id']}
            } 
            $list[$id]['service_name'] = $sname;
        }
        
        $balance = DB::line("SELECT SUM(`price`) as `balance` FROM `pjok_transaction` WHERE `user_id`={$uid} AND (`service_id` IN ({$ssv}))");
        $balance = $balance['balance'];  
        require($this->templatePath);
    }
    
    protected function send_info() {
        if ($send_id = $this->getVar('send_id', 0)) {
            DB::line("SELECT * FROM pjok_send WHERE send_id={$send_id}");
            require($this->templatePath);
        }
    }
    
    protected function getUIDS() {
        preg_match_all("/([\d]+),*/i", $this->request->getVar('uid', '351762715688'), $list);
        return $list[1];
    }
    
    public function add() {  
        $uids = $this->getUIDS();
        $params = $this->request->getVar('params', 'vmaya');
        $service_id = $this->request->getVar('service_id', 0);
        if (($price = $this->request->getVar('price', 0)) != 0) {
            $time = date('Y-m-d H:i:s');
            
            $result = count($uids) > 0;
            foreach ($uids as $uid) {
                $query = "INSERT INTO `pjok_transaction` (`user_id`, `time`, `service_id`, `price`, `params`) VALUES ({$uid}, '{$time}', {$service_id}, {$price}, '{$params}')";
                $result &= DB::query($query);
                //echo $query.'<br>';
            }
        }
        require($this->templatePath);
    }
}