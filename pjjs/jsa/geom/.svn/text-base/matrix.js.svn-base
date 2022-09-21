var MATRIX2D = {
    DEG_TO_RAD: Math.PI/180
}

var Matrix = new Class({
    a: 1,
    b: 0,
    c: 0,
    d: 1,
    tx: 0,
    ty: 0,
    initialize: function(a, b, c, d, tx, ty){
        this.a = (a == null) ? 1 : a;
        this.b = b || 0;
        this.c = c || 0;
        this.d = (d == null) ? 1 : d;
        this.tx = tx || 0;
        this.ty = ty || 0;
        return this;
	},
    
    append: function(a, b, c, d, tx, ty) {
            var a1 = this.a;
            var b1 = this.b;
            var c1 = this.c;
            var d1 = this.d;

            this.a = a*a1+b*c1;
            this.b = a*b1+b*d1;
            this.c = c*a1+d*c1;
            this.d = c*b1+d*d1;
            this.tx = tx*a1+ty*c1+this.tx;
            this.ty = tx*b1+ty*d1+this.ty;
            return this;
    },
    
    appendMatrix: function(mat) {
            return (mat?this.append(mat.a, mat.b, mat.c, mat.d, mat.tx, mat.ty):this);
    },
    
    appendTransform: function(x, y, scaleX, scaleY, rotation, skewX, skewY, regX, regY) {
            if (rotation%360) {
                    var r = rotation*MATRIX2D.DEG_TO_RAD;
                    var cos = Math.cos(r);
                    var sin = Math.sin(r);
            } else {
                    cos = 1;
                    sin = 0;
            }

            if (skewX || skewY) {
                    // TODO: can this be combined into a single append?
                    skewX *= MATRIX2D.DEG_TO_RAD;
                    skewY *= MATRIX2D.DEG_TO_RAD;
                    this.append(Math.cos(skewY), Math.sin(skewY), -Math.sin(skewX), Math.cos(skewX), x, y);
                    this.append(cos*scaleX, sin*scaleX, -sin*scaleY, cos*scaleY, 0, 0);
            } else {
                    this.append(cos*scaleX, sin*scaleX, -sin*scaleY, cos*scaleY, x, y);
            }

            if (regX || regY) {
                    // prepend the registration offset:
                    this.tx -= regX*this.a+regY*this.c;
                    this.ty -= regX*this.b+regY*this.d;
            }
            return this;
    },
    
    rotate: function(angle) {
            var cos = Math.cos(angle);
            var sin = Math.sin(angle);

            var a1 = this.a;
            var c1 = this.c;
            var tx1 = this.tx;

            this.a = a1*cos-this.b*sin;
            this.b = a1*sin+this.b*cos;
            this.c = c1*cos-this.d*sin;
            this.d = c1*sin+this.d*cos;
            this.tx = tx1*cos-this.ty*sin;
            this.ty = tx1*sin+this.ty*cos;
            return this;
    },
    
    skew: function(skewX, skewY) {
        skewX = skewX*MATRIX2D.DEG_TO_RAD;
        skewY = skewY*MATRIX2D.DEG_TO_RAD;
        this.append(Math.cos(skewY), Math.sin(skewY), -Math.sin(skewX), Math.cos(skewX), 0, 0);
        return this;
    },

    scale: function(x, y) {
        this.a *= x;
        this.d *= y;
        this.c *= x;
        this.b *= y;
        this.tx *= x;
        this.ty *= y;
        return this;
    },
            
    translate: function(x, y) {
        this.tx += x;
        this.ty += y;
        return this;
    },
    
    invert: function() {
        var a1 = this.a;
        var b1 = this.b;
        var c1 = this.c;
        var d1 = this.d;
        var tx1 = this.tx;
        var n = a1*d1-b1*c1;

        this.a = d1/n;
        this.b = -b1/n;
        this.c = -c1/n;
        this.d = a1/n;
        this.tx = (c1*this.ty-d1*tx1)/n;
        this.ty = -(a1*this.ty-b1*tx1)/n;
        return this;
    },
    
    clone: function() {
        return new Matrix(this.a, this.b, this.c, this.d, this.tx, this.ty);
    },
    
    copy: function(matrix) {
        return this.initialize(matrix.a, matrix.b, matrix.c, matrix.d, matrix.tx, matrix.ty);
    },   
    
    transformPoint: function(x, y, pt) {
        pt = pt||(new Vector());
        pt.x = x*this.a+y*this.c+this.tx;
        pt.y = x*this.b+y*this.d+this.ty;
        return pt;
    },

    identity: function() {
        this.b = this.c = this.tx = this.ty = 0;
        return this;
    },
    
    isIdentity: function() {
        return this.tx == 0 && this.ty == 0 && this.a == 1 && this.b == 0 && this.c == 0 && this.d == 1;
    }
});  

Matrix.enterHere = function(inRect, size, rescale, rescaleFromMax) {
    var scale = 1;
    if (rescale && ((size.x != inRect.width) || (size.y != inRect.height))) scale = Math[!rescaleFromMax?'min':'max'](inRect.width/size.x, inRect.height/size.y);
    size = size.multiply(scale);
    return new Matrix(scale, 0, 0, scale, inRect.x + (inRect.width - size.x) / 2, inRect.y + (inRect.height - size.y) / 2, size.x, size.y);
} 
