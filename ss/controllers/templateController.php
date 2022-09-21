<?
include_once(CONTROLLERS_PATH.'/appController.php');

define('ITEMSPERPAGE', 10);

class templateController extends appController {
    private $tid;
    protected $asPNG = false;
    function __construct($a_request) {
        GLOBAL $_GET;
        if (isset(ss::$task[2]))  {
            $tid = explode('-', ss::$task[2]);
            $a_request->values['tid'] = $tid[0];
            $_GET['tid'] = $tid[0];
            
            if (isset($tid[1])) {
                if ($tid[1] == 'png') {
                    $this->asPNG = true;
                    if (isset($tid[2])) $a_request->values['gid'] = $tid[2];
                } else if (is_numeric($tid[1])) $a_request->values['gid'] = $tid[1];
            } 
            unset(ss::$task[2]); 
        }
        parent::__construct($a_request);
    } 
    
    protected function aliase() {
        return 't'.$this->request->getVar('tid', false);
    }
    
    function view() {
        $this->viewjs();
    } 
    
    function viewjs() {
        GLOBAL $locale, $sheme;
        $items = null;
        if ($tmpl_id = $this->request->getVar('tid', false)) {

    		if (!($tmpl = $this->getTemplate($tmpl_id))) {
    			$this->redirect($this->createController('article'), 'viewArticle,home');
    			return;
    		}
		
            if (($gid = $this->request->getVar('gid', 0)) == 0) {
                if (isset(ss::$task[3])) {
                    $gida = explode('-', ss::$task[3]);
                    if (is_numeric($gida[0])) $tmpl['gid'] = $gida[0];
                }
            } else if (is_numeric($gid)) $tmpl['gid'] = $gid;
            
            $this->meta['page-image'] = $tmpl['preview'];  

            if ($this->asPNG) $this->title = $locale['PHOTOFRAMEPNG'];
            else $this->title = $locale['PHOTOFRAME']; 
            $this->title .= $tmpl['name']?(' '.$tmpl['name']):(' №'.$tmpl_id);

            if ($this->asPNG) $this->description = $locale['PHOTOFRAMEPNGDESC'];
            else $this->description =  $locale['PHOTOFRAMEDESC']; 

            $this->description .= $tmpl['desc']?(' '.$tmpl['desc']):(' №'.$tmpl_id);
            
            if ($tmpl['desc']) $alt .= ', '.$tmpl['desc'];
            if ($this->asPNG) $addWhere = 'AND tmpl_id > '.FIRSTPNGID;
            
            if ($tmpl['gid']) {
                $query = "SELECT SQL_CALC_FOUND_ROWS *, DATE_FORMAT(`insertTime`, '%d.%m.%Y') AS `date` FROM
                            (SELECT tmpl_id, COUNT(tmpl_id) AS `count`, COUNT(`weight`) AS `weight` 
                            FROM `gpj_templates` 
                            WHERE group_id={$tmpl['gid']} AND tmpl_id != {$tmpl_id} $addWhere GROUP BY tmpl_id) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                        WHERE tg.`tmpl_id` < 60000 AND tg.`count`=1
                        ORDER BY tmo.`user_rate` DESC LIMIT 0,".ITEMSPERPAGE;
                $items = DB::asArray($query);
                $groupIds = array($tmpl['gid']);
                foreach ($items as $i=>$item)
                    $items[$i]['info'] = appController::templateInfo($item);
                    
                //$group = DB::line('SELECT * FROM ');
            }     

            $this->headers[] = '<link rel="canonical" href="'.$this->getBaseURL().DS.'template'.DS.$tmpl_id.'.html"/>';
        }
    
        //if (ss::$isPhone) require(MOBILETEMPLATEPATH.'viewjs'.($_GET['dev']?'':'_old').'.html');
        if (ss::$isPhone) require(MOBILETEMPLATEPATH.'viewjs.html');
        else if ($this->asPNG) require(TEMPLATES_PATH.'viewpng.html'); 
        else require(TEMPLATES_PATH.'viewjs.html');
    }
}    
?>
