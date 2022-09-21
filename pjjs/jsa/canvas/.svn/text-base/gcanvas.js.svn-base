var GC_EVENTS = {
    UPDATE      : 'UPDATE',
    ROLLOVER    : 'ROLLOVER',
    ROLLOUT     : 'ROLLOUT',
    
    COMPLETE    : 'COMPLETE',
    STARTMODIFY : 'STARTMODIFY',
    PROCESSMODIFY: 'PROCESSMODIFY',
    STOPMODIFY   : 'STOPMODIFY',
    REFRESHREQUIRE: 'REFRESHREQUIRE'
}

var MOUSE_EVENTS = {
    MOUSEDOWN   : 'mousedown',
    MOUSEUP     : 'mouseup',
    MOUSECLICK  : 'click',
    MOUSEMOVE   : 'mousemove',
    MOUSEOVER   : 'mouseover',
    MOUSEOUT    : 'mouseout',
    MOUSELEAVE  : 'mouseleave',
    DOUBLECLICK : 'doubleclick',
    CANVASCLICK : 'CANVASCLICK'
}

var GC_CONST = {
    CENTER          : 'center',
    UNDEFINED       : 'undefined',
    DBLCTIME        : 500
}

var Canvas = new Class({
    Extends         : gcObject,
    _context        : null,
    _refreshRequire : false,
    _refreshStage   : false,
    _focus          : null,
    _frameTimerID   : 0,
//    _mouseDown      : null,
    _waitDClick     : false,
    _input          : null,
    
    init: function(inputClass) {
        this._context = this.getContext("2d");
        this._context.antialias = true;
        this.listenerEvents();
        if (inputClass) this._input = new inputClass(this);
    },
    
    listenerEvents: function() {
    },
    
    resetTransform: function () {
        this._context.setTransform(1,0,0,1,0,0);
    },
    
    clear: function() {
        this.resetTransform();
        this._context.clearRect(0, 0, this.width, this.height);
    },
            
    addChild: function (child) {
        this.parent(child);
        this.refreshRequire();
        return child;
    },
    
    _refreshFrame: function() {
        if (!this._refreshStage) {
            this._refreshStage = true;
            this.clear();
            this._objects.each(this._refreshObject.bind(this));
            this._refreshRequire = this._refreshStage = false;
        }
    },
            
    _enterFrame: function() {
        this._objects.each((function(item) {
            if (item.canUse()) item._enterFrame();
        }).bind(this));
    },
    
    _refreshObject: function(item) {
        if (item.canUse()) item._refresh(this._context);
    },
    
    removeChild: function (object) {
        if (this.parent(object)) this.refreshRequire();
    },
    
    swap: function(index1, index2) {
        this.parent(index1, index2);
        this.refreshRequire();
    },
    
    play: function(msc) {
        this._frameTimerID = this.doFrame.periodical(msc, this);    
        this.doFrame();        
    },
    
    stop: function() {
        clearInterval(this._frameTimerID);
        this._frameTimerID = 0;
    },
    
    nowPlaying: function () {
        return this._frameTimerID != 0;
    },
    
    doFrame: function() {
        this._enterFrame();
        if (this._refreshRequire) this._refreshFrame();
    },
    
    refreshRequire: function () {
        this._refreshRequire = true;
        this.fireEvent(GC_EVENTS.REFRESHREQUIRE);
    },
    
    setFocus: function (a_focus, e) {
        if (this._focus != a_focus) {
            if (this._focus) this._focus.fireEvent(GC_EVENTS.ROLLOUT, e);
            this._focus = a_focus;
            if (this._focus) this._focus.fireEvent(GC_EVENTS.ROLLOVER, e);
        }
    },
    
    hitObjectTest: function (gp) {
        this.setFocus(this.hitTest(Utils.globalToLocal(gp, this)));
    },
    
    _deligateEvent: function (e) {
        if (this._focus) this._focus.fireEvent(e.type, e);
    },
    
    _doClick: function(e) {
        e = Object.clone(e);
        e.type = MOUSE_EVENTS.MOUSECLICK;
        this._deligateEvent(e);
    },
    
    resize: function(size) {
        this.width = size.x;
        this.height = size.y;
        this.refreshRequire();
    },

    setRect: function(rect) {
        this.setStyle('left', rect.x);
        this.setStyle('top', rect.y);
        this.resize(rect.getSize());
    },
    
    getRect: function () {
        var size = this.getSize();
        return new Rectangle(0, 0, size.x, size.y);
    },
    
    doubleClick: function(e) {
        this.fireEvent(MOUSE_EVENTS.DOUBLECLICK);
    },
    
    click: function(e) {
        this._waitDClick = true;
        
        this._deligateEvent(e);
        this._doClick(e);
            
        (function() {
              this._waitDClick = false;
        }).bind(this).delay(GC_CONST.DBLCTIME);

        this.fireEvent(MOUSE_EVENTS.CANVASCLICK);        
    } 
});

function canvasImplement(canvas, canvasClass, inputClass) {
    canvasClass = canvasClass?canvasClass:Canvas;
    Object.merge(canvas, new canvasClass());
    canvas.init(inputClass);
    return canvas;
};