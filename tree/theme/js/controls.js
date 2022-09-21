var BUTTON_IMAGE_URL = 'theme/tree/images/buttons/';
var DEFAULT_USER_IMAGE = 'theme/tree/images/def_male.png';
var DEFAULT_FEMALE_IMAGE = 'theme/tree/images/def_female.png';
var BUTTON_SPEED = 200; 
var MESSAGE_SPEED_ANIM = 200; 
var ACTION_SPEED = 500;
var LIBIMGCOUNT = 27;
 
Utils = {
    windowSize: function() {
        return Utils.viewSize($(window));
    },
    viewSize: function(view) {
        return new Vector(view.outerWidth(), view.outerHeight());  
    },
    viewCenter: function(view) {
        return Utils.viewSize(view).divide(2);
    },
    formatDate: function(date) {
        if (typeof(date) != 'object') date = new Date(date);
        return date.toLocaleString("ru", {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    },
    filterClone: function(src, fields) {
        var af = fields.split(',');
        var result = {};
        for (var i=0; i<af.length; i++)
            result[af[i]] = src[af[i]];
        return result;
    },
    fieldsInt: function(item, fd) {
        if ($.type(fd) == 'string') fd = fd.split(',');
        if ($.type(item) == 'array') 
            for (var i=0; i<item.length; i++) Utils.fieldsInt(item[i], fd);
        else for (var i=0; i<fd.length; i++) item[fd[i]] = parseInt(item[fd[i]]);
        return item;
    },
    fieldsIntA: function(item, fd) {
        for (var i in item) Utils.fieldsInt(item[i], fd);
    },
    decodeURI: function(item, fd) {
        if ($.type(fd) == 'string') fd = fd.split(',');
        if ($.type(item) == 'array') 
            for (var i=0; i<item.length; i++) Utils.decodeURI(item[i], fd);
        else for (var i=0; i<fd.length; i++) item[fd[i]] = decodeURIComponent(item[fd[i]]);
        return item;
    },
    ckExPPL: function(list, elems) {
        return (list.length > 0) && ((typeof(elems) == 'undefined') || (elems.length == 0));
    },
    defImg: function(item) {
        return (item.gender == 1)?DEFAULT_FEMALE_IMAGE:DEFAULT_USER_IMAGE;
    },
    uids: function(list) {
        var res = [];
        $.each(list, function(i, itm) {res.push(itm.uid)});
        return res;
    },
    uidsToStr: function(list, sep) {
        return Utils.uids(list).join(sep?sep:',');
    },
    unique: function(item) {
        return item.id + 'c' + item.childIds.toString() + 'p' + item.parentIds.toString();
    },
    // Пример использования: Utils.decline(bl, 'подсказ-ка,ки,ок')
	decline: function(num, word) {
        var n = Math.floor(num).toString() + " ", p = word.split(/-|,/g);
        var pn = n.match(/([^1]|^)1 /) ? 1 : (n.match(/([^1]|^)[234] /) ? 2 : 3);
        return n + p[0] + p[pn];
    },
    money: function(num) {
        return Utils.decline(num, pay_locale.MONEYFORMAT);
    },
    proxy: function(url) {
        return url;    
    },
    pvalue: function(btn, field) {
        var val = 0, i = 0;
        while ((val = btn.attr(field)) == undefined) {
            i++;
            btn = btn.parent();
            if (i > 30) break;
        }
        return val;
    }       
}

$.fn.extend({
    _show: 0,
    _opacityShow: 1,
    _dstype: '',
    vshow: function() {
        var This = this;
        this._show = 1;
        this.css('display', this._dstype);
        setTimeout(function() { 
            This.css('opacity', This._opacityShow);
        }, 100);
    }, 
    isShow: function() {
        return this._show;    
    },    
    vhide: function(onComplete) {
        if (this._show == 1) {
            var This = this;
            this.css('opacity', 0);
            this._show = 0;
            setTimeout(function() {
                if (This._show == 0) This.css({'display': 'none'});
                if (onComplete) onComplete();
            }, TRANSITONSPEED);
        }
    },
    vtrans: function(st_opac, dstype) {
        this._dstype = dstype?dstype:'block'; 
        this.css({opacity: st_opac?st_opac:0, transition: 'opacity ' + (ACTION_SPEED/1000) + 's ease-in-out', visibility: 'visible', block: st_opac?this._dstype:'none'});
    },
    fclick: function(onProc) {
        var stop = false;
        this.click(function(e) {
            if (!stop) {
                onProc(e);
                stop = true;
                setTimeout(function() {
                    stop = false;
                }, 1000);
            }
        });
        return this;
    }
}); 

$.extend({
    getUrlVars: function(){
        var vars = {}, hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++) {
          hash = hashes[i].split('=');
          //vars.push(hash[0]);
          vars[hash[0]] = hash[1];
        }
        return vars;
    },
    getUrlVar: function(name){
        return $.getUrlVars()[name];
    },
    undefined: function(val) {
        return typeof(val) == 'undefined';
    },
    browser:  (/mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase()))?'mozilla':
                (/webkit/.test(navigator.userAgent.toLowerCase())?'webkit':(
                    (/opera/.test(navigator.userAgent.toLowerCase())?'opera':(
                        (/msie/.test(navigator.userAgent.toLowerCase())?'msie':'')))))
});


$.ck = function(obj, def) {
    return (typeof(obj) != 'undefined')?obj:def;
}

$.idEmpty= function(obj) {
    return (typeof(obj) == 'undefined') || ((typeof(obj) == 'array') && (obj.length == 0));
}

function controllCB(a_data, field) {
    return {
        elem: null,
        cb: null,
        create: function(elem) {
            this.elem = elem;
            this.cb = elem.bt_checkbox();
            this.cb.setIndex(a_data?1:0);            
            return this;
        },
        fromPeople: function(people) {
             this.cb.setIndex(people[field]);
        },
        val: function() {
            return this.cb.getIndex();
        }
    } 
}

function conrollPCB(item_id, rel, gender, child_id) {
    var This;
    var a_data = item_id?rel.find(item_id):{id:0};
    var item_id = a_data.id;
    return This = {
        elem: null,
        btn: null,
        image: null,
        label: null,
        isShowList: function() {
            return this.list.css('display') == 'block';
        },
        showList: function(show) {
            //this.list.css('display', show?'block':'none');
            This.list.fadeTo(400, show?1:0, function() {
                This.list.css('display', show?'block':'none');
            });
        },
        updateList: function() {
            function createLi(id, cap, selected) {
                var liclass = selected?'select':''; 
                return $('<div class="' + liclass + '" data-id="' + id + '">' + cap + '</div>');
            }
            this.list.empty();
            this.list.append(createLi(0, locale.EMPTYITEM, false));
            var items = rel.getList();
            for (var i=0;i<items.length;i++) {
                var it = items[i];
                if ((child_id != it.id) && ((typeof(gender) == 'undefined') || (gender == it.gender))) {
                    this.list.append(createLi(it.id, it.displayName(), item_id == it.id));
                }
            }                        
        },
        select: function(a_id) {
            if (item_id != a_id) {
                var ix = rel.indexOf(item_id);
                this.list.children(ix).removeClass('select');                
                item_id = a_id;
                ix = rel.indexOf(item_id);
                $(this.list.children()[ix]).addClass('select');
                this.reset(rel.getList()[ix]);
                This.showList(false);
            }
        },
        create: function(elem) {
            this.elem = elem;
            this.list = this.elem.children('.cb-list');
            this.btn = elem.children('.button').button(null, this.elem);
            $(window).click(function(e) {
                var ctrl = $(e.target);
                if (This.elem.has(ctrl).length > 0) {
                    var id = ctrl.attr('data-id');
                    if (id) This.select(id);
                    else This.showList(true);
                } else This.showList(false);
            });
            this.image = imageCanvas().create(elem.children('.cb_image'));
            this.label = elem.children('.label');
            this.reset(a_data);
            this.updateList();
            return this;
        },
        reset: function(r_data) {
            if ((a_data = r_data) && (item_id = a_data.id)) {
                this.image.src(a_data.img);
                this.label.text(a_data.family + ' ' + a_data.name);
            } else {
                item_id = 0;
                this.image.src(gender?DEFAULT_FEMALE_IMAGE:DEFAULT_USER_IMAGE);
                this.label.text('');
            }
        },
        val: function() {
            return item_id;
        }
    } 
}

function controlMsg(a_data) {
    return {
        elem: null,
        create: function(elem) {
            this.elem = elem;
            this.elem.find('p').html(a_data);
            return this;
        }
    }
}

function controlProfile(app, onSelect) {
    return {
        elem: null,
        create: function(elem) {
            this.elem = elem;
            this.elem.find('.button').button(this.onRelease);
            return this;                          
        },
        onRelease: function(e) {
            app.friendsDialog(function(users) {
                if (users.length > 0) onSelect(users[0]);
            }, [], true);
        },
        val: function() {
        }
    } 
}

function controlFIO(a_data) {
    return {
        elem: null,
        create: function(elem) {
            for (var n in a_data) {
                elem.find('.' + n)
                    .keydown(function() {
                        elem.trigger('change')
                    });
            }
            this.elem = elem;
            this.fromPeople(a_data);
            return this;
        },
        fromPeople: function(itm) {
            var This = this;
            for (var n in itm)
                This.elem.find('.' + n).val(itm[n]);
        },
        val: function() {
            var result = {};
            for (var n in a_data)
                result[n] = this.elem.find('.' + n).val();
            return result;
        }
    } 
}

function controlListFriends(selected, onSelect) {
    var itmpl = $('#templates .friend_item');
    var list, select=[];
    
    var This = {
        elem: null,
        create: function(elem) {
            this.elem = elem;
            var This = this;
            social.friend_list(function(a_list) {This.assign(a_list)});
            return this;
        },
        doChange: function() {
        },
        val: function() {
            var result = [];
            for (var i=0; i<select.length; i++)
                result.push(list[select[i]]);
            return result;
        },
        assign: function(a_list) {
            list = a_list;
            this.refresh();
        },
        refresh: function(filter) {
            var lelm = this.elem.find('.list');
            lelm.empty();
            for (var i=0; i<list.length; i++) {
                if (!filter || filter(list[i])) {
                    var itm = itmpl.clone();
                    var item = list[i];
                    itm.find('.img').css('background-image', 'url(' + social.item_img(item) + ')');
                    itm.find('span').text(social.item_userName(item));
                    if (selected && selected.length) {
                        var idx = selected.indexOf(item.uid);
                        if (idx > -1) {
                            itm.addClass('select');
                            select.push(i);
                            selected.splice(idx, 1); 
                        }
                    } else if (select.indexOf(i) > -1) itm.addClass('select');
                    itm.attr('data-idx', i);
                    itm.fclick(onItemClick);
                    lelm.append(itm);
                }
            }
        },
        filter: function(str) {
            this.refresh(social.friendFilter(str));               
        }
    }
    
    function onItemClick(e) {
        var item = $(e.target).parents('.friend_item');
        item.toggleClass('select');
        var idx = parseInt(item.attr('data-idx'));
        var sid = select.indexOf(idx);
        if (sid > -1) select.splice(sid, 1);
        else select.push(idx); 
        onSelect();
    }
    
    return This; 
} 

function controlInput(a_data, field) {
    return {
        elem: null,
        create: function(elem) {
            elem.find('input')
                .val(a_data)
                .keydown(function() {
                    elem.trigger('change')
                });;
            this.elem = elem;
            return this;
        },
        fromPeople: function(pitem) {
            this.elem.find('input').val(pitem[field]);
        },
        doChange: function() {
        },
        val: function() {
            return this.elem.find('input').val();
        }
    }
}  

$.fn.imageLib = function(pathURL, count, onSelect) {
    var list = this.find('.list');
    for (var i=1; i<=count; i++) {
        list.append($('<img src="' + pathURL + i + '.jpg">'));
    };
    list.css('width', 86 * count);
    list.fclick(function(e) {
        var t = $(e.target);
        if (t.attr('src')) {
            onSelect(t.attr('src'));
        } 
    });
    return this;
}

function controlIMAGE(a_data) {
    var origin = imageCanvas();
    return $.extend(origin, {
        file: null,
        strMime  : "image/jpeg",
        modified: false,
        defaultImage: function() {
            return DEFAULT_USER_IMAGE;
        },
        super_create: origin.create
        ,
        create: function(elem) {
            this.super_create(elem);
            var This = this;
            this.btns = this.elem.find('.buttons');
            this.btns.vtrans();
              
            this.file = elem.find('input');
            this.file.change(function(e) {This.onSelectFile(e.target.files);});
            this.imglib = this.elem.find('.imglib');
            this.imglib.imageLib('theme/tree/images/libs/', LIBIMGCOUNT, function(imgURL) {
                This.setDataImage(imgURL);
            });
            this.imglib.vtrans();
            this.imglib.mouseleave(function() { 
                This.imglib.vhide();            
            });
            
            this.btns.find('.button').each(function(i, btn) {
                btn = $(btn);
                btn.button().fclick(function(e) {
                    This.doClick(btn.attr('data-button'));
                });
            });
            
            elem.mouseenter(function() {This.btns.vshow();});
            elem.mouseleave(function() {This.btns.vhide();});
            
            this.src(a_data?a_data:this.defaultImage());
            return this;
        },
        fromPeople: function(itm) {
            if (itm && itm.img) this.setDataImage(itm.img);
        },        
        doClick: function(db) {
            if (db == 'imgload') {
                this.elem.find('input').click();
            } else this.imglib.vshow();
        },
        setDataImage: function(image_data) {
            this.img.attr('src', image_data);
            this.modified = true;        
        },
        onSelectFile: function(fileList) {
            var count = 0; 
            var readFile = (function (file) {
                var reader = new FileReader();
                reader.onload = (function(e) {
                    this.setDataImage(e.target.result);
                  }
                ).bind(this);
                reader.readAsDataURL(file);
            }).bind(this);   
            
            readFile(fileList[count]);               
        },
        val: function() {
            return {modified: true, value: this.getImage()};
        },
        getImage: function() {
            var cnv = this.canvas[0];
            try {
                return cnv.toDataURL(this.strMime);
            } catch (err) {
                return this.img.attr('src');
            }
        }       
    })
}

function imageCanvas() {
    return {
        elem: null,
        img: null,
        canvas: null,
        create: function(elem) {
            this.elem = elem;
            var This = this;
            this.canvas = elem.find('canvas');
            this.img = elem.find('img');
            this.img.load(function() {This.onLoad();});
            return this;
        },
        onLoad: function() {
            this.updateCanvas();
        },
        src: function(url) {
            this.img.attr('src', url);
            return this;
        }, 
        updateCanvas: function() {
            var hp = parseInt(this.canvas.css('margin-left')) + parseInt(this.canvas.css('margin-right'));
            var vp = parseInt(this.canvas.css('margin-top')) + parseInt(this.canvas.css('margin-bottom'));
            
            var imf = this.elem.find('.image_frame');
            var size = new Vector(imf.innerWidth() - hp, imf.innerHeight() - vp);
            this.canvas.attr('width', size.x);
            this.canvas.attr('height', size.y);
            
            var ctx = this.canvas[0].getContext('2d');
            var scale = Math.max(size.x / this.img.width(), size.y / this.img.height());
            var t = new Vector(-(this.img.width() * scale - size.x) / 2, -(this.img.height() * scale - size.y) / 2);
            ctx.setTransform(scale,0,0,scale,t.x,t.y);
            ctx.drawImage(this.img[0], 0, 0);
        }       
    }
}

$.fn.button = function(listener, focusCtrl, releaseData) {
    var control = this;
    var img = null;
    var img_focus = null;
    
    function onEnter(e) {
        img_focus.vshow(); 
        img.vhide();
    }
    
    function onLeave(e) {
        img_focus.vhide();
        img.vshow();
    }
    
    function initialize() {
        var btn_type = control.attr('data-button');
        if (btn_type) {
            img = $('<img src="' + BUTTON_IMAGE_URL + 'bt_' + btn_type + '.png">');
            img_focus = $('<img src="' + BUTTON_IMAGE_URL + 'bt_' + btn_type + '_focus.png">');
            
            control.append(img);
            control.append(img_focus);
            
            img.vtrans(1);
            img_focus.vtrans();
            
            focusCtrl = focusCtrl?focusCtrl:control;
            focusCtrl.mouseenter(onEnter);
            focusCtrl.mouseleave(onLeave);
            if (listener) {
                var a_wait;
                focusCtrl.fclick(function(e) {
                    setTimeout(function() {a_wait = false}, 1000);
                    if (!a_wait) {
                        a_wait = true;
                        var event = control.attr('data-event') || control.attr('data-button');
                        if ($.type(listener) == 'function') listener(event, releaseData); 
                        else listener.trigger(jQuery.Event(event + 'Require', releaseData));
                    }
                });
            }
        } else console.log(control);
    } 
    
    initialize();
    
    return this;
}

$.fn.bt_checkbox = function() {
    var element = this;
    var btns = [];
    var index = -1;
    
    var This = new (function() {
        var lists = {};
        function getLists(elenType) {
            if (!lists[elenType]) lists[elenType] = [];
            return lists[elenType];
        }
        
        function doChange() {
            var list = getLists('CHANGE');
            for (var i=0; i<list.length; i++) list[i](index);
        }
                
        this.element = function() {
            return element;
        }
        
        this.setIndex = function(a_index) {
            function updateBt(show) {
                if (index > -1) btns[index].css('display', show?'block':'none');
            }
            if (index != a_index) {
                updateBt(false);
                index = a_index;
                updateBt(true);
                doChange();
            }
        }
        
        this.getIndex = function() {
            return index;
        }
        
        this.doClick = function() {
            this.setIndex((index + 1) % btns.length);
        }
        
        this.onChange = function(listener) {
            getLists('CHANGE').push(listener)
        }
    });
    
    this.find('.button').each(function(i, btn) {
        var bt = $(btn).button(element);
        btns.push(bt);
        bt.fclick(function() {
            This.doClick();
        });
        bt.css('display', 'none');
    });
    
    This.setIndex(0);
    return This;
}

function vm_viewCreate(elem) {
    //elem.draggable({scroll: true});
    var object = {
        show: function(pos, text) {
            elem.css({display: 'block', position: 'absolute'});
            elem.find('p').text(text);
            elem.animate({
                opacity: 1,
                left: pos.x,
                top: pos.y
            }, MESSAGE_SPEED_ANIM);
        },
        hide: function() {
            elem.animate({opacity: 0}, MESSAGE_SPEED_ANIM, null, function() {
                elem.css('display', 'none');
            });
        }
    };
    return object;
}

$.fn.submenu = function(app, data) {
    var This = this;
    var submenu = This.find('.submenu');
    var buttons = This.find('.button');
    var isShow = '';
    var _releases;
    submenu.vtrans();
    
    this.setData = function(a_data) {
        data = a_data;
    }
    
    this.hideSubmenu = function() {
        if (isShow) {
            submenu.vhide();
            isShow = '';
        }
    }
    
    this.release = function(a_releases) {
        _releases = a_releases;
    }
    
    this.onItemRelease = function(e) {
        var list = data[isShow];
        if (_releases[isShow]) {
            for (var i=0; i<list.length; i++) 
                if (list[i].id == $(this).attr('data-id')) {
                _releases[isShow](list[i]);
                This.hideSubmenu();
            }
        }
    }
    
    this.assignList = function(list) {
        var item;
        
        submenu.empty();
        for (var i=0; i<list.length; i++) {
            var acitm = true; 
            if (list[i].check) eval("acitm = " + list[i].check);
            
            if (acitm) item = $('<a class="mitem ' + $.ck(list[i].access, '') + '" data-id="' + list[i].id + '"><span>' + list[i].name + '</span></a>');
            else item = $('<div class="mitem ' + $.ck(list[i].access, '') + '" data-id="' + list[i].id + '"><span>' + list[i].name + '</span></div>');

            submenu.append(item.click(This.onItemRelease));
        }
    }
    
    this.showSubmenu = function(idx) {
        if (!isShow) submenu.vshow();
        if (idx != isShow) {
            This.assignList(data[idx]);
            isShow = idx;
        }
    }
    
    buttons.fclick(function(e) {
        var btn = $(e.target);
        var idx = Utils.pvalue(btn, 'data-id');
        if (data[idx]) This.showSubmenu(idx);
        else if (_releases[idx]) _releases[idx]();
    });
    
    function refreshButtons() {
        This.find('.edit').css('display', (app.isEditPermission()?'block':'none'));
    }
    
    $(window).click(function(e) {
        var tg = $(e.target); 
        if (!$.contains(This[0], e.target)) This.hideSubmenu();
        else {
            var idx = tg.attr('data-id');
            if (idx) {
                if (data[idx])
                    This.showSubmenu(idx);
                else This.hideSubmenu();
            }
        }
    });
    
    app.tree.on('ASSIGN', function() {
        refreshButtons();
    });
    
    return this;
}

var dialog_count = 0;
$.fn.dialog = function(data, onComplete, validator, dlg_class, onCancel, yesBtn) {
    var templates   = $('#templates');
    var toplayer     = $('#toplayer');
    
    var dialog = templates.children('.dialog').clone();
    var w = $(window);
    
    var dlgObj = {
        title: dialog.children('.title'),
        dialog: dialog,
        pperc: null,
        vm_view: vm_viewCreate(dialog.find('.vm_view')),
        controls: {},
        pos: function() {
            return new Vector(dialog.position());
        },
        set_pos: function(v) {
            this.pperc = v.add(Utils.viewCenter(dialog)).divide(Utils.windowSize());
            dialog.css({left: v.x, top: v.y});
            return this; 
        },
        calcCenterPos: function() {
            var ws = Utils.windowSize();
            var size = Utils.viewSize(dialog);
            return new Vector((ws.x - size.x) / 2, (ws.y - size.y) / 2);
        },
        toCenter: function() {
            this.set_pos(this.calcCenterPos());
            return this;
        },
        close: function() {
            dialog.animate({opacity: 0}, 500, null, function() {
                dialog.empty();
                dialog.remove();
                dialog_count--;
                if (dialog_count == 0) $.main.curtain(false);
            });
            return this;
        },
        show: function() {
            var c = this.calcCenterPos();
            dialog.css('top', c.y * 0.5); 
            dialog.animate({opacity: 1, left: c.x, top: c.y}, {
                duration: ACTION_SPEED, 
                specialEasing: {top: "easeOutBack"}
            });
            $.main.curtain(true);
            dialog_count++;
            return this;
        },
        percToPos: function() {
            if (this.pperc) {
                var v = Utils.windowSize().multiply(this.pperc).sub(Utils.viewCenter(dialog));
                dialog.css({left: v.x, top: v.y});
            }            
        },
        getValues: function() {
            var result = {};
            for (var n in this.controls) {
                var ctrl = this.controls[n];
                result[n] = ctrl.val?ctrl.val():null;
            }
            return result;
        },
        validateValues: function(a_data) {
            var result = true;
            if (validator)
                for (var n in this.controls) {
                    if (validator[n]) {
                        var vr = validator[n].validate(this.controls[n]); 
                        if (vr) {
                            var vw = this.vm_view;
                            vr.input.focus();
                            this.controls[n].elem.one('change', vw.hide);
                            vw.show(vr.position, vr.text);
                            result = false;
                            break;
                        }
                    }
                }
            return result;
        },
        apply: function() {
            if (this.validateValues()) {
                onComplete(this.getValues());
                this.close();
            }
        }
    }
    
    var down = false;
    var prevP = null;
    
    function onMouseMove(e) {
        if (down) {
            var cur = new Vector(e.pageX, e.pageY);
            dlgObj.set_pos(dlgObj.pos().add(cur.sub(prevP)));                
            prevP = cur;
        }
    }
    
    function onMouseUp(e) {
        down = false;
    }
    
    function onMouseDown(e) {
        down = true;
        prevP = new Vector(e.pageX, e.pageY);
    }
    
    function onResize(e) {
        dlgObj.percToPos();
    }
    
    function onCloseRequire(e) {
        if (onCancel) onCancel();
        dlgObj.close();
    }
    
    function onApplyRequire(e) {
        dlgObj.apply();
    }
    
    dialog.find('.button').each(function(i, btn) {
        $(btn).button(dlgObj.dialog);        
    });
    
    dialog.css('opacity', 0);
    dialog.on('closeRequire', onCloseRequire);
    dialog.on('cancelRequire', onCloseRequire);
    dialog.on('applyRequire', onApplyRequire); 
    dialog.on('yesRequire', onApplyRequire);
    
    if (yesBtn) {
        dialog.find('.yes').css('display', 'inline-block');
        dialog.find('.apply').css('display', 'none');
    }
    
    w.mousemove(onMouseMove);
    w.mouseup(onMouseUp);
    w.resize(onResize);
    dlgObj.title.mousedown(onMouseDown);
    
    if (dlg_class) dlgObj.dialog.addClass(dlg_class);
    
    toplayer.append(dialog);
    
    var dlg_content = dialog.find('.dlg_content');
    dlgObj.controls = data;
    for (var n in data) {
        var control = templates.children('.' + n);
        if (control) {
            var ctrlObj = data[n].create(control.clone());
            dlg_content.append($('<div class="input"></div>').append(ctrlObj.elem));
            dlgObj.controls[n] = ctrlObj;
        }
    }
    
    return dlgObj;
}

function vinDragable(app) {
    $.extend(this, new Events());
    var ppl = new baseUserView();
    var elem = ppl.getElement();
    elem.css('z-index', 100);
    $('body #page').append(elem);
    
    $.extend(this, new Events());
    
    var dragItem = null;
    var dpos;
    var isDrag = 0;
    var itemID = 0;
    var source = '';
    
    ppl.setScale(0.7);
    function ppldUpdate(e) {
        ppl.setPosition(new Vector(e.clientX - (elem.outerWidth() / 2 * ppl.getScale() + 5), e.clientY));
    }
    
    function onMouseDown(e) {
        dpos = new Vector(e.screenX, e.screenY);
    }
    
    function onDragitem(e) {
        if (e.id) {
            isDrag = 1;
            itemID = e.id;
            source = e.source;
        }
    }
    
    function onMouseUp(e) {
        if (dragItem) {
            elem.css({opacity: 0, visibility:'hidden'});
            setTimeout(function() {
                $(e.target).trigger(jQuery.Event('dropitem', {item: dragItem, source: source}));
                app.trigger($.Event('enddrag', {data: dragItem}));
                dragItem = null;
            }, 50);
        }     
        isDrag = 0;
    }
    
    function onMouseMove(e) {
        if (isDrag > 0) {
            if (isDrag == 1) {
                var cpos = new Vector(e.screenX, e.screenY);
                if (cpos.sub(dpos).length() > 5) { 
                    dragItem = app.rel.find(itemID);
                    ppl.setData(dragItem);
                    //elem.attr('data-id', e.id);
                    ppl.show(0.7);
                    isDrag = 2;
                    app.trigger($.Event('begindrag', {data: dragItem}));
                }
            }
            ppldUpdate(e);
        }
    }
    
    $(window).mousedown(onMouseDown).
                mouseup(onMouseUp).
                mousemove(onMouseMove);
    $(window).on('dragitem', onDragitem);
} 

function Assistant(app) {

    var elem = $('#templates .assistant').clone();
    $('#page').append(elem);
    
    var fassis, assText;
    var arrow = elem.find('.arrow');
    var yi = 0;
    elem.vtrans();
    
    var This = this;
    var tli = 0;
    var timeline;
    var _pos;
    var _noarrow;
    var _close = elem.find('.hclose'); 
    var _page = elem.find('.page');
    
    _close.button();
    _close.fclick(function() {
        timeline = null;
        This.hide();
    });
        
    
//PUBLIC    
    this.setTimeline = function(a_timeline) {
        function lstart() {
            tli = -1;
            timeline = a_timeline;
            nextFrame();                
        } 
        if (fassis) this.hide(lstart);
        else lstart();       
    },
    
    this.show = function(a_assis, text) {
        if (fassis = a_assis) {       
            assText = text;        
            fromAssist();
        }
    }
    
    this.hide = function(onComplete) {
        elem.vhide(onComplete);
        fassis = null;
    }
    
    function curFrame(frame, attempt) {
        if (frame['if']) {
            var ifr;
            eval('ifr = ' + frame['if'] + ';');
            if (!ifr) {
                nextFrame();
                return;
            }
        }
        
        _noarrow = frame['noarrow'];
        arrow.css('display', _noarrow?'none':'block'); 
        var tlelem = $(frame.control);
        var event = frame.event?frame.event:'click';
        
        function onRelease() {
            tlelem.off(event, onRelease);            
            This.hide();
            setTimeout(nextFrame, frame.delay?frame.delay:300);
        }
        
        if (tlelem && (tlelem.css('display') != 'none')) {
            This.show(tlelem, frame.text);
            tlelem.on(event, onRelease);
        } else {
//                app.alert(locale.UNAVAILABLELESSON);
            if (attempt < 5)
                setTimeout(function() {
                    curFrame(frame, attempt + 1);
                }, 1000);
        }
    }
    
    function nextFrame() {
        if (timeline && (tli + 1 < timeline.length)) {
            tli++;
            curFrame(timeline[tli], 0);
        }
    }
    
    function calcPosition() {
        if (fassis) {
            var afp = new Vector(fassis.offset());
            var p = afp.clone();
            if (p.length() > 0) {
                var s = Utils.viewSize(fassis);
                
                var ws = Utils.windowSize();
                var ms = Utils.viewSize(elem).add(new Vector(30, 60));
                
                if (p.x + ms.x > ws.x) p.x = ws.x - ms.x; 
                if (p.y + ms.y > ws.y) p.y = ws.y - ms.y - s.y;
                return new Vector(p.x + s.x / 2, p.y + s.y);
            } 
        } 
        return null; 
    }

    function fromAssist() {
        if (fassis) {
            if (_pos = calcPosition()) {             
                elem.css({display: 'block', opacity: 0, left: _pos.x, top: _pos.y + Utils.viewSize(elem).y * 0.3});
                elem.animate({left: _pos.x, top: _pos.y}, {
                    duration: ACTION_SPEED, 
                    specialEasing: {top: "easeOutBack"}
                });
                elem.vshow();
            }
            setText(assText);
        }
    } 
    
    function setText(dt) {
        elem.find('.text').html(dt);
    }
    
    setInterval(function() {
        if (fassis) {
            yi++;
            arrow.css('background-position-y', Math.sin(yi * 0.3) * 3);
        }
    }, Math.round(1000 / 32));
    
    function checkAssist() {
        if (fassis) {
            function checkTop(a_itm) {
                var p = a_itm.parent();
                if (p) {
                    var tn = p.prop("tagName");
                    if (tn == 'BODY') return true;
                    else if (typeof(tn) == 'undefined') return false; 
                    else return checkTop(p);
                } else return false;  
            }
            
            return checkTop(fassis); 
        } else return false;
    }
    
    setInterval(function() {
        if (checkAssist()) {
            var sp = calcPosition();
            if (sp && (_pos.sub(sp).length() > 0)) {
                elem.stop();
                elem.animate({left: sp.x, top: sp.y}, ACTION_SPEED);
                _pos = sp;
            }
        } else if (fassis) This.hide();
    }, 1000);
}

function Tips(app) {
    
    var tips = $('.tips').clone();
    $('body').append(tips);
    
    $(document).on('DOMNodeInserted', function(e) {
        initItems($(e.target).find('.hint'));
    });
    
    tips._opacityShow = 0.8;    
    tips.vtrans();
    
    var focus = null;
    var show = false;
    var wmtimer = 0;
    
//PRIVATE
    
    function setShow(vis) { 
        if (show = vis) tips.vshow(); else {
            tips.vhide();
            focus = null;
        }
    }
    
    function initItems(items) {
        if (items.length > 0) {
            items.each(function(i, btn) {
                btn = $(btn);
                var title = btn.attr('title');
                if (title) {
                    btn.attr('data-title', locale.buttons[title] || title);
                    btn.mouseleave(onLeave);    
                    btn.mouseenter(onEnter);
                    btn.attr('title', '');
                }
            });
        }
    }
    
    initItems($('.hint'));
    
    function onLeave(e) {
        setTimeout(function() {
            if (!focus) setShow(false);  
        }, 100);
        focus = null;
    }
    
    function onmousedown(e) {
        setShow(false);
    }
    
    function setText(dt) {     
        dt = dt.split(':');
        tips.find('.tip-title').html((dt.length>1)?dt[0]:locale.TIPSTITLE);
        tips.find('.tip-text').html((dt.length>1)?dt[1]:dt[0]);
    }
    
    function onEnter(e) {
        function top(elem, l) {
            if (l > 5) return;
            
            var dt = elem.attr('data-title');
            if (dt) focus = elem; 
            else top(elem.parent(), l + 1);
        }
        
        if (!e.buttons) top($(e.target), 0);
    }
    
    function Show(elem) {
        var ifhint = elem.attr('data-ifhint');
        var ifr = true;
        var dt = elem.attr('data-title');
        
        if (ifhint) eval('ifr=' + ifhint);
        
        if (ifr) {        
            setShow(true);
            setText(dt);
        }
    }
    
    function onmMove(e) {         
        var ws = Utils.windowSize();
        var ms = Utils.viewSize(tips).add(new Vector(20, 10));
        var p = new Vector(e.pageX + 8, e.pageY + 8);
        if (p.x + ms.x > ws.x) p.x = e.pageX - ms.x; 
        if (p.y + ms.y > ws.y) p.y = e.pageY - ms.y; 
        
        tips.css({
            left: p.x,
            top: p.y
        });
        
        if (wmtimer) {
            clearTimeout(wmtimer);
            wmtimer = 0;
        }
        wmtimer = setTimeout(function() {
            if (wmtimer > 0) {
                wmtimer = 0;
                if (focus) Show(focus);
            }
        }, 300);
    }

    $(window).mousemove(onmMove);
    $(window).mousedown(onmousedown);
}

function History(elem, commng) {
    elem.find('.button').each(function(i, itm) {
        $(itm).button();
    });
    
    var btn_back = elem.find('.back');
    var btn_forward = elem.find('.forward');
    btn_back.vtrans(1);
    btn_forward.vtrans(1);
    
    btn_back.click(function() {
        commng.back();
    });
    
    btn_forward.click(function() {
        commng.forward();
    });
    
    function onHAdd(e) {
        btn_back.vshow();
    }
    
    function refreshButtons() {
        if (!commng.isBack()) btn_back.vhide();
        else btn_back.vshow();
        if (!commng.isForward()) btn_forward.vhide();
        else btn_forward.vshow();
    }
    
    commng.on('HISTORY_ADD', onHAdd);
    commng.on('HISTORY_BACK', refreshButtons);
    commng.on('HISTORY_FORWARD', refreshButtons);
    commng.on('HISTORY_CLEAR', refreshButtons);
}

//-----------BASE 64----------------

(function(global) {
    'use strict';
    // existing version for noConflict()
    var _Base64 = global.Base64;
    var version = "2.1.9";
    // if node.js, we use Buffer
    var buffer;
    if (typeof module !== 'undefined' && module.exports) {
        try {
            buffer = require('buffer').Buffer;
        } catch (err) {}
    }
    // constants
    var b64chars
        = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    var b64tab = function(bin) {
        var t = {};
        for (var i = 0, l = bin.length; i < l; i++) t[bin.charAt(i)] = i;
        return t;
    }(b64chars);
    var fromCharCode = String.fromCharCode;
    // encoder stuff
    var cb_utob = function(c) {
        if (c.length < 2) {
            var cc = c.charCodeAt(0);
            return cc < 0x80 ? c
                : cc < 0x800 ? (fromCharCode(0xc0 | (cc >>> 6))
                                + fromCharCode(0x80 | (cc & 0x3f)))
                : (fromCharCode(0xe0 | ((cc >>> 12) & 0x0f))
                   + fromCharCode(0x80 | ((cc >>>  6) & 0x3f))
                   + fromCharCode(0x80 | ( cc         & 0x3f)));
        } else {
            var cc = 0x10000
                + (c.charCodeAt(0) - 0xD800) * 0x400
                + (c.charCodeAt(1) - 0xDC00);
            return (fromCharCode(0xf0 | ((cc >>> 18) & 0x07))
                    + fromCharCode(0x80 | ((cc >>> 12) & 0x3f))
                    + fromCharCode(0x80 | ((cc >>>  6) & 0x3f))
                    + fromCharCode(0x80 | ( cc         & 0x3f)));
        }
    };
    var re_utob = /[\uD800-\uDBFF][\uDC00-\uDFFFF]|[^\x00-\x7F]/g;
    var utob = function(u) {
        return u.replace(re_utob, cb_utob);
    };
    var cb_encode = function(ccc) {
        var padlen = [0, 2, 1][ccc.length % 3],
        ord = ccc.charCodeAt(0) << 16
            | ((ccc.length > 1 ? ccc.charCodeAt(1) : 0) << 8)
            | ((ccc.length > 2 ? ccc.charCodeAt(2) : 0)),
        chars = [
            b64chars.charAt( ord >>> 18),
            b64chars.charAt((ord >>> 12) & 63),
            padlen >= 2 ? '=' : b64chars.charAt((ord >>> 6) & 63),
            padlen >= 1 ? '=' : b64chars.charAt(ord & 63)
        ];
        return chars.join('');
    };
    var btoa = global.btoa ? function(b) {
        return global.btoa(b);
    } : function(b) {
        return b.replace(/[\s\S]{1,3}/g, cb_encode);
    };
    var _encode = buffer ? function (u) {
        return (u.constructor === buffer.constructor ? u : new buffer(u))
        .toString('base64')
    }
    : function (u) { return btoa(utob(u)) }
    ;
    var encode = function(u, urisafe) {
        return !urisafe
            ? _encode(String(u))
            : _encode(String(u)).replace(/[+\/]/g, function(m0) {
                return m0 == '+' ? '-' : '_';
            }).replace(/=/g, '');
    };
    var encodeURI = function(u) { return encode(u, true) };
    // decoder stuff
    var re_btou = new RegExp([
        '[\xC0-\xDF][\x80-\xBF]',
        '[\xE0-\xEF][\x80-\xBF]{2}',
        '[\xF0-\xF7][\x80-\xBF]{3}'
    ].join('|'), 'g');
    var cb_btou = function(cccc) {
        switch(cccc.length) {
        case 4:
            var cp = ((0x07 & cccc.charCodeAt(0)) << 18)
                |    ((0x3f & cccc.charCodeAt(1)) << 12)
                |    ((0x3f & cccc.charCodeAt(2)) <<  6)
                |     (0x3f & cccc.charCodeAt(3)),
            offset = cp - 0x10000;
            return (fromCharCode((offset  >>> 10) + 0xD800)
                    + fromCharCode((offset & 0x3FF) + 0xDC00));
        case 3:
            return fromCharCode(
                ((0x0f & cccc.charCodeAt(0)) << 12)
                    | ((0x3f & cccc.charCodeAt(1)) << 6)
                    |  (0x3f & cccc.charCodeAt(2))
            );
        default:
            return  fromCharCode(
                ((0x1f & cccc.charCodeAt(0)) << 6)
                    |  (0x3f & cccc.charCodeAt(1))
            );
        }
    };
    var btou = function(b) {
        return b.replace(re_btou, cb_btou);
    };
    var cb_decode = function(cccc) {
        var len = cccc.length,
        padlen = len % 4,
        n = (len > 0 ? b64tab[cccc.charAt(0)] << 18 : 0)
            | (len > 1 ? b64tab[cccc.charAt(1)] << 12 : 0)
            | (len > 2 ? b64tab[cccc.charAt(2)] <<  6 : 0)
            | (len > 3 ? b64tab[cccc.charAt(3)]       : 0),
        chars = [
            fromCharCode( n >>> 16),
            fromCharCode((n >>>  8) & 0xff),
            fromCharCode( n         & 0xff)
        ];
        chars.length -= [0, 0, 2, 1][padlen];
        return chars.join('');
    };
    var atob = global.atob ? function(a) {
        return global.atob(a);
    } : function(a){
        return a.replace(/[\s\S]{1,4}/g, cb_decode);
    };
    var _decode = buffer ? function(a) {
        return (a.constructor === buffer.constructor
                ? a : new buffer(a, 'base64')).toString();
    }
    : function(a) { return btou(atob(a)) };
    var decode = function(a){
        return _decode(
            String(a).replace(/[-_]/g, function(m0) { return m0 == '-' ? '+' : '/' })
                .replace(/[^A-Za-z0-9\+\/]/g, '')
        );
    };
    var noConflict = function() {
        var Base64 = global.Base64;
        global.Base64 = _Base64;
        return Base64;
    };
    // export Base64
    global.Base64 = {
        VERSION: version,
        atob: atob,
        btoa: btoa,
        fromBase64: decode,
        toBase64: encode,
        utob: utob,
        encode: encode,
        encodeURI: encodeURI,
        btou: btou,
        decode: decode,
        noConflict: noConflict
    };
    // if ES5 is available, make Base64.extendString() available
    if (typeof Object.defineProperty === 'function') {
        var noEnum = function(v){
            return {value:v,enumerable:false,writable:true,configurable:true};
        };
        global.Base64.extendString = function () {
            Object.defineProperty(
                String.prototype, 'fromBase64', noEnum(function () {
                    return decode(this)
                }));
            Object.defineProperty(
                String.prototype, 'toBase64', noEnum(function (urisafe) {
                    return encode(this, urisafe)
                }));
            Object.defineProperty(
                String.prototype, 'toBase64URI', noEnum(function () {
                    return encode(this, true)
                }));
        };
    }
    // that's it!
    if (global['Meteor']) {
        Base64 = global.Base64; // for normal export in Meteor.js
    }
    if (typeof module !== 'undefined' && module.exports) {
        module.exports.Base64 = global.Base64;
    }
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define([], function(){ return global.Base64 });
    }
})(typeof self !== 'undefined' ? self
 : typeof window !== 'undefined' ? window
 : typeof global !== 'undefined' ? global
 : this
);