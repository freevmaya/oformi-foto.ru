var tabletInput = new Class({
    Extends         : pcInput,
    initMCTRL: function() {
    
        this._MCTRLEvents = {
            touchmove : this.moveDrag.bind(this),
            touchend : this.endDrag.bind(this)
        }; 
    },
    
    listenEvents: function() {
/*    
        this._canvas.addEvents({
            touchstart: this._doCursorDown.bind(this),
            touchmove : this._doCursorMove.bind(this)
        });
*/        
        
        this._canvas.getParent().addEvents({
            touchstart: this._doCursorDown.bind(this),
            touchmove: this._doCursorMove.bind(this),
            touchend: this._doCursorUp.bind(this)
        });
    },
    
    getEventPos: function(e) {
        var page = new Vector();
        if (event.touches) {
            page.x = event.touches[0].clientX;
            page.y = event.touches[0].clientY;
        }
        return Utils.localToGlobal(page, e.currentTarget);
    },
    
    _doCursorDown: function(e) {
        this._cursorPos = this.getEventPos(e);
        this.parent(e);
        e.preventDefault();
    },
    
    _doCursorMove: function(e) {
        this.parent(e);
        e.preventDefault();
    }, 
    
    _doCursorUp: function(e) {
        this.parent(e);
        e.preventDefault();
    } 
});