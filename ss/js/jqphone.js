function loaderVisible(value) {
    $.mobile.loading(value?'show':'hide', {
    	text: locale.LOADTEXT,
    	textVisible: true,
    	theme: 'a',
    	html: ""
    });
}

function vindicatorEvent(vidElem, onEvent) {
    var t = 0;
    var mpid = vidElem.parents('div[data-role="page"]').attr('id');
    
    function checkBottom() {
        if (mpid == $.mobile.activePage.attr('id')) {
            w = $(window);
            var wb = w.scrollTop() + w.height();
            if (wb > Math.floor(vidElem.position().top) - 10) {
                if (t == 0) onEvent('show');
                t = 1;                
            } else if (t > 0) {
                t = 0;
                onEvent('hide');
            }
        }
    }
    $(window).scroll(checkBottom);
    checkBottom(); 
}

function onError() {
    alert(locale.WENTWRONG);
    $.mobile.loading('hide');
}   

function ajaxSendA(action, method, formData, onSuccess) {
    $.ajax({
        url: action,
        type: method,
        dataType: 'json',
        success: function(data) {
            onSuccess(data);
        },
        error: onError,
        data: formData,
        contentType: false,
        processData: false,
        cache: false
    });         
}

function topHide(elem, limit) {
    var vis = -1;
    function uVis() {
        var avis = $(window).scrollTop() > limit;
        if (avis != vis) elem[(vis = avis)?'fadeIn':'fadeOut']();
    }
    
    $(document).on('scroll.register', $(window).selector, uVis);    
    uVis();
}

(function() {
    var page_loaded = {};
    $(document).bind('pagechange', function() {

        let p = $.mobile.activePage;
        mainid = p.attr("id");
        if (!page_loaded[mainid]) {
            page_loaded[mainid] = true;

            topHide(p.find('.min-nav'), 50);

            $(document).on('click.register', p.find('.totop').selector, function() {
                $("html, body").stop().animate({scrollTop:0}, '500', 'swing');
            });

            $(document).on('click.register', p.find('.smartmbtn').selector, function() {
                p.find('[data-role="panel"]').panel('toggle');
            });
        }
    });
})();


var BASEURL = document.location.protocol + "//" + document.location.hostname + "/";

window.addEvent = function(n, a) {}