<?
    include_once(dirname(__FILE__).'/base_model.php');
    $charset    = 'utf8';
    class dataModel extends base_model {
        protected function add() {
            GLOBAL $_GET, $mysqli;
            $res = array();
            if ($data = @$_GET['data']) {
                $data = $mysqli->real_escape_string($data);
                $a = explode('/', $data);
                $browser = '';
                $flash = '';
                if (count($a) >= 3) {
                    $browser = $a[0];
                    $flash = $a[1];
                    $start = strlen($a[0]) + strlen($a[1]) + 2; 
                    $data = substr($data, $start);
                }
                $uid = isset($_GET['uid'])?$_GET['uid']:0;
                $res['result'] = DB::query("INSERT INTO pjok_js_log (`uid`, `browser`, `flash`, `data`) VALUES ({$uid}, '{$browser}', '{$flash}', '{$data}')")?1:0;
            }
            return $res;
        }
        protected function add_log() {
            GLOBAL $_GET, $mysqli;
            $res = array();
            if ($data = @$_GET['data']) {
                $data = $mysqli->real_escape_string($data);
                $uid = isset($_GET['uid'])?$_GET['uid']:0;
                $app = isset($_GET['app'])?$_GET['app']:'pj';
                $res['result'] = DB::query("INSERT INTO pjok_log (`uid`, `data`, `app`) VALUES ({$uid}, '{$data}', '{$app}')")?1:0;
            }
            return $res;
        }
        
        protected function fapi_error() {
            GLOBAL $_GET, $mysqli;
            $res = array();
            if (($data = @$_GET['data']) && ($uid = @$_GET['uid'])) {
                if (is_numeric($uid) && is_numeric($data)) {
                    $m = @$_GET['m']; 
                    $res['result'] = DB::query("REPLACE INTO pjok_user_errors (`uid`, `error_code`, `method`) VALUES ($uid, $data, '$m')")?1:0;
                }
            }
            return $res;
        }
    }
?>