<?
    define('STORAGE_URL', 'http://fotoprivet.com/games/data/temp_storage.json');
    define('NEWGROUPID', 14200007);
    define('HOLIDAYGROUP', 14200008);
/*    
    $oldGroups = array(41, 101, 102, 103);
    $brand_groups = array(300, 301);
*/    
    
    $oldGroups = array(48);
    $brand_groups = array(45, 49, 300, 301, 302, 303);
    $groups = array(60, 61);
    
    class dataModel {
        private  $request;
        function __construct($request) {
            $this->request = $request;
        }
        
        protected function setBrand() {
            GLOBAL $oldGroups, $brand_groups;
            
            $result = 0;
              
            if ($tmpl_id = $this->getVar('tmpl_id', false)) {
                $result = DB::query("REPLACE gpj_templates (`group_id`, `tmpl_id`) VALUES (303, $tmpl_id)")?1:0;
            }
            return array('result'=>$result);
        }
        
        protected function getList() {     
            GLOBAL $oldGroups, $brand_groups;        
            
            $query  = '';
            $lang   = strtolower($this->getLang('rus'));
            $start  = $this->getVar('page', 0);
            $groups = $this->getVar('groups', '40');
            $itemCount = $this->getVar('count', 100);
//            $start = ($page - 1) * $itemCount;
            
            $langWhere = '';
            
            if ($groups) {
                $langWhere = "(tmo.`lang`='any' OR tmo.`lang`='{$lang}')";
                
                $groupIds = explode('-', $groups);
                $count = count($groupIds);
                if ($count == 1) {
                    if ($groupIds[0] == NEWGROUPID) {
                        $query = "SELECT SQL_CALC_FOUND_ROWS id AS tmpl_id, tmo.width, tmo.height FROM gpj_templates_new tn 
                        INNER JOIN gpj_templates t ON t.tmpl_id = tn.id 
                        INNER JOIN gpj_tmplOptions tmo ON tn.id = tmo.tmpl_id 
                        WHERE tmo.`active`=1 AND $langWhere AND tmo.height > 0 GROUP BY id ORDER BY id DESC" ;
                    } else if ($groupIds[0] > HOLIDAYGROUP) {
                        $holiday_id = $groupIds[0] - HOLIDAYGROUP;
                        $query = "SELECT SQL_CALC_FOUND_ROWS ht.tmpl_id, tmo.width, tmo.height FROM gpj_holidayTmpls ht
                        INNER JOIN gpj_tmplOptions tmo ON ht.tmpl_id = tmo.tmpl_id AND tmo.active=1
                        WHERE ht.holiday_id={$holiday_id} AND $langWhere AND tmo.height > 0 ORDER BY ht.tmpl_id DESC LIMIT $start, $itemCount";
                    } 
                }
                
                $where = '';
                foreach ($groupIds as $id)  
                    $where .= ($where?' OR ':'').'t.group_id='.$id;
                    
                $g_where = '';
                foreach ($oldGroups as $id)  
                    $g_where .= ($g_where?' OR ':'').'tc.group_id='.$id;
                    
                $hwhere = '';
                foreach ($brand_groups as $id)
                     $hwhere .= ($hwhere?' OR ':'').'group_id='.$id;
                     
                if ($query == '') {
                    $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id, tmo.width, tmo.height  FROM
                                (SELECT t.tmpl_id, COUNT(t.tmpl_id) AS `count`, COUNT(t.`weight`) AS `weight` 
                                FROM `gpj_templates` t INNER JOIN `gpj_templates` tc ON t.tmpl_id = tc.tmpl_id AND ({$g_where}) 
                                WHERE $where AND t.`tmpl_id` < 60000 GROUP BY t.tmpl_id ORDER BY t.tmpl_id DESC) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                            WHERE tg.`count`>=$count AND tmo.`active`=1 AND $langWhere 
                            ".($hwhere?"AND (tmo.tmpl_id NOT IN (SELECT tmpl_id FROM gpj_templates WHERE $hwhere))":'')." AND tmo.height > 0 
                            LIMIT $start, $itemCount";
                }
                
            } else {
                $query = "SELECT SQL_CALC_FOUND_ROWS tmo.tmpl_id FROM `gpj_templates` tmpls INNER JOIN gpj_tmplOptions tmo ON tmpls.tmpl_id = tmo.tmpl_id 
                            WHERE tmpls.`tmpl_id` < 60000 AND tmo.`active`=1 AND $langWhere AND tmo.height > 0 GROUP BY tmpls.`tmpl_id` DESC LIMIT $start,".$itemCount;
                $groupIds = array();
            }
            
            //echo($query);
            
            $items = DB::asArray($query);
            $totalCount = query_one('SELECT FOUND_ROWS()');
            
            $list           = array();
            $count          = 0;
            $resultCount    = 0;
            $maxpreviewsize = $this->getVar('maxpreviewsize', 120); 
            $display_width  = $this->getVar('display_width', 800);
            $lwidth         = 0;
            
            $resultCount    = count($items);
            
            foreach ($items as $item) {
                $rate = round($item['width']/$item['height'] * 100) / 100; 
                $list[] = array($item['tmpl_id'], $rate);
                $size = $maxpreviewsize * $rate;
                $lwidth += $size;
                if ($lwidth >= $display_width) {
                    //echo $lwidth;
                    $resultCount = $count;
                    $lwidth = $size;
                }
                $count++;
                //$resultCount++;
            }
            
            if ($resultCount < $count)
                array_splice($list, $resultCount);
            
            $nextStart = $start + $resultCount + 1;
            
            //echo $totalCount.' next start: '.$nextStart.' count: '.$resultCount;
            
            if ($totalCount > $nextStart) {
                $list[] = 'page='.$nextStart.'&count='.$itemCount.'&groups='.$groups;
            }
            
            return $list;
        }
        
        protected function checkGroupsCount($groups, $lang) {
            $time = date('Y-m-d H:i:s', strtotime('-12 HOUR'));
            $query = "SELECT * FROM `gpj_groups_count` WHERE (`lang`='any' OR `lang`='{$lang}') AND `refresh_time`>'{$time}' AND `section`='child'";
            trace($query); 
            $list = DB::asArray($query);
            
            foreach ($groups as $i=>$group) {
                $isCount = false;
                foreach ($list as $sgroup)
                    if ($sgroup['group_id'] == $group['group_id']) {
                        $isCount = ($groups[$i]['count'] = $sgroup['count']) > 0;
                        break;
                    }
                    
                if (!$isCount) 
                    $groups[$i]['count'] = $this->refreshGroupCount($group['group_id'], $lang);
            }
            return $groups;
        }
        
        protected function refreshGroupCount($gid, $lang) {
            GLOBAL $oldGroups, $brand_groups;
            
            $langWhere  = "(tmo.`lang`='any' OR tmo.`lang`='{$lang}')";
            $where      = 't.group_id='.$gid;
                
            $g_where = '';
            foreach ($oldGroups as $id)  
                $g_where .= ($g_where?' OR ':'').'tc.group_id='.$id;
                
            $hwhere = '';
            foreach ($brand_groups as $id)
                 $hwhere .= ($hwhere?' OR ':'').'group_id='.$id;
                 
            $count = 1;
               
            $query = "SELECT count(tmo.tmpl_id) AS `count` FROM
                                (SELECT t.tmpl_id, COUNT(t.tmpl_id) AS `count`, COUNT(t.`weight`) AS `weight` 
                                FROM `gpj_templates` t INNER JOIN `gpj_templates` tc ON t.tmpl_id = tc.tmpl_id AND ({$g_where}) 
                                WHERE $where AND t.`tmpl_id` < 60000 GROUP BY t.tmpl_id ORDER BY t.tmpl_id DESC) tg INNER JOIN gpj_tmplOptions tmo ON tg.tmpl_id = tmo.tmpl_id 
                            WHERE tg.`count`>=$count AND tmo.`active`=1 AND $langWhere 
                            ".($hwhere?"AND (tmo.tmpl_id NOT IN (SELECT tmpl_id FROM gpj_templates WHERE $hwhere))":'');
//            trace($query);
            $result = DB::line($query);
            $count = isset($result['count'])?$result['count']:0; 
            $time = date('Y-m-d H:i:s');
            
            DB::query("REPLACE `gpj_groups_count` (`group_id`, `lang`, `section`, `count`, `refresh_time`) VALUES ($gid, '$lang', 'child', {$count}, '{$time}')");
            return $count;
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
            GLOBAL $charset, $groups;
            $charset = 'utf8';
            
            $newNames = array('Rus'=>'Новые', 'Eng'=>'New', 'Zh'=>'最新');
            
            $filter = '';
            foreach ($groups as $hg) {
                $filter .= ($filter?' OR ':'').'g.group_id = '.$hg;
            }
                    
        
            $lang = $this->getLang();
            $alang = $lang?$lang:'Rus';
            
            $query = "SELECT g.group_id as group_id, g.`name` as `name`, p.`name` as `partName` FROM `gpj_parts{$lang}` p, `gpj_groups{$lang}` g 
                        WHERE p.part_id = g.part_id AND p.visible = 1 AND g.visible = 1 AND ($filter) ORDER BY p.`sort`";
            $result = DB::asArray($query);
            
            $tmp = $this->checkGroupsCount($result, strtolower($alang));
            $i = 0;
            $result = array();
            foreach ($tmp as $item)
                if ($item['count'] > 0) $result[] = $item;
                        
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