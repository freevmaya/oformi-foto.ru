<?
define('COUNTPERPAGE', 30);
define('TMPLSPREVIEW', 'pj/jpg_preview/');
define('TMPLS120', 'pj/preview120/'); 
define('TMPLSFULL', 'JPG/');

define('TEMPLATESURL', Admin::sheme().'://oformi-foto.ru/'.TMPLSPREVIEW.'i');
define('TEMPLATES120URL', Admin::sheme().'://oformi-foto.ru/'.TMPLS120);
define('TEMPLATESFULLURL', Admin::sheme().'://oformi-foto.ru/'.TMPLSFULL);
define('FRAMEPREVIEWURL', Admin::sheme().'://oformi-foto.ru/385/');
define('HOLESURL', Admin::sheme().'://oformi-foto.ru/pj/holes/%s/holes.js');

define('FILE_PATH', DATA_PATH.'files/');
define('FILE_URL', DATA_URL.'files/');

GLOBAL $AUTORS, $LANGS, $LANG_LABELS; 
$AUTORS = array(
    0=>'Неизвестен',
    '1731353195984349210'=>'Света',
    '12700092426321616713'=>'Алена',
    '8062938299454250872'=>'Вадим',
    '1079135306711455982'=>'Вика'
);

include_once(INCLUDE_PATH.'/_edbu2.php');

class catalogController extends controller {
    protected function state_delete() {
        return false;//DB::query('DELETE FROM `gpj_groups` WHERE `group_id`='.$this->request->getVar('group_id', 0));
    }
    
    protected function state_new() {
        return array('name'=>'Новая группа');
    } 
    
    protected function state_edit() {
        $lang   = admin::lang();
        if ($lang == 'ru') $lang = "";
        else $lang = ucfirst($lang);

        return DB::line("SELECT g.*, t.name as translit FROM gpj_groups{$lang} g INNER JOIN gpj_groupsTrans t ON g.group_id=t.group_id WHERE g.group_id=".$this->request->getVar('group_id', 0));
    } 
    
    protected function state_update() {
        GLOBAL $mysqli;
        $lang   = admin::lang();
        if ($lang == 'ru') $lang = "";
        else $lang = ucfirst($lang);
    
        $group_id = $this->request->getVar('group_id', false);
        if (($name = $this->getStrValue('name', '')) && ($group_id !== false)) {
            $translit = $mysqli->real_escape_string($this->request->getVar('translit', controller::translit($name))); 
            $small_desc = $mysqli->real_escape_string($this->request->getVar('small_desc', '')); 
            $desc = $mysqli->real_escape_string($this->request->getVar('desc', ''));
            $keywords = $mysqli->real_escape_string($this->request->getVar('keywords', ''));  
            
            $tquery = "UPDATE gpj_groupsTrans SET `name`='{$translit}' WHERE `group_id`={$group_id}";
            $query = "UPDATE `gpj_groups{$lang}` SET `name`='{$name}', `small_desc`='{$small_desc}', `keywords`='{$keywords}', `desc`='{$desc}' WHERE `group_id`={$group_id}";
            return DB::query($query) && DB::query($tquery);
        } else return false;
    }
    
    protected function getStrValue($name, $default='') {
        GLOBAL $mysqli;
        return $mysqli->real_escape_string($this->request->getVar($name, $default));
    }
    
    public function groups() {
        if ($state = $this->request->getVar('state', false)) {
            $method = 'state_'.$state; 
            $result = $this->$method();
        }            
    
        $page   = $this->svar('cg-page', 1);
        $lang   = admin::lang();
        if ($lang == 'ru') $lang = "";
        else $lang = ucfirst($lang);

        $list   = DB::asArray("SELECT SQL_CALC_FOUND_ROWS g.*, t.name as translit FROM gpj_groups{$lang} g INNER JOIN gpj_groupsTrans t ON g.group_id=t.group_id ORDER BY group_id LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE);
        $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count  = $count['count'];
        require($this->templatePath);
    }
    
    protected function getTmplList() {
        preg_match_all("/([\d]+),*/i", $this->request->getVar('items', ''), $list);
        return $list[1];
    }
    
    protected function tmpls_action_add() {
        $result = false;
        if ((count($list = $this->getTmplList()) > 0) && ($group_id = $this->request->getVar('group_id', 0))) {
            $values = '';
            foreach ($list as $item)
                $values .= (($values?',':'').'('.$group_id.','.trim($item).')');
                
            $query = "REPLACE gpj_templates (group_id, tmpl_id) VALUES $values";
            $result = DB::query($query);
        }
        return $result;
    }
    
    protected function tmpls_action_delete() {
        $result = false;
        if (count($list = $this->getTmplList()) > 0) {
            $group_id = $this->request->getVar('group_id', 0);
            $values = '';
            foreach ($list as $item)
                $values .= (($values?' OR ':'').($group_id?('(group_id='.$group_id.' AND '):'(').'tmpl_id='.trim($item).')');
            $query = "DELETE FROM gpj_templates WHERE $values";
            $result = DB::query($query);
        }
        return $result;
    }
    
    protected function tmpls_action_view() {
        $result = '';
        if ($group_id = $this->request->getVar('group_id', 0)) {
            $list = DB::asArray("SELECT tmpl_id FROM gpj_templates WHERE group_id={$group_id}");
            foreach ($list as $item)
                $result .= ($result?',':'').$item['tmpl_id'];
        }
        return $result; 
    }
    
    public function tmpls() {
        if ($action = $this->request->getVar('action', false)) {
            $method = 'tmpls_action_'.$action; 
            $result = $this->$method();
        } 
        $groups = DB::asArray("SELECT group_id, name FROM gpj_groups");
        require($this->templatePath);
    }
    
    protected function checkSize($filePath, $srcPath, $maxSize, $quality=80) {
        $image = imagecreatefromjpg($srcPath);
        echo $srcPath.'<br>';
        
        $size = array(imagesx($image), imagesy($image));
        if (($size[0] > $maxSize[0]) && ($size[1] > $maxSize[1])) {
            $scale = min($maxSize[0]/$size[0], $maxSize[0]/$size[1]);
            $newsize = array(round($size[0] * $scale), round($size[1] * $scale));
            
            $tmp = imagecreatetruecolor($newsize[0], $newsize[1]);
            $fillColor = imagecolorallocate($result, 255, 255, 255);
            
            imagefilledrectangle($tmp, 0, 0, $newsize[0], $newsize[1], $fillColor);
            imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newsize[0], $newsize[1], $size[0], $size[1]);
            imagedestroy($image);
            $image = $tmp;
            
            imagejpeg($image, $filePath, $quality);
        }
        return $image;
    }
    
    protected function refreshImages($tmpl_id) {
/*
define('TMPLSPREVIEW', 'pj/jpg_preview/');
define('TMPLS120', 'pj/preview120/'); 
define('TMPLSFULL', 'pj/JPG/');
*/    
        $previewPATH = MAINPATH.TMPLSPREVIEW."i{$tmpl_id}.jpg";
        $preview120PATH = MAINPATH.TMPLS120."{$tmpl_id}.jpg";
        $fullPATH = MAINPATH.TMPLSFULL."{$tmpl_id}.jpg";

        if (!file_exists($previewPATH)) {
            if (file_exists($preview120PATH)) $this->checkSize($previewPATH, $preview120PATH, array(85, 85));
            else if (file_exists($fullPATH)) $this->checkSize($previewPATH, $fullPATH, array(85, 85)); 
        }
        
        if (!file_exists($preview120PATH)) {
            if (file_exists($fullPATH)) $this->checkSize($preview120PATH, $fullPATH, array(120, 120));
            else if (file_exists($previewPATH)) $this->checkSize($preview120PATH, $previewPATH, array(120, 120)); 
        }
    }
    
    public function tmpl_edit() {
        $tmpl_id = $this->request->getVar('tmpl_id');
        $files = DB::asArray("SELECT * FROM gpj_files WHERE tmpl_id={$tmpl_id}");
        $autors = DB::asArray('SELECT * FROM gpj_autors WHERE uid = 0');
         
        $kfiles = array();
        
        if ($this->svar('hole_visible', false))
            $holes = @file_get_contents(sprintf(HOLESURL, $tmpl_id));
        else $holes = 'none';
        
//        $this->refreshImages($tmpl_id);
        
        foreach ($files as $file) $kfiles[$file['type']] = $file;
        
        $item = DB::line("SELECT *, a.autor_id AS aid FROM gpj_templates t LEFT JOIN gpj_autorTmpl a ON a.tmpl_id=t.tmpl_id INNER JOIN gpj_tmplOptions o ON t.tmpl_id=o.tmpl_id WHERE o.tmpl_id={$tmpl_id}");
        if ($item['width'] == 0) $this->reset_size($item['tmpl_id']);
        $result = array(
            'item'=>$item,
            'files'=>$kfiles,
            'cats'=>DB::asArray("SELECT g.group_id, g.name as name, t.tmpl_id AS isGroup, gp.name AS part_name 
                    FROM gpj_groups g INNER JOIN gpj_parts gp ON gp.part_id = g.part_id
                                    LEFT JOIN gpj_templates t ON t.group_id=g.group_id AND t.tmpl_id=".$this->request->getVar('tmpl_id')),
            'autors'=>$autors,
            'holes'=>$holes            
        );
        return $result;
    }
    
    protected function reset_size($tmpl_id) {
        $result = json_decode(file_get_contents("https://oformi-foto.ru/pj/info.php?id={$tmpl_id}"));
        if ($result && (count($result) == 2)) {
            DB::query("UPDATE gpj_tmplOptions SET `width`={$result[0]}, `height`={$result[1]} WHERE tmpl_id={$tmpl_id}");
        }
    }
    
    private function uploadFile($tmpl_id, $ext) {
        GLOBAL $_FILES;
        
        $field = "file".strtoupper($ext); 
        if (isset($_FILES[$field]) && $_FILES[$field]['name']) {
            if (is_uploaded_file($_FILES[$field]["tmp_name"])) {
                $filePath = FILE_PATH.$ext.'/'.$tmpl_id.'.'.$ext;
                move_uploaded_file($_FILES[$field]["tmp_name"], $filePath);
                $size = filesize($filePath); 
                DB::query("REPLACE gpj_files (`tmpl_id`, `type`, `storage`, `date`, `size`) VALUES ($tmpl_id, '{$ext}', 0, NOW(), {$size})");
            } else echo 'error upload file ' + $_FILES[$field]["tmp_name"]; 
        }
    }
    
    private function uploadFiles($tmpl_id) {
        $this->uploadFile($tmpl_id, 'psd');
        $this->uploadFile($tmpl_id, 'png');
    }
    
    public function tmpl_update() {
        $autor_id = $this->request->getVar('autor_id', null);

        if (($tmpl_id = $this->request->getVar('tmpl_id', 0)) && $autor_id) {
            $name = $this->getStrValue('name');
            $desc = $this->getStrValue('desc');
            $active = $this->request->getVar('active', false)?1:0;
            $lang = $this->request->getVar('lang', 'any');

            $weight = ($lang == 'rus')?2:1;
                        
            if ($cats = $this->request->getVar('cats', null)) {
                DB::query("DELETE FROM gpj_templates WHERE tmpl_id={$tmpl_id}");
                $values = '';
                foreach ($cats as $cat) $values .= ($values?',':'')."({$cat},{$tmpl_id},{$weight})";
                $query = "REPLACE gpj_templates (`group_id`, `tmpl_id`, `weight`) VALUES $values";
                DB::query($query);
            }
                        
            DB::query("DELETE FROM gpj_autorTmpl WHERE tmpl_id={$tmpl_id}");
            if ($aid = $this->request->getVar('aid', false) | $this->request->getVar('uid')) {
                DB::query("REPLACE gpj_autorTmpl (`tmpl_id`, `autor_id`) VALUES ({$tmpl_id}, {$aid})");
                if ($aid > 1000) {
                    DB::query("REPLACE gpj_autors (`autor_id`, `uid`, `name`) VALUES ({$aid}, {$aid}, '{$aid}')");
                }
            }
            
            $query = "UPDATE gpj_templates SET `weight`={$weight} WHERE tmpl_id={$tmpl_id}"; 
            if (DB::query($query)) {
                $query = "UPDATE gpj_tmplOptions SET `name`='{$name}', `desc`='{$desc}', `autor_id`={$autor_id}, `active`={$active}, `lang`='{$lang}' WHERE tmpl_id={$tmpl_id}";
                $result = DB::query($query);
                $this->uploadFiles($tmpl_id);
                return $result;
            } else return false;
        } else return false;
    }  
    
    public function tmpl_delete() {
        if ($tmpl_id = $this->request->getVar('tmpl_id')) {
            return DB::query("DELETE FROM gpj_templates WHERE tmpl_id=$tmpl_id") && 
                    DB::query("DELETE FROM gpj_tmplOptions WHERE tmpl_id=$tmpl_id");
        }
        return false;
    }  
    
    public function tmpl_set_cat() {
        if (($cat = $this->request->getVar('cat')) && ($tmpl_id = $this->request->getVar('tmpl_id'))) {
            if ($this->request->getVar('is_set') == 1) $query = "REPLACE gpj_templates (group_id, tmpl_id) VALUES ($cat, $tmpl_id)";
            else $query = "DELETE FROM gpj_templates WHERE group_id=$cat AND tmpl_id=$tmpl_id";
             
            return DB::query($query);
        } 
        return 'false';
    }
    
    public function tmpl_set_rus() {
        if ($tmpl_id = $this->request->getVar('tmpl_id')) {
            $lang = ($this->request->getVar('is_set') == 1)?'rus':'any'; 
            $query = "REPLACE gpj_tmplOptions (lang, tmpl_id) VALUES ('{$lang}', {$tmpl_id})";
             
            return DB::query($query);
        } 
        return 'false';
    }
    
    public function edit() {
        GLOBAL $LANGS, $LANG_LABELS; 
        $childGroups = array(101, 102, 103);
            
        $result = false;
        
        $filter_lang = array();
        $select_cats = array();
        $cur_autor = 0;
        $childOnly = 0;
        $operand = $this->svar('operand', 0);
        $view_mode = $this->svar('view_mode', 'medium');
        
        $set_cat_id = 304; 
        $set_cat_name = 'Есть персонаж';
        
        if ($state = $this->request->getVar('state')) {
            if ($this->request->getVar('update')) $this->tmpl_update(); 
            $method = 'tmpl_'.$state;
            $result = $this->$method();
        } else {
        
            if ($this->request->getVar('clearFilter')) {
                $this->setSession('filter_lang', null);
                $this->setSession('cats', null);
                $this->setSession('ce-page', 1);
                $this->setSession('autor', 0);
                $this->setSession('childOnly', 0);
            } else {
                $filter_lang = $this->svar('filter_lang', array());
                $select_cats = $this->svar('cats', array());
                $cur_autor = $this->svar('autor', 0);
                $childOnly = ($this->svar('childOnly', 0) == 1);
            }
        
            $cats = DB::asArray("SELECT g.name, g.group_id, gp.name AS part_name FROM gpj_groups g INNER JOIN gpj_parts gp ON gp.part_id = g.part_id ORDER BY gp.part_id");
            foreach ($cats as $i=>$catItem) 
                $cats[$i]['isGroup'] = in_array($catItem['group_id'], $select_cats);                    
        }
        
        if ($this->request->getVar('bigImage', -1) > -1) {
            $this->setSession('bigImage', $bigImage = $this->request->getVar('bigImage'));
        } else $bigImage = $this->getSession('bigImage', 0);
        
        
        $where = '';
        $lwhere = '';
        if ($filter_lang) {
            foreach ($filter_lang as $item) {
                $lwhere .= ($lwhere?' OR ':'')."o.lang = '{$item}'"; 
            }
            $where = "($lwhere)";
        }
        
        $gwhere = '';
        if ($select_cats) {
            foreach ($select_cats as $catItem) {
                $gwhere .= ($gwhere?' OR ':'')."t.group_id = {$catItem}"; 
            }
            
            $gwhere = '('.$gwhere.')';
            
            if ($operand == "NOT") 
                $gwhere = "(t.tmpl_id NOT IN (SELECT tmpl_id FROM gpj_templates t WHERE {$gwhere}))";
            else {
                $wcount = count($select_cats);
                if ($operand == "AND") $gwhere = "((SELECT COUNT(t.tmpl_id) FROM gpj_templates t WHERE {$gwhere} AND t.tmpl_id=o.tmpl_id)={$wcount})";
            }
            
            $where .= ($where?' AND ':'').$gwhere;
        }
        
        //<--Показывать только детские 
        if ($childOnly) {
            $ch_where = '';
            foreach ($childGroups as $id)  
                    $ch_where .= ($ch_where?' OR ':'').'t.group_id='.$id;
            $where .= ($where?' AND ':'')."({$ch_where})";
        }
        //Показывать только детские-->
        
        if ($cur_autor) $where .= ($where?' AND ':'').'at.autor_id='.$cur_autor;
        
        if (!$state) {
            if ($where) $where = 'WHERE '.$where;
            
            $page   = $this->svar('ce-page', 1);
            $autors = DB::asArray("SELECT * FROM gpj_autors");
            $query = "SELECT SQL_CALC_FOUND_ROWS o.tmpl_id, t.group_id, o.name, o.desc, o.insertTime, o.autor_id, o.lang, at.autor_id AS og_autor_id, a.name AS autor_name, 
                                    (SELECT group_id FROM gpj_templates WHERE group_id=$set_cat_id AND tmpl_id=o.tmpl_id) AS group_id 
                                FROM gpj_tmplOptions o INNER JOIN gpj_templates t ON t.tmpl_id=o.tmpl_id
                                    LEFT JOIN gpj_autorTmpl at ON t.tmpl_id=at.tmpl_id 
                                    LEFT JOIN gpj_autors a ON at.autor_id=a.autor_id
                                $where 
                                GROUP BY t.tmpl_id ORDER BY t.tmpl_id DESC LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
            
                                               
            $list   = DB::asArray($query);
            $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
            $count  = $count['count'];
        }
        
        if (($state == 'set_cat') || ($state == 'set_rus')) echo $result;
        else require($this->templatePath);
    }  
    
    public function add() {
        require($this->templatePath);
    } 
}
?>