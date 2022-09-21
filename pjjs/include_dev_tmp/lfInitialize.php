<?
    $v          = '';//(isset($_GET['dev'])?'?v='.rand(1, 100000):''); 
    $jsExt      = 'js';//.($isDev?('?v='.rand(10, 100000)):'');
    $jaPath     = $isDev?'jsa':'js22';
    $dataModel  = 'pj_lf'.($isDev?'_dev':'');
    $hostURL    = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $baseURL    = $hostURL.dirname($_SERVER['PHP_SELF']).'/';
    $language   = 'rus';
    $charset    = 'utf-8';
    $shareButtons = '';

    $title = 'Удобный фотосервис, которому можно доверить оформление ваших фотографий, другой имидж, детские шаблоны, поздравления, фотографии';
    $description = 'Создай открытку, календарик, этикетку со своим фото, сохрани на компьютер, отправь другу или размести в гостевой. Преврати свое фото в шедевр!';
    
    $head           = '';
    $body           = '';
    $script         = '';
?>