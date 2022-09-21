<?

include_once(INCLUDE_PATH.'/_edbu2.php');
class pjmController extends controller {
    public function historyList() {
        $time = date('Y-m-d H:i:s', strtotime('-1 day'));
        $list = DB::asArray("SELECT *
                            FROM `pjm_history` g 
                            WHERE `createTime`>'{$time}'");
        require($this->templatePath);
    }
    
    protected function historyLink($history) {
        return 'http://my.mail.ru/apps/546295#hv='.$history['history_id'];
    }
}

?>