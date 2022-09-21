var LISTSTICK = {
    LEFT: 1,
    TOP: 2,
    RIGHT: 3,
    BOTTOM: 4
}

var BIGEDGE = '66%';
var SMALLEDGE = '34%';
var TMPLSOURCEURL = PROTOCOL + '://oformi-foto.ru/games/data/';
var STORAGEURL = PROTOCOL + '://oformi-foto.ru/games/data/storage_v02.php';
var DEFAULT_GROUP = 61; 

var lappTips = new Class({
    Extends: Tips,
    _mouseDown: false,
	setOptions: function(a_options) {
        this.parent(a_options);
        window.addEvent(MOUSE_EVENTS.MOUSEDOWN, (function(e) {
            this._mouseDown = true;
        }).bind(this));
        window.addEvent(MOUSE_EVENTS.MOUSEUP, (function(e) {
            this._mouseDown = false;
        }).bind(this));
    },
    
    show: function(element){
		if (!this._mouseDown) this.parent(element);
	}
});

var leftPJApp = new Class({
    Extends         : baseApp,
    _list           : null,
    _listWindow     : null,
    _categories     : null,
    _listStick      : 0,
    _listSticks     : [LISTSTICK.LEFT, LISTSTICK.TOP],
    _groups         : '',     
    _tmplCount      : 100,
    _tips           : null,
    _injectPhoto    : null,
    _storageData    : null,
    _plugins        : [],
    _editor         : null,   
    initialize      : function(tmplCount) {
        this._tmplCount = tmplCount;
        this.parent();
        
        this.loadStorageInfo((function(storageData) {
            this._storageData = storageData;                     
//            PJCONST.TMPLURLPATHFULL = PROTOCOL + '://' + storageData.options.JPG_URL + '/';
            PJCONST.TMPLURLPATHPREVIEW = PROTOCOL + '://' + storageData.options.PREVIEW_URL + '/i';
            
            this.parseArgs();
        }).bind(this));
    },  
    
    parseArgs: function() {
        if (this._args.photo) GPDImages[0][0] = this._args.photo;
        if ($('listLayer')) this.setGroups(this._args.group?this._args.group:0);
        else if (this._args.tid) {
            if (this._args.gid) {
                this._groups = parseInt(this._args.gid);
                this.refreshFromGroup();
            }
            this.setTemplateID(this._args.tid, _defaultImages);
        }
    },
    
    createComponents: function() {
        this.parent();
        this._listWindow = $('listWindow');
        this._categories = $('categories');
        this._injectPhoto = $('injectPhoto');
        
        var l_listElement = $('listLayer'); 
        if (l_listElement) this._list = new baseTmplList(l_listElement);
        
        this._updateFromWindow();
    },
    
    loadStorageInfo: function(onComplete) {
        (new Asset.javascript(STORAGEURL, {
            onload: (function(e) {onComplete(GLOBALSTORAGE);})
        }));
    },
    
    _deleteIPButton: function() {
        if (this._injectPhoto) {
            this._injectPhoto.destroy();
            this._injectPhoto = null;
        }
    },
    
    listenEvents: function() {
        var doStartComplete = (function() {
            this._canvas.removeEvent(PJEVENTS.COMPLETE, doStartComplete);
            if (!Utils.isTouchOnly()) this._tips = new lappTips($$('.Tips1'));   
            (function() {this._canvas.addEvent(HOLE_EVENTS.IMAGECOMPLETE, this._doImageComplete.bind(this))}).delay(3000, this);
        }).bind(this);
        
        var doOneImageComplete = (function (event) {
            window.removeEvent(PJEVENTS.REQUESTPHOTO, doOneImageComplete);
            this._canvas.removeEvent(GC_EVENTS.STOPMODIFY, doStopModify);
        }).bind(this);        
        
        var doStartModify = (function() {
            if (this._injectPhoto) this._injectPhoto.fade('hide');
            if (this._tips) this._tips.hide();
        }).bind(this);
        
        var doStopModify = (function() {
            if (this._injectPhoto) this._injectPhoto.fade('in');
        }).bind(this);
        
        this.parent();
        if (this._list) {
            this._list.addEvent(PJEVENTS.TMPLSELECT, this.doTmplSelect.bind(this));   
            this._list.addEvent(PJEVENTS.COMPLETE, this.doListStartComplete.bind(this));
        }

        this._canvas.addEvent(PJEVENTS.COMPLETE, doStartComplete);
        this._canvas.addEvent(GC_EVENTS.STARTMODIFY, doStartModify);
        this._canvas.addEvent(GC_EVENTS.STOPMODIFY, doStopModify);
        
        window.addEvent(PJEVENTS.REQUESTPHOTO, doOneImageComplete);
    },
    
    _doImageComplete: function(holeIndex) {
        this._deleteIPButton();
        this._saveBeat.delay(1000);
    },
    
    _saveBeat: function() {
        Utils.beat($('saveButton'), 'margin-bottom');
    },
    
    doTmplSelect: function(id) {
        this.setTemplateID(id, _defaultImages);
    }, 
    
    doListStartComplete: function() {
        if (this._args.tid) {
            this.setTemplateID(this._args.tid, _defaultImages);
        } else this.setTemplateID(this._list._list[0], _defaultImages);
        
        this._list.removeEvent(PJEVENTS.COMPLETE, this.doListStartComplete);    
    },    
    
    doComplete: function(tmplID) {
        this.parent(tmplID);
        if (this._list) this._list.setCurrent(tmplID);
        if (this._injectPhoto) {
            this.injectPhotoAnim();
            this.checkStartTextFrame();            
        }            
    },
    
    injectPhotoAnim: function() {
        if (this._injectPhoto) {
            var frame = this._canvas.getFrame(),rect,p;
            if (frame) {
                rect = this._canvas.getCoordinates(this._wrapper);
                if (!frame.currentHole()) frame.setSelectHole(0);
                
                var mrect = frame.currentHole().mask.getRect();
                if (mrect.width > 0) {
                    p = frame.currentHole().mask.localToGlobal(new Vector(mrect.width / 2, mrect.height));
                    
                    if (this._injectPhoto) {                    
                        (new Fx.Morph(this._injectPhoto, {
                            transition: Fx.Transitions.Back.easeInOut
                        })).start({
                            left: rect.left + p.x,
                            top: Math.min(rect.top + p.y, rect.top + rect.height - this._injectPhoto.getSize().y)
                        });
                    }
                } else this.injectPhotoAnim.delay(300, this);
            }
        }
    },
    
    loaderVisible: function(value) {
        this.parent(value);
        if (this._injectPhoto) this._injectPhoto.fade(value?0:1);
    }, 
    
    refreshFromGroup: function() {
        var i = parseInt(this._groups);
        
        if (i && GPDImages[i]) _defaultImages = GPDImages[i];
        else _defaultImages = GPDImages[0];
        
        if (isTextGroup(i)) this._textInsPhoto(LOCALE.INSERTYOUPTEXT);
        else this._textInsPhoto(LOCALE.INSERTYOUPHOTO);
    },
    
    openImageDialog: function() {
        if (isTextGroup(parseInt(this._groups))) this._textEditor();
        else this.parent();
    },  
    
    checkStartTextFrame: function() {
        var frame = this._canvas.getFrame();    
        if (isTextGroup(parseInt(this._groups)) && frame) {
            TextEditor.restoreTextCanvas((function(canvas) {
                if (canvas != null) this._textCanvasToHole(canvas);            
            }).bind(this));                                
        }    
    },        
    
    _textCanvasToHole: function(canvas) {
        var frame = this._canvas.getFrame();  
        if (frame && frame.currentHole()) {        
            var hole = frame.currentHole();
            var img = hole.image._image;
            var holeRect = frame.holeRect(frame._selectHole);
            var rect = new Rectangle(0, 0, Math.min(holeRect.width * 3, IMAGECONST.MAXWIDTH), Math.min(holeRect.height * 3, IMAGECONST.MAXHEIGHT));
            if  (prevSize = hole.image.getImageSize().length() >= rect.size().length())
                 rect = new Rectangle(0, 0, img.width, img.height);
            
            
            var space = Math.max(rect.width, rect.height) * 0.05; 
            var mat = Matrix.enterHere(new Rectangle(space, space, rect.width - space * 2, rect.height - space * 2), 
                                        new Vector(canvas.width, canvas.height), true, false);
    
            var tcanvas = frame.createCanvas(rect.width, rect.height);
            var ctx = tcanvas.getContext("2d");
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, tcanvas.width, tcanvas.height);
            ctx.setTransform(mat.a,mat.b,mat.c,mat.d,mat.tx,mat.ty);
            ctx.drawImage(canvas, 0, 0);
            
            var data = tcanvas.toDataURL("image/png");
            if (prevSize) hole.image.setValue('src', data);
            else frame._setHoleImage(frame._selectHole, data);
            tcanvas.destroy();      
            
            _defaultImages[frame._selectHole] = data;
        }                            
    },                     
    
    _textEditor: function() {
        if (!this._editor) {
            this._editor = new TextEditor($('editor'), this._canvas.getSize());
            this._editor.addEvent('apply', (function(result) {
                if (result && result.canvas) {
                    this._textCanvasToHole(result.canvas);
                } 
                this._editor = null;    
            }).bind(this));
            this._deleteIPButton();
        }
    },
    
    _textInsPhoto: function(text) {
        if (this._injectPhoto) this._injectPhoto.getElement('p').set('text', text);
    },
        
    setGroups: function(a_group) {
        a_group = parseInt(a_group);
        
        if (!a_group) a_group = Cookie.read('group');
        else Cookie.write('group', a_group);
        
        this._groups = a_group;
        this.refreshFromGroup();
         
        if (this._groups > 0) this._list.loadTemplates(TMPLSOURCEURL, 'model=' + DATA_MODEL + '&method=getList&page=1&count=' + this._tmplCount + '&groups=' + this._groups, true);
        else if (this._storageData) {
            this._list.assign(this._list.parseStorage(this._storageData.templates), true);
            this._list.fireEvent(PJEVENTS.TMPLSELECT, this._storageData.defaults[0].DEFAULT_MASK);
        } 
    },
    
    doResize: function () {
        this.parent();
        this._updateFromWindow();
    },
    
    _updateListStick: function() {
        var lwtop = 0;
        if (this._categories) {
            this._list.checkVisibleItems();
        }        
        if (this._listWindow) { 
            this._listWindow.set('class', 'lw' + this._listStick);
        }
        
        this._wrapper.set('class', 'wr' + this._listStick);
    },                             
    
    setListStick: function(value) {
        if (this._listStick != value) {
            this._listStick = value;
            this._updateListStick();
        }
    },

    _updateFromWindow: function() {
        if (this._list) {
            var size = window.getSize();
            if (size.x > size.y) this.setListStick(this._listSticks[0]);
            else this.setListStick(this._listSticks[1]);
        }
        this.injectPhotoAnim()        
    }    
});