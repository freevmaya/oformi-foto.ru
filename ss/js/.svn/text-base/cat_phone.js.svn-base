var cat_phone = function(page, MAXIMGWIDTH, groups, FRAMES_URLPREVIEW, pagesURL, type) {
    var list, tmpls, items, mainpage, mainid, loadState, itemTmpl, end_list=false, isResize = false, scrollY;
    var loaded, topy = [];
    
    function oval(a_data, field) {
        return a_data?a_data[field]:'';
    }
    
    var tpc = {
        c: {
            initImg: function(ibox, onComplete) {
                var img = ibox.find('img');
                function imgLoaded() {
                    loaded++;
                    checkComplete(onComplete);
                };
                if (img.prop('complete')) imgLoaded();
                else img.load(imgLoaded);
                return ibox;
            },
            
            resetIBox: function(ibox, a_data) {
                ibox.css({'margin-left': 0, 'margin-top': -500});
                ibox.find('p').text(oval(a_data, 'name'));
                ibox.find('a').attr('href', oval(a_data, 'link'));
                ibox.find('img').attr('src', a_data?(FRAMES_URLPREVIEW + a_data.tmpl_id + '.jpg'):'');
                return ibox;
            },
            
            appendTmpls: function(a_data) {
                function onComplete() {
                    tpc.refresh(newItems);
                    loaderVisible(false);
                }
                var items_layer = list.find('.items_layer');
                var newItems = [];
                for (var i=0; i<a_data.length; i++) {
                    var ibox = tpc.resetIBox(itemTmpl.clone(), a_data[i]);
                    tpc.initImg(ibox, onComplete);
                    items_layer.append(ibox);
                    newItems.push(ibox);
                }
                items   = list.find('.item');
                checkComplete(onComplete);
            },
                
            refresh: function(items) {
                var ix, li, mwidth, cols, col, iwidth;
                ix = 0;
                li = 0;
                mwidth = list.width();
                cols = Math.ceil(mwidth / (MAXIMGWIDTH + 14)); //14 - space around image
                col = 0;
                iwidth = Math.floor(mwidth / cols);
                
                $.each(items, function () {
                    var item = $(this);
                    item.find('table').css('width', iwidth + 'px');
                    var si = {x:item.outerWidth(), y: Math.round(item.outerHeight())};
                    if ((topy[col] == undefined) && (col < cols)) {
                        item.css({'margin-left': ix + 'px', 'margin-top': '0px'});
                        topy[col] = si.y;
                        ix+=si.x; 
                        col++;
                    } else {
                        var minh = 100000000;
                        for (var i=0; i<cols; i++) 
                            if (minh > topy[i]) {
                                minh = topy[i];
                                li = i; 
                            }
                        item.css({'margin-left': Math.round(li * si.x) + 'px', 'margin-top': topy[li] + 'px'});
                        topy[li] += si.y;
                    }
                });
                
                var height = 0;
                for (var i=0; i<topy.length;i++) height = Math.round(Math.max(height, topy[i]));
                list.find('.items_layer').css('height', height + 'px');  
            }      
                
        },
        h: {
            initImg: function(ibox, onComplete) {
                var img = ibox.find('img');
                function imgLoaded() {
                    loaded++;
                    checkComplete(onComplete);
                };
                if (img.prop('complete')) imgLoaded();
                else img.load(imgLoaded);
                return ibox;
            },
            
            resetIBox: function(ibox, a_data) {
                var hname = oval(a_data, 'name'); 
                var image = (a_data && a_data.tmpl_id)?(FRAMES_URLPREVIEW + a_data.tmpl_id + '.jpg'):'';
                ibox.find('.hName').text(hname);
                ibox.find('.hlink').attr('href', oval(a_data, 'hlink'));
                ibox.find('.desc b').text(oval(a_data, 'date'));
                ibox.find('p').text(oval(a_data, 'desc'));
                ibox.find('.hTmpl a').attr('href', oval(a_data, 'link'));
                ibox.find('img').attr('src', image).attr('alt', hname);
                return ibox;
            },
            
            appendTmpls: function(a_data) {
                function onComplete() {
                    tpc.refresh(newItems);
                    loaderVisible(false);
                }
                var items_layer = list.find('.items_layer');
                var newItems = [];
                for (var i=0; i<a_data.length; i++) {
                    var ibox = tpc.resetIBox(itemTmpl.clone(), a_data[i]);
                    if (a_data[i].tmpl_id) tpc.initImg(ibox, onComplete);
                    else loaded++;
                    items_layer.append(ibox);
                    newItems.push(ibox);
                }
                items   = list.find('.item');
                checkComplete(onComplete);
            },
                
            refresh: function(items) {
            }      
                
        }
    }[type];
    
    $(document).bind('pagechange', function() {
        if (!mainid) {
            mainid = $.mobile.activePage.attr('id');
            mainpage = $.mobile.activePage;
            initialize();
        } else if (isMainPage()) {
            mainpage = $.mobile.activePage;
            onEvents();
            if (isResize) onResize(); 
            $(window).scrollTop(scrollY);
        }
    });
    
    function isMainPage() {
        return mainid == $.mobile.activePage.attr('id');
    }
    
    function initialize() {
        loaded      = 0;
        list        = mainpage.find('.list');
        tmpls       = mainpage.find('.tmpls');
        itemTmpl    = tpc.resetIBox($(list.find('.item')[0]).clone(), null);
        loaderVisible(true);
        
        items   = list.find('.item');
        items.each(function() {
            tpc.initImg($(this), startOnComplete);
        });
        onEvents();
        $(window).resize(onResize);
    } 
    
    function loadPage(page, groups, onSuccess) {
        var formData = new FormData();
        formData.append('page', page);
        formData.append('groups', groups);
        ajaxSendA(pagesURL, 'POST', formData, onSuccess);
    }
    
    function onVind(state) {
        if (isMainPage() && !loadState && (state == 'show') && isLoaded() && !end_list) {
            loaderVisible(true);
            loadState = true;
            page++;
            loadPage(page, groups, function(a_data) {
                if (a_data.length) tpc.appendTmpls(a_data);
                else endPage();
                loadState = false;
            });
        }
    }
        
    
    function onEvents() {
        vindicatorEvent(tmpls.find('.v-indicator'), onVind);
        $(window).scroll(onScroll);
    }
    
    function endPage() {
        tmpls.find('.v-indicator').text(locale.ENDPAGE);
        loaderVisible(false);
        end_list = true;
    }
    
    function isLoaded() {
        return loaded == items.length;
    }
    
    function checkComplete(onComplete) {
        if (isLoaded()) onComplete();
    }
    
    function refreshAllItems() {
        topy = [];
        tpc.refresh(list.find('.item'));
    } 
    
    function startOnComplete() {
        refreshAllItems();
        loaderVisible(false);
    }
    
    function onResize() {
        if (isMainPage()) {
            refreshAllItems();
            isResize = false;
        } else isResize = true;
    } 
    
    function onScroll() {
        if (isMainPage()) scrollY = $(window).scrollTop();
    }
}    


var viewjsInit = function(pid, appURL, topSpace) {
    var mainid, ps = '#' + pid + ' ';
    
    $(document).bind('pagechange', function() {
        if (!mainid) {
            mainid = $.mobile.activePage.attr("id");
            initialize();
        } else if (mainid == $.mobile.activePage.attr("id")) {
            $(window).resize(doWinResize);
        }
    });
    
    function initialize() {
        loaderVisible(true);
        doWinResize();
        var preview = $(ps + '.tmpl_image');
        if (preview.prop('complete')) showApp();
        else preview.load(showApp);             
        $(window).resize(doWinResize);
    }
    
    function getSize(elem) {
        return {x: $(elem).width(), y: $(elem).height()};
    } 
    
    function doWinResize() {
        var size = getSize(window);
        $(ps + '.application').css({
            height: size.y - 6 - topSpace,
            top: topSpace
        });
    }   
    
    function showApp() {
        var size = getSize(window);
        var tmpl_preview = $(ps + '.tmpl_preview');
        var application = $(ps + '.application');
        
        application.load(function() {
            application.css('visibility', 'visible');
            application.animate({'opacity': 1}, 500);
            setTimeout(function() {
                loaderVisible(false);
                $(ps + '.tmpl_html').animate({'opacity': 0}, 500);                    
            }, 500);
        });
        application.attr('src', appURL);
    }
}