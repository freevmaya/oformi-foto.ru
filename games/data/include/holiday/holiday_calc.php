<?

class holiday_calc {
    protected static $mount = array('December', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    protected static $wday = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
    
    public static $year = '';
    public static $dateFormat = 'd.m.Y';
    
    protected static $PAS = '20 April 2014';
     
    public static function date($params) {
        return date(holiday_calc::$dateFormat, strtotime($params[1].' '.holiday_calc::$year)); 
    }
    
    public static function dw($params) {
        $str = $params[2].' '.holiday_calc::$wday[$params[1]].' '.holiday_calc::$mount[$params[3]].' '.holiday_calc::$year; 
        return date(holiday_calc::$dateFormat, strtotime($str)); 
    }
    
    public static function lm($params) {
        $str = 'last '.holiday_calc::$wday[$params[1]].' '.holiday_calc::$mount[($params[2] + 1) % 12].' '.holiday_calc::$year; 
        return date(holiday_calc::$dateFormat, strtotime($str)); 
    }
    
    public static function dy($params) {
        $str = '01-01-'.holiday_calc::$year." +{$params[1]} day"; 
        return date(holiday_calc::$dateFormat, strtotime($str)); 
    }
    
    public static function dc($params) {
        $str = holiday_calc::$$params[2]." {$params[1]} day";
        return date(holiday_calc::$dateFormat, strtotime($str)); 
    }
    
    public static function calc($holiday) {
        $curDate = date(holiday_calc::$dateFormat);
        $fileNames = array();
        for ($i=0; $i<count($holiday); $i++) {
            $fileName = str_replace('.', '', $holiday[$i]->date);
            $e = explode(',', $holiday[$i]->date);
            if (count($e) > 1) $holiday[$i]->hdate = @holiday_calc::$e[0]($e);
            else $holiday[$i]->hdate = $holiday[$i]->date.'.'.holiday_calc::$year;
            
            if ($holiday[$i]->hdate == $curDate) $holiday[$i]->today = true;
            
            $fileNames[$fileName] = isset($fileNames[$fileName])?($fileNames[$fileName] + 1):0;
            $index = $fileNames[$fileName];
            $holiday[$i]->image = $fileName.(($index>0)?(' '.$index):'');
            $holiday[$i]->index = md5($holiday[$i]->name);
        }
        
        return $holiday;
    } 
    
    public static function query($holiday, $minDate, $maxDate) {
        $dates = array();
        
        for ($i=0; $i<count($holiday); $i++) {
            $time = strtotime($holiday[$i]->date);
            if (($time >= $minDate) && ($time <= $maxDate)) {
                $dates[] = $holiday[$i];
            }
        }
        
        return $dates;
    }
}
?>