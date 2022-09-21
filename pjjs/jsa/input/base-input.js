var IACTION = {
    CURSORDOWN  : 'CURSORDOWN',
    CURSORMOVE  : 'CURSORMOVE',
    CURSORUP    : 'CURSORUP'
}

var baseInput = new Class({
    _canvas: null,
    _cursorPos: new Vector(0, 0),
    _cursorDown: null,
    _curPos: null,
    _startStep: 0,
    _drag: false,
    
    _rollAnchor: null,
    _moveAnchor: null,
    _spotAnchor: null,
    _toastcount: 0,

    help: function(aliase) {
        let now = Math.floor(Date.now() / 86400000);
        let last = parseInt(localStorage.getItem('bi-last'));
        this._toastcount++;

        if (!last || (last < now)) {
            if (this._toastcount > 4)
                localStorage.setItem('bi-last', now);
        } else return;

        $(window).fireEvent('TOAST', aliase);
    },
    
    initialize: function(canvas) {
        this._canvas = canvas;
        this.listenEvents();
        
        this._rollAnchor = this._canvas.addChild(new gcImage({src: 'images/ra.png', alpha: 0.4})).setVisible(false);
        this._moveAnchor = this._canvas.addChild(new gcImage({src: 'images/ma.png', alpha: 0.4})).setVisible(false);
    },
    
    cursorPos: function() {
        return Utils.globalToLocal(this._cursorPos, this._canvas)
    },
    
    getEventPos: function(e) {
        return Utils.localToGlobal(e.client, e.currentTarget);
    },
    
    _doSetSpot: function(asRoll, item) {
        if (!item) {
            if (this._spotAnchor) this._spotAnchor.setVisible(false);
            this._spotAnchor = null;
        } else {
            var sv = new Vector(this._canvas.getSize());
            this._spotAnchor = asRoll?this._rollAnchor:this._moveAnchor;
            this._spotAnchor.setVisible(true);
            this._canvas.toFront(this._spotAnchor);
            
            this._spotAnchor.setPosition(item.getAnchor(this._canvas).sub(this._spotAnchor.center()));
        } 
        return this._spotAnchor;
    },
    
    _doCursorDown: function(e) {
        this._cursorDown = this._cursorPos.clone();
        if (this._canvas._focus && this._canvas._focus._allowDragAndDrop) this._canvas.beginDrag(this._focus, 2);
        if (this._canvas._frame) {
            var index = this._canvas._frame.hitHole(this.cursorPos());
            if (index > -1)
                this._canvas._frame.holeDown(index, e.event.ctrlKey?HOLEEDITMODE.ROTATE:(e.event.shiftKey?HOLEEDITMODE.SCALE:HOLEEDITMODE.DEFAULT));
                
            this._canvas.fireEvent(IACTION.CURSORDOWN, this._cursorPos);
        }
    },
    
    _doCursorMove: function(e) {
        var gp = this.getEventPos(e); 
        this._cursorPos.copy(gp);
        this._canvas.hitObjectTest(gp);
        this._canvas.fireEvent(IACTION.CURSORMOVE, gp);
        
        if (this._curPos) this.moveDrag(e);
    }, 
    
    _doCursorUp: function(e) {
        if (this._canvas._waitDClick) this._canvas.doubleClick(e);
        else if (this._cursorPos.sub(this._cursorDown).length() < 2) this._canvas.click(e);
        
        this._canvas.fireEvent(IACTION.CURSORUP, this._cursorPos);
        if (this._curPos) {
            this.endDrag(e);
            this._curPos = null;
        }
    },
    
    endDrag: function (e) {
        if (this._drag) this._canvas.fireEvent(GC_EVENTS.STOPMODIFY);
        this._drag = false;
        this._doSetSpot(false, null);
    },
    
    _doStartModify: function() {
        this._drag = true;
        this._canvas.fireEvent(GC_EVENTS.STARTMODIFY);
    },
    
    moveDrag: function (e) {
        var v = this._curPos.sub(this._cursorPos);
        if (!this._drag) {
            if (v.length() >= this._startStep) {
                this._doStartModify();
                if (this._handleStart) this._handleStart(e, v);
            }
        } else {
            this._curPos.copy(this._cursorPos);
            if (this._handleDrag) this._handleDrag(e, v);
            this._canvas.fireEvent(GC_EVENTS.PROCESSMODIFY);
        }
        return false;
    },
    
    beginMoveControl: function (item, startStep, handleStart, handleDrag) {
        if (!this._curPos) {
            this._curPos = new Vector(this._cursorPos);
            this._drag = false;
            this._startStep = startStep;
            this._handleStart = handleStart;
            this._handleDrag = handleDrag;
        }
    },
    
    beginDrag: function (item, startStep) {
        this.beginMoveControl(item, startStep, (function(e, v) {
            this._doSetSpot(false, item);            
        }).bind(this), function(e, v) {
            item.setGlobalPos(item.getGlobalPos().sub(v));
        });
        this.help('MOVETOAST');
    },
    
    beginResize: function (item, startStep) {
        var ganchor = item.localToGlobal(item.getAnchorVector());
        var gll = (function () {
            return ganchor.sub(this.cursorPos()); 
        }).bind(this);
        
        var startDist = gll().length();
        this.beginMoveControl(item, startStep, (function(e, v) {
            this._doSetSpot(false, item);            
        }).bind(this), function(e, v) {
            var nd = gll().length();
            var scale = nd/startDist;
            var r = item.getRect();
            item.setValues({
                width: r.width * scale,
                height: r.height * scale 
            });
            startDist = nd;
        });
        this.help('ROTATETOAST');
    },
    
    beginRotate: function (item, startStep) {
        var glg = (function () {
            return item._parent.globalToLocal(this.cursorPos()); 
        }).bind(this);
        
        var startAngle = item.getAnchorAngle(glg()); 
        this.beginMoveControl(item, startStep, (function(e, v) {
            this._doSetSpot(true, item);            
        }).bind(this), function(e, v) {
            var curAngle = item.getAnchorAngle(glg()); 
            item.setValue(GC_PROPS.ROTATE, item._rotate + (startAngle - curAngle) / Math.PI * 180);                 
            startAngle = curAngle;
        });
        
        this.help('ROTATETOAST');
    },                              
    
    beginMoveRotate: function (item, startStep) {
        var ganchor = item.localToGlobal(item.getAnchorVector());
        
        var glg = (function () {
            return item._parent.globalToLocal(this.cursorPos()); 
        }).bind(this);
        
        var gll = (function () {
            return ganchor.sub(this.cursorPos()); 
        }).bind(this);
        
        var startAngle = item.getAnchorAngle(glg());
        var startDist = gll().length();
        this.beginMoveControl(item, startStep, (function(e, v) {
            this._doSetSpot(true, item);            
        }).bind(this), function(e, v) {
            var curAngle = item.getAnchorAngle(glg()); 
            item.setValue(GC_PROPS.ROTATE, item._rotate + (startAngle - curAngle) / Math.PI * 180);                 
            startAngle = curAngle;
            
            var nd = gll().length();
            var scale = nd/startDist;
            var r = item.getRect();
            item.setValues({
                width: r.width * scale,
                height: r.height * scale 
            });
            
           //item.setGlobalAnchor(ganchor);
            startDist = nd;
        });
        
        this.help('ROTATETOAST');
    }    
});