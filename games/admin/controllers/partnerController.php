<?
include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(CONTROLLERS_PATH.'utilsController/config.php');

class partnerController extends controller {
    public function users() {
        $udata = Admin::userData();
        
        $curDate = $this->svar('curDate', date('Y-m-d'));
        $ref_id = $this->svar('ref', $udata['uid']); 
        $startDate = date('Y-m-01', strtotime($curDate));
        $result = array();
        
        function pushData(&$result, $title, $value) {
            $result[] = array(
                            'title'=>$title,
                            'value'=>$value
                        );
        }
        
        
        $percent = $udata['percent'];
        $kof = 0.3 * ($percent/100);
        
        $total_value = query_one("SELECT SUM(t.price) FROM pjok_referer r LEFT JOIN pjok_transaction t ON r.uid=t.user_id WHERE r.ref_id={$ref_id} AND t.service_id=1 AND t.transaction_id>0");  
        $current_value = query_one("SELECT SUM(t.price) FROM pjok_referer r LEFT JOIN pjok_transaction t ON r.uid=t.user_id ".
                        "WHERE r.ref_id={$ref_id} AND t.service_id=1 AND t.transaction_id>0 AND t.time >= '{$startDate}' AND t.time <= '{$curDate}'");
                        
        $current_value = $current_value?$current_value:0;
        $total_value = $total_value?$total_value:0;
          
        pushData($result, 'Всего пользователей', query_one("SELECT COUNT(r.uid) FROM pjok_referer r WHERE r.ref_id={$ref_id}"));
        pushData($result, 'Всего потрачено пользователями', $total_value);
        pushData($result, 'Заработано всего ('.$percent.'% от дохода приложения)', round($total_value * $kof));
        
        pushData($result, 'Пользователей за этот месяц', query_one("SELECT COUNT(r.uid) FROM pjok_referer r LEFT JOIN pjok_options o ON r.uid=o.uid WHERE r.ref_id={$ref_id} AND o.createDate >= '{$startDate}' AND  o.createDate <= '{$curDate}'"));
        pushData($result, 'Потрачено пользователями за этот месяц', $current_value);
        pushData($result, 'Заработано за этот месяц ('.$percent.'% от дохода приложения)', round($current_value * $kof));
        
        
        require_once TEMPLATES_PATH.'partner_page.html';
    }    
}
?>