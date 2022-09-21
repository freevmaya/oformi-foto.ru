<?
define('COUNTPERPAGE', 30);

include_once(INCLUDE_PATH.'/_edbu2.php');

class seoController extends controller {
    public function words() {
        GLOBAL $charset;
        $finders = array('yandex.ru'=>'/search/?text=',
                        'www.yandex.ru'=>'/search/?text=',  
                        'google.ru'=>'/?q=',  
                        'www.google.ru'=>'/?q=', 
                        'nova.rambler.ru'=>'/search?query=', 
                        'www.nova.rambler.ru'=>'/search?query=',  
                        'go.mail.ru'=>'/?q=', 
                        'www.go.mail.ru'=>'/?q=',  
                        'bing.com'=>'/?q=', 
                        'www.bing.com'=>'/?q=',  
                        'ask.com'=>'/?q=',  
                        'www.ask.com'=>'/?q=',  
                        'vmaya.ru'=>'/?search=');
        
        $charset = 'utf8';
        
        if ($word = @$_GET['delete']) {
            DB::query("DELETE FROM `search_questions` WHERE `word`={'$word'}");
        }
        
        if ($weight = @$_POST['weight']) {
            $query = "UPDATE `search_questions` SET `weight`=$weight WHERE `host`='{$_POST['host']}' AND `text`='{$_POST['word']}'";
            DB::query($query);
        }            
        
        
        $page = $this->svar('page', 1);
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `search_questions` ORDER BY `weight` DESC, `count` LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
        require($this->templatePath);                                
    }
    
    protected function getTypes() {
        return array('article', 'news', 'message', 'file');
    }
    
    protected function getTypesHtml($name, $current) {
        $result = "<select name=\"{$name}\">\n";
        $types = $this->getTypes();
        foreach ($types as $type) {
            $selected = '';
            if ($current == $type) $selected = 'SELECTED';
            $result .= "<option value=\"{$type}\" $selected>{$type}</option>\n";
        }
        
        return $result."</select>\n";
    }
    
    protected function getGroupsHtml($current) {
        $result = "<select name=\"group_id\">\n<option value=\"0\">none</option>\n";
        $groups = DB::asArray('SELECT * FROM gpj_textgroups');
        foreach ($groups as $group) {
            $selected = '';
            if ($current == $group['group_id']) $selected = 'SELECTED';
            $result .= "<option value=\"{$group['group_id']}\" $selected>{$group['name']}</option>\n";
        }
        
        return $result."</select>\n";
    }
    
    public function texts() {
        GLOBAL $mysqli;
        $item = array();
        $state = $this->request->getVar('state', false);
        if ($action = $this->request->getVar('action', false)) {
            $item['date'] = date('Y-m-d');
            $item['time'] = date('H:i:s');
            $item['type'] = $this->request->getVar('type', 'article');
            $item['autor'] = $this->request->getVar('autor', 'vmaya');
            $item['title'] = $this->request->getVar('title', ''); 
            $item['keywords'] = $this->request->getVar('keywords', '');
            $item['description'] = $this->request->getVar('description', '');
            $item['group_id'] = $this->request->getVar('group_id', 0);
            if (!$item['translit'] = $this->request->getVar('translit', ''))
                $item['translit'] = controller::translit(str_replace(' ', '-', $item['title']));
            
            if ($item['title'] &&  $item['translit'] && 
                ($item['text'] = $mysqli->real_escape_string($this->request->getVar('text', '')))) {
                $item['translit'] = $mysqli->real_escape_string($item['translit']); 
                $item['title'] = $mysqli->real_escape_string($item['title']); 
                $item['autor'] = $mysqli->real_escape_string($item['autor']); 
                $item['keywords'] = $mysqli->real_escape_string($item['keywords']); 
                $item['description'] = $mysqli->real_escape_string($item['description']);  
                $inmenu = ($this->request->getVar('inmenu', 0))?1:0;
                if ($action == 'add') {
                    $query = "INSERT INTO `gpj_texts` (`type`, `group_id`, `date`, `time`, `autor`, `title`, `translit`, `text`, `inmenu`, `keywords`, `description`) VALUES 
                            ('{$item['type']}', {$item['group_id']}, '{$item['date']}', '{$item['time']}', '{$item['autor']}', '{$item['title']}', '{$item['translit']}', 
                            '{$item['text']}', {$inmenu}, '{$item['keywords']}', '{$item['description']}')";
                            
                } else if ($action == 'update') { 
                    $query = "UPDATE `gpj_texts` 
                            SET `type`='{$item['type']}', `group_id`={$item['group_id']}, `autor`='{$item['autor']}', `title`='{$item['title']}', 
                                `translit`='{$item['translit']}', `text`='{$item['text']}', `inmenu`=$inmenu,
                                `keywords`='{$item['keywords']}', `description`='{$item['description']}'
                            WHERE `text_id`=".$this->request->getVar('text_id', '');
                }           
                $result = DB::query($query);
            } else $state = 'new';
        }
        if ($state) {
            if ($state == 'edit') $item = DB::line('SELECT * FROM `gpj_texts` WHERE `text_id`='.$this->request->getVar('text_id', 0));
            else if ($state == 'delete') {
                $result = DB::query('DELETE FROM `gpj_texts` WHERE `text_id`='.$this->request->getVar('text_id', 0));
                $state = false;
            }
        } 
        
        if (!$state) {
            $page = $this->svar('page', 1);
            $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `gpj_texts` ORDER BY `date` DESC LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
            $list = DB::asArray($query);
            $count = DB::line('SELECT FOUND_ROWS() AS `count`');
            $count = $count['count'];
        }
        require($this->templatePath);                                
    }
    
    protected function generateSiteMapMenu($prefix, $pror, $lang='ru') {
        GLOBAL $articleList;
        $result = '';
        
        function parseList($prefix, $items, $pror) {
            $date = date(DATE_ATOM);
            $result = '';
            foreach ($items as $url=>$item) {
                $urla = explode('"', $url);
                $urlxml = str_replace('\'', '%27', $urla[0]);
                $result .= 
"<url>
	<loc>{$prefix}oformi-foto.ru/{$urlxml}</loc>
	<lastmod>{$date}</lastmod>
	<priority>{$pror}</priority>
</url>\n";
                if (isset($item['submenu']) && is_array($item['submenu'])) {
                    $result .= parseList($prefix, $item['submenu'], round($pror * 0.8 * 10) / 10);
                }
            }
            return $result;
        }
        
        return parseList($prefix, $menuList, $pror);
    }
    
    protected function nameToUrl($name) {
        return str_replace(' ', '-', htmlentities(urlencode(trim(str_replace('\'', '-', controller::translit($name)))))); 
    }
    
    protected function genSMTemplate($prefix, $pror) {
        include_once(MAINPATH.'ss/ssconfig.php');
        
        $result = '';
        $tmpls = DB::asArray("SELECT * FROM gpj_tmplOptions WHERE `name` != '' AND `active`=1 AND `desc` != ''");
        $date = date(DATE_ATOM);
        foreach ($tmpls as $tmpl) {  
            $name = trim($this->nameToUrl($tmpl['name']));
            if (trim(preg_replace("/\W/i", '', $name)) > '') {
                $urlxml = $tmpl['tmpl_id'].'-'.$name.'.html';
                $result .= 
"<url>
	<loc>{$prefix}oformi-foto.ru/template/{$urlxml}</loc>
	<lastmod>{$date}</lastmod>
	<priority>{$pror}</priority>
</url>\n";        
        }
        }
        
        return $result;
    }
    
    public function getSMCatalog($prefix, $pror) {
        $result = '';
        $catalog = DB::asArray("SELECT * FROM gpj_groups WHERE `visible` = 1");
        $date = date(DATE_ATOM);
        foreach ($catalog as $group) {
            $urlxml = $group['group_id'].'-'.$this->nameToUrl($group['name']).'.html';
            $result .= 
"<url>
	<loc>{$prefix}oformi-foto.ru/fotoramki/{$urlxml}</loc>
	<lastmod>{$date}</lastmod>
	<priority>{$pror}</priority>
</url>\n"; 
        }
        
        return $result;
    } 
    
    public function getSMHolidays($prefix, $pror) {
        $result = '';
        $holidays = DB::asArray("SELECT * FROM gpj_holiday WHERE `disabled` = 0");
        $date = date(DATE_ATOM);
        foreach ($holidays as $hol) {  
            $name = trim($this->nameToUrl($hol['name']));
            if (trim(preg_replace("/\W/i", '', $name)) > '') {
                $urlxml = 'holiday-'.$hol['holiday_id'].'-'.$name.'.html';
                $result .= 
"<url>
	<loc>{$prefix}oformi-foto.ru/holidays/{$urlxml}</loc>
	<lastmod>{$date}</lastmod>
	<priority>{$pror}</priority>
</url>\n"; 
        }
        }
        
        return $result;
    }
    
    public function sitemap() {
        $prefix = $this->request->getVar('prefix', 'http://');
        $lang = $this->request->getVar('lang', 'ru');

        include_once(MAINPATH.'ss/ssconfig.php');
        include_once(SSPATH.'helpers/link.php');
        //include_once(SSPATH.'language/'.$lang.'/menu.php');

        if ($action = $this->request->getVar('send', false)) {
            $result = '';
            //$result .= $this->generateSiteMapMenu($prefix, 0.9, $lang);
            $result .= $this->genSMTemplate($prefix, 0.5);
//            $result .= $this->getSMCatalog($prefix, 0.6);
            $result .= $this->getSMHolidays($prefix, 0.6);
            //echo $result;
        }
        require($this->templatePath);                                
    }
}
?>                                               