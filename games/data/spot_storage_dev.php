<?
    $servers = array(
        '{"JPG_URL":"oformi-foto.ru/JPG","PREVIEW_URL":"oformi-foto.ru/pj/jpg_preview","PREVIEW_URL120":"oformi-foto.ru/preview120","OUTSIDEID":"27343"}',        
        '{"JPG_URL":"pjof.ru/JPG","PREVIEW_URL":"pjof.ru/jpg_preview","PREVIEW_URL120":"pjof.ru/preview120","OUTSIDEID":"27343"}',        
        '{"JPG_URL":"oformi-foto.ru/JPG","PREVIEW_URL":"pjof.ru/pj/jpg_preview","PREVIEW_URL120":"oformi-foto.ru/preview120","OUTSIDEID":"27343"}'        
        //'{"JPG_URL":"cloth.1gb.ru/JPG","PREVIEW_URL":"cloth.1gb.ru/jpg_preview","PREVIEW_URL120":"cloth.1gb.ru/preview120","OUTSIDEID":"25776"}'        
    ); 
    
    $dev = isset($_GET['dev']) && $_GET['dev']; 
    $server_index = $dev?0:0;//((rand(0, 1000) > 300)?1:2);
    
    $cfg = json_decode(file_get_contents('temp_storage.json'));
    $cfg->options = json_decode($servers[$server_index]);
    $cfg->state = sys_getloadavg();
?>
