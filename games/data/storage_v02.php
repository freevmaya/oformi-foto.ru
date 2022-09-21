<?
    header("Content-Type: text/javascript; charset=utf-8");
    header('Cache-Control: max-age=900, must-revalidate'); //Хранить в кеше 15 минут
    
    include_once('spot_storage.php');
    echo 'var GLOBALSTORAGE='.json_encode($cfg).';';
?>