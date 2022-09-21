<?php
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
        
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine.php');
    include_once('/home/secrects.inc');
   
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
            include_once('include/transaction.php');
            $transaction = new g_transaction();
            $result = $transaction->begin($request);
        } else $result = '{"status":"0"}';
        echo $result;
        if ($db) mysql_close($db);
        exit;
    }
    
    if ($request->getVar('pid', false)) $pid_add .= ',{"pid":"'.$request->getVar('pid').'"}';
    
    if ($check_sig) {
        $querycount = $request->getVar('querycount', 0);
        $result = '';
        for ($i=1;$i<=$querycount;$i++) {
            $query = $request->getVar('query'.$i);
            if ($query) {
                $query = explode(';', $query);
                $modelName = $query[0];
                $method = $query[1];
                if ($modelName) {
                    array_splice($query, 0, 2);
                    $model = new $modelName($app);
                    $query_result = '';
                    if ($method) {
                        if (method_exists($modelName, $method)) {
                        
                            $test_result = $model->$method($query);
                            if (is_string($test_result)) $query_result = $test_result;
                            else $query_result = $app->arr_to_json($test_result);
                            
                        } else $query_result = $app->errorResult("Method \'$method\' not found from \'$modelName\'");
                    }
                    
                    $result .= ($result?',':'').$query_result;
                }
            } else $result = '"not query"';
        }
    } else $result = '{"error":"error signature"}';
    
    echo '{"response":['.$result.$pid_add.']'.errorJSON().'}';

    if ($db) $db = null;
?>