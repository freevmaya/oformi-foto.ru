var MODTHR = 3;

var HOLE_EVENTS = {
    HOLESELECT      : 'HOLESELECT',
    HOLEUNSELECT    : 'HOLEUNSELECT',
    IMAGECOMPLETE   : 'IMAGECOMPLETE'      
}

var HOLEEDITMODE = {
    DEFAULT: 0,
    MOVE: 1,
    ROTATE: 2,
    SCALE: 3
}

var gcHolesImage = new Class({
    Extends         : gcImage,
    _colors         : null,
    _holesURL       : '',
    _holes          : null,
    _holesInfo      : null,
    _selectHole     : -1,
    _imagesURL      : null,
    _icanvas        : null,
    _uic_wh         : -1,
    CURMODE         : HOLEEDITMODE.DEFAULT,
    initialize: function(options, events) {
        this.parent(options, events);
	},
/*    
    _setholesURL: function(holesURL) {
        this._holesURL = holesURL;
        var dataScript = Utils.addScript({
            src: holesURL + '/holes.js',
        });
        dataScript.onload = (function(e) {
            this.setHoles(holes);
        }).bind(this);        
    },
*/    
    _setimages: function(a_list) {
        this._imagesURL = a_list;
        if (this._holes) this._updateImageUrls();
    },
    
    _updateImageUrls: function() {
        this._imagesURL.each((function(url, index) {
            if ((index < this._holes.length) && url) this._setHoleImage(index, url);
        }).bind(this));         
    },
    
    _updateMasks: function() {
        this._holes.each((function(item, index) {
            this._setHoleMask(index, this._holesURL + item.file);
        }).bind(this));         
    },
    
    _setHoleImageData: function(holeIndex, image) {
        if (this._holes[holeIndex].image._imageLoaded) this._holes[holeIndex].image.unload();
        if (this._colors) image.setColors(colorTransform.toInt(this._colors));
        this._holes[holeIndex].image = image;
        this._completeLoadHole(holeIndex);
        this.fireEvent(HOLE_EVENTS.IMAGECOMPLETE, holeIndex);
    },
    
    _setHoleImage: function(holeIndex, imageURL) {
        var onComplete = (function() {
            this._setHoleImageData(holeIndex, image);
            image.removeEvent(GC_EVENTS.COMPLETE, onComplete);
        }).bind(this);
        var image = this.addChild(new gcMImage({
            src         : imageURL,
            visible     : false,
            alpha       : 0.4
        }, {
            COMPLETE: onComplete
        }));
    },
    
    _setHoleMask: function(holeIndex, maskURL) {
        var hole = this._holes[holeIndex]; 
        hole.mask = this.addChild(new gcImage({
            x       : hole.pos.x,
            y       : hole.pos.y,
            src     : maskURL,
            visible : false
        }, {
            COMPLETE: (function() {
                            this._completeLoadHole(holeIndex)
                    }).bind(this)
        }));
    },
    
    hitHole: function(p) {
        for (var i=0; i<this._holes.length; i++) {
            if (this.hitHoleTest(i, p)) return i;
        }        
        return -1;
    },
    
    hitHoleTest: function(index, p) {
        return this.holeRect(index).containsPoint(this.globalToLocal(p));
    },
    
    holeRect: function(holeIndex) {
        return this._holes[holeIndex].mask.getRect();        
    }, 
    
    getPosition: function() {
        if (this._selectHole > -1) {
            var m = this._holes[this._selectHole].mask;
            return m.localToGlobal(m.center());
        } else return this.localToGlobal(new Vector())        
    },
    
    currentHole: function() {
        return (this._selectHole > -1)?this._holes[this._selectHole]:null;
    },
    
    holeDown: function(holeIndex, mode) {
        var image = this._holes[holeIndex].image; 
        var mask = this._holes[holeIndex].mask;
        
        if (image._imageLoaded && mask._imageLoaded) {
            var input = this._canvas._input;
            mode = (mode==HOLEEDITMODE.DEFAULT)?this.CURMODE:mode;
            var doCursorUp = (function (e) {
                this._canvas.removeEvent(IACTION.CURSORUP, doCursorUp);
                this._canvas.removeEvent(GC_EVENTS.STARTMODIFY, doStartModify);
                this._uic_wh = 10000;
                image.setVisible(false);
                this.fireEvent(HOLE_EVENTS.HOLEUNSELECT, holeIndex);
                this.CURMODE = HOLEEDITMODE.DEFAULT;
            }).bind(this);
            
            var doStartModify = (function(e) {
                this._uic_wh = holeIndex;
                image.setVisible(true);
            }).bind(this);
            
            this._canvas.addEvent(IACTION.CURSORUP, doCursorUp);
            this._canvas.addEvent(GC_EVENTS.STARTMODIFY, doStartModify);
            
//            mask._updateGlobalMatrix();
            var mc = mask.center();
            image.setGlobalAnchor(mask.localToGlobal(mc));
            
            switch (mode) {
                case HOLEEDITMODE.ROTATE: input.beginRotate(image, MODTHR);
                    break; 
                case HOLEEDITMODE.SCALE: input.beginResize(image, MODTHR);
                    break;
                default: {
                    var lcpos = mask.globalToLocal(input.cursorPos());
                    if (lcpos.sub(mc).length()/(mc.length()) > 0.4)
                        input.beginMoveRotate(image, MODTHR);
                    else input.beginDrag(image, MODTHR);
                }
            }
        }
        if (holeIndex != this._selectHole) this.setSelectHole(holeIndex);
    },
    
    setSelectHole: function(holeIndex) {
        this._selectHole = holeIndex;
        this.fireEvent(HOLE_EVENTS.HOLESELECT, this._selectHole);
        return holeIndex;
    },
    
    _doLoad: function() {
        this.parent();
        this._updateICanvas();
    },
    
    _completeLoadHole: function(holeIndex) {
        var hole = this._holes[holeIndex];
        if (hole.image._image) {
            if (hole.image._imageLoaded &&
                hole.mask._imageLoaded) {
                hole.image.setRect(hole.mask.getRect().enterHere(hole.image.getSize(), true, true));
                this._updateICanvas();
                this._afterUpdate();
            }
        }
    },
    
    drawRect: function(context) {
        if (this._uic_wh > -1) this._updateICanvas(this._uic_wh);
        if (this._icanvas) context.drawImage(this._icanvas, 0, 0);
    },
    
    _updateICanvas: function(w_hole_i) {
        if (!this._icanvas && this._image.width && this._image.height)
            this._icanvas = this.createCanvas(this._image.width, this._image.height);
        if (this._icanvas) {
            var ctx = this._icanvas.getContext('2d');
            ctx.setTransform(1,0,0,1,0,0);
            ctx.drawImage(this._image, 0, 0);
            this.drawHoles(ctx, w_hole_i);
        }
        this._uic_wh = -1;
    },    
    
    _drawImage: function(context, image, matrix) {
        context.setTransform(matrix.a,matrix.b,matrix.c,matrix.d,matrix.tx,matrix.ty);
        image.drawRect(context);
    },
    
    drawHoles: function(context, w_hole_i) {
//        var matrix = this.getMatrix();
        if (this._holes)
            this._holes.each((function(item, index) {
                if (item.mask._imageLoaded && item.image._imageLoaded) {
                    var tCanvas = this.createCanvas(item.mask._width, item.mask._height);
                    var imat = new Matrix();//this._relativeMatrix.clone().invert();
                    var ctx = tCanvas.getContext("2d");
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, tCanvas.width, tCanvas.height);
                    if (w_hole_i != index)
                        this._drawImage(ctx, item.image, item.image.getMatrix().translate(-item.pos.x, -item.pos.y));
                    ctx.globalCompositeOperation = 'destination-out';
                    this._drawImage(ctx, item.mask, item.mask.getMatrix().translate(-item.pos.x, -item.pos.y));
                    imat = item.mask.getMatrix().clone(); 
                    context.setTransform(imat.a,imat.b,imat.c,imat.d,imat.tx,imat.ty);
                    context.drawImage(tCanvas, 0, 0);
                    tCanvas.destroy();
                }        
            }).bind(this));
    },
    
    _setholes: function(a_holesInfo) {
        this._holesInfo = a_holesInfo;
        this._holes = new Array();
        this._holesInfo.each((function(hole, i) {
            this._holes.push({
                pos         : new Vector(hole[0], hole[1]),
                file        : i + '.png',
                image       : {},
                mask        : {} 
            });
        }).bind(this));        
        if (this._imagesURL) this._updateImageUrls();
        this._updateMasks();
    }    
});

var gcMImage = new Class({
    Extends         : gcImage,
    initialize: function(options, events){
        this.parent(options, events);
	},
    
    setFilter: function(filterName, filter) {
        this.parent(filterName, filter);
        this._parent._uic_wh = 10000;
    },
    
    setColors: function(a_colors) {
        var ct = null;
        if (a_colors) {
            ct = this._filters.colorTransform || (new colorTransform({adjustment: 1}));
            ct.setOptions(Object.clone(a_colors));
        } 
        this.setFilter('colorTransform', ct);        
    }
});