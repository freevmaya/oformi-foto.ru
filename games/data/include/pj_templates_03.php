<?
    define('STORAGE_URL', 'http://fotoprivet.com/games/data/temp_storage.json');
    define('NEWGROUPID', 14200007);
    define('HOLIDAYGROUP', 14200008);
    
    class dataModel {
        private  $request;
        function __construct($request) {
            $this->request = $request;
        }
        
        protected function getList() {
            
            $query  = '';
            $lang   = strtolower($this->getLang('rus'));
            $page   = $this->getVar('page', 1);
            $groups = $this->getVar('groups', '40');
            $itemCount = $this->getVar('count', 100);
            $start = ($page - 1) * $itemCount;
            
            $langWhere = '';
            
            if ($groups) {
                $langWhere = "(tmo.lang='any' OR tmo.lang='{$lang}')";
                
                $groupIds = explode('-', $groups);
                $where = '';
                $count = count($groupIds);
                foreach ($groupIds as $id)  
                    $where .= ($where?' OR ':'').'group_id='.$id;
                    
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
                
                if ($query == '') {
                    $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id FROM
                                (SELECT tmpl_id, COUNT(tmpl_id) AS `count`, COUNT(`weight`) AS `weight` 
                                FROM `gpj_templates` 
                                WHERE $where AND `tmpl_id` < 60000 GROUP BY tmpl_id ORDER BY tmpl_id DESC) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                            WHERE tg.`count`=$count AND tmo.`active`=1 AND $langWhere 
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
                    
        
            $lang = $this->getLang();
            $query = "SELECT g.group_id as group_id, g.`name` as `name`, p.`name` as `partName` FROM `gpj_parts{$lang}` p, `gpj_groups{$lang}` g WHERE p.part_id = g.part_id AND p.visible = 1 ORDER BY p.`sort`";
            $result = DB::asArray($query);
            if (($lang == '') || ($lang == 'Rus')) {
                $minDate = date('Y-m-d', strtotime("now"));
                $maxDate = date('Y-m-d', strtotime("now +18 day"));
                
                $query   = "SELECT DATE_FORMAT(`date`, '%d.%m') AS `date`, holiday_id + ".HOLIDAYGROUP." as group_id, `name` AS `name`, `name` AS `partName` FROM gpj_holiday ".
                        "WHERE `deftml_id`>0 AND `date`>='$minDate' AND `date`<='$maxDate' AND `type` < 10 LIMIT 0,3";
                $holidays = DB::asArray($query);
                foreach ($holidays as $key=>$holiday) {
                    $holidays[$key]['name'] = $holiday['date'].' '.$holiday['name'];
                    unset($holidays[$key]['date']);
                }
                $result = array_merge($holidays, $result);
                
                //$result = $this->aconv($result, array('name', 'partName'), 'CP1251', 'UTF-8');
            }
            
            $alang = $lang?$lang:'Rus';
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