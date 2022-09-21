function beat(elem, style) {
    style = style?style:'margin-left';
    var fx = new Fx();
    fx.set = function(value) {
        sine = Math.sin(value * Math.PI * 2);
        elem.setStyle(style, Math.round(sine * 10));
        return value;
    }  
    fx.start(0, 1);      
}

function vObject(view, obj) {
    $each(obj, function(value, field) {
        var cl = 'd-' + field;
        if (ac = view.getElements('.' + cl)) {
            ac.forEach(function(c) {
                c.removeClass(cl);
                tag = c.get('tag');
                if (tag == 'img') c.set('src', value);
                else if (tag == 'a') {
                    c.set('href', value);
                    if (index = c.get('data-id')) c.set('data-id', 'a-' + obj[index]);
                } else c.set('html', value);
            });
        }
    });
    return view;
}

function vindicatorEvent(vidElem, onEvent) {
    var t = 0;
    
    function checkBottom() {
        scr = $(window).getScroll();
        size = $(window).getSize();
        if ((scr.y + size.y) > vidElem.getPosition().y) {
            if (t == 0) onEvent('show');
            t = 1;                
        } else if (t > 0) {
            t = 0;
            onEvent('hide');
        }
    }
    $(window).addEvent('scroll', checkBottom);
    checkBottom(); 
}

function links(data_rel) {
    var list = [];
    $$('a').each(function(item) {
        if ($(item).get('data-rel') == data_rel) list.push($(item));
    });
    return list;   
}

function resetIframeLinks() {
    links('iframeBox').each(function(item) {
        item.addEvent('click', function() {
            var indent = ($(this).get('data-id') || '').split('-');
            if (indent[0] == 'a') {
                var fileURL = FRAMES_URL + indent[1] + '.jpg';
                var image = new Element('img');
                image.addEvent('load', (function() {
                    var wSize = window.getSize();
                    var border = 30;
                    var scale = Math.min(1, Math.min((wSize.x - border)/image.width, (wSize.y - border - 70)/image.height));
                    
                    var url = this.href;
        			SqueezeBox.fromElement(url, {handler: 'iframe', size: {
                        x: Math.round(image.width * scale),
                        y: Math.round(image.height * scale) + 40
                    }});
                }).bind(this));
                image.src = fileURL;
            } else {
                var wSize = window.getSize();
    			SqueezeBox.fromElement(this.href, {handler: 'iframe', size: {
                    x: Math.round(wSize.x * 0.8),
                    y: Math.round(wSize.y * 0.8)
                }});
            }
            return false;             
    	});
    });
}

function resetItemTools() {
    $$('.item-tools').each(function(item) {
        var sn = 'margin-left';
        var iw = item.getSize().x + 10;
         
        function showTools(show) {
            item.tween(sn, item.getStyle(sn), show?0:-iw);
        }
        item.setStyle(sn, -iw);
        item.set('tween', {duration: 'long', transition: Fx.Transitions.Back.easeOut});
        item.getParent().addEvent('mouseover', function() {showTools(true);}); 
        item.getParent().addEvent('mouseout', function() {showTools(false);});
    });        
}

window.addEvent('domready', function() {
    var user = null;
    window.addEvent('userInfo', function(a_user) {user = a_user;});
    
    SqueezeBox.assign($$('a[rel=imageBox]'), {handler: 'image'});
    resetIframeLinks();    
    if ((sml = $$('.submitLayer')).length > 0) button = sml[0].getElement('input');
    if ((cf = $('cadsForm')) && button)
        cf.getElements('input').addEvent('click', function(e) {
        beat(button);
        sml.highlight('#F00', '#FFF');
    });
});

function catalogListInit(tmplListURL, browserURL, page, groups) {
    resetItemTools();
    var ix = 0; li = 0, topy = [];
    var tmpls = $('tmpls'); 
    var pngLinkDl = new Element('a', {
        target: "_blank",
        referrerpolicy: "origin"
    });
    pngLinkDl.inject(tmpls);
    var list = tmpls.getElement('.list');
    
    var mwidth = tmpls.getSize().x;
    var loaded = 0;
    var items = $$('.item');
    var isEndPages = false;
    
    function onComplete() {
        var height = 0;
        for (var i=0; i<topy.length;i++) height = Math.max(height, topy[i]);
        list.setStyles({
            height: height,
            width: ix
        });   
    }
    
    function refreshItems(items) {
        items.each(function(item) {
            var img = item.getElement('img');
            var ims = img.getSize();
            function checkSize() {return (ims.y > 0) && (ims.x > 0)};
            
            function onLoaded() {
                if (!checkSize()) ims = img.getSize();
                if (ims.y < ims.x * 0.3) onLoaded.delay(50);
                else {  
                    var si = item.getSize();
                    if ((topy[li] == undefined) && (ix + si.x < mwidth)) {
                        item.setStyle('margin-left', ix);
                        topy[li] = si.y;
                        ix+=si.x; 
                        li++;
                    } else {
                        var minh = 1000000;
                        for (var i=0; i<topy.length; i++) 
                            if (minh > topy[i]) {
                                minh = topy[i];
                                li = i; 
                            }
                        item.setStyle('margin-left', li * si.x);
                        item.setStyle('margin-top', topy[li]);
                        topy[li] += si.y;
                    }
                    
                    loaded++;
                    item.tween('opacity', 0, 1);
                    onComplete();
                }
            }
            item.setStyle('position', 'absolute');
            if (img.complete || checkSize()) onLoaded();
            else {
                item.setStyle('visibility', 'hidden');
                img.addEvent('load', onLoaded);
            } 
            
            var pnga = item.getElement('.download_png');
            if (pnga) pnga.addEvent('click', function() {
                app.afterAdv(function() {
                    var url = pnga.get('href');
                    pngLinkDl.set('download', url);
                    pngLinkDl.set('href', url);
                    
                    var evt = document.createEvent("MouseEvents");
                    evt.initEvent("click", true, false);
                    
                    pngLinkDl.dispatchEvent(evt);
                });
                return false;
            });                                    
        });
    }
    
    function assignTmpls(items) {
        if (items && (tmpl = $('item-tmpl'))) {
            tmpl = tmpl.clone();
            tmpl.addClass('item');
            var a_items = [];
            items.each(function(item) {
                a_items.push(vObject(tmpl.clone(), item).inject(list));
            });
            
            resetIframeLinks();
            resetItemTools();
            refreshItems(a_items);
        }
    }
    
    function setLocation(curLoc){
        try {
          history.pushState("object or string", document.title, curLoc);
          return;
        } catch(e) {}
    }     
    
    function setPage(a_page) {
        if (page != a_page) {
            page = a_page;
            
            //setLocation(browserURL.replace('%s', page));

            history.replaceState(null, null, location.href.replace(/[\/\d]*\.html/, '/' + page + '.html'));
            
            $$('.paginator').each(function(pgn) {
                pgn.getElements('span').each(function(span) {
                    if (span.hasClass('current')) span.removeClass('current');
                    if (span.getElement('a').get('text') == page) span.addClass('current');                
                });
            });
        }
    } 
    
    var vind;
    if (vind = $(document).getElement('.v-indicator')) vindicatorEvent(vind, function (state) {
        if (!isEndPages && (state == 'show')) {
            (new Request.JSON({
                url: tmplListURL,
                data: 'page=' + (page + 1) + '&groups=' + groups,
                method: 'POST',
                onComplete: function(result) {
                    assignTmpls(result);
                    if (isEndPages = result.length == 0) {
                        vind.set('text', locale.ENDPAGE);
                        vind.removeClass('loader');
                    } else setPage(page + 1);
                },
                onError: function(text, error) {
                    alert(locale.WENTWRONG + ' ' + text);                    
                }
            })).send();
        }
    });
    
    refreshItems(items);
} 

function doSendCads() {
    var form = $('cadsForm');
    var catsElems = form.getElements('input');
    var cats = [];
    catsElems.each(function(elem) {
        if (elem.checked) cats.push(elem.value);
    }); 
    if (cats.length > 0) {
        var link = '';
        cats.each(function(id) {
            link += (link?'-':'') + id;
        });
        
        form.set('action', MAINURL + '/' + CATALOGSELECTOR + '/' + link + '.html');
        return true;
    }
    alert('Надо выбрать хотя бы одну категорию');
    return false;
}

function onImgLoad(img) {
    if (ptd = img.getParent('td')) ptd.removeClass('noPicture');
}

window.addEvent('domready', function() {
    near = $$('.near');
    if (near && (near.length > 0)) {
        var tid = (function() {
            tid = 0;
            var myFx = new Fx({duration: 2000, transition: Fx.Transitions.Back.easeInOut});
            myFx.set = function(value) {
                window.scrollTo(0, value);
            }
            
            myFx.start(0, near[0].getPosition().y - 90);
        }).delay(2000);
        window.addEvent('scroll', function() {
            if (tid) {
                clearTimeout(tid);
                tid = 0;
            }
        });
    }
});
