<?
    $isDev      = isset($_GET['dev']);
    $v          = '';//(isset($_GET['dev'])?'?v='.rand(1, 100000):'');
    $jsExt      = 'js';
    $jaPath     = 'jsa';
    $dataModel  = 'pj_lf'.($isDev?'_dev':'');
    $hostURL    = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $baseURL    = $hostURL.dirname($_SERVER['PHP_SELF']).'/';

    $head           = '';
    $body           = '';
    $script         = '';
?>