<?

    header("Content-Type: text/json; charset=windows-1251");
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    include('holiday_calc.php');

    $df = 'd.m.Y';    
    $json = file_get_contents('holiday.json');
    $holiday = json_decode($json);
    
    $curQDate = isset($_GET['cur'])?$_GET['cur']:'now'; 
    $days = isset($_GET['days'])?$_GET['days']:3;
    
    holiday_calc::$year = date('Y');
    
    $minDate = strtotime(date($df, strtotime($curQDate)));
    $maxDate = strtotime(date($df, strtotime("$curQDate +$days day")));
    
    holiday_calc::calc($holiday);
    $dates =  holiday_calc::query($holiday, $minDate, $maxDate);
    
    $result = array('interval'=>date($df, $minDate).'-'.date($df, $maxDate), 'dates'=>$dates);
    print_r(json_encode($result));
?>