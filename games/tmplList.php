<?php
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    define('TEMPLATESLIMIT', 1000);
	date_default_timezone_set('Europe/Moscow');

    include_once('include/engine.php');
    include_once('/home/secrects.inc');
    include_once(INCLUDE_PATH.'/_edbu2.php');
    
    function getGroupTemplates($groups, $page, $limit) {
        $where      = '';
        $count      = count($groups);
        foreach ($groups as $group_id)
            $where .= ($where?' OR ':'')."group_id=$group_id";
        $query = 'SELECT * FROM
                    (SELECT tmpl_id, COUNT(tmpl_id) AS `count`, COUNT(`weight`) AS `weight` 
                    FROM `gpj_templates` 
                    WHERE (%s) GROUP BY tmpl_id) tmpls
                WHERE tmpls.`count`='.$count.'
                ORDER BY tmpls.`tmpl_id` DESC, tmpls.`weight` DESC
                LIMIT '.($page * $count).','.$limit;
        
        $list = DB::asArray($query, $where);
        $result = array();
        foreach ($list as $item) $result[] = $item['tmpl_id'];
        return $result;
    }
    
    if ($group = @$_GET['group']) {
        header('Content-Type: text/json; charset=utf-8');
        $limit  = isset($_GET['limit'])?$_GET['limit']:TEMPLATESLIMIT;
        $page   = isset($_GET['page'])?$_GET['page']:0;
        echo 'var tmpls = '.json_encode(getGroupTemplates(explode(',', $group), $page, $limit)).';';
    };
?>