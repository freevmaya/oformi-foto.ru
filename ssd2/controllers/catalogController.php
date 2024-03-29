<?

define('MINITEMCOUNT', 5);
define('CACHEEXPIRED', 60 * 60 * 8); // 8 �����

define('ITEMPERPAGE_ANY', 7 * 4);
define('ITEMPERPAGE_MOBILE', 20 * 4);
                   
include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(CONTROLLERS_PATH.'/appController.php');
include_once(SSPATH.'controllers/catalog/list.php');
include_once(SSPATH.'/helpers/templates.php');

class catalogController extends controller {
    function __construct($a_request) {
        parent::__construct($a_request);
        if (ss::$task[1] == 'templatesA') {
            ss::$task[2] = $this->getVar('groups');
            ss::$task[3] = $this->getVar('page', 1);
        }
    }
    
    protected function groupEng() {
        $geng = DB::asArray("SELECT group_id, name FROM gpj_groupsTrans", null, true);
        $result = array();
        foreach ($geng as $item) { 
            $result[$item['group_id']] = $item['name'];
        }
            
        return $result;
    } 
    
    protected function pageID() {
        return 'cat'.md5(isset(ss::$task[2])?ss::$task[2]:1);
    }  
    
    
    protected function groupEngID($groupsEng, $groupName) {
        foreach ($groupsEng as $key=>$value)
            if ($value == $groupName)
                return $key;
        
        return -1;
    }
    
    protected function view() {
        GLOBAL $contentType, $_SERVER;
        
        if ($contentType == 'json') {
            ss::$task[2] = $this->getVar('groups');
            ss::$task[3] = $this->getVar('page', 1);
            $templatePath = MOBILETEMPLATEPATH.'catalog_json.html';
        } else $templatePath = ss::$isPhone?(MOBILETEMPLATEPATH.'catalog.html'):(TEMPLATES_PATH.'viewCatalog.html');
        $this->viewFromTmpl($templatePath);
    }
    
    protected function viewFromTmpl($tmplFile) {
        GLOBAL $locale;
        
        function tmplidOnly($value) {
            return $value['tmpl_id'];
        }   
        
        $task = ss::$task;
//        print_r($task);
        
        $ITEMSPERPAGE = ss::$isPhone?ITEMPERPAGE_MOBILE:ITEMPERPAGE_ANY;
        $page = max(isset(ss::$task[3])?ss::$task[3]:1, 1);
        $start = ($page - 1) * $ITEMSPERPAGE;
        
        $task2 = isset($task[2])?$task[2]:'';
        $groupsStr = str_replace(['_', '+'], ' ', $task2);
        
        $groupIds = array();
        $search = array();
        
        $groupEng = $this->groupEng();

        $keywords = array();
        $holiday  = null;
        $holidayNoWhere = '';
        //������� ������, ������� ��� ������� ������� � ���������
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM gpj_tmplOptions tmo WHERE tmo.`tmpl_id` < 60000 ORDER BY tmo.`tmpl_id` DESC LIMIT $start,".$ITEMSPERPAGE;
                      
        if ($groupsStr && ($groupsStr != 'novie')) {          
            $groupIds = array();
            $groupStr = explode('-', $groupsStr);
            $unions = array();
            
            foreach ($groupStr as $key=>$gid) {
                if ($gid == 'holiday') break;
                else if (!is_numeric($gid)) {
                    if (($id = $this->groupEngID($groupEng, $gid)) != -1)
                        $groupIds[$key] = $id;
                    else {         
                        $sa = explode(' ', $gid);
                        foreach ($sa as $i=>$ia) {
                            $search[] = $ia;
                            $unions[] = $i == count($sa) - 1;
                        }
                    }
                } else $groupIds[$key] = $gid;
            }
            
            if ((count($groupIds) == 0) && (count($search) > 0)) {  // ��� ������
                //$this->noindex = true;
                if (isBot()) {
                    $this->capbot();
                    return;
                }
//�����         
//                if (ss::relReferer()) {            
                $searchStr = '';
                $where = '';
                $nwhere = '';
                $groupWhere = '';
                foreach ($search as $i=>$s) {
                    $ru = controller::translit($s, true);
                    $keywords[] = $ru;
                    $searchStr .= ($where?', ':'').$ru;
                    $union = $unions[$i]?' OR ':' AND ';                    
                    $where .= ($where?$union:'')."(o.`name` LIKE '%{$ru}%' OR o.`desc` LIKE '%{$ru}%')";
                    
                    $groupWhere .= ($groupWhere?$union:'')."(o.`name` LIKE '%{$ru}%' OR o.`keywords` LIKE '%{$ru}%')";
                }
                
                
                $groupQuery = "SELECT o.`group_id`, o.`name`, t.`name` as translit FROM `gpj_groups` o INNER JOIN `gpj_groupsTrans` t ON t.group_id = o.group_id WHERE $groupWhere";
                $groups = DB::asArray($groupQuery, null, true);
                                                                   
                $nids = implode(',', array_map('tmplidOnly', DB::asArray("SELECT nt.tmpl_id FROM gpj_nameTmpl nt, gpj_names2 o WHERE nt.name_id = o.name_id AND ($where)", null, true)));
                if ($nids) $where .= " OR t.tmpl_id IN ($nids)";
                
                $query = "SELECT SQL_CALC_FOUND_ROWS * FROM gpj_tmplOptions o INNER JOIN gpj_templates t ON t.tmpl_id = o.tmpl_id WHERE $where GROUP BY t.tmpl_id DESC LIMIT $start,".$ITEMSPERPAGE;
//                }
            } else {
//���������            
                if (!$groupIds) $groupIds = explode('-', $groupsStr);
                if (($groupIds[0] == 100000) || ($groupIds[0] == 'holiday')) {
                    $ITEMSPERPAGE = ITEMPERPAGE_ANY;
                    $start = ($page - 1) * $ITEMSPERPAGE;
                    
                    if (@$holiday_id = $groupIds[1]) {                                                                                                                                             
                        $query = 'SELECT SQL_CALC_FOUND_ROWS  t.tmpl_id, 0 AS group_id, o.`name` as `name`, o.`desc` as `desc`, o.save_rate, o.user_rate 
                                FROM gpj_holidayTmpls t LEFT JOIN gpj_tmplOptions o ON t.tmpl_id = o.tmpl_id 
                                WHERE t.holiday_id='.$holiday_id.' ORDER BY t.tmpl_id DESC LIMIT '.$start.','.$ITEMSPERPAGE;
                        $holiday  = DB::line("SELECT * FROM `gpj_holiday` WHERE `holiday_id`=:holiday_id", [
                            'holiday_id'=>$holiday_id
                        ]);
                        $holidayNoWhere = " AND `holiday_id`!={$holiday_id}";
                        $groupIds = array();
                    } else {
                        if (!isset($task[3])) { // ���� ��� ��������� ��������
                            $date = date('Y-m-d');
                            if ($near_holiday = DB::line("SELECT * FROM `gpj_holiday` WHERE date>='{$date}' ORDER BY `date` LIMIT 0, 1")) { // ���� ������� ��� ��������� ��������
                            
                                $near_holiday_id = $near_holiday['holiday_id'];
                                $line_count = DB::line("SELECT COUNT(`holiday_id`) AS `count` FROM `gpj_holiday` WHERE date<'{$date}'"); // �������� ���������� ���������� ����� ���
                                $page = floor($line_count['count'] / $ITEMSPERPAGE) + 1;
                                $start = ($page - 1) * $ITEMSPERPAGE; // �������� ����� ��������
                            }
                        }
                        
                        $query = "SELECT SQL_CALC_FOUND_ROWS h.deftml_id AS defTmplID, ht.tmpl_id, 0 AS group_id, h.name as hName, h.desc as hDesc, h.date as `date`, h.holiday_id as holiday_id, t.*, tmo.* 
                                    FROM gpj_holiday h 
                                    INNER JOIN gpj_holidayTmpls ht ON h.holiday_id = ht.holiday_id
                                    INNER JOIN `gpj_templates` t ON ht.tmpl_id = t.tmpl_id 
                                    LEFT JOIN gpj_tmplOptions tmo ON ht.tmpl_id = tmo.tmpl_id AND tmo.name > ''
                                    GROUP BY ht.`holiday_id` DESC ORDER BY h.`date` LIMIT $start,".$ITEMSPERPAGE;
                        $holidays = true;
                    }
                } else {
//������ �������                  
                    $where = '';
                    $count = count($groupIds);
                    foreach ($groupIds as $id) {
                        $where .= ($where?' OR ':'').'group_id='.$id;
                    }              
                    
                    $query = "SELECT SQL_CALC_FOUND_ROWS *, DATE_FORMAT(`insertTime`, '%d.%m.%Y') AS `date` FROM
                                (SELECT tmpl_id, COUNT(tmpl_id) AS `count`, COUNT(`weight`) AS `weight` 
                                FROM `gpj_templates` 
                                WHERE ($where) GROUP BY tmpl_id) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                            WHERE tg.`tmpl_id` < 60000 AND tg.`count`=$count
                            ORDER BY tg.`tmpl_id` DESC LIMIT $start,".$ITEMSPERPAGE;
                }
            }
        }                         
        

        //echo $query;
        $items = DB::asArray($query, null, true);
        
        foreach ($items as $i=>$item) 
            $items[$i]['info'] = appController::templateInfo($item);
        
        $totalPages = ceil(DB::one('SELECT FOUND_ROWS()') / $ITEMSPERPAGE);
        $menu = getCatalogList();
                                                    
        require($tmplFile);
    }
    
    public function templates() {
        ss::$task[2] = $this->getVar('groups');
        ss::$task[3] = $this->getVar('page', 1);
        $templatePath = MOBILETEMPLATEPATH.'catalog_json.html';
        $this->viewFromTmpl($templatePath);
    } 
    
    public function templatesA() {
        $this->viewFromTmpl(TEMPLATES_PATH.'catalog/catalog_json.html');
    } 
    
    public function extendItem($groupIds, $item, $imgAlt='', $firstAlt='', $appealLink=false) {
        GLOBAL $_SERVER;
        $ext = array();
        $tmplId = $item['tmpl_id'];
        
        $gid = implode(',', $groupIds);
        $tl_name = controller::translit($item['name']);
        
        $ext['tmpl_id'] = $item['tmpl_id'];
        $ext['info'] = $item['info'];
        $ext['previewURL'] = FRAMES_URLPREVIEW.$item['tmpl_id'].'.jpg';
        $ext['appLink'] = MAINURL.'/template/'.$tmplId.','.$gid.'-'.$tl_name.'.html';
        $ext['appJSLink'] = MAINURL.'/pjjs/'.$tmplId.'.html';  
        $ext['itemAlt'] = ($imgAlt?($imgAlt.'. '):'').$firstAlt.($item['name']?($item['name'].'. '):'').$item['desc'];
        $ext['iframeLink'] = BASEAPP_URL.'/pjjs/view.php?tid='.$tmplId.'&gid='.$gid.(isset($_GET['dev'])?'&dev=1':'');
//        if ($appealLink) $ext['appealLink'] = MAINURL.'/?task=user,appeal&id='.$tmplId.'&uid_autor='.$uid_autor;

        $ext['title'] = $item['name'];
        $ext['desc'] = limitWords($item['desc'], 7);
        
        if (ss::isHavePNG($tmplId)) {
            $ext['pngFileURL'] = sprintf(FRAMES_PNGURL, $tmplId);
            $ext['pngLink'] = 'http://'.$_SERVER['HTTP_HOST'].'/template/'.$tmplId.'-png,'.$gid.'-'.$tl_name.'.html';
        } 
        return $ext;  
    }
    
    public function cacheKey() {
        return ss::cacheKeyDefault();
    }
    
    public function isCached() {
        return !ss::$isAdmin;
    }   
    
    public function cacheExpire() {
        return CACHEEXPIRED;        
    }  
}
?>    
