var baseApp = new Class({
    Extends         : Events,
    _canvas         : null,
    _args           : null,
    _wrapper        : null,
    _input          : null,
    _loader         : null,
    _saveButton     : null,
    _fps            : 14,            
    
    initialize: function() {
        var inputClass = Utils.isTouchOnly()?tabletInput:pcInput;
        
        this._canvas = canvasImplement($('canvas'), PJCanvas, inputClass);
        this._args = this.getArgs();
        this.doResize();
        this.createComponents();
        this.listenEvents();
        this._canvas.play(1000/this._fps);
    },         
    
    createComponents: function() {
        this._wrapper   = $('wrapper');
        this._input     = $('openFile');
        this._loader    = $('loader');
        this._saveButton = $('saveButton');
        this._loader.getElement('p').set('text', LOCALE.LOADERTEXT);
        
        canvasSave.init(this);
    },
    
    listenEvents: function() {
        window.addEvent('resize', this.doResize.bind(this));
        this._input.addEvent('change', this._doOpenFileChange.bind(this)); 
        this._canvas.addEvent(PJEVENTS.SHOWIMAGEDIALOG, this.openImageDialog.bind(this));
        this._canvas.addEvent(PJEVENTS.STARTLOAD, this.doStartLoad.bind(this));       
        this._canvas.addEvent(PJEVENTS.COMPLETE, this.doComplete.bind(this));
        this.addEvent(canvasSave.START, this.doSaveStart.bind(this));       
        this.addEvent(canvasSave.COMPLETE, this.doSaveComplete.bind(this));
        this.addEvent(canvasSave.CHECKSAVE, this.doCheckSave.bind(this));
        
        window.addEvent('dblclick', this.clearSelection.bind(this));       
    },
    
    clearSelection: function() {
        try {
            window.getSelection().removeAllRanges();
        } catch(e) {
            document.selection.empty(); // IE<9
        }
    },        
    
    setTemplateID: function(id, defaultImages) {
        this._canvas.setTemplate(id, (this._canvas._images != defaultImages)?defaultImages:this._canvas._images);
    },
    
    loaderVisible: function(value) {
        this._loader.setStyle('visibility', value?'visible':'hidden');
    },
    
    doStartLoad: function(e) {
        this.loaderVisible(true);
    },
    
    doComplete: function(tmplID) {
        this.loaderVisible(false);
    },
    
    openImageDialog: function() {
        var event = {
            app     : this,
            result  : false
        };
        window.fireEvent(PJEVENTS.REQUESTPHOTO, event);
        if (!event.result) this._imitateClickOpenFile();
    },
    
    createClick: function() {
        var evt = document.createEvent("MouseEvents");
        evt.initEvent("click", true, false);
        return evt;
    },
    
    _imitateClickOpenFile: function() {
        this._input.dispatchEvent(this.createClick());     
    },    
    
    getCanvasResult: function(resize) {
        var canvas = document.createElement('canvas');
        var frame = this._canvas._frame;
        var fsize = new Vector(frame._image.width, frame._image.height); 
        var size = resize?resize:fsize.clone();
        var scale = Math.min(size.x / fsize.x, size.y / fsize.y); 
           
        canvas.width = fsize.x * scale; 
        canvas.height = fsize.y * scale;
        
        var ctx = canvas.getContext("2d");
        ctx.antialias = true;
        
        ctx.setTransform(scale, 0, 0, scale, 0, 0);
        frame.draw(ctx, new Matrix(scale, 0, 0, scale));
        return canvas;
    },
    
    doSaveComplete: function(fileName) {
        this.loaderVisible(false);
        this._saveButton.fade('in');
    },
    
    doCheckSave: function(complete) {
        complete();
    },
    
    doSaveStart: function() {
        this.loaderVisible(true);
        this._saveButton.fade('out');
    },
    
    saveToServer: function(actionPath, a_fileName, a_onComplete, resize, a_path, replaceMime) {
        var request = new Request({
            url : actionPath,
            data: {
                image: canvasSave.getDataAsJPG(replaceMime, resize),
                fileName: a_fileName,
                path: a_path?a_path:'' 
            },
            onComplete: a_onComplete
        });
        
        request.post();
    },
    
    doResize: function () {
        this._canvas.refreshRect();
    },
    
    getArgs: function() {
        var args = document.location.href.split('?');
        if (args.length > 1) return args[1].parseQueryString();
        return {};
    },
        
    _doOpenFileChange: function(e) {
        if (this._args.uid) {
            var onComplete = (function(img) {
                this._canvas.removeEvent(PJEVENTS.IMGFILECOMPLETE, onComplete);
                this.setUserPhoto(this._canvas._frame._selectHole, img);
            }).bind(this);
            this._canvas.addEvent(PJEVENTS.IMGFILECOMPLETE, onComplete);
        }
        this._canvas.injectImageFiles(e.target.files);
    },
    
    setUserPhoto: function(hole_index, image) {
       var request = new Request({
            url : 'upload_photo.php',
            data: {
                image: image,
                uid: this._args.uid,
                hole_index: hole_index 
            }
        });
        
        request.post();
    },
    
    afterAdv: function(after) {
        after();
    },
    
    getFileName: function() {
        return 'oformi-foto.ru.jpg'
    }
});