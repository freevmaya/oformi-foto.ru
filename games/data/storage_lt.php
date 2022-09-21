<?
    header("Content-Type: text/html; charset=UTF-8");
    if (isset($_GET['dev']) && $_GET['dev']) 
        header('Cache-Control: no-cache '); //Без кеша
    else header('Cache-Control: max-age=900, must-revalidate'); //Хранить в кеше 15 минут
    
    //include_once('spot_storage.php');
    $cfg = json_decode(file_get_contents('temp_storage.json'));
    $cfg->options = json_decode('{"JPG_URL":"//oformi-foto.ru/JPG","PREVIEW_URL":"//oformi-foto.ru/jpg_preview","PREVIEW_URL120":"//oformi-foto.ru/preview120","OUTSIDEID":"23938"}');
?>    
var GLOBALSTORAGE = <?=json_encode($cfg);?>;
