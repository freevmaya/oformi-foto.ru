    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?=$description?>" />
    <meta name="keywords" content="<?=$keywords?>" />
    <meta name="verify-admitad" content="28733bfc01" />
    <meta name="mrc__share_title" content="<?=$title?>" />
    <meta name="mrc__share_description" content="<?=$controller->getDescription('', 30)?>" />
    <meta name='yandex-verification' content='4a7430ebd7905ad9' />
    <meta name="cpazilla-site-verification" content="854691c9d4b4c96e3" />
    <meta name="google-site-verification" content="IYv6UEcutwK-7_cpJXZY_BeMYxMM2dLnOV1xSRB6F-U" />
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    
<?
    $controller->addScript(SSURL.'language/js/'.ss::lang().'.js');
        
    GLOBAL $sheme;
    $isImage = false;
    $isTitle = false;
    foreach ($controller->og() as $og_name=>$value) {
        if ($og_name == 'image') $isImage = true;
        else if ($og_name == 'title') $isTitle = true;
        echo "<meta property=\"og:{$og_name}\" content=\"{$value}\" />\n";
    }
    
    if (!$isImage) echo '<meta property="og:image" content="'.$controller->getMeta('page-image').'"/>'."\n";
    if (!$isTitle) echo '<meta property="og:title" content="'.$title.'"/>'."\n";
?>    

    <meta property="fb:app_id" content="1626953174227585" />
         
    <link rel="image_src" href="<?=$controller->getMeta('page-image')?>" />
<?
    foreach ($controller->meta() as $meta_name=>$meta) {
        if (is_array($meta)) {
            echo "<meta name=\"$meta_name\" ";    
            foreach ($meta as $key=>$value) {
                echo $key."=\"$value\" ";
            }
            echo "/>\n";
        }
    }
    
if ($controller->noindex) {?>    
    <meta name="robots" content="noindex, follow"/>
<?}?>    
    <title><?=$title?></title>
    <link rel="stylesheet" href="<?=SSURL.$controller->getFileStyle('styles_phone.css')?>" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?=SSURL.$controller->getFileStyle('cat_phone.css')?>" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?=MAINURL?>/pjjs/styles/styles-view.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?=MAINURL?>/pjjs/styles/styles-slider.css" type="text/css" media="screen" />
<?
    foreach ($controller->getStyles() as $fileStyle) {
        echo '<link rel="stylesheet" type="text/css" href="'.$fileStyle.'">'."\n";
    }
?>  
    <link rel="stylesheet" href="<?=SSURL?>css/themes/default/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="<?=$sheme?>fonts.googleapis.com/css?family=Open+Sans:300,400,700">

	<script src="<?=SSURL?>js/jquery.min.js"></script>
    <script src="<?=SSURL?>_assets/js/index.js"></script>
    <script src="<?=SSURL?>js/jquery.mobile-1.4.5.min.js"></script>
    <script src="<?=SSURL?>js/jqphone.js"></script>
    <script src="<?=SSURL?>js/cat_phone.js"></script>
    <script src="<?=SSURL?>js/adv/adv-google.js"></script>
    <script data-ad-client="ca-pub-8187394655464689" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script stype="text/javascript">var PROTOCOL = document.location.protocol + '//';</script>
<?


//PJJS
    $language = ss::lang();
    $jaPath = MAINURL.'/pjjs/jsa';
?>
    <script src="<?=$jaPath?>/locale/<?=$language?>.js" type="text/javascript"></script>
<?    
    if (!isset($_GET['dev'])) {
?>
    <script src="<?=$jaPath?>/vmini.js"></script>
<?} else {?>

    <script src="<?=$jaPath?>/debug.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/base64.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/events.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/ismobile.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/canvasSave.js" type="text/javascript"></script>

    <script src="<?=$jaPath?>/geom/matrix.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/geom/vector.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/geom/rectangle.js" type="text/javascript"></script>

    <script src="<?=$jaPath?>/canvas/utils.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/canvas/draw-object.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/canvas/draw-object-more.js" type="text/javascript"></script> 
    <script src="<?=$jaPath?>/canvas/gcanvas.js" type="text/javascript"></script> 
    <script src="<?=$jaPath?>/canvas/holes-image.js" type="text/javascript"></script>
     
    <script src="<?=$jaPath?>/input/base-input.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/input/pc-input.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/input/tablet-input.js" type="text/javascript"></script>

    <script src="<?=$jaPath?>/pjApp/baseTmplList.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/pjApp/defImages.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/pjApp/pjcanvas.js" type="text/javascript"></script> 
    <script src="<?=$jaPath?>/pjApp/baseapp.js" type="text/javascript"></script>
    <script src="<?=$jaPath?>/pjApp/leftPJApp.js" type="text/javascript"></script> 
    <script src="<?=$jaPath?>/pjApp/toast.js" type="text/javascript"></script>  

    <script src="<?=$jaPath?>/locale/bypass.js" type="text/javascript"></script>
<?
    }

    foreach ($controller->getScripts() as $fileScript) {
        echo '<script src="'.$fileScript.'" type="text/javascript"></script>'."\n";
    } 
?>        