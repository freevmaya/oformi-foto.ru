    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?=$description?>" />
    <meta name="keywords" content="<?=$keywords?>" />
    <meta name="verify-admitad" content="28733bfc01" />
    <meta name="mrc__share_title" content="<?=$title?>" />
    <meta name="mrc__share_description" content="<?=$controller->getDescription('', 30)?>" />
    <meta name='yandex-verification' content='4a7430ebd7905ad9' />
    <meta name='yandex-verification' content='a97a87aff38821fb' />    
    <meta name="cpazilla-site-verification" content="854691c9d4b4c96e3" />
    <meta name="google-site-verification" content="IYv6UEcutwK-7_cpJXZY_BeMYxMM2dLnOV1xSRB6F-U" />
    <meta name="google-site-verification" content="R10SM2CHpCVyMae91cdzUSa9FrOj37QY4DSFMTZq_Lc" />
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    
<?
    $controller->styles[] = SSURL.'sbox/assets/SqueezeBox.css';
    $controller->styles[] = SSURL.'controls.css';
    $controller->addScript(SSURL.'sbox/SqueezeBox.js');
    $controller->addScript(SSURL.'js/utils.js');
    $controller->addScript(SSURL.'js/thankView.js');
    $controller->addScript(SSURL.'js/comments.js');
    $controller->addScript($sheme.'vk.com/js/api/share.js', true);
    $controller->addScript($sheme.'vk.com/js/api/openapi.js', true);
//    $controller->addScript($sheme.'mc.yandex.ru/metrika/watch.js');
    $controller->addScript(SSURL.'language/js/'.ss::lang().'.js');
    

    if (is_string($controller->og)) {
        echo $controller->og; 
    } else { 
        $isImage = false;
        $isTitle = false;
        foreach ($controller->og() as $og_name=>$value) {
            if ($og_name == 'image') $isImage = true;
            else if ($og_name == 'title') $isTitle = true;
            echo "<meta property=\"og:{$og_name}\" content=\"{$value}\" />\n";
        }
        
        if (!$isImage) echo '<meta property="og:image" content="'.$controller->getMeta('page-image').'"/>'."\n";
        if (!$isTitle) echo '<meta property="og:title" content="'.$title.'"/>'."\n";
    }
?>    

    <meta property="fb:app_id" content="1626953174227585" />
         
    <link rel="image_src" href="<?=$controller->getMeta('page-image')?>" />
<?
    foreach ($controller->meta as $meta_name=>$meta) {
        echo "<meta name=\"$meta_name\" ";    
        if (is_array($meta)) {
            foreach ($meta as $key=>$value) {
                echo $key."=\"$value\" ";
            }
        } else echo 'content="'.$meta.'"'; 
        echo "/>\n";
    }
    
if ($controller->noindex) {?>    
    <meta name="robots" content="noindex, follow"/>
<?}?>    
    <title><?=$title?></title>
	<link rel="stylesheet" href="<?=MAINURL.'/'.SSRELATIVE.$controller->getFileStyle('styles.css')?>?v=<?=$ver?>" type="text/css" media="screen" />
<?
        foreach ($controller->getStyles() as $fileStyle) {
            echo '<link rel="stylesheet" type="text/css" href="'.$fileStyle.'?v='.$ver.'">'."\n";
        }
?>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/loader-copy-mailru.js" type="text/javascript" charset="UTF-8"></script>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/ok_api.js" type="text/javascript"></script>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/swfobject.js" type="text/javascript"></script>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/mootools-1.2.6-core-yc.js" type="text/javascript"></script>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/mootools-1.2.5.1-tips.js" type="text/javascript"></script>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/app.js" type="text/javascript"></script>
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/post-image.js" type="text/javascript"></script> 
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/helpers.js" type="text/javascript"></script>  
    <script src="<?=MAINURL.'/'.SSRELATIVE?>js/adv/popupView.js" type="text/javascript"></script>  
<?if (!ss::$noadv) {?>    
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<?
    }
        foreach ($controller->getScripts() as $fileScript) {
            echo '<script src="'.$fileScript.'" type="text/javascript"></script>'."\n";
        }
        
    echo ss::getAllCSS();
?>
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "WebSite",
  "url": "<?=MAINURL?>",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "<?=MAINURL?>/naydeny-ramki/{search_term_string}.html",
    "query-input": "required name=search_term_string"
  }
}
</script>
<?if (!ss::$noadv) {?>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-8187394655464689",
          enable_page_level_ads: true
     });
</script>
<?}?>