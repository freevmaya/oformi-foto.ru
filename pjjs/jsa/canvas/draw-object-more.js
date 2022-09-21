var gcBaseRect = new Class({
    Extends     : gcLinkedObject,
    _width      : 0,
    _height     : 0,
    draw: function (context) {
        this.parent(context);
        this.drawRect(context);
    },
    
    drawRect: function(context) {
    },
    
    setRect: function(rect) {
        this.setValues({
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height
        });
    },
    
    hitTest: function(p) {
        var result = this.parent(p);
        if (!result) {
            var p = this.globalToLocal(p);
            if ((p.x >=0) && (p.x < this._width / this.scaleX()) && (p.y >=0) && (p.y < this._height / this.scaleY())) result = this;
        }
        return result;
    },
        
    center: function () {
        return new Vector(this._width / 2, this._height / 2);
    },
    
    getRect: function () {
        return new Rectangle(this._x, this._y, this._width, this._height);
    },
    
    rectResetAnchor: function(rect) {
        if (typeOf(this._anchor) != 'string') {
            var r = this.getPosition().sub(rect.leftTop());
            this._anchor = this.getAnchorVector().add(r); 
        }
        this.setRect(rect);
    },
    
    getSize: function() {
        return new Vector(this._width, this._height);
    }
})

var gcRect = new Class({  
    Extends     : gcBaseRect,  
    drawRect: function(context) {
        context.strokeRect(0, 0, this._width, this._height);
        context.fillRect(0, 0, this._width, this._height);
    }
});

var gcCircle = new Class({
    Extends : gcLinkedObject,
    _radius  : 100,
    draw: function (context) {
        this.parent(context);
        context.beginPath();
        context.arc(0, 0, this._radius * 2, 0, Math.PI*2, false);
        context.fill();        
    },
    
    hitTest: function(p) {
        var result = this.parent(p);
        if (!result) {
            var p = this.globalToLocal(p);
            if (p.length() <= this._radius * 2) result = this;
        }
        return result;
    }
});

var IMAGECONST = {
    MAXWIDTH        : 1800,
    MAXHEIGHT       : 1800
}

var gcImage = new Class({
    Extends         : gcBaseRect,
    _icanvas        : null,
    _image          : null,
    _imageLoaded    : false,
    _filters        : {},     
    initialize: function(options, events){
        this.parent(options, events);
	},
    
    _doLoad: function() {
        var checkSize = (function () {
            if (((this._image.width > IMAGECONST.MAXWIDTH) || (this._image.height > IMAGECONST.MAXHEIGHT)) && (this._image.src.substr(0, 4) == 'data')) {
                var scale = Math.min(IMAGECONST.MAXWIDTH/this._image.width, IMAGECONST.MAXHEIGHT/this._image.height);
                this._image.set('src', (Utils.scaleImage(this._image, scale)));
                return false; 
            } 
            return true;
        }).bind(this);
        
        if (checkSize()) {
            this._updateSizeFromImage();
            this._afterUpdate();     
            this._imageLoaded = true;
            this.fireEvent(GC_EVENTS.COMPLETE, {
                type: GC_EVENTS.COMPLETE,
                target: this
            });
        }  
    },  
    
    _setimage: function(value) {
        this._image = value;
        this._doLoad();
    },
    
    _setscale: function(value) {
        this.parent(value);
        if (this._image) {
            var rect = new Rectangle(0, 0, this._image.width, this._image.height);
            this.setRect(rect.rescale(this.getAnchor(), value));
        }
    },
    
    setFilter: function(filterName, filter) {
        if (filter) this._filters[filterName] = filter;
        else delete(this._filters[filterName]);
        this._afterUpdate();
    },
    
    getImageSize: function() {
        return new Vector(this._image.width, this._image.height);
    },
    
    _setwidth: function(value) {
        this._width = value;
        this._scale = this.scaleX();
    },
    
    _updateSizeFromImage: function() {
       this._width = this._image.width * this._scale;    
       this._height = this._image.height * this._scale;    
    },
    
    unload: function() {
        this._image.src     = '';
        this._imageLoaded   = false;        
        this._afterUpdate();     
    },
    
    _setsrc: function(src) {
        if (this._image) this.unload();
        this._image = new Image();
        this._image.setAttribute('crossOrigin', 'anonymous');
        /*if (this._image.complete) this._doLoad();
        else*/ this._image.addEvent('load', this._doLoad.bind(this));
        
        this._image.src = src;
        this._image.setStyle('visibility', 'hidden');      
    },
    
    scaleX: function() {
        return this._image?(this._width / this._image.width):1;
    },
    
    scaleY: function() {
        return this._image?(this._height / this._image.height):1;
    },
    
    canUse: function() {
        return this.parent() && this._imageLoaded;
    },
    
    drawRect: function(context) {
/*    
        context.drawImage(c, 0, 0);
*/        
        
        if (Object.getLength(this._filters) == 0) context.drawImage(this._image, 0, 0);
        else {
            if (!this._icanvas)
                this._icanvas = this.createCanvas(this._image.width, this._image.height);
            
            var ich = false;
            Object.each(this._filters, (function(filter) {
                ich = ich || filter.isChanges();
            }));
            
            if (ich) {
                var ctx = this._icanvas.getContext('2d');
                ctx.drawImage(this._image, 0, 0);
                var data = ctx.getImageData(0, 0, this._icanvas.width, this._icanvas.height);
                
                Object.each(this._filters, (function(filter) {
                    if (filter.isChanges())
                        data = filter.apply(data);
                }));
                
                ctx.putImageData(data, 0, 0);
            }
            context.drawImage(this._icanvas, 0, 0);
        }
    },
    
    dispose: function() {
        this._image.src = '';
        this.parent();
    }
});
/*
var gcAnchor = new Class({
    Extends : gcCircle,
    draw: function (context) {
        this.parent(context);
        context.beginPath();
        context.arc(0, 0, this._radius * 2, 0, Math.PI*2, false);
        if (this._fillStyle) context.fill();        
        if (this._strokeStyle) context.stroke();        
    },
    
    refreshLinkPos: function() {
        this.parent();
        this._link.setGlobalAnchor(this._link.localToGlobal(this._linkPos));
    }
});*/