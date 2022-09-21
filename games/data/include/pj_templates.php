<?
    define('STORAGE_URL', 'http://fotoprivet.com/games/data/temp_storage.json');
    define('NEWGROUPID', 14200007);
    
    class dataModel {
        private  $request;
        function __construct($request) {
            $this->request = $request;
        }
        
        protected function getList() {
            
            $lang   = $this->getLang();
            $page   = $this->getVar('page', 1);
            $groups = $this->getVar('groups', '40');
            $itemCount = $this->getVar('count', 100);
            $start = ($page - 1) * $itemCount;
            
            $langWhere = '';
            
            if ($groups) {
                if ($lang && ($lang != 'Rus')) $langWhere = "AND `weight`=1";
                
                $groupIds = explode('-', $groups);
                $where = '';
                $count = count($groupIds);
                foreach ($groupIds as $id)  
                    $where .= ($where?' OR ':'').'group_id='.$id;

                if (($count == 1) && ($groupIds[0] == NEWGROUPID)) {
                    $query = "SELECT SQL_CALC_FOUND_ROWS id AS tmpl_id FROM gpj_templates_new GROUP BY id" ;
                } else {
                    $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id FROM
                                (SELECT tmpl_id, COUNT(tmpl_id) AS `count`, COUNT(`weight`) AS `weight` 
                                FROM `gpj_templates` 
                                WHERE ($where $langWhere) GROUP BY tmpl_id) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                            WHERE tg.`tmpl_id` < 60000 AND tg.`count`=$count 
                            ORDER BY tg.`tmpl_id` DESC LIMIT $start,".$itemCount;
                }
            } else {
                if ($lang && ($lang != 'Rus')) $langWhere = "AND tmpls.`weight`=1";
                
                $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id FROM `gpj_templates` tmpls INNER JOIN gpj_tmplOptions tmo ON tmpls.tmpl_id = tmo.tmpl_id 
                            WHERE tmpls.`tmpl_id` < 60000 $langWhere GROUP BY tmpls.`tmpl_id` DESC LIMIT $start,".$itemCount;
                $groupIds = array();
            }
                              
            $items = DB::asArray($query);
            $totalPages = ceil(query_one('SELECT FOUND_ROWS()') / $itemCount);
            
            $result = array();
            foreach ($items as $item) {
                $result[] = $item['tmpl_id'];
            }
            
            if ($totalPages > $page) {
                $result[] = 'page='.($page + 1).'&count='.$itemCount.'&groups='.$groups;
            }
            return $result;
        }
        
        protected function aconv($array, $fields, $sourceCharset, $descCharset) {
            foreach ($array as $key=>$item) {
                foreach ($fields as $field)
                    $array[$key][$field] = iconv($sourceCharset, $descCharset, $item[$field]); 
            }
            
            return $array;
        }
        
        protected function getLang() {
            return $this->getVar('lang', '');
        }
        
        protected function getCats_rus() {
            $newNames = array('Rus'=>'Новые', 'Eng'=>'New');        
        
            $lang = $this->getLang();
            $query = "SELECT g.group_id as group_id, g.`name` as `name`, p.`name` as `partName` FROM `gpj_parts{$lang}` p, `gpj_groups{$lang}` g WHERE p.part_id = g.part_id AND p.visible = 1 ORDER BY p.`sort`";
            $result = DB::asArray($query);
            if (($lang == '') || ($lang == 'Rus')) $result = $this->aconv($result, array('name', 'partName'), 'CP1251', 'UTF-8');
            
            $alang = $lang?$lang:'Rus';
            if (isset($newNames[$alang]))
                $result = array_merge($result, array(array('group_id'=>NEWGROUPID, 'name'=>$newNames[$alang], 'partName'=>$newNames[$alang])));
            
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