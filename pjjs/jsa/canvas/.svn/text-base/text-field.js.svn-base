function getFontHeight(font) {
    var parent = new Element("span");
    parent.set('text', 'H');
    parent.setStyles({
        font: font 
    });
    parent.inject(document.body);
    var height = parent.getSize().x;
    parent.destroy();
    return height;
}

var TEXTDEFAULT = 'Текстовое поле';
        
var gcTextField = new Class({
    Extends     : gcBaseRect,
    _text       : '',
    _font       : 'arial',
    _color      : 0,
    _size       : 12,
    _textSize   : null,
    _strokeStyle  : '#223333',
    _shadow       : null,  
    
    draw: function (context, matrix) {
        this.parent(context, matrix);
        this.setValue('text', TEXTDEFAULT);
        if (this._text) this.drawText(context, matrix);
    },
    
    getFont: function() {
        return 'normal ' + this._size + 'px ' + this._font;
    },
    
    applyStyle: function (context) {
        this.parent(context);
        
        context.fillStyle = "#" + parseInt(this._color).toString(16);
        context.strokeStyle = context.fillStyle;
        context.font = this.getFont();
        context.textAlign = 'left';
        
        if (this._shadow) {
            context.shadowColor = this._shadow.color; 
            context.shadowOffsetX = this._shadow.x; 
            context.shadowOffsetY = this._shadow.y; 
            context.shadowBlur = this._shadow.blur; 
        }
    },
    
    _defaultShadow: function() {
        return {
            color: '#000',
            x: 1,
            y: 1,
            blur: 2
        }
    },
    
    _setshadow: function(a_value) {
        if (a_value) this._shadow = this._defaultShadow();
        else this._shadow = null;
    },
    
    _updateTextSize: function(context) {
        this._textSize = new Vector(context.measureText(this._text).width, getFontHeight(this.getFont()))
    },
    
    afterUpdate: function() {
        this.parent();
        this._textSize = null;
    },
    
    getTextSize: function(context) {
        if (!this._textSize) this._updateTextSize(context);
        return this._textSize;
    },
                              
    drawText: function (context, matrix) {
        var tRect = (new Rectangle(0, 0, this._width, this._height)).enterHere(this.getTextSize(context));
        context.fillText(this._text, tRect.x, tRect.y + tRect.height);
    }
});