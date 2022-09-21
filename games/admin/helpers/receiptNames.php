<?
    header("Content-Type: text/json; charset=utf-8");
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    if ($_POST['value'] && $_POST['tmpl_id']) {
        include_once('/home/config.php');
        include_once(HOMEPATH.'/secrects.inc');
        include_once(INCLUDE_PATH.'/_dbu.php');
        $charset = 'utf8';
        
        $a = explode('/', addslashes($_POST['value']));
        $autor = isset($_POST['autor'])?$_POST['autor']:0;
        $name = $a[0];//iconv('CP-1251', 'UTF-8', $a[0]);
        $desc = isset($a[1])?$a[1]:'';//isset($a[1])?iconv('CP-1251', 'UTF-8', $a[1]):'';
        echo sql_query("UPDATE gpj_tmplOptions SET `autor_id`=$autor, `name`='$name', `desc`='$desc' WHERE tmpl_id={$_POST['tmpl_id']}");
    } else echo '0';
?>