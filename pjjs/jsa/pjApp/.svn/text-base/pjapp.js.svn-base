var PJURL = 'view.php?tid=';

var emptyObject = {
    addEvent: function(event, proc) {
    }
}

Window.implement('$', function(el, nc, defaultImplement){
    var elem = document.id(el, nc, this.document); 
	return elem?elem:Object.merge(emptyObject, defaultImplement);
});
            

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
                   

var PJApp = new Class({
    Extends         : baseApp,
    _injectPhoto    : null,
    _tmplList       : null,
    _tmplIndex      : -1,
    _tmplId         : -1,
    _tips           : null,
    
    initialize: function(a_listClass, a_tmplURL) {
        this.parent();
        this.createList(a_listClass, a_tmplURL);
    },
    
    createList: function(a_listClass, a_tmplURL) {
        this._tmplList = new a_listClass($('listWindow'));
        this._tmplList.addEvent(PJEVENTS.TMPLSELECT, (function(id) {
            this.setTemplateID(id);
        }).bind(this));
        
        this._tmplList.tmplURL = a_tmplURL;
//        this._tmplList.requestHide();
    },
    
    loadTemplates: function(group, doLoadTmpls) {
        this._tmplList.loadTemplates(group, (function() {
            this.doLoadTmpls();
            doLoadTmpls();
        }).bind(this));
    },
    
    doLoadTmpls: function() {
        this.setTemplateID(_storage.defaults[0].DEFAULT_MASK);
    },
    
    setTemplateID: function(id, defaultImages) {
        this.parent(id, defaultImages?defaultImages:this.getDefaultImages());
        if (this._tmplList._list)
            this._tmplList._list.each((function (item, i) {
                if (item == id) {
                    this._tmplIndex = i;
                    this._tmplId = id;
                }            
            }).bind(this));
    },
    
    getDefaultImages: function() {
        return ['images/img10.jpg', 'images/img2.jpg', ''];
    },
    
    setTemplateIndex: function(a_tmplIndex) {
        if ((this._tmplIndex != a_tmplIndex) && (a_tmplIndex >= 0) && (a_tmplIndex < this._tmplList._list.length)) {
            this._tmplIndex = a_tmplIndex;
            this._canvas.setTemplate(this._tmplList._list[this._tmplIndex], this._canvas._images?this._canvas._images:this.getDefaultImages());
        }
    },
    
    createComponents: function() {
        this.parent();
        this._injectPhoto = $('injectPhoto');
        $('saveButton').set('download', 'oformi-foto.ru.jpg');
    },
    
    listenEvents: function() {
        this.parent();
        
        $('saveButton').addEvent(MOUSE_EVENTS.MOUSEOVER, this.doSaveButtonOver.bind(this));
        $('tmplsButton').addEvent(MOUSE_EVENTS.MOUSECLICK, this.doTmplsButton.bind(this));
        $('centerBlock').addEvent(MOUSE_EVENTS.MOUSECLICK, this.doCanvasLayerClick.bind(this));
        $('edit').addEvent(MOUSE_EVENTS.MOUSECLICK, this.doEditClick.bind(this));
        this._injectPhoto.addEvent(MOUSE_EVENTS.MOUSECLICK, this.doInjectPhoto.bind(this));
        
        var doOneImageComplete = (function (event) {
            window.removeEvent(PJEVENTS.REQUESTPHOTO, doOneImageComplete);
            this._injectPhoto.destroy();
            this._injectPhoto = null;
            this._canvas.removeEvent(GC_EVENTS.STOPMODIFY, doStopModify);
        }).bind(this);        
        
        var doStartModify = (function() {
            if (this._injectPhoto) this._injectPhoto.fade('hide');
        }).bind(this);
        
        var doStopModify = (function() {
            if (this._injectPhoto) this._injectPhoto.fade('in');
        }).bind(this);
        
        this._canvas.addEvent(HOLE_EVENTS.HOLESELECT, doStartModify);
        this._canvas.addEvent(GC_EVENTS.STARTMODIFY, doStartModify);
        this._canvas.addEvent(GC_EVENTS.STOPMODIFY, doStopModify);
        
        window.addEvent(PJEVENTS.REQUESTPHOTO, doOneImageComplete);
        
        var saveButton = $('saveButton'); 
        if (Browser.name == 'unknown') saveButton.addEvent(MOUSE_EVENTS.MOUSECLICK, this.saveToFile.bind(this));
        else saveButton.addEvent(MOUSE_EVENTS.MOUSEOVER, this.doSaveButtonOver.bind(this));
        
        var doStartComplete = (function() {
            this._canvas.removeEvent(PJEVENTS.COMPLETE, doStartComplete); 
            this._tips = new lappTips($$('.Tips1'));   
        }).bind(this);
        
        var doStartModify = (function() {
            if (this._injectPhoto) this._injectPhoto.fade('hide');
            if (this._tips) this._tips.hide();
        }).bind(this);
        
        var doStopModify = (function() {
            this._injectPhoto.fade('in');
        }).bind(this);
        
        this._canvas.addEvent(PJEVENTS.COMPLETE, doStartComplete);
        this._canvas.addEvent(HOLE_EVENTS.HOLESELECT, doStartModify);
        this._canvas.addEvent(GC_EVENTS.STARTMODIFY, doStartModify);
        this._canvas.addEvent(GC_EVENTS.STOPMODIFY, doStopModify);
    },
    
    doSaveButtonOver: function() {
        var canvas = document.createElement('canvas');
        var frame = this._canvas._frame; 
        canvas.width = frame._image.width; 
        canvas.height = frame._image.height;
        var ctx = canvas.getContext("2d");
        ctx.antialias = true;
        
        frame.draw(ctx, new Matrix());
        $('saveButton').set('href', Canvas2Image.getDataAsJPG(canvas));
        return false;
    },                                
    
    doInjectPhoto: function() {
        this.openImageDialog();
        return false;
    },
    
    doTmplsButton: function(e) {
        this._tmplList.show();
        return false;
    },
    
    doCanvasLayerClick: function(e) {
        this._tmplList.hide();
    },
    
    doEditClick: function(e) {
        var params = 'menubar=no,location=no,resizable=no,scrollbars=no,status=no,width=800,height=750';
        var win = window.open(PJURL + this._tmplId, "pjview", params);
    }
});
