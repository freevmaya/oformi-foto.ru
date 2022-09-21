var MD5 = (function () {

    var hex_chr = "0123456789abcdef";

    function rhex(num) {
        str = "";
        for (j = 0; j <= 3; j++) {
            str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
                    hex_chr.charAt((num >> (j * 8)) & 0x0F);
        }
        return str;
    }

    /*
     * Convert a string to a sequence of 16-word blocks, stored as an array.
     * Append padding bits and the length, as described in the MD5 standard.
     */
    function str2blks_MD5(str) {
        nblk = ((str.length + 8) >> 6) + 1;
        blks = new Array(nblk * 16);
        for (i = 0; i < nblk * 16; i++) {
            blks[i] = 0;
        }
        for (i = 0; i < str.length; i++) {
            blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
        }
        blks[i >> 2] |= 0x80 << ((i % 4) * 8);
        blks[nblk * 16 - 2] = str.length * 8;
        return blks;
    }

    /*
     * Add integers, wrapping at 2^32. This uses 16-bit operations internally
     * to work around bugs in some JS interpreters.
     */
    function add(x, y) {
        var lsw = (x & 0xFFFF) + (y & 0xFFFF);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
    }

    /*
     * Bitwise rotate a 32-bit number to the left
     */
    function rol(num, cnt) {
        return (num << cnt) | (num >>> (32 - cnt));
    }

    /*
     * These functions implement the basic operation for each round of the
     * algorithm.
     */
    function cmn(q, a, b, x, s, t) {
        return add(rol(add(add(a, q), add(x, t)), s), b);
    }

    function ff(a, b, c, d, x, s, t) {
        return cmn((b & c) | ((~b) & d), a, b, x, s, t);
    }

    function gg(a, b, c, d, x, s, t) {
        return cmn((b & d) | (c & (~d)), a, b, x, s, t);
    }

    function hh(a, b, c, d, x, s, t) {
        return cmn(b ^ c ^ d, a, b, x, s, t);
    }

    function ii(a, b, c, d, x, s, t) {
        return cmn(c ^ (b | (~d)), a, b, x, s, t);
    }

    /*
     * Take a string and return the hex representation of its MD5.
     */
    function calcMD5(str) {
        x = str2blks_MD5(str);
        a = 1732584193;
        b = -271733879;
        c = -1732584194;
        d = 271733878;

        for (i = 0; i < x.length; i += 16) {
            olda = a;
            oldb = b;
            oldc = c;
            oldd = d;

            a = ff(a, b, c, d, x[i + 0], 7, -680876936);
            d = ff(d, a, b, c, x[i + 1], 12, -389564586);
            c = ff(c, d, a, b, x[i + 2], 17, 606105819);
            b = ff(b, c, d, a, x[i + 3], 22, -1044525330);
            a = ff(a, b, c, d, x[i + 4], 7, -176418897);
            d = ff(d, a, b, c, x[i + 5], 12, 1200080426);
            c = ff(c, d, a, b, x[i + 6], 17, -1473231341);
            b = ff(b, c, d, a, x[i + 7], 22, -45705983);
            a = ff(a, b, c, d, x[i + 8], 7, 1770035416);
            d = ff(d, a, b, c, x[i + 9], 12, -1958414417);
            c = ff(c, d, a, b, x[i + 10], 17, -42063);
            b = ff(b, c, d, a, x[i + 11], 22, -1990404162);
            a = ff(a, b, c, d, x[i + 12], 7, 1804603682);
            d = ff(d, a, b, c, x[i + 13], 12, -40341101);
            c = ff(c, d, a, b, x[i + 14], 17, -1502002290);
            b = ff(b, c, d, a, x[i + 15], 22, 1236535329);

            a = gg(a, b, c, d, x[i + 1], 5, -165796510);
            d = gg(d, a, b, c, x[i + 6], 9, -1069501632);
            c = gg(c, d, a, b, x[i + 11], 14, 643717713);
            b = gg(b, c, d, a, x[i + 0], 20, -373897302);
            a = gg(a, b, c, d, x[i + 5], 5, -701558691);
            d = gg(d, a, b, c, x[i + 10], 9, 38016083);
            c = gg(c, d, a, b, x[i + 15], 14, -660478335);
            b = gg(b, c, d, a, x[i + 4], 20, -405537848);
            a = gg(a, b, c, d, x[i + 9], 5, 568446438);
            d = gg(d, a, b, c, x[i + 14], 9, -1019803690);
            c = gg(c, d, a, b, x[i + 3], 14, -187363961);
            b = gg(b, c, d, a, x[i + 8], 20, 1163531501);
            a = gg(a, b, c, d, x[i + 13], 5, -1444681467);
            d = gg(d, a, b, c, x[i + 2], 9, -51403784);
            c = gg(c, d, a, b, x[i + 7], 14, 1735328473);
            b = gg(b, c, d, a, x[i + 12], 20, -1926607734);

            a = hh(a, b, c, d, x[i + 5], 4, -378558);
            d = hh(d, a, b, c, x[i + 8], 11, -2022574463);
            c = hh(c, d, a, b, x[i + 11], 16, 1839030562);
            b = hh(b, c, d, a, x[i + 14], 23, -35309556);
            a = hh(a, b, c, d, x[i + 1], 4, -1530992060);
            d = hh(d, a, b, c, x[i + 4], 11, 1272893353);
            c = hh(c, d, a, b, x[i + 7], 16, -155497632);
            b = hh(b, c, d, a, x[i + 10], 23, -1094730640);
            a = hh(a, b, c, d, x[i + 13], 4, 681279174);
            d = hh(d, a, b, c, x[i + 0], 11, -358537222);
            c = hh(c, d, a, b, x[i + 3], 16, -722521979);
            b = hh(b, c, d, a, x[i + 6], 23, 76029189);
            a = hh(a, b, c, d, x[i + 9], 4, -640364487);
            d = hh(d, a, b, c, x[i + 12], 11, -421815835);
            c = hh(c, d, a, b, x[i + 15], 16, 530742520);
            b = hh(b, c, d, a, x[i + 2], 23, -995338651);

            a = ii(a, b, c, d, x[i + 0], 6, -198630844);
            d = ii(d, a, b, c, x[i + 7], 10, 1126891415);
            c = ii(c, d, a, b, x[i + 14], 15, -1416354905);
            b = ii(b, c, d, a, x[i + 5], 21, -57434055);
            a = ii(a, b, c, d, x[i + 12], 6, 1700485571);
            d = ii(d, a, b, c, x[i + 3], 10, -1894986606);
            c = ii(c, d, a, b, x[i + 10], 15, -1051523);
            b = ii(b, c, d, a, x[i + 1], 21, -2054922799);
            a = ii(a, b, c, d, x[i + 8], 6, 1873313359);
            d = ii(d, a, b, c, x[i + 15], 10, -30611744);
            c = ii(c, d, a, b, x[i + 6], 15, -1560198380);
            b = ii(b, c, d, a, x[i + 13], 21, 1309151649);
            a = ii(a, b, c, d, x[i + 4], 6, -145523070);
            d = ii(d, a, b, c, x[i + 11], 10, -1120210379);
            c = ii(c, d, a, b, x[i + 2], 15, 718787259);
            b = ii(b, c, d, a, x[i + 9], 21, -343485551);

            a = add(a, olda);
            b = add(b, oldb);
            c = add(c, oldc);
            d = add(d, oldd);
        }
        return rhex(a) + rhex(b) + rhex(c) + rhex(d);
    }

    return {
        calc:calcMD5
    };
}());


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

function clearCache(cacheKey, onComplete) {
    app.request('ajax,clearCache', {cacheKey: cacheKey}, function(a_data) {
        if (onComplete) onComplete(a_data);
        else {
            if (confirm(locale.RELOADCONFIRM)) location.href = location.href;
        }
    });
}

var DEFE = new Element('div');

function $_DE(selector) {
    return $(selector) || DEFE;
}

function $val(v1, v2, v3) {
    return v1?v1:(v2?v2:v3);
} 

function getTime() {
    return (new Date).getTime();
}

function scaleImage(image, scale) {
    scale = scale?scale:1;
    var canvas = new Element('canvas', {
        width: image.width * scale,
        height: image.height * scale
    });
    var context = canvas.getContext('2d');
    var mat = new Matrix();
    mat.scale(scale, scale);
    context.antialias = true;
    context.setTransform(mat.a, mat.b, mat.c, mat.d, mat.tx, mat.ty);
    context.drawImage(image, 0, 0);
    return canvas.toDataURL();      
}

function fitTo(image, WIDTH, HEIGHT) {
    scale = Math.max(WIDTH/image.width, HEIGHT/image.height);
    var canvas = new Element('canvas', {
        width: WIDTH,
        height: HEIGHT
    });
    var context = canvas.getContext('2d');
    var mat = new Matrix();
    mat.translate(-image.width / 2, -image.height / 2);
    mat.scale(scale, scale);
    mat.translate(WIDTH / 2, HEIGHT / 2);
    
    context.antialias = true;
    context.setTransform(mat.a, mat.b, mat.c, mat.d, mat.tx, mat.ty);
    context.drawImage(image, 0, 0);
    return canvas.toDataURL();      
}