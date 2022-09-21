var comblock = 0;

function comments(com_url, cnt_id, onAfterSent, onAfterLike) {
    var blockID = 'com-' + comblock, user;
    var block, list, editor, replybox, ed_active, blockEditor, ta, answer_to, header, hcount;
    var loaded = 0, page = 0;
    var cur_url = document.location.href.replace(/((http|https):\/\/.+(\.com|\.ru|\.net)\/)|#/g, '');   
    var content_id = cnt_id?cnt_id:MD5.calc(cur_url);
    var footer, cbtn, afc = 'at-footer', pageEnd=false, isInFooter, login_block;         
    
    document.write('<div class="of-com" id="' + blockID + '"><a class="close"></a>'+
                        '<div class="header"><span>' + locale.DISCUSSION + '</span><span class="of-count">, <a class="total tipz" title="' + locale.COMMENTS + '::' + locale.LOADALL + '"></a> ' + locale.MESSAGES + '</span></div>'+
                        '<div class="com-body">'+
                        '<div id="block-editor">' + 
                            '<div class="reply-editor">'+
                                '<table><tr>'+
                                '<td class="avatar" rowspan="2">'+
                                    '<div><div><span class="name">' + locale.ANONYM + '</span></div>'+
                                    '<a class="image"><img src="' + AVADEFAULTURL + '"></a></div>'+
                                    '<div class="login"></div>' +
                                '</td>'+
                                '<td><textarea id="editor"></textarea></td>' +
                                '</tr><tr><td class="reply-editor-footer"><table class="editor-desc"><tr><td class="text-left">' + locale.EDITORANONIMDESC + '</td><td>'+ 
                                    '<a href="" class="ctrl-button app-link">' + locale.SEND + '</a></td</tr></table>'+
                                '</td></tr>'+
                                '</table>'+
                            '</div>'+
                            '<div class="reply-box">' + locale.NEWCOMMENT + '</div>'+
                        '</div>'+
                        '<div class="list"></div>'+
                        '<div class="clr"></div>'+
                        '</div>'+
                    '</div>');
    comblock++;
    
    function isFixed() {
        return block.hasClass(afc);
    }
    
    function commentFixed(fix) {
        if (fix && !isFixed()) block.addClass(afc);
        else if (!fix && isFixed()) block.removeClass(afc);        
        visEditor(false);        
    }
    
    function toggleCom() {
        block.toggleClass(afc);
        visEditor(false);        
    }
    
    function ajax(params, a_onSuccess) {
        params = $merge({content_id :content_id, url: cur_url, user: app?JSON.encode(app.userRequest()):null}, params);
        return new Request.JSON({url: com_url, onSuccess: a_onSuccess}).post(params);
    }
    
    function onAnswer(e) {
        e.stop();
        answer_to = e.target.get('data-answer').split('-');
        ed_active = true;
        updateEditor($('com-' + answer_to[0]).getElements('.subitem')[0], true);
    } 
    
    function onHeart(e) {
        e.stop();
        if (!e.target.get('data-lock')) {        
            var data_cm = e.target.get('data-cm').split('-');
            ajax({method: 'addLike', comment_id: data_cm[0]}, function(a_data) {
                if (a_data) {
                    e.target.set('text', empty(a_data.toInt()));
                    e.target.set('data-lock', 1);
                    if (onAfterLike) onAfterLike(data_cm);
                }
            });
        }
    }
    
    function $e(Class) {
        return $$('#' + blockID + ' .' + Class)[0];
    }
    
    function commentItems(a_data) {
        if (a_data && a_data.list) {
            a_data.list.each(function(item) {createItem(item)});
            loaded += a_data.list.length;
            if (loaded < a_data.total)
                hcount.set('text', loaded + ' ' + locale.OF + ' ' + a_data.total); 
            else $e('of-count').setStyle('display', 'none');  
            page = a_data.next_page; 
        }
    }
    
    function createItem(itemData, parent, pos) {
        var answerdata = itemData.comment_id + '-' + itemData.uid + '-' + itemData.source; 
        var ibox = new Element('div', {
            'data-id': itemData.comment_id,
            'id': 'com-' + itemData.comment_id,
            'class': 'com-item',
            html: '<table><tr><td class="avatar"><a href="' + itemData.user_url + '" class="image"><img src="' + AVAURL + itemData.avatar + '"></a></td>' +
                    '<td class="com-textsec">' +
                    '<div class="autor">' + (itemData.nick?itemData.nick:locale.ANONYM) + '</div>' + 
                   '<div class="text">' + parseText(itemData.text) + '</div>' +
                   '<div class="foot"><span>' + itemData.msgTime + '</span><a href="#" class="answer" data-answer="' + answerdata + '">' + locale.COMMENT + '</a>' + 
                   '<a class="post-icon heart m-icon" data-cm="' + answerdata + '">' + empty(itemData.likes.toInt()) + '</a></div>' +
                   '</td></tr><tr><td colspan="2">' + 
                   '<div class="subitem"></div></td></tr></table>'
        });
        
        ibox.getElements('.answer')[0].addEvent('click', onAnswer);
        ibox.getElement('.heart').addEvent('click', onHeart);
        ibox.inject(parent?(parent.getElements('.subitem')[0]):list, pos?pos:'bottom');
        
        if (itemData.answer_count.toInt() > 0) {
            itemData.childs.each(function(child) {
                createItem(child, ibox, 'top');
            });
        }
        
        return ibox;
    }
    
    function onWindowClick(e) {
        var ps = e.target.getParents();
        if (isFixed() && (e.target != block) && (ps.indexOf(block) == -1)) toggleCom();
        else if (ed_active && ((e.target != blockEditor) && (ps.indexOf(editor) == -1))) visEditor(false);
    }
    
    function updateEditor(parent, clear) {
        editor.setStyle('display', 'none');
        replybox.setStyle('display', (!parent && ed_active)?'none':'block');
        
        if (ed_active) {
            ta.setStyle('width', 10);
            
            if (parent) editor.inject(parent, 'after');
            else editor.inject(blockEditor);
            
            editor.setStyles({
                display: 'block',
                visibility: 'hidden',
                opacity: 0
            });
            editor.fade('in');
            
            (function() {
                tas = ta.getParent().getSize();
                ta.tween('width', tas.x);
                
                if (clear) ta.set('value', '');        
                
                ta.focus();
                ta.selectionEnd = 0;
                if (isFixed()) {
                    var cb = $e('com-body');
                    (function() {
                        //cb.scrollTo(0, editor.getPosition(list).y + h.getSize().y);
                        (new Fx().start(cb.getScrollTop(), editor.getPosition(list).y + header.getSize().y)).set = function(now) {
                            cb.scrollTo(0, now);
                        };
                    }).delay(200);
                }
            }).delay(200);
        }   
    }
    
    function visEditor(visible, parent) {
        if (ed_active != visible) {
            ed_active = visible;
            answer_to = null;
            updateEditor(parent);
        }
    }
    
    function onSend(e) {
        e.stop();
        var text = ta.get('value').trim();
        if (text) {
            ajax({
                method: 'send',
                text: text,
                answer_to: answer_to?answer_to[0]:0
            }, function(comment) {
                if (comment) {
                    if (onAfterSent) onAfterSent(comment, answer_to);
                      
                    var p = answer_to?$('com-' + answer_to[0]):null;
                    createItem(comment, p, 'top').highlight('#000', '#FFF');
                    visEditor(false);
                    ta.set('value', '');
                    hcount.addInt(1);
                } 
            });
        }
    }
    
    function onUserInfo(a_user) {
        user = a_user;
        editor.getElements('.name')[0].set('text', app.user_login(user));
        editor.getElements('img')[0].set('src', app.user_avatar(user));
        editor.getElements('.text-left')[0].set('text', '');
    }
    
    function onLoginStatus(e) {
        login_block.fade(e.status?'out':'in');
    }
    
    function onScroll() {
        var ss = block.getParent().getPosition();
        if (window.getScroll().y + window.getSize().y > ss.y) {
            if (!pageEnd) {
                pageEnd = true;
                commentFixed(false);
                cbtn.fade(0);
            }
        } else {
            if (pageEnd) {
                pageEnd = false;
                cbtn.fade(0.5);
            }
        }
    }
    
    function loadNext(all) {
        if (page > -1) ajax({method: 'getList', all: all, page: page}, commentItems);
    }
    
    function onReady() {
        $e('close').addEvent('click', function(e) {
            commentFixed(false);
            e.stop();            
        });
        block = $(blockID);
        header = $e('header');
        hcount = header.getElement('.total');
        
        login_block = $$('.tmpl-login').clone().inject($e('login')).removeClass('tmpl-login');
        
        loginBlockInit(login_block);
        
        list = block.getElement('.list');
        ajax({method: 'getList'}, commentItems);
        footer = $('footer');
        blockEditor = $('block-editor');
        editor = blockEditor.getElements('.reply-editor')[0];
        
        replybox = blockEditor.getElements('.reply-box')[0];
        replybox.addEvent('mousedown', function () {
            visEditor(true);
        });
        ta = editor.getElement('textarea');
        blockEditor.getElements('.ctrl-button')[0].addEvent('click', onSend);       
        
        visEditor(false);

        var fw = $$('#fixed-footer .wrapper');
        cbtn = (new Element('a', {
            events: {click: function() {toggleCom.delay(100)}},
            text: locale.DISCUSSION 
        })).inject(fw[0], 'top');
        
        if (isInFooter = (block.getParent().get('id') == 'social'))
            window.addEvent('scroll', onScroll);
            
        hcount.addEvent('click', function() {loadNext(1)});
    }
    
    window.addEvent('click', onWindowClick);
    window.addEvent('userInfo', onUserInfo);
    window.addEvent('domready', onReady);
    window.addEvent('login_status', onLoginStatus);
}