var app = null;

var PJApp;
(function() {

    var USERFIELDS = ['uid', 'source', 'nauid', 'avatar', 'first_name', 'last_name', 'nick', 'email', 'url', 'gender', 'birthday', 'session'];
    var AFLE = {source  : 'all', status  : false, session : null};
    
    PJApp = new Class({
    Implements  : [Events, Options],
    nauid   : 0,
    user    : null,                                                                        
    isChrome : /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor),
    
	initialize: function(options) {
		app = this;
		this.setOptions(options);
        this._noauxUID(this._startData.bind(this));
	},
    
    _setNauid: function(a_naud) {
        t = this; a_nauid = a_naud.toInt();
        if (a_nauid && (t.nauid != a_nauid)) {
            t.nauid = a_nauid;
            Cookie.write('NAUID', t.nauid, {duration: 365 * 10}); //10 лет
        }
    },
        
    _noauxUID: function(af) {
        t = this;
        if (!(nauid = Cookie.read('NAUID'))) {
            var u = t.options.user;
            if (u && u.nauid) {t._setNauid(u.nauid);af()}
            else t.request('ajax,getNAUID', {sesset: 1}, function(a_data) {
                if (a_data && a_data.response.result) t._setNauid(a_data.response.nauid);  
                af();                
            })
        } else {t._setNauid(nauid);af()}
    },
    
    _startData: function() {
        var u = this.options.user;
        if (u && (u.source != 'none')) {
            if (u.source == 'fb') {
                this._startDataFB(this._afterFailLogin.bind(this));
            } else if (u.source == 'mm') {
                this._startDataMM(this._afterFailLogin.bind(this));
            } else if (u.source == 'ok') {
                this._startDataOK(this._afterFailLogin.bind(this));
            } else if (u.source == 'vk') {
                this._startDataVK(this._afterFailLogin.bind(this));
            } else if (u.source == 'of') {
                this._startDataOF(this._afterFailLogin.bind(this));
            } 
        } else {
            //if (this.options.outdoorReferer) {
                this.reloadAll();
            //} else this.resetAll();
        }

        /*
        let fd = Cookie.read('FIRST_DATE');
        if (fd) {
            var delta = Math.floor((Date.now() - fd) / (1000 * 60 * 60 * 24));
            if (delta == 0)
                app.alert(locale.MESSAGE, locale.DONATE + 
                    '<iframe src="https://www.donationalerts.com/widget/goal/5583673?token=ISQ30rEkyt0VayuGkhkT" style="width:400px;height:400px;border:none;"></iframe>', null);
        }
        else Cookie.write('FIRST_DATE', Date.now());
        */
    },
    
    resetAll: function() {
        window.fireEvent('login_status', AFLE);
    },
    
    reloadAll: function() {
        var source = (Cookie.read('LAST_SOURCE') || 'mm').toUpperCase();
        var delta = (Date.now() - Cookie.read('LAST_TIME')) / (1000 * 60);
        if (delta > 30) this['_startData' + source](this._afterFailLogin.bind(this));
        else this.resetAll();
    },
    
// Private methods
    _afterFailLogin: function() {
        window.fireEvent('login_status', AFLE);
    },

    _safeSource: function(source) {
        var dr = {duration: 30};
        Cookie.write('LAST_SOURCE', source, dr);
        Cookie.write('LAST_TIME', Date.now(), dr);
    },

/*---------На сайте-------------*/
    _startDataOF: function(afterProc) {
       window.fireEvent('login_status', {
            source  : 'of',
            status  : this.options.user != null,
            session : true
        });
        
//        window.fireEvent('afterSetUser', this.user);                    
        this._safeSource('of');
        this._updateUser(this.options.user);
        window.fireEvent('afterSetUser', this.user);                    
    },

/*---------Мой мир-------------*/
    loginMM: function() {
        this.loadMM((function() {
            mailru.connect.login(['photos']);
        }).bind(this));
    },
    
    loadMM: function(onComplete) {
        if (mailru.connect==undefined) {
            mailru.loader.require('api', (function() {
                mailru.connect.init(APPID.mm.id, APPID.mm.key);
                mailru.events.listen(mailru.connect.events.login, (function(session){
                    if (session) this._afterLogin_mm(session);
                    else console.error("session is null");
                }).bind(this));
                
                mailru.events.listen(mailru.connect.events.logout, (function(){
                    this.logout('mm');
                }).bind(this));
                onComplete();
            }).bind(this));
        } else onComplete();
    },
    
    _startDataMM: function(afterProc) {
        var _isResult = false;
        
        this.loadMM((function() {
            mailru.connect.getLoginStatus((function(result) {
                _isResult = true;
                if (result && (parseInt(result.is_app_user) == 1))
                    this._afterLogin_mm(result);
                else afterProc();
            }).bind(this));
            
            (function() {
                if (!_isResult) afterProc();
            }).delay(5000, this);
        }).bind(this));
    },

    _afterLogin_mm: function(a_session) {
        window.fireEvent('login_status', {
            source  : 'mm',
            status  : parseInt(a_session.is_app_user),
            session : a_session
        });
        mailru.common.users.getInfo((function(user_list) {
            if (user_list.error) console.error(user_list.error);
            else if (d = user_list[0]) {
                d.url = d.link;
                d.gender = (d.sex==0)?'male':'female';
                this._setUser(d, 'mm');
            }
        }).bind(this), a_session.viewer_id);
    },
    
/*------FACEBBOK---------*/
    loginFB: function() {
        this.loadFB((function() {
            FB.getLoginStatus(this._changeCallbackFB((function() {
                FB.login((function(response){
                    this.loginFB();
                }).bind(this), {scope: 'user_photos,public_profile,user_friends'}); //https://developers.facebook.com/docs/facebook-login/permissions#reference-public_profile   
            }).bind(this)));
        }).bind(this));
    },
    
    logoutFB: function() {
        if (FB) {
            FB.logout();
            this.logout('fb');
        }
    },
      
    _changeCallbackFB: function(loginProc) { 
        return (function(response) {
            if (response && response.status === 'connected') {
                window.fireEvent('login_status', {
                    source  : 'fb',
                    status  : true,
                    session : response
                });
                FB.api('/me?fields=id,name,picture,birthday,gender,first_name,last_name,email,link', (function(response) {
                    response.uid = response.id;
                    response.nick = response.name;
                    response.url = response.link;
                    this._setUser(response, 'fb');
                }).bind(this));
                return;
            } else if (loginProc != null) loginProc();
        }).bind(this);
    },
    
    _startDataFB: function(afterProc) {
        this.loadFB((function() {
            FB.getLoginStatus(this._changeCallbackFB(afterProc).bind(this));
        }).bind(this)); 
    },
    
    loadFB: function(onComplete) {
        if (window['FB'] == undefined) {
            window.fbAsyncInit = (function() {
                FB.init({
                    appId      : APPID.fb,
                    cookie     : true,
                    xfbml      : true,
                    version    : 'v2.7'
                });
                 
                if (onComplete) onComplete();
            }).bind(this); 
            
            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/ru_RU/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            })(document, 'script', 'facebook-jssdk');
        } else if (onComplete) onComplete();
    },       
    
/*-----VK block------*/
    loginVK: function() {
        VK.init({apiId: APPID.vk});    
        VK.Auth.login((function(response) {
            this._afterLogin_vk(response.session);
        }).bind(this), 4);
    },

    _startDataVK: function(afterProc) {
        VK.init({apiId: APPID.vk});
        function authInfoVK(response) {
            if (response.session) this._afterLogin_vk(response.session);
            else afterProc();
        }
        VK.Auth.getLoginStatus(authInfoVK.bind(this));
    },
    
    _afterLogin_vk: function(a_session) {     
        if (a_session) {
            window.fireEvent('login_status', {
                source  : 'vk',
                status  : a_session != null,
                session : a_session
            });
            
            if (this.user == null) {
                VK.Api.call('users.get', {uids: a_session.mid, v:"5.73", fields: 'contacts,photo_100,photo_max,sex,screen_name,bdate'}, (function(r) {
                    user = r.response[0];
                    user.uid = user.id;
                    user.nick = user.screen_name;
                    user.birthday = user.bdate;
                    user.gender = (user.sex==2)?'male':'female';
                    this._setUser(user, 'vk');
                }).bind(this)); 
            } else this._updateUser(this.user, 'vk');
        } else this._setUser(null, 'vk');
    },
/*-----OK block----*/

    loginOK: function(session) {
        pt = location.protocol;
        var client_id=125582592;
        var redirect_uri= pt + '//oformi-foto.ru/ssd2/ok_auth.php';
        window.authSuccess = (function(s) {this._afterLogin_ok(s);}).bind(this);         
        window.open(pt + "//connect.ok.ru/dk?cmd=WidgetCtrl&st.cmd=OAuth2Permissions&st.scope=PUBLISH_TO_STREAM%3BSET_STATUS%3BPHOTO_CONTENT%3BVALUABLE_ACCESS%3BAPP_INVITE%3BVIDEO_CONTENT&st.response_type=token&st.redirect_uri=" + redirect_uri + "&st.client_id=" + client_id + "&st.show_permissions=off",
                    'AuthWindow', 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=0,height:0');
    },
    
    _startDataOK: function(afterFailStatus) {
        if (this.options.user && (ses = this.options.user.session)) {
            ses = (typeof(ses) == 'string')?JSON.decode(ses):ses;
            this._afterLogin_ok({
                 access_token       : ses.token,
                 session_secret_key : ses.key,
                 application_key    : APPID.ok
            }, afterFailStatus);
        } else afterFailStatus();
    },
    
    _afterLogin_ok: function(a_session, afterFailStatus) {
        if (a_session && (a_session.access_token) && (a_session.session_secret_key)) {
            var userRequest = (function() {
                if (!OkClient.initialized)
                    OkClient.initialize(document.location.protocol + '//api.odnoklassniki.ru/', a_session);
                    
                if (this.user == null) {
                    OkClient.call({method:"users.getCurrentUser", fields: 'name,first_name,last_name,pic128x128,url_profile,birthday,gender,email'}, (function(method, result, data){
                        if (data && (data.error_code == 102)) {
                            OkClient.initialized = false;
                            afterFailStatus();
                        } else if (result) {
                            result.session = {token: a_session.access_token, key: a_session.session_secret_key};
                            
                            window.fireEvent('login_status', {
                                source  : 'ok',
                                status  : a_session != null,
                                session : a_session
                            });
                        
                            result.nick = result.name;
                            result.url = result.url_profile;
                            this._setUser(result, 'ok');
                        } else if (afterFailStatus) afterFailStatus();
                    }).bind(this));
                                    
                } else afterFailStatus();
            }).bind(this);
            
            userRequest();
            
        } else afterFailStatus(); 
    },
    
    _updateUser: function(uif) {
        var u = null;
        if (uif) {
            u = {};
            USERFIELDS.each(function(f) {u[f]=uif[f] || ''});
        }
        this.user = u;
        window.fireEvent('userInfo', this.user);
    },
    
    _setUser: function(userInfo, a_source) {
        var t = this;
        if (userInfo && userInfo.uid) {
            var params = {};//uid: userInfo.uid, source: a_source}; 
            userInfo = $merge({source: a_source, avatar: t.user_avatar(userInfo), nauid: $val(t.nauid,userInfo.nauid,0)}, userInfo);
            var ou = t.options.user;
            USERFIELDS.each(function(field) {
                if (field in userInfo) {
                    if (ou) {
                        if (ou[field] != userInfo[field]) params[field] = userInfo[field];
                    } else params[field] = userInfo[field]; 
                }
            });
            
            if (!ou && userInfo.session) params.session = JSON.encode(userInfo.session);
            
            if (params.uid) {
                t.request('ajax,setUser', params, function(a_data) {
                    if (!a_data) {
                        let eparams = {"app.js": {"params": params}};
                        ym(4524184,'reachGoal','js_mobil_error', eparams);
                    } else {
                        var r = a_data.response; 
                        if (r && r.result) {
                            if (r.nauid && (r.nauid != t.nauid)) t._setNauid(r.nauid);
                            window.fireEvent('afterSetUser', t.user);
                        }
                    }             
                });
            }
            this._safeSource(a_source);
        }        
        t._updateUser(userInfo);
    },
    
    
// Public methods
	
    getUserLogin: function() { // Возвращает полный логин текущего пользователя
        return this.user_login(this.user);
    },
    
    getUserAvatar: function() {
        return this.user_avatar(this.user);
    },                         
    
    user_login: function(user) {
        var result = '';
        if (user) {
            if (user.name) result = user.name;
            else if (user.nick) result = user.nick;
            else if (user.screen_name) result = user.screen_name; 
            else {
                if (user.first_name) result += user.first_name;
                if (user.last_name) result += ' ' + user.last_name;
            }
        } 
        if (!result) result = locale.ANONYM;
        return result;
    },
    
    user_avatar: function(user) {
        var iu;
        if (user) {
            if (user.pic) iu = user.pic;
            else if (user.pic_2) iu = user.pic_2;
            else if (user.pic_small) iu = user.pic_small;
            else if (user.photo_100) iu = user.photo_100;
            else if (user.pic128x128) iu =  user.pic128x128;
            else if (user.picture) iu = user.picture.data.url; 
            else if (user.source && user.uid) iu = AVAURL + user.source + '/' + user.uid;  
            else iu = AVADEFAULTURL;
            if (user.last_time) iu += '?v=' + user.last_time.replace(/\D/g, '');            
        } else iu = AVADEFAULTURL;
        return iu;
    },
    
    user_avatar_big: function(user) {
        if (user.photo_max) return user.photo_max;
        else return this.user_avatar(user);
    },
    
    userRequest: function() {
        return {uid: (this.user?$val(this.user.uid,this.nauid,0):$val(this.nauid,0,0)).toString(), 
                source: this.user?$val(this.user.source,'none'):'none'};
    },
    
    request: function(a_task, params, a_onSuccess) {
        params = $merge({task: a_task, user: JSON.encode(this.userRequest())}, params);
        var method = params.method?params.method:'get';
        delete(params.method);
        var jsonRequest = new Request.JSON({url: this.options.URL, onSuccess: a_onSuccess})[method](params);
    },
    
    SendEvent: function(type, var_int1, var_int2, var_source, var_str1, var_str2) {
        this.request('ajax,event', {
            type: type,
            var_int1: var_int1,
            var_int2: var_int2,
            var_source: var_source,
            var_str1: var_str1,
            var_str2: var_str2
        })
    },
    
    logout: function(a_source) {
        if (!a_source && this.user) a_source = this.user.source;
        this.request('ajax,logout', {source: a_source}, (function(response) {
            this.options.user = null;
            this._updateUser(null);
            this.resetAll();        
            window.fireEvent('login_status', {source:a_source,status: false});
        }).bind(this));
    },
    
    alert: function(a_title, a_text, afterProc, options) {
    //isCancel, okCap, width
        var form = $('alert-form').clone();
        
        options = $merge({width: 300, isCancel: false}, options) 
        if (form) {
            form.set('id', '');
            form.getElement('h2').set('text', a_title);
            form.getElement('.message').set('html', a_text);
            if (options.okCap) form.getElement('.ok').setProperty('value', options.okCap);
            form.getElement('.ok').addEvent('click', (function() {
                sbox.close();  
                if (afterProc != null) afterProc();        
            }).bind(this));
            
            var cancel = form.getElement('.cancel'); 
            
            if (options.isCancel) {
                cancel.addEvent('click', (function() {
                     sbox.close();
                }).bind(this));
            } else cancel.setStyle('display', 'none');
            
            var sbox = SqueezeBox.open(form, {
        		handler: 'adopt',
        		size: {x: options.width, y: 'auto'}
        	});
        }
        return sbox;
    },
    
    removeConfirm: function(onOk) {
        this.alert(locale.WARNING, locale.DELETEQUESTION, onOk, {isCancel: true});
    },
    
    auxonly: function(crProc, noAux, caption) {
        var sbox = null;
        _repeatCR = null;
        window.addEvent('userInfo', function(a_user) {
            if (a_user && _repeatCR) _repeatCR();
            _repeatCR = null;
        });
        
        if (app.user) crProc()
        else {
            sbox = app.alert(locale.AUTORIZATION, locale.ACCESSAUXONLY + ($$('.login')[0] || DEFE).get('html'), function() {
                if (noAux) crProc.delay(200); 
            }, {okCap: caption});
            loginBlockInit(sbox.element.getElement('.aux'));
            _repeatCR = (function() {
                sbox.close();
                crProc.delay(200);
            }).bind(this);
        }         
        return sbox;
    },
    
    _checkResize: function(image, MAXWIDTH, MAXHEIGHT) {
        var checkSize = (function () {
            if (MAXWIDTH && MAXHEIGHT && ((image.width > MAXWIDTH) || (image.height > MAXHEIGHT)) && (image.src.substr(0, 4) == 'data')) {
                var scale = Math.min(MAXWIDTH/image.width, MAXHEIGHT/image.height);
                image.set('src', (scaleImage(image, scale)));
                return false; 
            } 
            return true;
        }).bind(this);
        
        return checkSize();
    },
    
    openImage: function(onComplete, MAXWIDTH, MAXHEIGHT) {
        var This = this;
        this.openFile(function(file_data) {
            var img = new Element('img', {src: file_data});
            img.addEvent('load', function(e) {
                This._checkResize(img, MAXWIDTH, MAXHEIGHT);
                onComplete(scaleImage(img));
            });
        });
    },
    
    openAvatar: function(onComplete, WIDTH, HEIGHT) {
        var This = this;
        this.openFile(function(file_data) {
            var img = new Element('img', {src: file_data});
            img.addEvent('load', function(e) {
                onComplete(fitTo(img, WIDTH, HEIGHT));
            });
        });
    },
    
    openFile: function(onComplete) {
        var input = $('open_file');
        if (!input) {
            input = new Element('input', {type:'file',id:'open_file',accept:'image',style:'display:none'});
            input.addEvent('change', function(e) {
                var fileList = e.target.files;
                var count = 0;
                 
                var readFile = function(file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        onComplete(e.target.result);
                        count++;
                        if (count < fileList.length) readFile(fileList[count]);
                        else fileList.length = 0;
                    };
                    reader.readAsDataURL(file);
                };  
                
                readFile(fileList[count]);
            }); 
            input.inject($('bodyArea'));
        }

        var evt = document.createEvent("MouseEvents");
        evt.initEvent("click", true, false);
        input.dispatchEvent(evt);
    },
    
    changeAvatar: function(onComplete) {
        var This = this;
        if (this.user) {
            this.openAvatar(function(image_data) {
                This.request('user,changeAvatar', {
                    method: 'post',
                    image: image_data
                }, function(a_data) {
                    onComplete((a_data.response && a_data.response.result)?image_data:null);
                    This.user.last_time = Date.now().toString();
                    window.fireEvent('CHANGEAVATAR');
                });
            }, 300, 300);
        }
    },
    
    afterAdv: function(after) {
        after();
    }
});
})();

function loginBlockInit(a_elem) {
    var block = a_elem;
    function onTypeClick(e) {
        if (t = e.target.get('data-type')) {
            app['login' + t.toUpperCase()]();
        }
        e.stopPropagation();
    }
    
    block.$$('img').each(function(link) {
        link.addEvent('click', onTypeClick);
    });
}

function loadLastEvents(parent, user) {
    parent.set('html', '');
    
    function actionInit(a) {
        a.addEvent('click', function() {
            app.request('ajax,setStateNotify', {notify_id: a.get('data-id'), state: 'remove'}, function(a_data) {
                var rp = a_data.response;
                if (rp && rp.result) {
                    var item = a.getParent('.item');
                    item.fade(0);
                    item.remove.delay(700, item);                
                } else app.alert(locale.ERROR, locale.WRONG_RESPONSE);
            });
        });
    }
    
    function itemInit(im) {
        function sentState() {
            app.request('ajax,setStateNotify', {notify_id: nfac.get('data-id'), state: 'sent'}, function(a_data) {
                var rp = a_data.response;
                if (rp && rp.result) {
                    im.removeClass('wait');
                    im.addClass('sent');
                    im.removeEvent('click', this);
                } else app.alert(locale.ERROR, locale.WRONG_RESPONSE);
            });
        }
        
        nfac = im.$('.ntf-action');
        submenuInit(im.$('.submenu'));
        actionInit(nfac);
        if (im.hasClass('wait')) {
            im.addEvent('click', sentState);
            
            var t;
            im.addEvent('mouseout', function() {clearTimeout(t);});
            im.addEvent('mouseover', function() {t = sentState.delay(2000);});
            
        }
    }
    
    function createList(a_data) {
        var list = '';
        a_data.each(function(a_item) {
            list += '<div class="item ' + a_item.state + '">' + 
                    '<div class="submenu"><a data-action="remove" data-id="' + a_item.notify_id + '" class="ntf-action">' + locale.REMOVE + '</a></div><div class="notify">' + 
                    a_item.data + 
                    '</div></div>' + 
                    '</div>';
        });
        parent.set('html', '<div><div class="list">' + list + '</div></div>')
        parent.$$('.item')[0].each(itemInit);            
    }
    app.request('ajax,afterEvents', user?{user: JSON.encode(user)}:{}, function(a_data) {
        var rp = a_data.response;
        if (rp && rp.data) createList(rp.data);
    });   
}

function checkFlash(doAfter, doError) {
    var fp;
    if (navigator.plugins && navigator.plugins.namedItem) fp = navigator.plugins.namedItem('Shockwave Flash');
    else fp = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
    
    if (fp) doAfter();
    else {
        (function() {
            if (app) {
                var text = locale.FLASHINACTIVE;
                if (app.isChrome) text = locale.FLASHINACTIVE_CHROME; 
                app.alert(locale.WARNING, text, null, {width: 800});
            }
        }).delay(400);
        if (doError) doError();
    }
}