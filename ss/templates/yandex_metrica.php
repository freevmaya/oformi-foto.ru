<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(4524184, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });

    window.addEvent('SAVETOFILE', function(e) {
        statTarget('save', {});
    });

    function statTarget(t, p) {
        if (window.yaCounter4524184)
           window.yaCounter4524184.reachGoal(t, p);
    }

    var oldError = window.onerror;
    window.onerror = function myErrorHandler(errorMsg, url, lineNumber) {
        let params = {};
        params[url] = {};
        params[url][errorMsg] = (lineNumber > 1 ? (lineNumber + " ") : "") + window.navigator.userAgent;
        ym(4524184,'reachGoal','js_mobil_error', params);
        if (oldError) return oldError(errorMsg, url, lineNumber);
        return false;
    }
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/4524184" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->