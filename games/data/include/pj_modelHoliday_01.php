<?
    include_once(dirname(__FILE__).'/base_model.php');
    include dirname(__FILE__).'/holiday_calc.php';
    
    $charset    = 'utf8';
    class dataModel extends base_model {
        protected function getList() {
            $df         = holiday_calc::$dateFormat;
            
            $curQDate = $this->getVar('cur', 'now'); 
            
            $minDate = date('Y-m-d', strtotime("now"));
            $maxDate = date('Y-m-d', strtotime("now +9 day"));
            
            $query      = "SELECT *, (SELECT COUNT(tmpl_id) FROM gpj_holidayTmpls WHERE holiday_id=h.holiday_id) AS tmpl_count  FROM gpj_holiday h WHERE h.`date`>='$minDate' AND h.`date`<='$maxDate' AND h.`type` < 10 AND h.`disabled`=0";
            $dates      = DB::asArray($query);
                                                                           
            $n2date     = date('md', strtotime($curQDate));                                                                           
            $query      = "SELECT * FROM `gpj_names2` n2 INNER JOIN `gpj_dateNames` dn ON dn.name_id=n2.name_id WHERE date='$n2date'";
            $names      = DB::asArray($query);
            
            return array('interval'=>$minDate.'-'.$maxDate, 'dates'=>$dates, 'names'=>$names);
        }
        
        protected function getTmpls() {
            $query = 'SELECT tmpl_id as id FROM gpj_holidayTmpls WHERE holiday_id='.$this->getVar('holiday', 0).' ORDER BY tmpl_id DESC';
            return DB::asArray($query);
        }
        
        protected function getNameTmpls() {
            $index = $this->getVar('holiday', 0);
            $type = $this->getVar('type', 0);
            if (is_numeric($index))
                $query = 'SELECT tmpl_id as id FROM gpj_nameTmpl WHERE (name_id=0 OR name_id='.$index.') AND `type`='.$type.' ORDER BY tmpl_id DESC';
            else $query = "SELECT nt.tmpl_id as id FROM gpj_nameTmpl nt INNER JOIN gpj_names2 n ON nt.name_id = n.name_id WHERE (nt.name_id=-1 OR n.name='$index') AND nt.`type`=$type ORDER BY nt.tmpl_id DESC";
            
            return DB::asArray($query);
        }
        
        protected function checkNameHoliday() {
            $users = explode(',', $this->getVar('users', ''));
            $result = array();
            $n2date     = date('md');
            
            foreach ($users as $user) {
                $da     = explode('-', $user);           
                $bday   = $da[1];
                
                if ($bday < $n2date) $where = "('{$bday}'<`date` AND `date`<='{$n2date}')";
                else $where = "(`date`<='{$n2date}' OR `date`>'{$bday}')";
                 
                $query  = "SELECT COUNT(dn.name_id) AS `count` FROM `gpj_names2` n2 INNER JOIN `gpj_dateNames` dn ON dn.name_id=n2.name_id WHERE $where AND (n2.name_id={$da[0]}) GROUP BY dn.name_id";
                $item   = DB::line($query);
                $result[] = $item['count']; 
            }
            return $result;
        }
        
        protected function getCongratulations() {
             $type = $this->getVar('type', 0);
             $query  = "SELECT `text` FROM `gpj_congratulations` WHERE `type`=$type";
             $items = DB::asArray($query);
             $count = count($items);
             if ($count > 0) {
                $index = rand(0, $count - 1);
                return $items[$index]['text'];
             } else return 0;
        }
        
        public function allHolidays() {
            $month = $this->getVar('month', 0);
            $month = (strlen($month)==1)?"0$month":$month;
            $startDate = date("Y-$month-01");
            $endDate = date('Y-m-d', strtotime('+1 month', strtotime($startDate)));
            $query = "SELECT * FROM gpj_holiday h INNER JOIN gpj_holidayTmpls ht ON h.holiday_id = ht.holiday_id AND h.deftml_id=ht.tmpl_id WHERE h.`date`>='$startDate' AND h.`date`<'$endDate' AND h.`disabled`=0";
            return DB::asArray($query);
        }
/*        
        
        protected function getNameIds() {
            GLOBAL $charset;
            //$charset = 'cp1251';
            $users = explode(',', $this->getVar('users', ''));
            $where = '';
            foreach ($users as $names) {
                $a_names = preg_split("/[\s]+/", $names);
                $name = trim($a_names[0]); 
//               print_r(trim($a_names[0])."\n");
                $where .= ($where?' OR ':'')."name='$name'";
            }
            $query  = "SELECT name, name_id FROM `gpj_names2` WHERE $where";
            return DB::asArray($query);
        }
*/              
    }
?>