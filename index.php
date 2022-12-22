<?php
    GLOBAL $contentType, $mysql_cache_expired;

    include_once('config/config.php');
    include_once(INCLUDE_PATH.'/request.php');
   
    $rqest = new Request();

    $ct1 = 'text';
    $contentType = $rqest->getVar('content_type', 'html');
    $types = array('html', 'json', 'rss');
    
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        preg_match_all("/([\w]+)\/([\w]+)/", $_SERVER['HTTP_ACCEPT'], $result);
        if (isset($result[2]) && isset($result[2][0])) {
            $ct1 = $result[1][0]; 
            $contentType = $result[2][0];
            if (!in_array($contentType, $types)) {
                exit;
            }
        }        
    }


    header('Content-Type: '.$ct1.'/'.$contentType.'; charset=utf-8');

//    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    error_reporting(E_ALL ^ E_NOTICE);
    session_start();
	date_default_timezone_set('Europe/Moscow');

    $target = $rqest->getVar('target', false);
                              
    define('SSRELATIVE', 'ss'.$target.'/');
    define('SSPATH', MAINPATH.SSRELATIVE);
    define('SSURL', MAINURL.'/'.SSRELATIVE);
    define('TEMPLATES_PATH', SSPATH.'templates/');
    define('CONTROLLERS_PATH', SSPATH.'controllers/');
    
    include_once(HOMEPATH.'/of-secrects.inc');
    include_once(INCLUDE_PATH.'/_dbu.php');
    include_once(INCLUDE_PATH.'/app.php');
    include_once(INCLUDE_PATH.'/fdbg.php'); 
    $charset = 'utf8';
    
    $mysqli = new mysqli($host, $user, $password, $dbname);
    include_once(SSPATH.'ss.php');
    include_once(SSPATH.'helpers/server.php');
    include_once(CONTROLLERS_PATH.'controller.php');
    
    $mysql_cache_expired = 60 * 60 * 2; //2 часа
    
//Отбрасываем заданные браузеры     
    $dropArents = array('/SemrushBot/');//'/bingbot\/2\.0;/', '/Chrome\/61\.0\.3163/');
    if (indexOfLike($dropArents, $_SERVER['HTTP_USER_AGENT']) > -1) {
        echo 'no permission';
        exit;
    }    
   
    new ss($rqest, (($contentType != 'html')?$contentType:'index').'.html');
//    new ss(new Request(), isset($_GET['type'])?($_GET['type'].'.html'):'index.html');

    echo ss::getInstance()->page();
  
    if ($db) $db = null;
    if ($mysqli) $mysqli->close();
?>
