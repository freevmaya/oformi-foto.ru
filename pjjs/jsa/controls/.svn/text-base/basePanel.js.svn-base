var basePanel = new Class({
    Extends     : Events,
    _app        : null,    
    _element    : null,
    
    initialize: function(a_app, element, events) {
        this._element = element;
        this._app = a_app;
        this.addEvents(events);
        this.createComponents();
        this.listenEvents();    
    },
    
    createComponents: function() {
    },
    
    listenEvents: function() {
    },
    
    show: function() {
        this._element.fade(0.8);
    },
    
    hide: function() {
        this._element.fade(0);
    }
});