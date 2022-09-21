<?php
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    error_reporting(E_ALL ^ E_NOTICE);
    session_start();
	date_default_timezone_set('Europe/Moscow');

    include_once('../config/config.php');
    include_once(dirname(__FILE__).'/ssconfig.php');
    
    define('TEMPLATES_PATH', SSPATH.'templates/');
    define('CONTROLLERS_PATH', SSPATH.'controllers/');
    
    include_once(HOMEPATH.'/of-secrects.inc');
    include_once(INCLUDE_PATH.'/_dbu.php');
    include_once(INCLUDE_PATH.'/app.php');
    include_once(INCLUDE_PATH.'/request.php');
    include_once(INCLUDE_PATH.'/fdbg.php');
    include_once(SSPATH.'ss.php');
    include_once(SSPATH.'helpers/server.php');
    include_once(CONTROLLERS_PATH.'controller.php');
    
    $charset = 'utf8';
    $mysqli = new mysqli($host, $user, $password, $dbname);
    
    
    new ss(new Request(), 'echo.json');
    echo ss::getInstance()->page();
  
    if ($db) $db = null;
?>