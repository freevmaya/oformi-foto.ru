callbackObject = {
    _callbackIndex: 0,
    _callbacks: {},
    
    setCallback: function(a_callback) {
        this._callbackIndex++;
        this._callbacks[this._callbackIndex] = a_callback;
        return this._callbackIndex;     
    }, 
    
    callback: function(callbackIndex, data) {
        this._callbacks[this._callbackIndex](data);
        delete(this._callbacks[this._callbackIndex]);
    }
}

var MAXCONTPERCENT = 0.6;
var MINCONTPERCENT = 0.4;
var SERVERURL = 'http://oformi-foto.ru';
var DISCUSMODEL = 'discus_model';
var RFCADV = 6; //Сколько раз голосовать без рекламы
var ADVTIME = 4; //сек

var VM_FULLVIEW = 0;
var VM_SMALLVIEW = 1;
var TIPSOPTIONS = {
    className : 'tipz',
    hideDelay: 50,
    showDelay: 50
};

var tmorph = {transition: Fx.Transitions.Back.easeOut};

var Discus = {
    _discus: null,
    _ds_content: null,
    _viewType: 0,
    _contentHeight: 0,
    _contentWidth: 1000,
    _contentMinHeight: 360,
    _contentMaxHeight: 540,
    _contHPercent: MAXCONTPERCENT,
    _rateBar: null,
    _editor: null,
    _model:null,
    init: function(a_discus, a_model) {
        this._discus = a_discus;
        this._model = a_model;
        window.addEvent('login_status', this._login_status.bind(this));
        window.addEvent('userInfo', this._refreshNotices.bind(this));
        window.addEvent('domready', this._domready.bind(this));
    },
    
    _login_status: function(e) {
        var acb = $('addComment');
        acb.addEvent('click', this._addComClick.bind(this));
        if (e.status) {
            eval('external=' + e.source + '_external');
            this._usersRefresh();
            this._refreshNotices();
        } else {
            new Tips(this._setTipsData(this._rateBar.getElement(), locale.MESSAGE, locale.ACCESSAUXONLY), TIPSOPTIONS);
            new Tips(this._setTipsData(acb, locale.MESSAGE, locale.ACCESSAUXONLY), TIPSOPTIONS);
        }
    },
    
    _refreshNotices: function(user) {
        if (user) {
            $('ds-list').getChildren('.ds-item').each(function(el) {
                new NoticeItem(el);
            });        
        }
    },
    
    _setTipsData: function(element, title, text) {
        element.store('tip:title', title);
    	element.store('tip:text', text);
        return element;
    },
    
    _createRateBar: function() {
        this._rateBar = new RateBar($('ds-content-footer'), {
            maxRate: 5,
            rate: this._calcRate(),
            readonly: false
        });
        this._rateBar.addEvent('changeRate', this._changeRate.bind(this));
    },
    
    _domready: function() {
        this._ds_content = $('ds-content');
        this._ds_content.setStyles({height: this._contentHeight, opacity: 0.0});
        (function() {
            this._refreshSize(this._contHPercent);
        }).delay(1000, this);
        this._createRateBar();
          
        $('ds-list').setStyle('margin-top', this._heightCalculate(this._contHPercent));
        
        window.addEvent('resize', this._resize.bind(this));
        window.addEvent('scroll', this._scroll.bind(this)); 
        
        $$('.tipz').each((function(element, index) {
            if (title = element.get('title')) {
        		var content = title.split('::');
                this._setTipsData(element, content[0], content[1])
            }
    	}).bind(this));
        
    	var tipz = new Tips('.tipz', TIPSOPTIONS);
        
        var This = this;
        $$('.admin').each(function(admin) {
            admin.getChildren('a').each(function(link) {
                link.addEvent('click', This._onALClick.bind(This));
            });
        });
    }, 
    
    _onALClick: function(e) {
        var a = e.target.get('rel').split(',');
        vmayaRequest(this._model, a[0], {
            id: this._discus.id,
            value: a[1]
        }, (function(json) {
            app.alert(locale.MESSAGE, 'ok');
        }).bind(this));  
    },
    
    _calcRate: function() {
        return (this._discus.vote_count>0)?this._discus.votes/this._discus.vote_count:0;    
    },
    
    _changeRate: function(newRate) {
        queryChangeRate(newRate, (function() {
            this._sendVote(newRate);
        }).bind(this));
    },
    
    _sendVote: function(newRate) {
        vmayaRequest(this._model, 'addVote', {
            id: this._discus.id,
            votes: Math.round(newRate),
            source: app.user?app.user.source:0
        }, (function(json) {
            app.alert(locale.MESSAGE, locale.VOTECOMPLETE);
            this._discus.votes += result.addVotes;
            this._rateBar.setRate(this._calcRate());
//            this._rateBar.setReadOnly(true);
        }).bind(this));    
    },
    
    _resize: function() {
        this._refreshSize(this._contHPercent);
    },
    
    _scroll: function() {
        var sy = window.getScroll().y;
        var a_viewType = (sy > GMHEIGHT)?VM_SMALLVIEW:VM_FULLVIEW;
        
        if (sy > GMHEIGHT) {
            if (a_viewType != this._viewType) {
                this._ds_content.setStyle('top', SMHEIGHT);
            }
        } else this._ds_content.setStyle('top', GMHEIGHT - sy * SMHEIGHT/GMHEIGHT);
        
        this._setViewType(a_viewType); 
    },
    
    _setViewType: function(a_viewType) {
        if (a_viewType != this._viewType) {
            this._contHPercent = (a_viewType == VM_SMALLVIEW)?MINCONTPERCENT:MAXCONTPERCENT;
            this._refreshSize(this._contHPercent);
            this._viewType = a_viewType;
        }
    },
    
    _usersRefresh: function() {
        var ctrls = [];
        $$('.ds-user').forEach(function(item) {
            var img = item.getElement('img');
            if (img.get('rel') == 0) {
                ctrls.push({
                    uid: item.get('rel'),
                    ctrl: item
                });
            }
        });    
        
        if (ctrls.length > 0) this._requestUserInfo(ctrls);
    },
    
    _requestUserInfo: function(userCtrls) {
        var uids = [];
        for (var i=0; i<userCtrls.length; i++) {
            if (uids.indexOf(userCtrls[i].uid) == -1) uids.push(userCtrls[i].uid);
        }
        
        setUserInfo = (function (ctrl, user) {
            ctrl.getElement('img').set('src', app.getUserAvatar(user));
        }).bind(this);
        external.getProfiles(callbackObject.setCallback(function(users) {
            userCtrls.forEach(function(item) {
                users.forEach(function(user) {
                    if (item.uid == user.uid) setUserInfo(item.ctrl, user)
                });                
            });
        }), uids);
    },
    
    _layerRect: function(layerIndex) {
        var result = {
            left: 0,
            top: 0,
            width: this._contentWidth,
            height: this._contentHeight - 38
        }
        
        if (layerIndex == 1) {
            result.left = 110;
            result.width = result.height;
        } else if (layerIndex == 2) {
            result.left = this._contentHeight + 120;
            result.top = (this._contentHeight - 360) * 0.2;
            result.height = this._contentHeight - result.height;
            result.width = this._contentWidth - this._contentHeight - 140; 
        } else if (layerIndex == 3) {
            result.left = 0;
            result.width = 95; 
        }
        return result;
    },
    
    _setRect: function(ctrl, rect) {
        $(ctrl).set('morph', tmorph);
        $(ctrl).morph({
            'margin-top': rect.top,
            'margin-left': rect.left,
             width: rect.width,
             height: rect.height
        });
    },
    
    _updateBlock1: function(rect) {
        this._setRect($('ds-image'), rect);
        var img = $('ds-image').getElement('img'); 
        img.set('morph', tmorph);
        img.morph({
            width: rect.width,
            height: rect.height
        });
    },
    
    _updateBlock2: function(rect) {
        this._setRect('ds-header', rect);
    },
    
    _updateBlock3: function(rect) {
        this._setRect('ds-concurent', rect);
        $('ds-conc-list').set('morph', tmorph); 
        $('ds-conc-list').morph({width: rect.width, height: rect.height});
        
        var area = $('ds-conc-area');
        var childs = area.getChildren();
        if (childs.length > 0) {
            var item_height = childs[0].getCoordinates().height;
            area.setStyle('height', childs.length * item_height);
        }
    },
    
    _heightCalculate: function(percent) {
        var docSize = window.getSize();
        var menuSize = $('menu').getSize();
        var h = (docSize.y - menuSize.y) * percent;
        if (h > this._contentMaxHeight) h = this._contentMaxHeight;
        else if (h < this._contentMinHeight) h = this._contentMinHeight;
        return h;
    },
    
    _refreshSize: function(percent) {
        this._contentHeight = this._heightCalculate(percent);
        this._ds_content.morph({height: this._contentHeight, opacity: 1});
        
        this._updateBlock1(this._layerRect(1));
        this._updateBlock2(this._layerRect(2));
        this._updateBlock3(this._layerRect(3));
    },
    
    _addComClick: function() {
        this.editMode(true);
    },
    
    addNotice: function(a_data, top) {
        return new NoticeItem($('ds-list'), $('notify-tmpl'), a_data, top);
    },
    
    _injectEditor: function(layer, rect) {
        this._editor = new DsEditor($$('.ds-editor')[0], rect);
        this._editor.element().inject(layer, 'top');
        this._editor.addEvent('close', this._closeEditor.bind(this));
        this._editor.addEvent('send', this._sendNotify.bind(this))
    },
    
    _sendNotify: function(e) {
        var form = e.form;
//        alert(JSON.encode(app.user).replace(/\x22/g, '&quot;'));
        form.elements['user'].value = encodeURIComponent(JSON.encode(app.user));//.replace(/\x22/g, '\\"');
        (new Request.JSON({
            method: 'POST',
            url: form.get('action'),
            onSuccess: (function(json) {
                if (json && json.result) {
                    (this.addNotice({
                        time: json.time,
                        text: form.elements['message'].value,
                        user: app.user
                    }, true)).blink();
                    e.after();
                } else app.alert(locale.ERROR, locale.ERR_SENDNOTICE);
            }).bind(this)
        })).send(form);
    },
    
    _showEditor: function() {
        var dsize = document.getSize();  
        var cheight = this._heightCalculate(MAXCONTPERCENT);
        
        this._injectEditor($('wrapper'), {
            'margin-top': cheight,
            height: dsize - cheight
        });
        
        $('ds-list').setStyle('display', 'none');
        $('addComment').setStyle('display', 'none');
    },
    
    _closeEditor: function() {
        this._editor.close();
        this._editor = null;        
        $('ds-list').setStyle('display', 'block').tween('opacity', 1);
        $('addComment').setStyle('display', 'block').tween('opacity', 1);
    },
    
    sendComment: function() {
        if (this._editor) this._editor.send();
    },
    
    editMode: function(a_value) {
        app.auxonly((function() {
            if (a_value) {
                (new toEditModeFX({
                    startScroll: window.getScroll().y,
                    onComplete: (function(e) {
                        this._showEditor();
                    }).bind(this) 
                })).start(1, 0);
            }        
        }).bind(this));
    }
}

function changeRate(newRate) {
    queryChangeRate(newRate, function() {
        this._sendVote(newRate);
    });
}

var toEditModeFX = new Class({
    Extends: Fx,
    _ds_list: null,
    _ds_button: null,
    initialize: function(options) {
        this.parent(options);
        this._ds_list = $('ds-list');
        this._ds_button = $('addComment');
    },
    set: function(now) {
        this._ds_list.setStyle('opacity', now);
        this._ds_button.setStyle('opacity', now);
        window.scrollTo(0, now * this.options.startScroll);
		return now;
	}
})

var NoticeItem = new Class({
    _layer: null,    
    initialize: function(element, template, data, top) {
        if (template) {
            this._layer = template.clone();
            this._layer.set('id', '');
            this._layer.inject(element, top?'top':'bottom');            
            this._resetData(data);
        } else this._layer = element;
        
        if (app.user) {
            var uid = this._layer.getElement('.ds-user').get('rel');
            if (uid == app.user.uid)
                this._deleteButton();
        }
    },
    
    dispose: function() {
        this._layer.fade('out');
        (function() {
            this._layer.dispose();
        }).delay(500, this);
    },
    
    _doDelete: function() {
        app.removeConfirm((function() {
            (new Request.JSON({
                url: SERVERURL + '/?task=discussion,deleteNotice&type=json&id=' + this._layer.get('rel'),
                onSuccess: (function(json) {
                    if (json.result) {
                        this.dispose();
                    } else app.alert(locale.ERROR, locale.ERR_SENDNOTICE);
                }).bind(this)
            })).send();
        }).bind(this));
        /*
        app.request('ajax,deleteNotice', {id: this._layer.get('rel')}, function(a_data) {
                if (a_data.response) {
                } else app.alert(locale.ERROR, locale.ERR_REQUEST);
            });        
        }).bind(this), true);
        */
    },
    
    _deleteButton: function() {
        var button = (new Element('div', {
            'class': 'close-button hidden',
            events: {
                click: this._doDelete.bind(this)
            }
        })).inject(this._layer, 'top');
        
        this._layer.addEvent('mouseover', function() {
            button.fade('in');
        });
        this._layer.addEvent('mouseout', function() {
            button.fade('out');
        });
    },
    
    _resetData: function(a_data) {
        if (a_data.user) {
            this._layer.getElement('.ds-user-name').set('text', app.user_login(a_data.user));
            this._layer.getElement('.ds-user-link').href = a_data.user.url;
            this._layer.getElement('.ds-user-pic').set('src', app.user_avatar(a_data.user));
        }
        this._layer.getElement('.ds-item-title').set('text', a_data.time);
        this._layer.getElement('.ds-item-desc').set('text', a_data.text);
    },
    
    blink: function() {
        this._layer.setStyle('background-color', '#000');
        this._layer.morph({
            'background-color': '#FFF'
        });
    }
})

function vmayaRequest(a_model, a_method, params, onComplete) {
    HttpRequest.get(document.location.protocol + '//oformi-foto.ru/games/data/index.php', $merge({
        model: a_model, 
        uid: app.user?app.user.uid:0,
        method: a_method
    }, params), onComplete);
}

var rate_form_inc=(Cookie.read('rate_form_inc') || '0').toInt();
var vote_form_tmpl;

function queryChangeRate(newRate, onComplete, noaux) {
    app.afterAdv(function() {
        if (noaux) crProc(); 
        else app.auxonly(crProc);
    }, locale.ADVTITLEDISC);

    function crProc() {
        var isadv = false;//(rate_form_inc >= RFCADV)?(Math.random()>0.5?1:0):0;
        if (isadv) {
            var sizes = [{x: 305, y: 100}, {x: 300, y: 'auto'}]
            var form = (vote_form_tmpl || (vote_form_tmpl = $$('.vote-form')[0])).clone();
            
            function $e(sel) {
                return form.getElement(sel);
            }
            
            new RateBar(form.getElement('.rate-state'), {
                maxRate: 5,
                rate: newRate,
                readonly: true
            });
            
            b=$e('.ok-button');
            b.addEvent('click', (function() {
                rate_form_inc++;
                Cookie.write('rate_form_inc', rate_form_inc, {duration:0});
                sbox.close();  
                onComplete(newRate);
            }).bind(this));
            
            var sbox = SqueezeBox.open(form, {
        		handler: 'adopt',
        		size: sizes[isadv]
        	});       
            
            var vb = form.getElement('.vote-block');
            var wb = form.getElement('.wait-block');
            
            function vbShow(value) { 
                vb.setStyle('display', (value?'block':'none'));
                wb.setStyle('display', (value?'none':'block'));
            }
        
            vbShow(true);
            form.addClass('is-adv');
            (new Element('div', {'class': 'adv', html: '<div id="advertur_136493"></div>'})).inject(form, 'top');
            
            var wb = $e('.wait-block');
            wb.setStyle('display', 'block');
            b.setStyle('visibility', 'hidden');
            var s = ADVTIME; 
            
            function updateSec() {
                var sp = $e('.wait-block span');
                if (sp) sp.set('text', s);
                else clearTimeout(pit);
            }
            
            updateSec();
            pit = (function() {
                s--;
                updateSec();
                if (s <= 0) {
                    b.fade('in');
                    wb.fade('out');
                    clearTimeout(pit);
                }
            }).periodical(1000);
            
            (function(w, d, n) {
                w[n] = w[n] || [];
                w[n].push({
                    section_id: 136493,
                    place: "advertur_136493",
                    width: 300,
                    height: 250
                });
            })(window, document, "advertur_sections");
        } else onComplete(newRate);
    }    
}

function discusListInit(modelURL) {
    window.addEvent('domready', function() {
        function l_changeRate(newRate) {
            var did = this.get('data-id');
            queryChangeRate(newRate, function() {
                vmayaRequest(modelURL, 'addVote', {
                    id: did,
                    votes: Math.round(newRate),
                    source: app.user?app.user.source:0
                }, (function(is_json) {
                    if (result && result.result) {
                        if (cr = $('rate-' + did))
                            cr.set('text', cr.get('text').toInt() + result.addVotes.toInt());
                        app.alert(locale.MESSAGE, locale.VOTECOMPLETE);
                    };// else app.alert(locale.ERROR, locale.WRONG_RESPONSE);
                }).bind(this));    
            });   
        }
        
        $$('.rate').each(function(element, index) {
            var _rateBar = new RateBar(element, {
                maxRate: 5,
                rate: 0,
                reset_value: true,
                readonly: false
            });
            _rateBar.addEvent('changeRate', l_changeRate.bind(element));
    	}); 
    });
}
