var CPFIELDS = ['gray', 'bright', 'con', 'r', 'g', 'b'];
 
var colorPanel = new Class({
    Extends : basePanel,
    _color  : {bright: 0, con: 0, gray: 0, r:0, g:0, b:0},
    _listener: null,
    _slideOptions: null,
    _sliders: {},
    
    initialize: function(a_app, element, events, options) {
        this._slideOptions = options;
        this.parent(a_app, element);
    },
    
    createComponents: function() {
        this.parent();
        var _this = this;
        this._element.getElements('.slider').each(function(slider, i) {
            var field = CPFIELDS[i];
            _this._sliders[field] = new Slider(slider, slider.getElement('.knob'), Object.merge({
                range : [-127, 127],
                wheel : true,
                onChange: function(){
                    _this._color[field] = this.step;
                    _this.doChange();
                }
            }, _this._slideOptions));
        });
    },
    
    onClearButton: function() {
        this.clear();
    },
    
    clear: function() {
        for (var i in this._sliders) {this._sliders[i].set(0)}; 
        if (this._listener) this._listener.fireEvent(PJEVENTS.COLORBALANCE, null);    
    },                             
        
    updatePosition: function() {
        if (this._listener) {
            var size = this._element.getSize();
            var w = window.getSize();
            
            var p = this._app._canvas.getHolePosition();
            p.x -= size.x / 2;
            if (p.x + size.x > w.x) p.x = w.x - size.x;
            else if (p.x < 0) p.x = 0;
            
            if (p.y > w.y / 2) p.y = 0;
            else p.y = w.y - size.y;
            
            this._element.setStyles({
                left: p.x,
                top: p.y
            });
        };        
    },
    
    listenEvents: function() {
        this.parent();
        this._app.addEvent(PJEVENTS.SHOWCOLORPANEL, (function(a_listener) {
            this._listener = a_listener;
            this.updatePosition();        
            this.show();
        }).bind(this));
        
        this._element.getElement('.button').addEvent(MOUSE_EVENTS.MOUSECLICK, this.onClearButton.bind(this));
        this._element.getElement('.close').addEvent(MOUSE_EVENTS.MOUSECLICK, (function() {
            this.hide();
        }).bind(this));
        
        this._app._canvas.addEvent(HOLE_EVENTS.HOLESELECT, this.refreshFromSelect.bind(this));
        this._app.addEvent('CHANGEMODE', this.doHide.bind(this));
        this._app._canvas.addEvent(PJEVENTS.COMPLETE, this.onComplete.bind(this));
        window.addEvent('resize', (function() {
            this.updatePosition();
        }).bind(this));                                               
    },
    
    refreshFromSelect: function() {
        if (this._app._canvas._frame) {
            var hole = this._app._canvas._frame.currentHole();
            if (hole && hole.image._filters) {
                var ct = hole.image._filters.colorTransform;
                if (ct) {
                    this.reset(ct);
                    return;
                }
            } 
        }
        
        this.clear();
        this.hide();
    },
    
    reset: function(ct) {
        if (ct) {
            this._color = ct.options;
            for (var i in this._sliders) this._sliders[i].set(ct.options[i]);
        } else clear();         
    },
    
    onComplete: function() {
        this.refreshFromSelect();
    },
    
    doHide: function() {
        this.hide();
    },
    
    doChange: function() {
        this.fireEvent('change', this._color);
        if (this._listener) this._listener.fireEvent(PJEVENTS.COLORBALANCE, this._color);
    }
});