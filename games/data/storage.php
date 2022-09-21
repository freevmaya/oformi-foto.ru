<?
    header("Content-Type: text/json; charset=UTF-8");
    
    $isDev = isset($_GET['dev']) && $_GET['dev'];
    if ($isDev) 
        header('Cache-Control: no-cache '); //Без кеша
    else header('Cache-Control: max-age=900, must-revalidate'); //Хранить в кеше 15 минут
    
    if ($isDev) $include = 'spot_storage_dev.php';
    else $include = 'spot_storage.php';
     
    include_once($include); 
    echo json_encode($cfg);
?>