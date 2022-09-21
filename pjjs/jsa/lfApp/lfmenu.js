var lfMenu = new Class({
    Extends         : Options,
    _layer          : null,
    _element        : null,
    _visible        : 0,
    initialize: function(a_element, a_options) {
        this._element = a_element;
        this.setOptions(a_options);
        var body = $$('body')[0];
        
        this._layer = (new Element('div', {'class':'mlayer'})).inject($$('body')[0]);
        this.options.list.each(this._createItem.bind(this));
        
        _app.addEvent('CHANGEMODE', this._onChangeMode.bind(this));
        body.addEvent('click', (function(e) {
            if (e.target != this._element) this._show(0);
            else this._show(this._visible?0:1);
        }).bind(this));
    },
    
    _show: function(visible) {
        this._layer.fade(visible);
        this._visible = visible;
    },
    
    _createItem: function(a_value) {
        (new Element('a', a_value)).inject(this._layer);
    },
    
    _onChangeMode: function(a_mode) {
        this.refreshFromMode(a_mode);
    },
    
    refreshFromMode: function(a_mode) {
        (function() {
            this._layer.getElements('a').each(function(item) {
                var value = item.get('value');
                if (value > -1) item.setStyle('display', (value == a_mode)?'block':'none');
            });
        }).delay(500, this);
    }
});