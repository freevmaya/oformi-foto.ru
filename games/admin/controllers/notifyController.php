<?

include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(INCLUDE_PATH.'/table.php');
define('BASEPATH', '/home/notify/');

class notifyController extends controller {
    protected function updatePath($path) {
        if ($config = $this->readConfig($path)) {
            $config->wait_mls = $this->request->getVar('wait_mls', $config->wait_mls);
            $config->enabled = $this->request->getVar('enabled', 0)?1:0;
            
            foreach ($config->messages as $key=>$mitem) {
                $akey = str_replace('.', '_', $key);
                $config->messages->$key = $this->request->getVar($akey, $config->messages->$key);
            }
            
            $query_value = explode('|', $this->request->getVar('query', $config->default_query));
            
            $config->default_query = $query_value[0];
            if (isset($query_value[1])) $config->table = $query_value[1];
            
//            print_r($config);
            $this->saveConfig($path, $config);
            
            
            $stateFile = BASEPATH.$path.'/state.dat';                                                  
            if (file_exists($stateFile))            
                file_put_contents($stateFile, $this->request->getVar('count', file_get_contents($stateFile)));
        }
    }
    
    protected function readConfig($path) {
        if ($this->isConfig($path)) {
            $json = json_decode(file_get_contents(BASEPATH.$path.'/config.json'));
            return $json;
        } 
        return null;
    }

    protected function saveConfig($path, $config) {
        if ($this->isConfig($path)) {
            $config_str = json_encode($config);
            $file = fopen(BASEPATH.$path.'/config.json', 'w+');
            fwrite($file, $config_str);
            fclose($file);
            return true;
        }
        return false;
    }

    protected function isConfig($path) {
        return (is_dir(BASEPATH.$path) && file_exists(BASEPATH.$path.'/config.json'));
    }

    public function viewList() {
    
        if ($path = $this->request->getVar('path', false)) {
            $this->updatePath($path);
        }
    
        $dir = scandir(BASEPATH);
        $items = array();
        include_once(MODEL_PATH.'pj_model_ok/user_groups.php');
        
        foreach ($dir as $item) {
            if ($config = $this->readConfig($item)) {
            
                $stateFile = BASEPATH.$item.'/state.dat';
                if ($this->request->getVar('reset', false) == $item) {
                    unlink($stateFile);
                    $state = 0;
                } else $state = file_exists($stateFile)?file_get_contents($stateFile):0;
                
                $logFile = BASEPATH.$item.'/notify.log';
                $log = array();
                
                if (file_exists($logFile) && (filesize($logFile) < 1024 * 1024)) {
                    $logText = trim(@file_get_contents($logFile));
                    if ($logText)
                        eval('$log = array('.$logText.');');
                }
                 
                $query_list = null;
                $queryListFile = BASEPATH.$item.'/query.list.php';
                if (file_exists($queryListFile)) {
                    include($queryListFile); 
                    
                    $alerts = DB::asArray('SELECT * FROM gpj_alertTemplates');
                    foreach ($alerts as $alert) {
                        $query_list[] =  array(
                            'name'=>'Тех у кого сообщение в приложении, шаблон: '.$alert['name'],
                            'query'=>'SELECT * FROM `pjok_alerts` WHERE `alert_id`='.$alert['id'].' LIMIT {$this->recNumber}, {$this->config->userCount}',
                            'table'=>'pjok_alerts'
                        );
                    }
                
                    $list = userGroup('pjok');
                    foreach ($list as $gitem) {
                        if (isset($gitem['move_table'])) {
                            $isTable = DB::line("SELECT COUNT(`TABLE_NAME`) AS `count` FROM information_schema.tables WHERE table_name = '{$gitem['move_table']}' LIMIT 1 ");
                            if ($isTable['count'] > 0) {
                                $query = "SELECT COUNT(`uid`) AS `count` FROM {$gitem['move_table']}";
                                $count = DB::line($query);
                                $query_list[] =  array(
                                    'name'=>'группа пользователей: '.$gitem['name']." ({$count['count']})",
                                    'query'=>'SELECT * FROM `'.$gitem['move_table'].'` LIMIT {$this->recNumber}, {$this->config->userCount}',
                                    'table'=>$gitem['move_table']
                                );
                            }
                        }
                    }
                }
                
                
                $countQuery = explode('LIMIT', $config->query?$config->query:$config->default_query);
                $countQuery = str_replace('*', 'COUNT(uid) as `count`', $countQuery[0]);
                $countQuery = str_replace('$this->config', '$config', $countQuery);
                eval('$countQuery = "'.$countQuery.'";');
                //echo $countQuery.'<br>';
                
                $line = DB::line($countQuery);
                $items[] = array(
                    'path'=>$item,
                    'config'=>$config,
                    'count'=>$state,
                    'state'=>$state.'/'.@$line['count'].', '.(@$line['count']?round($state/$line['count'] * 100):0).'%',
                    'log'=>$log,
                    'query_list'=>$query_list
                );
            }
        }
        
        require_once TEMPLATES_PATH.'notify_viewList.html';
    }
    
    public function alertTmpls() {
        $query = 'SELECT * FROM gpj_alertTemplates';
        $list = DB::asArray($query);
        require_once TEMPLATES_PATH.'notify_alertTmpls.html';
    } 
    
    public function alertList() {
        $select = $this->request->getVar('select', null);
        if (count($select) > 0) {
            $action = $this->request->getVar('action', '');
            if ($action == 'clear') {
                foreach ($select as $alert_id)
                    DB::query('DELETE FROM `pjok_alerts` WHERE alert_id='.$alert_id);
            }
        }
        $query = 
'SELECT COUNT(al.alert_id), al.alert_id, at.*
FROM `pjok_alerts` AS al, `gpj_alertTemplates` AS at
WHERE at.id=al.alert_id
GROUP BY al.alert_id';
//        $query = 'SELECT * FROM `gpj_alertTemplates` AS at ORDER BY at.date'; 
        $list = DB::asArray($query);
        $column = array_keys($list); 
        require_once TEMPLATES_PATH.'notify_alertList.html';
    }
}
?>