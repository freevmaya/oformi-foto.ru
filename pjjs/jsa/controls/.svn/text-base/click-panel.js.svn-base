var clickPanel = new Class({
    Extends: Events,
    _element: null,
    _center: new Vector(60, 60),
    _canvas: null,
    _hitHole: 0,
    _app    : null,
    _elemSelect: false,
    _waitTimer : 0,
    initialize: function(a_elem, a_app) {
        this._app = a_app;
        this._element = a_elem;
        this._canvas = a_app._canvas;
        this.listenEvents();
        this.initControls();
        this.hide();
    },
    
    listenEvents: function() {
        window.addEvent('resize', this.doWindowResize.bind(this))
/*        
        if (Utils.isTouchOnly()) window.addEvent('touchstart', this.doCursorDown.bind(this));
        else window.addEvent(MOUSE_EVENTS.MOUSEDOWN, this.doCursorDown.bind(this));
*/        
        
        this._canvas.addEvent(IACTION.CURSORDOWN, this.doCursorDown.bind(this));
        this._canvas.addEvent(IACTION.CURSORUP, this.doCursorUp.bind(this));
        this._canvas.addEvent(MOUSE_EVENTS.CANVASCLICK, this.checkAndShow.bind(this));
        this._canvas.addEvent(MOUSE_EVENTS.DOUBLECLICK, this.doDoubleClick.bind(this));  
    },
    
    initControls: function() {
        var elems = this._element.getElements('div'); 
        elems.each((function(item, i) {
            function doElemMouseOver(e) {
                item.setStyle('opacity', 0.5);
            }
        
            function doElemMouseOut(e) {
                item.setStyle('opacity', 0);
            }
        
            function doElemCursorDown(e) {
                if (this.visible()) {            
                    this.hide();
                        
                    var mode = i + 1;
                    if (mode < 4) {
                        this._canvas._frame.holeDown(this._hitHole, mode);
                        this._canvas._frame.CURMODE = mode;
                    } else if (mode == 4) this._app.fireEvent(PJEVENTS.SHOWCOLORPANEL, this._canvas);
                    this._elemSelect = true;
                    if (e.preventDefault) e.preventDefault();
                }                    
            }
        
            item.setStyle('opacity', 0);
            if (Utils.isTouchOnly()) {
                item.addEvent('touchstart', doElemCursorDown.bind(this));
            } else {
                item.addEvent(MOUSE_EVENTS.MOUSEOVER, doElemMouseOver.bind(this));
                item.addEvent(MOUSE_EVENTS.MOUSEOUT, doElemMouseOut.bind(this));
                item.addEvent(MOUSE_EVENTS.MOUSEDOWN, doElemCursorDown.bind(this));
            }
        }).bind(this));
    },
    
    doWindowResize: function(e) {
        this.hide();
    },
    
    doCursorUp: function(event) {
        this._elemSelect = false;   
    },
        
    checkAndShow: function () {
        if (!this.visible() && !this._elemSelect) {    
            this._hitHole = this._canvas.getFocusHole();
            if (this._hitHole > -1) {
                var p = this._canvas._input._cursorPos.clone();
                if (!this._waitTimer) this._waitTimer = (function(){
                    this._waitTimer = 0;
                    this.show(p);
                }).delay(GC_CONST.DBLCTIME, this);
            }
        } 
    },
            
    
    doCursorDown: function(event) {
        this.hide();      
    },
    
    doDoubleClick: function () {
        if (this._waitTimer) {
            clearTimeout(this._waitTimer);
            this._waitTimer = 0;
        } 
        this.hide();    
    },
    
    visible: function() {
        return this._element.getStyle('opacity') == 1;    
    },            
    
    show: function(cp) {
        var p = Utils.globalToLocal(cp, this._element.getParent());
        this._element.setStyles({
            'margin-left': p.x - this._center.x, 
            'margin-top' : p.y - this._center.y
        });
        this._element.fade('in');
        window.fireEvent(PJEVENTS.SHOWCTRLPANEL);  
    },
    
    hide: function() {
        if (this.visible()) this._element.fade('hide');
    }
});