<?php
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
        
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine2.php');
    include_once($homePath.'/secrects.inc');
    include_once($homePath.'/vmaya/mm/tree/data/include/tree_config_mm.php');
    
    $dbname = '_tree_mm';
    $charset = 'utf8';
    $FDBGLogFile = LOGPATH.'pay_tree_mm.log';
    
    trace($_SERVER['HTTP_REFERER'].' '.$_SERVER['QUERY_STRING']);    
   
/* Формат запроса: querycount=1&query1=model;method;param1;param2...

Примеры запросов:
    ?querycount=3&query1=sokoban;getUserData;2;vkontakte.ru&query2=sokoban;setUserData;2;vkontakte.ru;1&query3=sokoban;setFavorite;1;vkontakte.ru;2;%D0%A4%D1%80%D0%BE%D0%BB%D0%BE%D0%B2%20%D0%92%D0%B0%D0%B4%D0%B8%D0%BC;100;134522
    ?querycount=1&query1=sokoban;getFavorite;1;vkontakte.ru
*/
    
    $app = new App('utf-8');
    $request = new Request();
    $gsig = '';
    $check_sig = false;
    $pid_add = '';
    
    if ($request->getVar('sig', false) && $request->getVar('app_id', false)) {
        $check_sig = (Request::genSig($request->values, $secrets) == $request->getVar('sig')) || 
                    (Request::genSig($request->values, $sKeys) == $request->getVar('sig'));
    }                 
    
    if ($request->getVar('transaction_id', false)) {    // ??? ?????? ? ??????? MAIN.RU
        if ($check_sig) {
            include_once('include/transaction_tree_mm.php');
            $transaction = new g_transaction();
            $result = $transaction->begin($request);
        } else $result = '{"status":"0"}';
        echo $result;
    }

    if ($db) mysql_close($db);
?>