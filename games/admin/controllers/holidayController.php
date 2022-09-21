<?

include_once(INCLUDE_PATH.'/_edbu2.php');
define('COUNTPERPAGE', 10);
define('SPLITER', "/[,\s-]+/");

class holidayController extends controller {

    public function getList() {
        GLOBAL $charset;
        $charset = 'utf8';
        
        if ($id = @$_GET['delete']) {
            DB::query('DELETE FROM `gpj_holiday` WHERE holiday_id='.$id);
        }
        
        $page = $this->svar('page', 1);
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `gpj_holiday` ORDER BY `date` ASC LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
        require(TEMPLATES_PATH.'holiday_getList.html');
    }
    
    public function addHoliday() {
        GLOBAL $charset, $_SERVER;
        
        include(DATA_PATH.'include/holiday_calc.php');
        $charset = 'utf8';
        
        holiday_calc::$year = date('Y');
        
        $holiday_id = $this->request->getVar('holiday_id', 0);
        
        $item = array(
            'holiday_id'=>$holiday_id,
            'name'=>$this->request->getVar('name', ''),
            'desc'=>$this->request->getVar('desc', ''),
            'type'=>$this->request->getVar('type', ''),
            'func'=>$this->request->getVar('func', date('d.m')),
            'congratulation'=>$this->request->getVar('congratulation', ''),
            'congratulation2'=>$this->request->getVar('congratulation2', ''),
            'image'=>$this->request->getVar('image', '')
        );
        
        if ($this->request->getVar('name', '')) {
            $params = explode(',', $item['func']);
            if (is_numeric(substr($params[0], 0, 2))) {
                $item['date'] = "{$params[0]}.".holiday_calc::$year;
            } else { 
                $item['date'] = holiday_calc::$params[0]($params);
            }
            
            $item['date'] = date('Y-m-d', strtotime($item['date']));
            
            if ($holiday_id == 0) {
                $query = "INSERT INTO gpj_holiday (`name`, `desc`, `type`, `func`, `date`, `congratulation`, `congratulation2`, `image`)". 
                        " VALUES ('{$item['name']}', '{$item['desc']}', {$item['type']}, '{$item['func']}', '{$item['date']}', '{$item['congratulation']}', '{$item['congratulation2']}', '{$item['image']}')";
//                echo $query; 
                DB::query($query);
                $holiday_id = DB::lastID();  
            } else {
                $query = "UPDATE gpj_holiday SET `name`='{$item['name']}', `desc`= '{$item['desc']}', `type`={$item['type']}, `func`='{$item['func']}', `date`='{$item['date']}', `congratulation`='{$item['congratulation']}', `congratulation2`='{$item['congratulation2']}', `image`='{$item['image']}' WHERE holiday_id=$holiday_id";
//                echo $query; 
                DB::query($query);
            }        
        }
        
        if ($holiday_id)
            $item = DB::line("SELECT * FROM gpj_holiday WHERE holiday_id=$holiday_id");
        
        require(TEMPLATES_PATH.'holiday_addHoliday.html');
    }  
    
    public function toCurrent() {
        if ($set_year = $this->request->getVar('year', false)) {
        
            include_once(DATA_PATH.'include/holiday_calc.php');
        
            $items = DB::asArray('SELECT `date`, `func`, `holiday_id` FROM `gpj_holiday`');
            holiday_calc::$year = $set_year;

            if ($PAS = $this->request->getVar('PAS', false)) {
                 holiday_calc::$PAS = $PAS;
            }
            
            foreach ($items as $item) {
                if (date('Y', strtotime($item['date'])) != $set_year) {
                
                    $params = explode(',', $item['func']);
                    if (is_numeric(substr($params[0], 0, 2))) {
                        $item['date'] = "{$params[0]}.".holiday_calc::$year;
                    } else { 
                        
                        try {
                            $item['date'] = holiday_calc::call($params[0], $params);
                        } catch (Exception $e) {
                            echo "Method error<br>";
                            echo json_encode($params)."<br>";
                            continue;
                        }
                    }
                      
                    $item['date'] = date('Y-m-d', strtotime($item['date']));              
                    $query = "UPDATE gpj_holiday SET `date`='{$item['date']}' WHERE holiday_id={$item['holiday_id']}";
                    echo $query.'<br>';
                    DB::query($query);
                }
            }
        }
        include($this->templatePath);
    }
    
    public function holidayTmpls() {
        GLOBAL $charset;
        $charset = 'utf8';
        if ($holiday_id = $this->request->getVar('holiday', 0)) {
            
            if ($default_id = $this->request->getVar('default_id', 0)) {
                DB::query("UPDATE gpj_holiday SET deftml_id=$default_id WHERE holiday_id=$holiday_id");
            }
            
            if ($tmpl_ids = $this->request->getVar('tmpl_id', 0)) {
                $tmpls = preg_split(SPLITER, $tmpl_ids);
                foreach ($tmpls as $tmpl_id)
                    DB::query("REPLACE gpj_holidayTmpls (`holiday_id`, `tmpl_id`) VALUES ($holiday_id, $tmpl_id)");
            }
            
            if ($delete = $this->request->getVar('delete', 0)) {
                foreach ($delete as $tmpl_id)
                    DB::query("DELETE FROM gpj_holidayTmpls WHERE `holiday_id`=$holiday_id AND `tmpl_id`=$tmpl_id");
            }
            
            $query = "SELECT * FROM `gpj_holiday` WHERE holiday_id=$holiday_id";
            $holiday = DB::line($query);
            
            $query = "SELECT * FROM `gpj_holidayTmpls` WHERE holiday_id=$holiday_id ORDER BY tmpl_id";
            $list = DB::asArray($query);
            require(TEMPLATES_PATH.'holiday_holidayTmpls.html');
        }
    } 

    public function getNameHolidays() {
        GLOBAL $charset;
        $charset = 'utf8';
        
        if (($names = $this->request->getVar('names', 0)) && ($date = $this->svar('nh-date', date('md')))) {
            $names = preg_split(SPLITER, $names);
            foreach ($names as $name) {
                if ($nameItem = DB::line("SELECT name_id FROM gpj_names2 WHERE name='$name'")) {
                    $name_id = $nameItem['name_id'];
                    DB::query("REPLACE gpj_dateNames (`name_id`, `date`) VALUES ($name_id, '$date')");
                }
            }
        }
        
        if ($delete = $this->request->getVar('delete', 0)) {
            foreach ($delete as $indent) {
                $a = explode('_', $indent);
                DB::query("DELETE FROM gpj_dateNames WHERE `date`='{$a[0]}' AND `name_id`={$a[1]}");
            }
        }
                
        $page = $this->svar('dpage', 1);
        $charPage = $this->svar('dchar', '');
         
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `gpj_dateNames` dn LEFT JOIN gpj_names2 n2 ON dn.name_id=n2.name_id ORDER BY `date` ASC LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
        $list = DB::asArray($query);
        $count = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count = $count['count'];
        require(TEMPLATES_PATH.'holiday_nameHolidays.html');
    }      

    public function getNames() {
        GLOBAL $charset;
        $charset = 'utf8';
        
        if ($names = $this->request->getVar('names', 0)) {
            $names = preg_split(SPLITER, $names);
            $date = $this->svar('n-date', '');
                        
            foreach ($names as $name) {
                if ($name = trim($name)) {
                    if (!is_numeric($name)) {
                        if ($nameItem = DB::line("SELECT name_id FROM gpj_names2 WHERE name='$name'")) {
                            $name_id = $nameItem['name_id'];
                        } else {
                            DB::query("INSERT INTO gpj_names2 (`name`) VALUES ('$name')");
                            $name_id = DB::lastID();
                        }
                        
                        if ($date && $name_id) DB::query("REPLACE gpj_dateNames (`date`, `name_id`) VALUES ('$date', $name_id)");
                    } else if ($name_id) {
                        $tmpl_id = $name;
                        DB::query("REPLACE gpj_nameTmpl (`name_id`, `tmpl_id`) VALUES ($name_id, $tmpl_id)");
                    }
                }
            }
        }
        
        if ($delete = $this->request->getVar('delete', 0)) {
            foreach ($delete as $name_id)
                DB::query("DELETE FROM gpj_names2 WHERE `name_id`=$name_id");
        }
                
        $where  = "";
        $page   = $this->svar('n2page', 1);
         
        if ($filter = $this->request->getVar('filter', '')) {
            $where = "WHERE n2.name LIKE '{$filter}%'";
            $limit = '';
        } else $limit  = "LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE;
        
        $query  = "SELECT SQL_CALC_FOUND_ROWS *, (SELECT tmpl_id FROM gpj_nameTmpl nt WHERE nt.name_id=n2.name_id LIMIT 0,1) AS tmpl_id FROM `gpj_names2` n2 $where ORDER BY n2.`name` ASC $limit";
        $list   = DB::asArray($query);
        $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count  = $count['count'];
        require(TEMPLATES_PATH.'holiday_getNames.html');
    } 
    
    public function namesTmpl() {
        GLOBAL $charset;
        $charset = 'utf8';
        
        $name_id = $this->request->getVar('name_id', 0);
        
        if ($tmpl_ids = $this->request->getVar('tmpl_id', 0)) {
            $tmpls = preg_split(SPLITER, $tmpl_ids);
            foreach ($tmpls as $tmpl_id)
                DB::query("REPLACE gpj_nameTmpl (`name_id`, `tmpl_id`) VALUES ($name_id, $tmpl_id)");
        }
        
        if ($types = $this->request->getVar('type', 0)) {
            foreach ($types as $type) {
                $a = explode('_', $type);
                DB::query("UPDATE gpj_nameTmpl SET `type`={$a[1]} WHERE `name_id`=$name_id AND `tmpl_id`={$a[0]}");
            }
        }
        
        if ($delete = $this->request->getVar('delete', 0)) {
            foreach ($delete as $tmpl_id)
                DB::query("DELETE FROM gpj_nameTmpl WHERE `name_id`=$name_id AND `tmpl_id`=$tmpl_id");
        }
        
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `gpj_names2` WHERE name_id=$name_id";
        $name = DB::line($query);
        
        $query = "SELECT * FROM gpj_nameTmpl WHERE name_id=$name_id";
        $list = DB::asArray($query);
        require(TEMPLATES_PATH.'holiday_namesTmpl.html');
    }      
    
    public function manageHolidayTmpls() {
        if (($action = $this->request->getVar('action', 0)) && ($holiday_id = $this->request->getVar('holiday_id', 0))) {
            if (($action == 'NYCopy') && ($group_id = $this->request->getVar('group_id', 0))) {
                $query = "REPLACE gpj_holidayTmpls (SELECT {$holiday_id}, tmpl_id FROM `gpj_templates` WHERE `group_id` = {$group_id})";
                $result = DB::query($query);
            } else if ($listStr = trim($this->request->getVar('tmpls', ''))) {
                $list = preg_split("/[\s+,\,]+/i", $listStr);
                if (count($list) > 0) {
                    if ($action == 'delete') {
                        $where = ' tmpl_id='.implode(' OR tmpl_id=', $list);   
                        $query = "DELETE FROM gpj_holidayTmpls WHERE holiday_id={$holiday_id} AND ($where)";
                    } else if ($action == 'add') {
                        $mysqlList = "{$holiday_id},".implode("),({$holiday_id},", $list);   
                        $query = "REPLACE gpj_holidayTmpls VALUES ($mysqlList)";
                    }
                }
                $result = DB::query($query);
            }
        }
        $query = "SELECT `holiday_id`, CONCAT(`date`, ' ', `name`) AS `name` FROM gpj_holiday ORDER BY `date`, `type`";
        $holidays = DB::asArray($query);
        $query = "SELECT `group_id`, `name` FROM gpj_groups ORDER BY `group_id`";
        $groups = DB::asArray($query);
        require(TEMPLATES_PATH.'holiday_manageTmpls.html');
    }      
    
    public function newyearTmpls() {
        require(TEMPLATES_PATH.'holiday_newyearTmpls.html');
    }
}
?>