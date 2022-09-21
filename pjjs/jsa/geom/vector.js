var Vector = new Class({
    x: 0,
    y: 0,
    initialize: function(axObj, ay){
        if (instanceOf(axObj, Array)) {
            this.x = axObj[0];
            this.y = axObj[1];
        } else if (instanceOf(axObj, Object)) this.copy(axObj)
        else {
            this.x = (typeof axObj != 'undefined')?axObj:0;
            this.y = (typeof ay == 'undefined')?this.x:ay;
        }
        return this;
	},
    
    clone: function() {
        return new Vector(this);
    },
    
    length: function() {
        return Math.sqrt(this.dot(this));
    },
    
    invert: function () {
        this.x = -this.x;
        this.y = -this.y;
        return this;
    },
    
    copy: function (v) {
        if (v) {
            this.x = v.x;
            this.y = v.y;
        }
        return this;
    },
    
    add: function (v) {
        var c = this.clone();
        if (v) {
            c.x += v.x;
            c.y += v.y;
        }
        return c;
    },
    
    sub: function (v) {
        var c = this.clone();
        if (v) {
            c.x -= v.x;
            c.y -= v.y;
        }
        return c;
    },
    
    multiply: function(v) {
        if (v instanceof Vector) return new Vector(this.x * v.x, this.y * v.y);
        else return new Vector(this.x * v, this.y * v);
    },
    
    divide: function(v) {
        if (v instanceof Vector) return new Vector(this.x / v.x, this.y / v.y);
        else return new Vector(this.x / v, this.y / v);
    },
    
    equals: function(v) {
        return this.x == v.x && this.y == v.y;
    },
    
    dot: function(v) {
        return this.x * v.x + this.y * v.y;
    },
    
    angle: function() {
       return Math.atan2(this.x, this.y);
    },
    
    max: function() {
        return Math.max(this.x, this.y);
    },
    
    min: function() {
        return Math.min(this.x, this.y);
    }
});


Vector.NULL = new Vector();