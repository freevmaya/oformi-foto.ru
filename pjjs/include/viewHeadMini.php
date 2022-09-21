<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$charset?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="styles/styles-view.css?v=1" rel="stylesheet">
<link href="styles/styles-slider.css" rel="stylesheet" />

<script stype="text/javascript">var HOSTURL='<?=$hostURL;?>';var PROTOCOL='<?=$protocol?>';var VER='<?=$v?>'; var MAXSAVEADV = 0;</script>

<!--[if IE]><script type="text/javascript" src="ie/excanvas.<?=$jsExt?>"></script><![endif]-->
<script src="<?=$jaPath?>/locale/<?=$language?>.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/vmini.<?=$jsExt?>" type="text/javascript"></script>
<!-- Yandex.Metrika counter --> <script type="text/javascript" > (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}; m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)}) (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym"); ym(25946306, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true }); </script> <noscript><div><img src="https://mc.yandex.ru/watch/25946306" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->

<script type="text/javascript">
    var oldError = window.onerror;
    window.onerror = function myErrorHandler(errorMsg, url, lineNumber) {

    	let params = {};
        params[url] = {};
        params[url][errorMsg] = (lineNumber > 1 ? (lineNumber + " ") : "") + window.navigator.userAgent;
        ym(25946306,'reachGoal','js_mobil_error', params);
        if (oldError) return oldError(errorMsg, url, lineNumber);
        return false;
    }
</script>
<?=$head?>