<?
    GLOBAL $ALIASES, $AUXTYPES, $EVENT_TYPES, $sheme, $ADMINS, $FDBGLogFile, $ADMINEMAILS, $ADMINNOTIFY;
    define('SSPATH', MAINPATH.'ss/');
    define('PJAPPID', '488687');
    define('DEFAULT_LANG', 'ru');
    define('COLLECT_QUESTION', true); 
    define('VER', 344);

    define('SS_DATEFORMAT', 'd.m.Y');
    define('SS_TIMEFORMAT', 'H:i:s');
    define('SS_DATETIMEFORMAT', SS_DATEFORMAT.' '.SS_TIMEFORMAT);
//<!---FROM CATALOG
    define('BASEHOST', 'oformi-foto.ru');
    define('FRAMES_PNGURL', 'https://'.BASEHOST.'/png_tmp/%s.png');
    define('BASEAPP_URL', MAINURL);
    define('BASESTORAGEURL', $sheme.BASEHOST);
    define('RESVSTORAGEURL', $sheme.BASEHOST);
//    define('RESVSTORAGEURL', 'http://pjof.ru');
    define('PJJS_URL', MAINURL);
//---FROM CATALOG-->

    define('CMDDLGGROUPS', '[{group_id: 1}, {group_id: 2}]');    
    
    define('FINDSELECTOR', 'naydeny-ramki');
    define('CATALOGSELECTOR', 'fotoramki');
    define('HOLIDAYSSELECTOR', 'holidays');
    define('TEMPLATESELECTOR', 'template');
    
    define('AVAPATH', MAINPATH.'user_avatars/');   
    define('AVAURL', MAINURL.'/user_avatars/');       
    
    define('ORDERPATH', MAINPATH.'images/order/');   
    define('ORDERURL', MAINURL.'/images/order/');

    define('TREEPATH', '/home/vmaya/tree/');   
    define('TREEURL', '//'.BASEHOST.'/tree/');
    define('GAMEPATH', MAINPATH.'images/game/');
    define('GAMEURL', $sheme.BASEHOST.'/images/game/');    

    define('PUSHALLKEY', 'fcc95ff04c1cfb96922f1eb48ecf1326');
    define('PUSHALLID', '3072');
    
    define('ANONIMUID', 0); 
    $FDBGLogFile = LOGPATH.'oformi-foto.log';
    
    $ADMINS = array(
        array('source'=>'mm', 'uid'=>'8062938299454250872'),
        array('source'=>'vk', 'uid'=>'44108006')
    );  
    
    $ADMINEMAILS = array(
        'notify'=>'fwadim@mail.ru'
    );
    
    $ADMINNOTIFY = false;
    
    $ALIASES = array(
        CATALOGSELECTOR=>'catalog',
        FINDSELECTOR=>'catalog',
        HOLIDAYSSELECTOR=>'catalog'
    );
    
    $AUXTYPES = array('vk', 'fb', 'ok', 'mm');
    $EVENT_TYPES = array('ADDVOTE','GA-COMM','GM-COMM','PCM-LIKE','GCM-LIKE','LOGIN','PA-COMM','PG-COMM','TOGAME');

    $LANGINSTALL = array('ru', 'uk', 'en', 'si');
?>
