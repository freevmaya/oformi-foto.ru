var pcInput = new Class({
    Extends         : baseInput,
    initialize: function(canvas) {
        this.parent(canvas);
    },
    
    listenEvents: function() {
        this._canvas.getParent().addEvents({
            'mousedown' : this._doCursorDown.bind(this),
            'mousemove' : this._doCursorMove.bind(this),
            'mouseup'   : this._doCursorUp.bind(this)
        });
    }
});