<?

define('GC_ITEMPERPAGE', 8);
define('DEFAULTGROUPID', 1);
define('CLOTH_PREVIEWWIDTH', 440);
define('CLOTH_PREVIEWHEIGHT', 440);

class gameBaseController extends controller {
    protected $task_vars;
    
    function __construct($a_request) {
        parent::__construct($a_request);
        $this->parseTask();
    }    
    
    public function ajax() {
        $method = $this->getVar('method', 'defaultMethod');
        echo json_encode($this->$method());  
    }
    
    protected function defaultTask2() {
        return 1;
    }
    
    protected function parseTask() {
        $task = ss::getTask(2, $this->defaultTask2());
        $p = explode('-', $task);
        if (count($p) > 1) {
            for ($i=0; $i<count($p); $i+=2) {
                $v = $this->escape($p[$i + 1]);
                $this->task_vars[$p[$i]] = $v;
                if (isset($this->$p[$i]) && $v) $this->$p[$i] = $v;
            }
            ss::trace($this->task_vars);
        } 
    }  
    
    public function getVar($varName, $default=0) {
        if (isset($this->task_vars[$varName])) 
            return $this->task_vars[$varName];
        return parent::getVar($varName, $default);
    }  
    
    protected function defaultMethod() {
        return 0;
    }
     
    protected function gameImageURL($item) {
        return GAMEURL.$item['group_id'].'/'.CLOTH_PREVIEWWIDTH.'/'.$item['game_id'].'.jpg';
    }
    
    protected function gameInfo($item, $sep='<br>') {
        GLOBAL $locale;
        return $locale['ADDTIME'].': '.$this->gameTime($item).$sep.
                $locale['STATE'].': '.$locale['GAMESTATES'][$item['state']].$sep.
                $locale['RATE'].': '.$item['votes'].
                ($item['all_votes']?($sep.$locale['ALLVOTES'].': '.$item['all_votes']):'').
                ($item['count_votes']?($sep.$locale['AVERAGERATING'].': '.(round($item['votes']/$item['count_votes']))):'');
    }
        
    protected function gameTime($item) {
        return date('d.m.Y H:i', strtotime($item['time']));
    }
    
    protected function remove() {
        $res = 0;
        if ($id = $this->getVar('id')) {
            $res = DB::query("UPDATE ".DBPREF."game SET `state`='remove' WHERE game_id=$id");         
        }
        
        echo json_decode($res);
    }
}