var RB_SHOWMODE = 0;
var RB_INPUTMODE = 1;

var RateBar = new Class({
//    Extends: Events,
	Implements: [Events, Options],
    _container: null,
    _mode: RB_SHOWMODE,
    _bar: null,
    initialize: function(a_container, a_options) {
        this.setOptions(a_options);
        this._container = a_container;
        this._initControl(); 
    },
    
    getElement: function() {
        return this._bar;
    },
 
    _initControl: function() {
        this._bar = new Element('div', {'class': 'ds-rate', styles: {
            width: this.options.maxRate * 32
        }});
        this._bar.inject(this._container);
        this._bar.addEvent('mousemove', this._mouseMove.bind(this)); 
        this._bar.addEvent('mouseover', this._mouseOver.bind(this)); 
        this._bar.addEvent('click', this._click.bind(this));
        this._bar.addEvent('mouseout', this._mouseOut.bind(this));
        
        for (var i=0; i<this.options.maxRate; i++) {
            var star = new Element('div', {'class': 'ds-rate-star star-e'});
            star.inject(this._bar);
        }
        
        this._refreshFromRate(this.options.rate);
    },
    
    _refreshFromRate: function(a_rate) {
        var childs = this._bar.getChildren();
        a_rate = Math.round(a_rate);
        for (var i=0; i<childs.length; i++) {
            if (i < a_rate) { 
                childs[i].removeClass('star-e');
                childs[i].addClass('star-f');
            } else {
                childs[i].removeClass('star-f');
                childs[i].addClass('star-e');
            }
        }        
    },
    
    _setMode: function(a_mode) {
        this._mode = a_mode;
        if (a_mode != RB_INPUTMODE) this._refreshFromRate(this.options.rate);
    },
    
    _mouseMove: function(e) {
        if (this._mode == RB_INPUTMODE) this._refreshFromRate(this._calcRate(e.client.x));
    }, 
    
    _click: function(e) {
        if (!this.options.readonly) {
            var rate = this._calcRate(e.client.x);
            this.fireEvent('changeRate', rate);
            if (this.options.reset_value) this.setRate(rate);
            this._setMode(RB_SHOWMODE);
        }
    },  
    
    _calcRate: function(posX) {
        var c = this._bar.getCoordinates();
        return Math.ceil((posX - c.left) / c.width * this.options.maxRate);
    },
    
    _mouseOver: function(e) {
        if (!this.options.readonly) this._setMode(RB_INPUTMODE);
    },   
    
    _mouseOut: function(e) {
        if (!this.options.readonly) this._setMode(RB_SHOWMODE);
    },
    
    setReadOnly: function(a_value) {
        if (this.options.readonly = a_value) this._setMode(RB_SHOWMODE);
    },
    
    setRate: function(a_value) {
        this.options.rate = a_value;
        this._refreshFromRate(this.options.rate);  
    }     
});