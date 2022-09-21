<?
    $scripts[] = '//api.ok.ru/js/fapi5.js';
    $uid = isset($_GET['logged_user_id'])?$_GET['logged_user_id']:'5241';
    
    $largs = explode('&', $_GET['custom_args']);
    $args = array();
    foreach ($largs as $arg) {
        $l = explode('=', $arg);
        $args[$l[0]] = $l[1];
    }
    
    
    $id = trim(isset($args['id'])?$args['id']:0);
    
?>