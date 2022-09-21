<?

    header("Content-Type: text/html; charset=utf-8");
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    include('/home/config.php');
    include('holiday_calc.php');
    
    $json = file_get_contents('holiday.json');
    $holiday = json_decode($json);
    
    holiday_calc::$year = date('Y');
    holiday_calc::calc($holiday);
    
    $link = mysqli_connect($host, $user, $password, _dbname_default);
    
?>
<pre>
<?    
    foreach ($holiday as $item) {
//        print_r($item);
        $name = mysqli_escape_string($link, $item->name);
        $desc = mysqli_escape_string($link, $item->desc);
        $date = date('Y-m-d', strtotime($item->hdate));
        echo "INSERT INTO gpj_holiday (`name`, `desc`, `type`, `func`, `date`) VALUES ('{$name}', '{$desc}', {$item->type}, '{$item->date}', '{$date}');\n";
    }
    
    $link->close();
?>
</pre>