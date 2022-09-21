var baseFilter = new Class({
    options: null,
    _isChanges: false,
    
    initialize: function(a_options) {
        this.setOptions(Object.merge(this.defaultOptions(), a_options));
    },
    
    setOptions: function(a_options) {
        this.options = a_options;
        this._isChanges = true;
    },
    
    defaultOptions: function() {
        return {};
    },
    
    apply: function(imageData) {
        this._isChanges = false;
        return imageData;
    },
    
    isChanges: function() {
        return this._isChanges;
    }
});