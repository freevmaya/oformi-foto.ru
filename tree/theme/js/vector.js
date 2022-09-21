function Vector(a_x, a_y) {
    this.initialize = function(axObj, ay){
        if (Array.isArray(axObj)) {
            this.x = axObj[0];
            this.y = axObj[1];
        } else if (typeof axObj == 'object') this.copy(axObj)
        else {
            this.x = (typeof axObj != 'undefined')?axObj:0;
            this.y = (typeof ay == 'undefined')?this.x:ay;
        }
        return this;
	};
    
    this.clone = function() {
        return new Vector(this);
    };
    
    this.length = function() {
        return Math.sqrt(this.dot(this));
    },
    
    this.invert = function () {
        this.x = -this.x;
        this.y = -this.y;
        return this;
    },
    
    this.copy = function (v) {
        this.x = v.x | v.left;
        this.y = v.y | v.top;
        return this;
    },
    
    this.add = function (v) {
        var c = this.clone();
        c.x += v.x;
        c.y += v.y;
        return c;
    },
    
    this.sub = function (v) {
        var c = this.clone();
        c.x -= v.x;
        c.y -= v.y;
        return c;
    },
    
    this.multiply = function(v) {
        if (v instanceof Vector) return new Vector(this.x * v.x, this.y * v.y);
        else return new Vector(this.x * v, this.y * v);
    },
    
    this.divide = function(v) {
        if (v instanceof Vector) return new Vector(this.x / v.x, this.y / v.y);
        else return new Vector(this.x / v, this.y / v);
    },
    
    this.equals = function(v) {
        return this.x == v.x && this.y == v.y;
    },
    
    this.dot = function(v) {
        return this.x * v.x + this.y * v.y;
    },
    
    this.angle = function() {
       return Math.atan2(this.x, this.y);
    },
    
    this.max = function() {
        return Math.max(this.x, this.y);
    },
    
    this.min = function() {
        return Math.min(this.x, this.y);
    }
    
    this.normalize = function() {
        var len = this.length();
        return new Vector(this.x/len, this.y/len);
    }
    
    this.initialize(a_x, a_y);
}

Vector.NULL = new Vector();