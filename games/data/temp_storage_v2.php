<?
    header("Content-Type: text/javascript; charset=utf-8");
    header('Cache-Control: no-cache');
    
    echo file_get_contents('temp_storage_v2.json');
?>