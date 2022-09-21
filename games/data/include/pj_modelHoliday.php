<?
    include_once(dirname(__FILE__).'/base_model.php');
    include dirname(__FILE__).'/holiday_calc.php';    
    
    $charset    = 'utf8';
    class dataModel extends base_model {
        protected function getList() {
            $df         = holiday_calc::$dateFormat;
            
            $curQDate = $this->getVar('cur', 'now'); 
            $days = $this->getVar('days', 3);
            
            $minDate = date('Y-m-d', strtotime($curQDate));
            $maxDate = date('Y-m-d', strtotime("$curQDate +$days day"));
            
            $query      = "SELECT * FROM gpj_holiday WHERE `date`>='$minDate' AND `date`<='$maxDate' AND `type` < 10";
            $dates      = DB::asArray($query);
            
            $query      = "SELECT * FROM gpj_names WHERE `date`>='$minDate' AND `date`<='$maxDate'";
            $names      = DB::asArray($query);
            //$names      = array();//DB::asArray($query);
            
            return array('interval'=>$minDate.'-'.$maxDate, 'dates'=>$dates, 'names'=>$names);
        }
        
        protected function getTmpls() {
            $query = 'SELECT tmpl_id as id FROM gpj_holidayTmpls WHERE holiday_id='.$this->getVar('holiday', 0).' ORDER BY tmpl_id DESC';
            return DB::asArray($query);
        }      
    }
?>