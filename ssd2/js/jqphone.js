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

function topHide(elem, limit, onClick) {
    var vis = -1;
    function uVis() {
        var avis = $(window).scrollTop() > limit;
        if (avis != vis) elem[(vis = avis)?'fadeIn':'fadeOut']();
    }
    
    $(document).on('scroll.register', $(window).selector, uVis);    
    if (onClick) $(document).on('click.register', elem.selector, onClick);    
    uVis();
}

$(window).ready(function() {
    topHide($('.totop'), 50, function() {
        $("html, body").stop().animate({scrollTop:0}, '500', 'swing');
    });
    
    topHide($('.smartmbtn'), 50, function() {
        $.mobile.activePage.find('[data-role="panel"]').panel('toggle');
    });
});