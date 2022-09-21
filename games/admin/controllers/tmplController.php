<?

define('CFGFILEPREPARE', '/home/html/games/data/temp_storage_prepare.json');
define('CFGFILE', '/home/html/games/data/temp_storage.json');
define('CFGFILEBACK', '/home/html/games/data/temp_storage_back.json');
define('TOADDTIME', 'INTERVAL -1 DAY');

class tmplController extends controller {
    public function add() {
        include_once(CONTROLLERS_PATH.'/tmpl/config.php');
        require(TEMPLATES_PATH.'/tmpl_add.html');
    }
    
    public function add_dev() {
        include_once(CONTROLLERS_PATH.'/tmpl/config.php');
        require(TEMPLATES_PATH.'/tmpl_add_dev.html');
    }
    
    public function report() {
        include_once(CONTROLLERS_PATH.'/tmpl/config.php');
        if ($autor = $this->request->getVar('autor', 0)) {
            $list = DB::asArray("SELECT *, DATE_FORMAT(DATE_ADD(`live`, ".TOADDTIME."), '%d.%m.%Y') AS `day` FROM pj_templates WHERE active=1 AND autor_id={$autor} ORDER BY `live`");
            $autor = DB::line("SELECT * FROM pj_admin WHERE uid={$autor}");
            
            $days = array();
            foreach ($list as $item) {
                if (!isset($days[$item['day']])) $days[$item['day']] = array('count'=>0, 'countf3'=>0);
                $days[$item['day']]['count']++;
                if ($params = $item['params']?json_decode($item['params']):null) {
                    if (isset($params->spots) && (count($params->spots) > 2)) {
                        $days[$item['day']]['countf3']++;
                    }
                }
            }
        } else {
            $list = DB::asArray("SELECT t.`day`, t.`params`, COUNT(tmpl_id) as `count`, `autor_id`, a.login FROM 
                                    (SELECT *, DATE_FORMAT(DATE_ADD(`live`, ".TOADDTIME."), '%d.%m.%Y') AS `day` FROM pj_templates WHERE active=1) AS t 
                                INNER JOIN pj_admin a ON a.uid=t.autor_id GROUP BY `autor_id`, `day`");
        }
        require(TEMPLATES_PATH.'/tmpl_report.html');
    }
    
    public function pickup() {
        include_once(INCLUDE_PATH.'/model.php');
        include_once(MODEL_PATH.'ta_dev_model.php');
        $result = false;
        
        $back_count = $this->request->getVar('back_count', 10);
        
        $ta = new ta_dev(Admin::getInstance());
        $queryA = "SELECT * FROM pj_templates WHERE `active`=1 ORDER BY tmpl_id DESC LIMIT $back_count, 10000";
        $templates = array();
        $items = DB::asArray($queryA);
        
        if ($this->request->getVar('apply', 0) == 1) {
            $query = "UPDATE pj_templates SET `active`=0 WHERE tmpl_id <= {$items[0]['tmpl_id']}";
            if ($result = DB::query($query)) {
                $ta->templatesToFile(CFGFILEPREPARE);
                //$ta->applyAll(null);
                $items = DB::asArray($queryA);
            }
        }        
        
        foreach ($items as $item) {
            $templates[$item['tmpl_id']] = json_decode($item['params'], true);
        } 
        
        ksort($templates);
        $tmpls = $ta->packTmpls($templates);
        
        require(TEMPLATES_PATH.'/tmpl_pickup.html');
    }
}
?>