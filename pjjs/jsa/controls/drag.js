var dragImplement = new Class({
    initialize: function(elems) {
        elems.each(function(item) {
            var _sp = null;
            item.addEvent(MOUSE_EVENTS.MOUSEDOWN, (function(e) {
                _sp = new Vector(e.page);                    
            }).bind(item));
            window.addEvent(MOUSE_EVENTS.MOUSEMOVE, (function(e) {
                if (_sp) {
                    var dragObj = item.getParent();
                    var curp = new Vector(e.page);
                    var rp = curp.sub(_sp);
                    var cp = (new Vector(dragObj.getPosition())).add(rp);
                    dragObj.setStyles({
                        left: cp.x,
                        top: cp.y
                    });
                    _sp = curp;
                }                    
            }).bind(item));
            window.addEvent(MOUSE_EVENTS.MOUSEUP, (function(e) {
                _sp = null;                   
            }).bind(item));
        });
    }
});