var DsEditor = new Class({
    Implements: [Events],
    _layer: null,
    initialize: function(template, styles) {
        this._layer = template.clone();
        this._layer.setStyles(styles);
        
        this._layer.getElement('textarea').addEvent('blur', this._tablur.bind(this));        
        this._layer.getElement('textarea').addEvent('focus', this._tafocus.bind(this));
        this._layer.getElement('.close-button').addEvent('click', this._onclose.bind(this));        
        return this;
    },
    
    getText: function() {
        return this._layer.getElement('textarea').value.trim();
    },
    
    close: function() {
        this.removeEvents();
        this._layer.dispose();
    },
    
    form: function() {
        return this._layer.getElement('form');
    },
    
    _onclose: function(e) {
        this.fireEvent('close');
    },
    
    _tablur: function(e) {
        if (!this.getText()) this._layer.getElement('.input-demo').fade('in');
    },
    
    _tafocus: function(e) {
         this._layer.getElement('.input-demo').fade('out');
    },
    
    element: function() {
        return this._layer;
    },
    
    send: function() {
        if (!this.getText()) {
            Discus.alert(locale.WARNING, locale.EMPTYTEXT);
        } else {
            this._layer.getElement('.send-button').dispose();
            this.fireEvent('send', {
                form: this.form(), 
                after: (function() {
                    this._onclose();
                }).bind(this)
            });
        }    
    }
})