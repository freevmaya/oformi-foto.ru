<?

define('GAMEURL', '');
define('DEFCOUNT', 20);

include_once(INCLUDE_PATH.'/_edbu2.php');

GLOBAL $REFS;
$REFS = array(
1=>array('ctype'=>array(
    1=>'SEARCH',
    2=>'HOLIDAY',
    3=>'EFFECT',
    4=>'INVITE',
    5=>'CATALOG',
    6=>'FRIENDPHOTO',
    7=>'SAVE',
    8=>'TOOLS',
    9=>'WAITSEC',
    10=>'TOOLS',
    11=>'PHOTORESCALE',
    12=>'SOLVENCY',
    13=>'REFERER',
    14=>'REFPLACE',
    15=>'ERRORCONNECT',
    16=>'PAYMORE',
    17=>'GIFSAVE'
)),
12=>array('value'=>array(
    1=>'Платящий и состоятельный',
    4=>'Неплатящий но состоятельный',
    8=>'Состоятельный и потенциальный',
    12=>'Неплатящий но играющий',
    16=>'Неплатящий и неиграющий'
)),
14=>array('context'=>array(
    'direct',
	'catalog',
	'banner',
	'banner_text',
	'banner_img',
	'friend_invitation',
	'friend_feed',
	'friend_notification',
	'present',
	'present_tooltip',
	'common_apps',
	'user_apps',
	'friend_apps',
	'recommended_apps',
	'new_apps',
	'top_apps',
	'pay_attention_apps',
	'app_search_apps',
	'shops_catalog_apps',
	'our_apps',
	'short_apps'
)));

class pj_stateController extends controller {
    public function gameCurrentBan() {
        $list = DB::asArray("SELECT * FROM `pjok_ban` b LEFT JOIN pjok_game g ON g.id = b.banContent WHERE g.time > DATE_ADD(NOW(), INTERVAL -1 DAY)");
        require($this->templatePath);
    }
    
    public function userGroups() {
        include_once(MODEL_PATH.'pj_model_ok/user_groups.php');
        include_once(MODEL_PATH.'pj_model_ok/user_group_manager.php');
        
        set_time_limit(0);
        
        $user_group     = userGroup($this->svar('prefix', 'pjok'));
        $manager        = new UGManager($user_group);
        $select_group   = array();
        $unionMode      = $this->svar('unionMode', 2);
        $reportType     = $this->svar('report_type', 1);
        $showQuery      = $this->request->getVar('showQuery', 0);
        $key            = 1000;
        $analize        = $this->request->getVar('analize', false);
        $distance       = 'pjok_alerts';
        $alert_id       = $this->request->getVar('alert_id', 1); 
        $moveData       = $this->request->getVar('moveData', 0);       
        
        $alerts         = DB::line("SELECT COUNT(uid) AS `count` FROM pjok_alerts WHERE alert_id=$alert_id");
        $alertTmpl      = DB::line("SELECT tp.*, ty.name AS typeName FROM gpj_alertTemplates tp INNER JOIN gpj_alertTypes ty ON tp.type=ty.id WHERE tp.id=$alert_id");
        $alert_count    = $alerts['count'];
        
        foreach ($user_group as $key=>$group) {
            $user_group[$key]['query'] = $query = $manager->queryFromType($key, $reportType, $distance, $alert_id);
            
            //$select_group[$key] = $this->request->getVar('group'.$key, 0);
            if ($this->request->getVar('group'.$key, 0) && $query) {
            
                if (isset($group['values'])) {
                    $query = sprintf($query, $this->request->getVar('select_'.$key.'_0', 0));
                }
                //$query = $this->request->getVar('group'.$key.'_query', $query); 
            
                $sqlQuery = $analize?('EXPLAIN '.$query):$query;
                
                if ($reportType == 2) $sqlQuery = $manager->query_to_alert($query, 'pjok_alerts', $alert_id);
                
                if ($showQuery) $result = $sqlQuery;
                else {
                    if ($analize) $result = DB::line($sqlQuery);
                    else { 
                        if ($reportType == 2) {                     // Подготовка к сообщению
                            $result = DB::query($sqlQuery);
                        } else if ($reportType == 3) {              // Перемещать
                            //echo $sqlQuery;
                            $manager->prepareMove($key);
                            $result = DB::query($sqlQuery);
                        } else {                                    // Запрос-отчет
                            $cache_key = md5($sqlQuery);
                            if (!$result = MCache::get($cache_key)) {
                                $result = DB::line($sqlQuery);
                                MCache::set($cache_key, $result, 60 * 60 * 24);
                            }
                        }   
                    }
                }
            
                $select_group[$key] = array_merge($user_group[$key], array('result'=>$result));
            }
        }   
        
        if ($otherQuery = $this->request->getVar('otherquery', '')) {
            $select_group[$key + 1] = array(
                'name'=>'Другой запрос',
                'result'=>DB::line($otherQuery),
                'color'=>'#444'
            );
        }
        
        $alerts = DB::asArray('SELECT * FROM `gpj_alertTemplates`');
        require($this->templatePath);
    }
    
    public function appeals() {
        if ($id = $this->request->getVar('id', false)) {
            DB::query('DELETE FROM ev_appeal WHERE id IN ('.implode(',', $id).')');
            
        }
        $list = DB::asArray('SELECT * FROM ev_appeal'); 
        require($this->templatePath);
    }
        
    public function stat() {
        GLOBAL $REFS;
        
        include(MODEL_PATH.'pj_model/tmpl_cats.php');
        $as_graph = $this->svar('as_graph', 0);
        $type = $this->request->getVar('type', 1);
        $refs = null;
        
        $dates = DB::line("SELECT MIN(`date`) AS min_date, MAX(`date`) AS max_date FROM `pjok_stat`");
         
        $groups = '';
        $fields = '';
        if ($as_graph) {
            $groups = '`date`,';
            $fields = 'date,';
        }
                
        $refs = @$REFS[$type];
        if ($type == 1) {
            $groups .= '`ctype`'; 
            $fields .= 'ctype,COUNT(value) AS count';
            $order = $as_graph?'`ctype`, `date`, `count`':'`count` DESC, `ctype`';
            $query = 'SELECT '.$fields.' FROM `pjok_stat` GROUP BY '.$groups.' ORDER BY '.$order.' DESC';
        } else if ($type == 2) {
            $groups .= '`value`';        
            $fields .= 'value,COUNT(value) AS count';
            $order = $as_graph?'`value`, `date`, `count`':'`count`';
            $query = 'SELECT '.$fields.' FROM `pjok_stat` WHERE ctype=5 GROUP BY '.$groups.' ORDER BY '.$order.' DESC';
            $refs = array('value'=>$cats);
        } else if ($type == 3) {
            $groups .= '`context`';   
            $fields .= 'context,COUNT(value) AS count';
            $order = $as_graph?'`context`, `date`, `count`':'`count`';
            $query = 'SELECT '.$fields.' FROM `pjok_stat` WHERE ctype=1 GROUP BY '.$groups.' ORDER BY '.$order.' DESC';  
        } else if ($type == 4) {
            $groups .= '`card_id`';        
            $fields .= 'card_id,count(uid) AS `count`';
            $order = $as_graph?'`card_id`, `date`, `count`':'`count`';
            $query = 'SELECT '.$fields.' FROM `pjok_send` WHERE `date`>=NOW() - INTERVAL 1 DAY GROUP BY '.$groups.' ORDER BY '.$order.' DESC LIMIT 0, 20';  
        } else if ($type == 12) {
            $groups .= '`value`';
            $fields .= 'value, COUNT(value) AS count';
            $order = $as_graph?'`value`, `date`, `count`':'`count`';
            $query = 'SELECT '.$fields.' FROM `pjok_stat` WHERE ctype=12 GROUP BY '.$groups.' ORDER BY '.$order.' DESC';
        } else if ($type == 14) {
            $groups .= '`context`';
            $fields .= 'context,COUNT(value) AS count';
            $order = $as_graph?'`context`, `date`, `count`':'`count`';
            $query = 'SELECT '.$fields.' FROM `pjok_stat` WHERE ctype=14 GROUP BY '.$groups.' ORDER BY '.$order.' DESC';
        }
          
        if ($as_graph) {
            
            $items = DB::asArray($query);
            //print_r($items);
            
            $fa = explode(',', $fields);
            $fields = array('Дата');
        	$result = array();
            $di = 0;
            $curType = -1;
            $gfield = $fa[1];
        	foreach ($items as $row) {
                if ($curType != $row[$gfield]) {
                    $curType = $row[$gfield];
                    $di = 0;
                    $fields[] = strval(@$refs[$gfield][$row[$gfield]]);
                }  
                //print_r($row);
                
                $value = intval($row['count']);
                if (!isset($result[$di])) $result[$di] = array($row['date'], $value);
                else $result[$di][] = $value;
                $di++;
            }
            
            $fcount = count($fields);
            for ($i=0; $i<count($result); $i++) {
                $rcount = count($result[$i]);
                if ($rcount < $fcount)
                    $result[$i] = array_merge($result[$i], array_fill(0, $fcount - $rcount, 0));      
            }                      
        } else {          
            $result = DB::asArray($query);
        } 
        require($this->templatePath);
    } 
}
?>