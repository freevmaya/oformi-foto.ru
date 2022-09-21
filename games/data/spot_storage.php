<?
    $servers = array(
        '{"JPG_URL":"oformi-foto.ru/JPG","PREVIEW_URL":"oformi-foto.ru/pj/jpg_preview","PREVIEW_URL120":"oformi-foto.ru/preview120","OUTSIDEID":"27343"}'
    ); 
    
    $dev = isset($_GET['dev']) && $_GET['dev']; 
    $server_index = $dev?0:0;//((rand(0, 1000) > 300)?1:2);
    
    $cfg = json_decode(file_get_contents('temp_storage'.($dev?'_dev':'').'.json'));
    $cfg->options = json_decode($servers[$server_index]);
    $cfg->state = sys_getloadavg();
?>
