<?
    $charset = 'UTF-8';
    header('Content-Type: text/html; charset='.$charset);
    include('/home/config.php');

    $FBApp          = '931425893607133';    
    $jaPath         = 'jsa';
    $sitename       = 'Прикольное оформление ваших фотографий';
    $title          = 'Оцените мое фото в рамке!';
    $og_title       = $title;
    
//--------------------------------     
    
    $sheme      = isset($_SERVER['HTTP_HTTPS'])?'https':'http';
    
    $link       = $sheme.'://oformi-foto.ru/pjjs.htm';
    $ref        = parse_url(@$_SERVER['HTTP_REFERER']);
    $ref_host   = @$ref['host'];
    $vkRef      = $ref_host == 'vk.com';
    $fbRef      = $ref_host == 'facebook.com';
    $ver        = 2;     
       
    $serverURL  = $sheme.'://'.$_SERVER['HTTP_HOST'];
    $baseURL    = $serverURL.dirname($_SERVER['PHP_SELF']).'/';
    if ($img = @$_GET['img']) {
        $imagePath = "images/users/$img.jpg";
        $imageURL   = $baseURL.$imagePath;
    } else $imageURL = null;
    
    $share = @$_GET['share'];
    $description    = 'Создай открытку, календарик, этикетку со своим фото, сохрани на компьютер, отправь другу или размести в гостевой.<br><a href="'.$link.'" target="_self">Преврати свое фото в шедевр!</a>';
    $isAndroid = strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;    
    $redirect = '//oformi-foto.ru';
    if ($isAndroid) $redirect = '//play.google.com/store/apps/details?id=com.oformifotoB';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$charset;?>">
<title><?=$title?></title>

<meta name="description" content="<?=strip_tags($description)?>" />
<meta name="viewport" content="width=device-width, initial-scale=1">    

<meta property="og:site_name" content="<?=$sitename?>" />
<meta property="og:url" content="<?=$baseURL.'/'.$_SERVER['REQUEST_URI']?>" />
<?if ($imageURL) {?><meta property="og:image" content="<?=$imageURL?>"/>
<?}?>
<meta property="og:title" content="<?=$og_title?>"/>
<meta property="fb:app_id" content="<?=$FBApp?>" />
<meta property="og:type" content="website" />

<link rel="image_src" href="<?=$imageURL?>" />

<link href="styles/user.css?v=<?=$ver?>" rel="stylesheet">
<script src="<?=$jaPath?>/mootools-core-1.4.5.js" type="text/javascript"></script>
<?if ($vkRef) {?><script type="text/javascript" src="//vk.com/js/api/openapi.js?105"></script><?}?>
<script type="text/javascript">

    function updateSize() {
        var content = $('content'); 
        var size = $('wrapper').getSize();
        var csize = block.getSize();
        
        content.setStyle('margin-top', Math.round((size.y - csize.y) * 0.4) + 'px');
    }
    
    window.addEvent('domready', function() {
        (function() {
            $('image').set('src', '<?=$imageURL?>');
            updateSize();
        }).delay(500);
    });
    
    function onLoadImage() {
        $('content').fade('in');
        if (loader=$('loader')) loader.destroy();
        updateSize();
    }
    
    function removeImageBeforeClose() {
        var request = new Request({
            url : 'images/transport.php',
            data: {removeFile: '<?=$img?>'}
        });
        request.post();
    }
    
    window.addEvent('resize', updateSize);
    
    <?if ($vkRef) {?>  VK.init({apiId: 4108301, onlyWidgets: true});<?}?>
    
</script>
<body>
<?
    if ($imageURL) {    
?>
<div id="wrapper">
    <div id="loader"><p>Подождите пожалуйста, загружается изображение</p></div>
    <div id="content">
        <div id="block">        
            <h1><?=$title?></h1>
            <a href="<?=$link;?>" target="_self"><img onload="onLoadImage()" align="left" id="image"/></a>
            <p><?=$description?></p>
<?if ($share) {?>
<div class="share-container">
    <?include('share/'.$share.'.php');?>
</div> 
<?}?>
            <div class="clr"></div>
        </div>
<?if ($vkRef) {?>        
        <div id="vk_comments"></div>
        
        <script type="text/javascript">
            VK.Widgets.Comments("vk_comments", {limit: 5, width: "720", attach: "*"});
        </script>
<?}?>        
    </div>
    
</div>
<?        
    } else {
?>
    <script type="text/javascript">
        document.location.href= '<?=$redirect?>';
    </script>
<?    
    }
    include('include/metrica.php');
?>
</body>
</html>