<?

    include_once(dirname(__FILE__).'/pj_model_ok/config.php');
    include_once(INCLUDE_PATH.'/statistic.inc');
    include_once(INCLUDE_PATH.'/_edbu2.php');
    
    GLOBAL $charset;
    $charset = 'utf8';

    class pj_okstat1 extends g_model {
        public function add($params) {
            $ct     = explode(',', $params[2]);
            $count  = count($ct);
            $result = true;
            
            for ($i=0; $i<$count; $i++) {
                if (is_numeric($ct[$i]))
                    $result = $result && DB::query("INSERT INTO pjok_stat (`date`, `uid`, `ctype`, `context`, `value`) VALUES (NOW(), {$params[0]}, {$params[1]}, {$ct[$i]}, {$params[3]})"); 
            }
            
            return array('result'=>$result);
        }
    }
?>