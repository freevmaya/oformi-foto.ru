<?
include_once(SSPATH.'/helpers/comments.php');
include_once(CONTROLLERS_PATH.'baseAjax.php');

GLOBAL $coinData;
$coinData = json_decode(file_get_contents(SSPATH.'coin.json'), true);

class coinController extends baseAjax {
    function __construct($a_request) {
        parent::__construct($a_request);   
    }
    
    protected function getList() {
    }
    
    public static function getBalance($nauid=0) {
        $nauid = $nauid?$nauid:ss::nauid();
        if ($nauid) {
            $value = DB::line("SELECT SUM(value) AS balance FROM of_transaction WHERE nauid={$nauid} AND state='active'");
            return $value?$value['balance']:0;
        }
        return 0;
    }

    public static function coinData() {
        GLOBAL $coinData;
        return $coinData;
    }
    
    protected function send() {
        $res = array('result'=>0);
        if (($user = ss::getUser()) && ($user['nauid'])) {
            $value = $this->getSafeVar('value', 0);
            $service = $this->getSafeVar('service', 0);
            DB::query("INSERT INTO of_transaction (nauid, `date`, service, value) VALUES ({$user['nauid']}, NOW(), {$service}, {$value})");
        }

        echo json_encode($res);
    }

    protected function DOGEAddress() {
        $user = ss::getUser();
        if ($user && ($address = $this->getSafeVar('address', false))) {
            $query = "REPLACE of_out_request (nauid, `date`, address) VALUES ({$user['nauid']}, NOW(), '{$address}')";
            echo DB::query($query)?1:0;
        } else echo 0;
    }
}    
?>