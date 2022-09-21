var CMDTIMEVARIATION = 1000;
var CMDDENSITY = 0.7;
var CMDSHOWCOUNT = 3;
var DEFAULT_GID = 1;
var WAITMIN = 3;

var game = {
    _groups: null,
    _url: '',    
    _lastSend: 0,
    init: function(a_groups, a_url) {
        this._groups = a_groups;
        this._url = a_url;                          
        compareDialogInit(a_groups);
    },
    
    getGroups: function(app) {
        gs = [];
        this._groups.each(function(g) {
            if (app == g.app) gs.push(g);
        });
        return gs;        
    },
    
    sendToGame: function(name, isComments, group_id, imageType, image) {
        function confirm_subsgame(a_text) {
            if (puahhAllHtml = $$('.pushall-widget')[0]) {
                app.alert(locale.SUBSCRIBE, '<p>' + a_text + '</p>' + puahhAllHtml.get('html'), function() {
                    window.open(SUBSLINK, locale.SUBSCRIBE, 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=600,height:500');
                }, {width: 360, isCancel: true}); 
            }
        }
    
        function _send() {
            var leftTime = WAITMIN + (this._lastSend - getTime()) / (1000 * 60);
            if (leftTime <= 0) {
                this.request({
                    method      : 'sendToGame',
                    isComments  : isComments,
                    name        : name,
                    image       : image,
                    group_id    : group_id,
                    imageType   : imageType
                }, function(a_data) {
                    gurl = location.protocol + '//' + location.hostname + '/game/view/' + a_data.game_id + '.html';
                    var text  = locale.ADDGAMECOMPLETE[group_id].replace(/%s/g, gurl);
                    if (a_data.subs_state.toInt()==0) confirm_subsgame(text);
                    else app.alert(locale.CONGRATULATION, text);
                });
                this._lastSend = getTime();
            } else app.alert(locale.WARNING, locale.REPEATSENDDESC.replace('%s', Math.round(leftTime)));
        }
        app.auxonly(_send.bind(this), true, locale.ASANONYM);
    },
    
    request: function(params, a_onSuccess) {
        (new Request.JSON({
            url: this._url, 
            onSuccess: function(a_res) {
                if (a_res && a_res.result.toInt() == 1) a_onSuccess(a_res.data)
                else alert(locale.WRONG_RESPONSE);
            }
        })).post($merge({user: JSON.encode(app.userRequest())}, params));
    }
}


function gameListInit() {
    window.addEvent('domready', function() {
        function l_changeRate(newRate) {
            var did = this.get('data-id');
            queryChangeRate(newRate, (function() {
                app.request('game,addVote', {
                    game_id: did,
                    votes: Math.round(newRate)
                }, function(a_data) {
                    if (a_data.response && a_data.response.result) {
                        if (v = $('votes-' + did)) v.set('text', a_data.response.votes);
                        app.alert(locale.MESSAGE, locale.VOTECOMPLETE);
                    } else app.alert(locale.ERROR, locale.WRONG_RESPONSE);
                });    
            }).bind(this), true);    
        }
        
        $$('.rate').each(function(element, index) {
            var _rateBar = new RateBar(element, {
                maxRate: 5,
                rate: element.get('data-rate'),
                reset_value: true,
                readonly: false
            });
            _rateBar.addEvent('changeRate', l_changeRate.bind(element));
    	}); 
    });
}


var scene = {
    init: function(a_elem, app) {
        this._elem = a_elem;
        this._share = this._elem.getElement('.share-panel');
        this._image = this._elem.getElement('.container img');
        this._clothing = this._elem.getElement('.' + app);
        
        if (this._image.complete)
            this.initShare();
        else {
            this._share.setStyle('height', 0);
            this._image.addEvent('load', (function() {
                 this.initShare();
            }).bind(this));
        }
    },
    
    initShare: function() {
        rect = this._image.getCoordinates();
        this._share.setStyles({
            width: rect.width,
            left: 'auto',
            right: 'auto',
            height: 0
        });

        idiv = this._clothing.getElement('div');
        this._share.set('tween', {duration: 'short', transition: Fx.Transitions.Back.easeOut});
        idiv.addEvent('mouseenter', (function() {
             this._share.tween('height', this._share.getSize().y, 35);
        }).bind(this));
                
        idiv.addEvent('mouseleave', (function() {
             this._share.tween('height', this._share.getSize().y, 0);
        }).bind(this));        
    }
}

function compareDialogInit(a_groups) {
    var scountKey = 'CD_COUNT';    
    var reset = location.href.indexOf('reset-cache') > -1;
    var showCount = 0;
    var groups = a_groups?a_groups.concat([]):null;
    var group_id;
    var ckey;
    
    function initialize() {
        var dlg;
        var imgs;
        var noids = Cookie.read(ckey);
        if (!noids || reset) noids = [];
        else noids = JSON.decode(noids); 
        
        function dialogCreate() {
            var tw = {transition: Fx.Transitions.Back.easeInOut};
            dlg = (new Element('div', {
                html: '<div class="head"><div>' + locale.CMDGAMETITLE + '<a class="close"></a></div></div><div class="content">' +
                        '<a><div class="cmd-img"><div class="cmd-check"></div></div></a><a><div class="cmd-img"><div class="cmd-check"></div></div></a>'+ 
                        '<div><a class="next">' + locale.NEXTCOLLAGES + '</a></div></div>',
                tween: tw,
            'class':'cmd-dialog'})).inject($('bodyArea'));
            
            dlg.$('.close').addEvent('click', hide);
            imgs = dlg.getElements('.cmd-img');
            imgs.addEvent('click', imgClick);
            imgs.set('tween', tw);
            
            dlg.getElement('.next').addEvent('click', function() {refresh()});
            
            show.delay(Math.random() * CMDTIMEVARIATION + 1000);
        }
        
        function imgClick() {
            app.request('game,addVote', {
                game_id: this.get('data-id'),
                votes: 5
            }, function(a_data) {
                refresh();
            });
        }
        
        function refresh(onComplete) {
            game.request({
                group_id    : group_id,
                method      : 'getCmdItems',
                noids       : JSON.encode(noids)
            }, function(a_data) {
                if (a_data.length == 2) {
                    for (i=0; i<2; i++) {
                        noids.push(a_data[i].game_id); 
                        imgs[i].set('data-id', a_data[i].game_id).tween('width', 160, 50).tween('height', 160, 50);
                    }
                    
                    (function() {
                        for (i=0; i<2; i++) {
                            imgs[i].setStyle('background-image', 'url(' + a_data[i].image_url + ')').tween('width', 50, 160).tween('height', 50, 160);
                        }
                    }).delay(500);
                    
                    Cookie.write(ckey, JSON.encode(noids), {path: '/'});
                    if (onComplete) onComplete();
                } else {
                    if (groups && groups.length) {
                        resetGroup(rndGroup());
                        show.delay(500);                    
                    }
                    hide();
                }
            });
        }
        
        function show() {
            refresh(function() {
                dlg.setStyle('display', 'block');
                dlg.tw('right', 10);
                if (incShowCount) incShowCount();
            });
        }
        
        function hide() {
            dlg.tw('right', -(dlg.getSize().x + 5));
            (function() {dlg.setStyle('display', 'none')}).delay(200);
            //dlg.fade('out');
        }
        
        dialogCreate();
        window.addEvent('click', function(e) {
            if (!dlg.containsAll(e.target)) hide();
        });
    }
    
    var incShowCount = function() {
        showCount++;
        Cookie.write(scountKey, showCount);
        incShowCount = null;
    }
    
    function checkInit() {
        if ((showCount < CMDSHOWCOUNT) && (Math.random()<CMDDENSITY)) initialize();
    }
    
    function resetGroup(a_gid) {
        scountKey = 'CD_COUNT' + a_gid;
        group_id = a_gid;
        ckey = 'noids_' + group_id;
        
        if (reset) {
            showCount = 0;
            Cookie.write(scountKey, showCount);
            Cookie.write(ckey, '[]');
        } else showCount = (Cookie.read(scountKey) || '0').toInt();
        return a_gid;
    }
    
    function rndGroup() {
        var i = Math.floor(Math.random() * groups.length);
        var gid = groups[i].group_id;
        groups.splice(i, 1);
        return gid;
    }
    
    function groupInit(a_gid) {
        resetGroup(a_gid);        
        checkInit();
    }
    
    window.addEvent('domready', function(e) {
        if (groups) groupInit(rndGroup());
        else groupInit(DEFAULT_GID);
    });
}
