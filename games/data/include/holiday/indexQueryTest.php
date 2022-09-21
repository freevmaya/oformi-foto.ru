<?

    header("Content-Type: text/html; charset=utf-8");
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    include('holiday_calc.php');
    $json = file_get_contents('http://oformi-foto.ru/games/data/index-test.php?model=pj_modelHoliday');
    echo $json;
?>