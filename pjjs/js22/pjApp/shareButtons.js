var shareButtons = new Class({
    _index: 0,
    _sessionName: '',
    _userViewURL: '',
    _app: null,
    _nextfn: true,
    initialize: function(a_shareButtonsLayer, a_app, userViewURL, sessionName) {
        this._sessionName =  sessionName;
        this._userViewURL = userViewURL;
        this._app = a_app;
        this.mailInit(a_shareButtonsLayer.getElement('#mail'));
        this.vkInit(a_shareButtonsLayer.getElement('#vk'));
        this.mmInit(a_shareButtonsLayer.getElement('#mm'));
        this.okInit(a_shareButtonsLayer.getElement('#ok'));
        this.fbInit(a_shareButtonsLayer.getElement('#fb'));
        this.twInit(a_shareButtonsLayer.getElement('#tw'));
        
        this._app._canvas.addEvent(GC_EVENTS.STARTMODIFY, this.doUpdate.bind(this));
        this._app._canvas.addEvent(PJEVENTS.IMGFILECOMPLETE, this.doUpdate.bind(this));
        
    },
    
    doUpdate: function() {
        this._nextfn = true;
    },
    
    uniqueFileName: function() {
        if (this._nextfn) {
            this._index++;
            this._nextfn = false;
        }
        return this._sessionName + this._index;
    },
    
    saveToServer: function(fileName, doComplete) {
        _app.saveToServer('images/transport.php', fileName, doComplete, new Vector(500, 500));
    },
    
    vkInit: function(span) {
        if (span) {
            var setButton = (function() {
                var fileName = this.uniqueFileName();
                span.set('html', VK.Share.button({url: this._userViewURL + "?img=" + fileName},{type: "custom", text: "<img src=\"" + PROTOCOL + "://vk.com/images/share_32.png\" width=\"32\" height=\"32\" />"}));
                var a = span.getElement('a');
                a.addEvent(MOUSE_EVENTS.MOUSEDOWN, (function(e) {
                    this.saveToServer(fileName, (function() {
                        a.destroy();
                        setButton();
                    }).bind(this));
                    return true;                
                }).bind(this));
            }).bind(this);
            
            setButton();
        }    
    },
    
    shareButton: function(span, social) {
        if (span) {
            span.addEvent(MOUSE_EVENTS.MOUSECLICK, (function(e) {
                var fileName = this.uniqueFileName();
                
                var params = 'menubar=no,location=no,resizable=no,scrollbars=no,status=no,width=800,height=750';
                var url = this._userViewURL + "?img=" + fileName + '&share=' + social;
                
                if (this._app && this._app._args.name) url += '&name=' + this._app._args.name;
                 
                var win = window.open(url, social + "_view", params);
                var srcURL = 'images/users/' + fileName + '.jpg';
                
                this.saveToServer(fileName, (function(a_result) {
                    eval('var ' + a_result + ';');
                    if (parseInt(result) == 0) {
                        alert('ERROR: ' + result);
                        win.close();
                    }
                }).bind(this));
                return true;                
            }).bind(this));
        }
    },
    
    mailInit: function(span) {
        this.shareButton(span, 'mail');
    },
    
    mmInit: function(span) {
        this.shareButton(span, 'mm');
    },
    
    okInit: function(span) {
        this.shareButton(span, 'ok');
    },
    
    fbInit: function(span) {
        this.shareButton(span, 'fb');
    } ,
    
    twInit: function(span) {
        this.shareButton(span, 'tw');
    }
});