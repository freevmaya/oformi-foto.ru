<?
    define('STORAGE_URL', 'http://fotoprivet.com/games/data/temp_storage.json');
    define('NEWGROUPID', 14200007);
    define('HOLIDAYGROUP', 14200008);
    
    $childGroups = array(41, 101, 102, 103);
    $hgroups = array(300, 301);
    
    class dataModel {
        private  $request;
        function __construct($request) {
            $this->request = $request;
        }
        
        protected function getList() {     
            GLOBAL $childGroups, $hgroups;        
            
            $query  = '';
            $lang   = strtolower($this->getLang('rus'));
            $page   = $this->getVar('page', 1);
            $groups = $this->getVar('groups', '40');
            $itemCount = $this->getVar('count', 100);
            $start = ($page - 1) * $itemCount;
            
            $langWhere = '';
            
            if ($groups) {
                $langWhere = "(tmo.`lang`='any' OR tmo.`lang`='{$lang}')";
                
                $groupIds = explode('-', $groups);
                $count = count($groupIds);
                if ($count == 1) {
                    if ($groupIds[0] == NEWGROUPID) {
                        $query = "SELECT SQL_CALC_FOUND_ROWS id AS tmpl_id FROM gpj_templates_new tn 
                        INNER JOIN gpj_templates t ON t.tmpl_id = tn.id 
                        INNER JOIN gpj_tmplOptions tmo ON tn.id = tmo.tmpl_id 
                        WHERE tmo.`active`=1 AND $langWhere GROUP BY id ORDER BY id DESC" ;
                    } else if ($groupIds[0] > HOLIDAYGROUP) {
                        $holiday_id = $groupIds[0] - HOLIDAYGROUP;
                        $query = "SELECT SQL_CALC_FOUND_ROWS ht.tmpl_id FROM gpj_holidayTmpls ht
                        INNER JOIN gpj_tmplOptions tmo ON ht.tmpl_id = tmo.tmpl_id
                        WHERE ht.holiday_id={$holiday_id} AND $langWhere ORDER BY ht.tmpl_id DESC LIMIT $start, $itemCount";
                    } 
                }
                
                $where = '';
                foreach ($groupIds as $id)  
                    $where .= ($where?' OR ':'').'t.group_id='.$id;
                    
                $g_where = '';
                foreach ($childGroups as $id)  
                    $g_where .= ($g_where?' OR ':'').'tc.group_id='.$id;
                    
                $hwhere = '';
                foreach ($hgroups as $id)
                     $hwhere .= ($hwhere?' OR ':'').'group_id='.$id;
                     
                if ($query == '') {
                    $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id FROM
                                (SELECT t.tmpl_id, COUNT(t.tmpl_id) AS `count`, COUNT(t.`weight`) AS `weight` 
                                FROM `gpj_templates` t INNER JOIN `gpj_templates` tc ON t.tmpl_id = tc.tmpl_id AND ({$g_where}) 
                                WHERE $where AND t.`tmpl_id` < 60000 GROUP BY t.tmpl_id ORDER BY t.tmpl_id DESC) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                            WHERE tg.`count`>=$count AND tmo.`active`=1 AND $langWhere 
                            ".($hwhere?"AND (tmo.tmpl_id NOT IN (SELECT tmpl_id FROM gpj_templates WHERE $hwhere))":'')." 
                            LIMIT $start, $itemCount";
                }
                
            } else {
                $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id FROM `gpj_templates` tmpls INNER JOIN gpj_tmplOptions tmo ON tmpls.tmpl_id = tmo.tmpl_id 
                            WHERE tmpls.`tmpl_id` < 60000 AND tmo.`active`=1 AND $langWhere GROUP BY tmpls.`tmpl_id` DESC LIMIT $start,".$itemCount;
                $groupIds = array();
            }
            
            //trace($query);
            
            $items = DB::asArray($query);
            $totalPages = ceil(query_one('SELECT FOUND_ROWS()') / $itemCount);
            
            $list = array();
            foreach ($items as $item) {
                $list[] = $item['tmpl_id'];
            }
            
            if ($totalPages > $page) {
                $list[] = 'page='.($page + 1).'&count='.$itemCount.'&groups='.$groups;
            }
            return $list;
        }
        
        protected function aconv($array, $fields, $sourceCharset, $descCharset) {
            foreach ($array as $key=>$item) {
                foreach ($fields as $field)
                    $array[$key][$field] = iconv($sourceCharset, $descCharset, $item[$field]); 
            }
            
            return $array;
        }
        
        protected function getLang($def='') {
            $lang = $this->getVar('lang', $def);
            if ($lang == 'Rus') $lang = $def;
            return $lang;
        }
        
        protected function getCats_rus() {
            GLOBAL $charset;
            $charset = 'utf8';
            
            $newNames = array('Rus'=>'Новые', 'Eng'=>'New', 'Zh'=>'最新');
            $filter = 'g.group_id != 71 AND g.group_id != 44';
                    
        
            $lang = $this->getLang();
            $alang = $lang?$lang:'Rus';
            
            $query = "SELECT g.group_id as group_id, g.`name` as `name`, p.`name` as `partName` FROM `gpj_parts{$lang}` p, `gpj_groups{$lang}` g 
                        WHERE p.part_id = g.part_id AND p.visible = 1 AND ($filter) ORDER BY p.`sort`";
            $result = DB::asArray($query);
                        
            if (isset($newNames[$alang])) {
                $countData = DB::line("SELECT COUNT(id) AS `count` FROM gpj_templates_new");
                if ($countData['count'] > 0)
                    $result = array_merge(array(array('group_id'=>NEWGROUPID, 'name'=>$newNames[$alang], 'partName'=>$newNames[$alang])), $result);
            }
            
            return $result;
        }
        
        protected function getVar($varName, $default) {
            return isset($this->request[$varName])?$this->request[$varName]:$default;
        }
        
        public function result() {
            $method = $this->getVar('method', 'getList');
            return $this->$method();
        }     
    }
?>