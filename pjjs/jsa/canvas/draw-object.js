var GC_PROPS = {
    SCALE   : 'scale',
    WIDTH   : 'width',
    HEIGHT  : 'height',
    LINK    : 'link',
    ROTATE  : 'rotate',
    ANCHOR  : 'anchor',
    X       : 'x',
    Y       : 'y'
}

var gcObject = new Class({
    _parent         : null,
    _objects        : null,
    
    initialize: function () {
        this._objects = new Array();           
    },
    
    _afterUpdate: function() {
    },
        
    addChild: function (child) {
        if (child._parent) child._parent.removeChild(child);
        this._objects.push(child);
        child._parent = this;
        child._canvas = this.getCanvas();
        this._afterUpdate();
        return child;
    },
    
    getCanvas: function() {
        if (this._canvas) return this._canvas;
        
        var ltop = this;
        while (ltop._parent) {
            ltop = ltop._parent
        }
        
        if (instanceOf(ltop, Canvas)) this._canvas = ltop;
        return this._canvas;
    },
    
    createCanvas: function(w, h) {
        return new Element('canvas', {width:  w, height:  h});        
    },
    
    removeChild: function (child) {
        var index = this._objects.indexOf(child);
        if (index > -1) {
            this._objects.splice(index, 1);
            child._parent = null;
            this._afterUpdate();
        }
        return index > -1;
    },
    
    swapChilds: function (child1, child2) {
        var index1 = this._objects.indexOf(child1);
        var index2 = this._objects.indexOf(child2);
        if ((index1 > -1) && (index2 > -1)) { 
            this.swap(index1, index2);
            return true; 
        }
        return false;
    },
    
    swap: function(index1, index2) {
        var tmp = this._objects[index1];
        this._objects[index1] = this._objects[index2];
        this._objects[index2] = tmp;
        this._objects[index1]._afterUpdate();
        this._objects[index2]._afterUpdate();
    },
    
    removeChilds: function() {
        while (this._objects.length > 0) this._objects[0].remove();
    },
    
    disposeChilds: function() {
        while (this._objects.length > 0) this._objects[0].dispose();
    },
    
    remove: function() {
        if (this._parent) this._parent.removeChild(this); 
    },
    
    dispose: function() {
        this.remove();
        this.disposeChilds();
    },
    
    getMatrix: function() {
        return new Matrix(1, 0, 0, 1, 0, 0);
    },
    
    hitTest: function(p) {
        for (var i = this._objects.length - 1; i>=0; i--) {
            var item = this._objects[i];
            if (item.canUse() && item._visible) {
                var result = item.hitTest(p);
                if (result) return result;
            }
        };
        return null;
    },
    
    toFront: function(object) {
        var index = this._objects.indexOf(object);
        if ((index > -1) && (index < this._objects.length - 1)) {
            var tmp = this._objects[index]; 
            this._objects.splice(index, 1);
            this._objects.push(tmp);
            tmp._afterUpdate();
        }
    },
    
    globalToLocal: function (p) {
        return p;
    },
    
    localToGlobal: function (p) {
        return p;
    },
    
    clone: function() {
        var clone = {};
        for (var key in this) {
            if (key == '_objects') {
                clone._objects = [];
                this._objects.each(function(item) {
                    var itemClone = item.clone();
                    itemClone._parent = clone;
                    clone._objects.push(itemClone);
                });
            } else clone[key] = (this[key] && this[key].hasOwnProperty('clone'))?this[key].clone():this[key];
        }
        return clone;
    }
});

var gcDrawBase = new Class({
    Extends         : gcObject
});

gcDrawBase.implement(Events.prototype);

var gcDrawObject = new Class({
    Extends         : gcDrawBase,
    _x              : 0,
    _y              : 0,
    _scale          : 1,
    _rotate         : 0,
    _fillStyle      : null,
    _strokeStyle    : null,
    _alpha          : 1,
    _anchor         : [0, 0],
    _relativeMatrix : null,      
    _globalMatrix   : null,    
    _visible        : true,
    _canvas         : null,
    _fillPatternStyle : '#000000',

	initialize: function(options, events){
        this.parent();
        this._relativeMatrix = new Matrix();
        this.setValues(options);
        if (events) this.addEvents(events);
	},
    
    setValues: function(values) {
        for (var i in values) this.setValue(i, values[i]);
    },
    
    setValue: function(varName, value) {
        var fname = '_set' + varName;
//        console.log(varName);        
        if (typeOf(this[fname]) == 'function') this[fname](value);
        else if (typeof this['_' + varName] != GC_CONST.UNDEFINED) {
            if (typeOf(value) == 'function') value.bind(this);
            else this['_' + varName] = value;
            this._afterUpdate();
        }
    },
    
    _afterUpdate: function() {
        this._relativeMatrix = this._calcRelativeMatrix();    
        if (this.getCanvas()) this.getCanvas().refreshRequire();
        this.fireEvent(GC_EVENTS.UPDATE, {
            type    :  GC_EVENTS.UPDATE,
            target  : this
        });     
    },
    
    getValue: function(varName) {
        return this['_' + varName];
    },      
    
    _refresh: function (context) {
        if (this._visible) {
            this._updateGlobalMatrix();                            
            var matrix = this.getGlobalMatrix();         
            context.setTransform(matrix.a,matrix.b,matrix.c,matrix.d,matrix.tx,matrix.ty);
            
            this.applyStyle(context);
            this.draw(context);
            this.refreshChilds(context);
        }
    }, 
    
    setVisible: function(visible) {
        this.setValue('visible', visible);
        return this;
    },
    
    refreshChilds: function(context) {
        this._objects.each(function(item) {
            item._refresh(context);
        });
    },
    
    _enterFrame: function () {
    },
    
    getAnchorVector: function() {
        return new Vector((typeOf(this._anchor) == 'string')?this[this._anchor]():this._anchor);
    },
    
    getAnchor: function(relative) {
        if (relative) {
            return relative.globalToLocal(this.localToGlobal(this.getAnchorVector()));
        } else return this.getAnchorVector();
    },
    
    getAnchorAngle: function(p) {
        return p.sub(this.getPosition().add(this.getAnchorVector())).angle();
    },    
    
    setGlobalAnchor: function(p) {
        var na = this.globalToLocal(p);
        //na = na.multiply(new Vector(this.scaleX(), this.scaleY()));
        p = this._parent.globalToLocal(p);
        this._x = p.x - na.x;
        this._y = p.y - na.y;
        this.setValue(GC_PROPS.ANCHOR, na);
    },       
    
    
    _calcRelativeMatrix: function() {
        var matrix  = new Matrix();
        var a       = this.getAnchorVector();
        var scale   = new Vector(this.scaleX(), this.scaleY());
        matrix.translate(-a.x, -a.y);
        matrix.scale(scale.x, scale.y);        
        matrix.rotate(this._rotate / 180 * Math.PI);     
        matrix.translate(this._x + a.x, this._y + a.y);
        return matrix;    
    },             
    
    _calcGlobalMatrix: function() {
        var matrix = this.getMatrix();
        var lparent = this._parent;
        while (lparent) {
            matrix = lparent.getMatrix().appendMatrix(matrix);
            lparent = lparent._parent;
        }
        return matrix;
    },
    
    _updateGlobalMatrix: function() {
        return this._globalMatrix = this._calcGlobalMatrix();    
    },        
        
    getGlobalMatrix: function() {
        return (this._globalMatrix || this._updateGlobalMatrix()).clone();
    },        
    
    getMatrix: function() {
        return this._relativeMatrix.clone();
    },
    
    setScale: function(value) {
        this.setValue('scale', value);
    },
    
    scaleX: function() {
        return this._scale;
    },
    
    scaleY: function() {
        return this._scale;
    }, 
    
    _setscale: function(value) {
        this._scale = value;
    },
    
    draw: function (context) {
    },
    
    applyStyle: function (context) {
        context.fillStyle   = this._fillStyle;
        context.strokeStyle = this._strokeStyle;
        context.globalAlpha = this._alpha;
    },
    
    beginDrag: function (canvas, startStep) {
        canvas.beginDrag(this, startStep);
    },
    
    setPosition: function(value) {
        this.setValues({
            x: value.x,
            y: value.y
        });
    },
    
    getPosition: function() {
        return new Vector(this._x, this._y);
    },
    
    setGlobalPos: function(value) {
        this.setPosition(this._parent.globalToLocal(value));
    },
    
    getGlobalPos: function() {
        return this._parent.localToGlobal(this.getPosition());
    },
    
    globalToLocal: function (p) {
        return this.getGlobalMatrix().invert().transformPoint(p.x, p.y);
    },
    
    localToGlobal: function (p) {
        return this.getGlobalMatrix().transformPoint(p.x, p.y);
    },
    
    createPattern: function(size) {
        var canvas = document.createElement("canvas");
        canvas.width = size.x;
        canvas.height = size.y;
        var ctx = patternDraw(canvas.getContext("2d"), size);
        ctx.fillStyle = this._fillPatternStyle;
        ctx.fill();
        return canvas;
    },
    
    patternDraw: function(ctx, size) {
        ctx.rect(0, 0, size.x / 2, size.y / 2);
    }
});

var gcLinkedObject = new Class({
    Extends         : gcDrawObject,
    _link           : null,
    _linkPos        : null,

    setValue: function(varName, value) {
       this.parent(varName, value);
       if (this._link && 
            ((varName == GC_PROPS.X) || (varName == GC_PROPS.Y))) this.refreshLinkPos();
    },
    
    _doLinkUpdate: function(e) {
        var p = this._link.localToGlobal(this._linkPos);
        this._x = p.x;
        this._y = p.y;
        this._relativeMatrix = this._calcRelativeMatrix();
    },
        
    refreshLinkPos: function() {
        this._linkPos = this._link.globalToLocal(this.getPosition());
    },
    
    unlink: function() {
        if (this._link) this._link.removeEvent(GC_EVENTS.UPDATE, this._doLinkUpdate);
        this._link = null;
    },
    
    link: function(a_link) {
        if ((a_link != this._link) && (a_link != this)) {
            if (this._link) this.unlink();
            this.setValue(GC_PROPS.LINK, a_link);
            if (this._link) {
                this.refreshLinkPos();
                this._link.addEvent(GC_EVENTS.UPDATE, this._doLinkUpdate.bind(this));
            }
        }
    },
    
    canUse: function() {
        return true;
    },
    
    dispose: function() {
        if (this._link) this.unlink();
        this.parent();
    }
});