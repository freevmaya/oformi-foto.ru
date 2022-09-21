<?php


    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    error_reporting(7);
	date_default_timezone_set('Europe/Moscow');

    include_once('../../include/engine2.php');
    include_once(INCLUDE_PATH.'/_edbu2.php');

    function outList($list) {
        foreach ($list as $item) {
            echo "{\n    id:{$item['id']},\n    ears: {$item['ears']},\n    group: {$item['group']}";
            if ($item['ws']) echo ",\n    ws:{$item['ws']}";
            if ($item['corr']) echo ",\n    corr:{$item['corr']}";
            echo "\n},";
        }
    } 
?>
<h1>Cloth</h1>
<pre>
<?
    outList(DB::asArray("SELECT * FROM `clt_templates` WHERE type='c' AND checked=0"));
?>
</pre>
<hr>
<h1>Hairs</h1>
<pre>
<?    
    outList(DB::asArray("SELECT id,ears,`group` FROM `clt_templates` WHERE type='h' AND checked=0"));
?>    
</pre>
<?
    if ($db) mysql_close($db);
?>