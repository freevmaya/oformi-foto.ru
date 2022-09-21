var lfMenu = new Class({
    Extends         : Options,
    _element         : null,
    initialize: function(a_element, a_options) {
        this._element = a_element;
        this.setOptions(a_options);
        this.options.list.each(this._createItem.bind(this));
        _app.addEvent('CHANGEMODE', this._onChangeMode.bind(this));
    },
    
    _createItem: function(a_value) {
        (new Element('option', a_value)).inject(this._element);
    },
    
    _onChangeMode: function(a_mode) {
        this.refreshFromMode(a_mode);
    },
    
    refreshFromMode: function(a_mode) {
        this._element.getElements('option').each(function(option) {
            var value = option.get('value');
            if (value > -1) option.setStyle('display', (value == a_mode)?'block':'none');
        });
    }
});