var vkshare;
function showShareView() {
    var sl = $('share_layer');
    sl.setStyle('display', 'flex');
    sl.tween('opacity', [0, 1]);
    
    (function() {
        sl.getElement('.close-label').tween('opacity', [0, 1]);
    }).delay(3000);
    
    if (!vkshare) (vkshare = $$('.vk-share')[0]).addEvent('click', function() {
        closeShareView.delay(5000);
    });
}

function closeShareView() {
    var sl = $('share_layer');
    sl.fade('out');
    (function() {
        if (pjsh = $('pjjs_share')) pjsh.destroy();
    }).delay(1000);             
}

function fb_shareDialog() {
    FB.ui({
        method: 'share',
        href: document.location.href,
    }, function(response){
        if (response) {
            closeShareView();
            console.log(response);
        }
    });
}

function shareInit() {
    VK.init({apiId: 3323844, onlyWidgets: true});
    
    function _as(d, s, id, src) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = src;
        fjs.parentNode.insertBefore(js, fjs);
    }
    //FACEBOOK
    _as(document, 'script', 'facebook-jssdk', "//connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v2.7&appId=1626953174227585");
    
    //OK
    !function (d, id, did, st) {
        function onShare(e) {
            if (e.data.split) {
                var args = e.data.split("$");
                if (args[0] == "ok_shared") closeShareView();   //"ok_setStyle"
            }
        }    
        var js = d.createElement("script");
        js.src = "https://connect.ok.ru/connect.js";
        js.onload = js.onreadystatechange = function () {
        if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
        if (!this.executed) {
          this.executed = true;
          setTimeout(function () {
            OK.CONNECT.insertShareWidget(id,did,st);
          }, 0);
        }
        }};
        d.documentElement.appendChild(js);
        
        if (window.addEventListener) window.addEventListener('message', onShare, false);
        else window.attachEvent('onmessage', onShare);
        
    }(document,"ok_shareWidget", document.location.href, "{width:125,height:35,st:'rounded',sz:30,ck:2,nc:1}");

    //VK    
//    VK.Widgets.Like("vk_like", {type: "button", height: 34});
    var sl = $('share_layer');
    sl.addEvent('click', function(e) {
        if (e.target == sl) closeShareView();
    });
}

function checkFlex() {
    var test = new Element('div', {styles: {display: 'flex'}});
    return test.getStyle('display') == 'flex';
}

window.addEvent('domready', function() {
    if (checkFlex()) {
        shareInit();
    } else {
        $('pjjs_share').destroy();
        $('share_layer').destroy();
    } 
});