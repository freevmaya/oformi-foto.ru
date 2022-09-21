<?php
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    error_reporting(E_ALL ^ E_NOTICE);
	date_default_timezone_set('Europe/Moscow');

    include_once('/home/config.php');
    include_once(HOMEPATH.'/domains.inc');
    
    $ref = @$_SERVER['HTTP_REFERER'];
    $result = $ref?explode('/', $ref):null;

/*    
    print_r($_SERVER);
    exit;
*/    

    if (array_search($result[2], $domains) !== false) {
        if ($id = @$_GET['id']) {
            header('Content-Type: image/png');
            echo file_get_contents(dirname(__FILE__)."/png_tmp/{$id}.png"); 
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo 'no "id" parametr';
        }
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo "Domain {$result[2]} is not allowed";
    } 
?>    