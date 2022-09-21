var gcVector = new Class({
     Extends    : gcLinkedObject,
     _length    : 0,
     _vector    : null,
     _lineWidth : 1,
     _arrow     : 15,
    
    initialize: function(options, events){
        this.parent(options);
        if (events) this.addEvents(events);
	},
    
    draw: function (context) {
        this.parent(context);
        this.drawVector(context);
    },
    
    applyStyle: function (context) {
        this.parent(context);
        context.lineWidth    = this._lineWidth;
    },
    
    radian: function() {
        return this._rotate / 180 * Math.PI;
    },
    
    getVector: function() {
        var matrix = new Matrix();
        matrix.rotate(this.radian());
        //matrix.scale(this._scale, this._scale);
        return matrix.transformPoint(0, this._length);
    },
    
    _setvector: function(vector) {
        this._vector = vector.clone();
        this._rotate = -Math.atan2(vector.x, vector.y) / Math.PI * 180;
        this._length = vector.length();
        this._afterUpdate();        
    },

    drawVector: function(context) {
        if (this._length > 0) {
            var aws = (this._arrow + this._lineWidth) / 3;
            var awh = this._arrow + this._lineWidth; 
            context.beginPath();
            context.moveTo(0, 0);
            context.lineTo(0, this._length - awh);
            context.stroke();
            if (this._arrow) {
                context.beginPath();
                context.moveTo(-aws, this._length - awh);
                context.lineTo(0, this._length);
                context.lineTo(aws, this._length - awh);
                context.fill();
            }
        }
    }    
})