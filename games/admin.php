<?php
    $CTYPE = @$_GET['ctype']?$_GET['ctype']:'html';  
    header("Content-Type: text/$CTYPE; charset=UTF-8");
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    error_reporting(E_ALL);
    session_start();
	date_default_timezone_set('Europe/Moscow');

    include_once('/home/config.php');

    $LANGS = array('any', 'rus', 'eng', 'zh');
    $LANG_LABELS = array('Любой', 'Русский', 'Английский', 'Китайский');
    $LANGSINSTALL = ['ru', 'eng', 'uk', 'si'];
    
    define('ADMINPATH', MAINPATH.'games/admin/');
    define('TEMPLATES_PATH', ADMINPATH.'templates/');
    define('CONTROLLERS_PATH', ADMINPATH.'controllers/');
    
    include_once(HOMEPATH.'/secrects.inc');
    include_once(INCLUDE_PATH.'/_dbu.php'); 
    include_once(INCLUDE_PATH.'/_edbu2.php');
    include_once(INCLUDE_PATH.'/app.php');
    include_once(INCLUDE_PATH.'/request.php');
    include_once(INCLUDE_PATH.'/fdbg.php');
    include_once(ADMINPATH.'admin.php');
    include_once(ADMINPATH.'helpers/server.php');
    include_once(CONTROLLERS_PATH.'controller.php');
    
    $charset = 'utf8';
    
    $mysqli = new mysqli($host, $user, $password, $dbname);    
    new Admin(new Request());
    echo Admin::getInstance()->page();
  
    if ($db) $db = null;

    if ($mysqli) $mysqli->close();
?>
