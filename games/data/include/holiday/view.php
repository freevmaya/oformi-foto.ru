<?
    include('holiday_calc.php');

    $df = 'd.m.Y';    
    $json = file_get_contents('holiday.json');
    $holiday = json_decode($json);
    
    $curQDate = isset($_GET['cur'])?$_GET['cur']:'now'; 
    $days = isset($_GET['days'])?$_GET['days']:3;
    
    holiday_calc::$year = date('Y', strtotime($curQDate));
    
    $minDate = strtotime(date($df, strtotime($curQDate)));
    $maxDate = strtotime(date($df, strtotime("$curQDate +$days day")));
    
    holiday_calc::calc($holiday);
    $dates =  holiday_calc::query($holiday, $minDate, $maxDate);
?>
<html>
<head>
    <style type="text/css">
        body {
            font-family: arial, tahoma;
        }
        
        table.holiday td {
            padding: 10px;
        }
    </style>
</head>
<body>
    <div>
        Начиная с <?=date($df, $minDate)?> по <?=date($df, $maxDate)?>
    </div>
    <table style="holiday">
        <tr>
            <th>
                Код
            </th>
            <th>
                Картинка
            </th>
            <th>
                Дата
            </th>
            <th>
                Праздник
            </th>
        </tr>
<? foreach ($dates as $item) {?>
    <tr>
        <td><?=$item->index?></td>
        <td><img src="64/<?=$item->image?>.jpg"></td>
        <td><?=$item->date?></td>
        <td><?=$item->name?></td>
    </tr>
<?}?>        
    </table>
</body>
</html>