<?

class sendController extends controller {
    protected function initSession() {
        return array('back_day'=>0, 'order'=>'count', 'min_count'=>10, 'inBox'=>1);
    }
    
    public function demand() {
        $limit = $this->svar('count', 20);
        $date = date('Y-m-d', strtotime('-'.$this->svar('back_day').' day'));
        $query = "SELECT gs.card_id AS `id`, COUNT(gs.uid) AS `count`, cn.name AS name
                                FROM `gpj_send` gs LEFT JOIN g_cardNames cn ON cn.id = gs.card_id 
                                WHERE gs.`time` >= '$date' GROUP BY gs.card_id ORDER BY `".$this->svar('order')."` DESC
                                LIMIT 0, $limit";
        $cards = query_array($query);
        
        foreach ($cards as $key=>$card) {
            $fileName = 'i'.$card['id'].'.jpg';
            $cards[$key]['image'] =  CARDS_URL.'jpg_preview'.DS.$fileName;
/*            if (file_exists(CARDS_PATH.'jpg_preview'.DS.$fileName)) {
                $cards[$key]['image'] =  CARDS_URL.'jpg_preview'.DS.$fileName;
            }*/
        }
        require TEMPLATES_PATH.'demandList.html';
    }
    
    public function saveFree() {
        $limit = $this->svar('count', 20);
        $date = date('Y-m-d', strtotime('-'.$this->svar('back_day').' day'));
        $cards = query_array("SELECT COUNT(varInt) AS `count`, varInt AS id
                                FROM `gpj_statistic` 
                                WHERE `time` >= '$date' 
                                GROUP BY varInt
                                ORDER BY `count` DESC
                                LIMIT 0, $limit");
        foreach ($cards as $key=>$card) {
            $fileName = 'i'.$card['id'].'.jpg';
            $cards[$key]['image'] =  CARDS_URL.'jpg_preview'.DS.$fileName;
        }
        require TEMPLATES_PATH.'demandList.html';
    }
    
    public function demand20() {
        $date = date('Y-m-d');
        $id = $this->request->getVar('id', 0);
        $cards = query_array("SELECT *
                                FROM `gpj_send`
                                WHERE card_id = $id
                                GROUP BY uid 
                                ORDER BY `time` DESC
                                LIMIT 0, 80");
        require TEMPLATES_PATH.'demand20List.html';
    }
    
    public function demandUser() {
        $date = date('Y-m-d');
        $id = $this->svar('uid', 0);
        $email = $this->request->getVar('email', '');
        $indent = ($this->svar('inBox', 1) == 1)?'sendTo':'uid';
        
        $where = "$indent = $id";
        if ($email) $where = "params LIKE('%$email%')";
        $cards = query_array("SELECT *
                                FROM `gpj_send`
                                WHERE $where
                                ORDER BY `time` DESC
                                LIMIT 0, 80");
        require TEMPLATES_PATH.'demand20List.html';
    }    
}

?>