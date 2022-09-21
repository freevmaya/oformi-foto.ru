<?
    $isDev      = isset($_GET['dev']);
    $v = '';//(isset($_GET['dev'])?'?v='.rand(1, 100000):'');
    $jsExt      = 'js';
    $jaPath     = $isDev?'jsdev':'js22';
    $dataModel  = 'pj_modelDev';
    $hostURL    = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $baseURL    = $hostURL.dirname($_SERVER['PHP_SELF']).'/';
    $language   = 'rus';
    $charset    = 'utf-8';

    $title = 'Удобный фотосервис, которому можно доверить оформление ваших фотографий, другой имидж, детские шаблоны, поздравления, фотографии';
    $description = 'Создай открытку, календарик, этикетку со своим фото, сохрани на компьютер, отправь другу или размести в гостевой. Преврати свое фото в шедевр!';
    
    $head           = '';
    $body           = '';
    $script         = '';
    $shareButtons   = '';
    
    include('include/shareButtons.php');
?>