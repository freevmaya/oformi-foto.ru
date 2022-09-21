<?
    $isDev      = isset($_GET['dev']);
    $v          = '';//(isset($_GET['dev'])?'?v='.rand(1, 100000):'');
    $jsExt      = 'js';
    $jaPath     = $isDev?'jsa':'js22';
    $dataModel  = 'pj_modelDev';
    $hostURL    = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $baseURL    = $hostURL.dirname($_SERVER['PHP_SELF']).'/';
    $language   = 'rus';
    $charset    = 'utf-8';

    $head           = '';
    $body           = '';
    $script         = '';
    $shareButtons   = '';  
    
    include('include/shareButtons.php');
?>