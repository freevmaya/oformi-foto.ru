<?

GLOBAL $sheme, $dbname;

include_once(INCLUDE_PATH.'/_edbu2.php');

define('COUNTPERPAGE', 30);
$dbname = '_clothing';

class cloth_transactionController extends controller {
    var $services = array(
            '2'=>'Мега оценки',
			'3'=>'Удвоение оценок за коллаж',
			'4'=>'Нексолько коллажей в ден',
            '5'=>'Убрать текст ссылки в коллаже',
            '6'=>'ВИП-статус',
            '7'=>'Сохранение в альбом'
        );
    public function tlist() {
        $uid = $this->request->getVar('uid', '351762715688');
        $page_field = 'page_'.$uid;
        $page = $this->svar($page_field, 1);
        $apage = (($page - 1) * COUNTPERPAGE);        
        
        $query = "SELECT SQL_CALC_FOUND_ROWS *, other_price AS price FROM `clt_oktransaction` WHERE `user_id`={$uid} ORDER BY `time` DESC LIMIT {$apage}, ".COUNTPERPAGE;
        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
        
        $ssv = '';
        foreach ($this->services as $sid=>$service) $ssv .= ($ssv?',':'').$sid;
        foreach ($list as $id=>$item) {
            $sid = $item['service_id'];
            $sname = $this->services[$sid];
            $list[$id]['service_name'] = $sname;
        }
        require($this->templatePath);
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
                $query = "INSERT INTO `clt_oktransaction` (`user_id`, `time`, `service_id`, `other_price`, `params`) VALUES ({$uid}, '{$time}', {$service_id}, {$price}, '{$params}')";
                $result &= DB::query($query);
                //echo $query.'<br>';
            }
        }
        require($this->templatePath);
    }
}