var Rectangle = new Class({
    x: 0,
    y: 0,
    width: 0,
    height: 0,
    initialize: function(axObj, ay, awidth, aheight){
        if (instanceOf(axObj, Array)) {
            this.x = axObj[0];
            this.y = axObj[1];
            this.width = axObj[2];
            this.height = axObj[3];
        } else if (instanceOf(axObj, Object)) this.copy(axObj)
        else {
            this.x = axObj;
            this.y = (typeof ay == GC_CONST.UNDEFINED)?axObj:ay;
            this.width = awidth;
            this.height = aheight;
        }
        return this;
	},
    
    clone: function() {
        return new Vector(this);
    },
    
    size: function() {
        return new Vector(this.width, this.height);
    },
    
    copy: function (r) {
        this.x = r.x?r.x:r.left;
        this.y = r.y?r.y:r.top;
        this.width = r.width;
        this.height = r.height;
        return this;
    },

    resize: function(anchor, delta) {
        var l = anchor.sub(this.leftTop());
        this.x -= delta.x * l.x / this.width;
        this.y -= delta.y * l.y / this.height;
        this.width  += delta.x;
        this.height += delta.y;
        return this;
    },
    
    rescale: function(anchor, scale) {
        var ns = new Vector(this.size().multiply(scale));
        return this.resize(anchor, ns.sub(this.size()));
    },
    
    leftTop: function() {
        return new Vector(this.x, this.y);
    },
    
    rightBottom: function() {
        return new Vector(this.right(), this.bottom());
    },
    
    setLeftTop: function(lt) {
        this.x = lt.x;
        this.y = lt.y;
    },
    
    setRightBottom: function(rb) {
        this.width = rb.x - this.x;
        this.height = rb.y - this.y;
    },
    
    offset: function(v) {
        this.x += v.x;
        this.y += v.y;
    },
    
    center: function() {
        return new Vector(this.x + this.width / 2, this.y + this.height / 2);
    },
    
    right: function() {
        return this.x + this.width; 
    },
    
    bottom: function() {
        return this.y + this.height; 
    },
    
    getSize: function() {
        return new Vector(this.width, this.height);
    },
    
    enterHere: function(size, rescale, rescaleFromMax) {
        var scale = 1;
        if (rescale && ((size.x != this.width) || (size.y != this.height))) scale = Math[!rescaleFromMax?'min':'max'](this.width/size.x, this.height/size.y);
        
        size = size.multiply(scale);
        
        return new Rectangle(this.x + (this.width - size.x) / 2, this.y + (this.height - size.y) / 2, size.x, size.y);
    },
    
    containsPoint: function(p) {
        return ((p.x >= this.x) && (p.x <= this.x + this.width)) &&  ((p.y >= this.y) && (p.y <= this.y + this.height))
    }
});
