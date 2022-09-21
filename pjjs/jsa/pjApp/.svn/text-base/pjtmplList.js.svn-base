var pjList = new Class({
    Extends         : baseTmplList,
    _curMousePos    : new Vector(),
    _waitMove       : 0,
    _tweenRun       : false,
    _up             : null,
    _cats           : null,
    tmplURL         : '',
    _count          : 0,   
    _l_tmpl         : [],
    
    listenEvents: function() {
        this.parent();
//        this._window.addEvent(MOUSE_EVENTS.MOUSEOVER, this._doOver.bind(this));
        document.body.addEvent(MOUSE_EVENTS.MOUSEOUT, this._doOut.bind(this));
        this._window.getParent().addEvent(MOUSE_EVENTS.MOUSEMOVE, this._doMouseMove.bind(this));
        this._window.addEvent(EVENTS.SCROLL, this._doScroll.bind(this));
        
        this._window.setStyle('top', this.getPSize().y);
        
        this._up = $('up');
        this._cats = $('cats');
        this._up.addEvent(MOUSE_EVENTS.MOUSECLICK, this.show.bind(this));
        this._up.addEvent(MOUSE_EVENTS.MOUSEOVER, this.showCats.bind(this));
        this._up.addEvent(MOUSE_EVENTS.MOUSEOUT, this._doHideCats.bind(this));
        this._cats.addEvent(MOUSE_EVENTS.MOUSEOUT, this._doHideCats.bind(this));
        this._cats.addEvent('change', this._doCatSelect.bind(this));
        
        this.hideCats();
    },
    
    _doHideCats: function() {
        this.hideCats.delay(3000, this);
    },
    
    getPSize: function() {
        return this._window.getParent().getSize();
    },
    
    _doMouseMove: function(e) {
        this._curMousePos = new Vector(e.page.x, e.page.y);
        var p = this._window.getParent().getPosition();
/*        
        if ((e.page.y <= p.y + this._windowHeight() * 0.1) && this.isHidden()) this.show();
        else {
*/        
            this._waitMoveClear();
            this._waitMove = this._noMoveProc.delay(1000, this);
//        }         
    },   
/*    
    isHidden: function() {
        return this._window.getStyle('top').toInt() != 0;
    },
*/    
    
    createComponents: function(a_window) {
        this.parent(a_window);
        this._window.set('tween', {
            onComplete: (function() {this._tweenRun = false}).bind(this),
            onStart: (function() {this._tweenRun = true}).bind(this)
        });
    },    
    
    _doScroll: function(e) {
        this._waitMoveClear();
    },    
    
    _waitMoveClear: function() {
        if (this._waitMove) clearTimeout(this._waitMove);
    },
    
    _windowHeight: function() {
        var wsize = window.getSize();
        return wsize.y;
    },
    
    _noMoveProc: function(e) {
        this.hide();
    },
    
    doItemClick: function(tmpl) {
        this.parent(tmpl);
        this.hide();
    },
    
    _doOut: function(e) {
        if (e.target == this._window) {
            this.requestHide(this._curMousePos);
        }
    },
    
    requestHide: function() {
        (function() {
            if (!this._checkMouseIn()) this.hide();
        }).bind(this).delay(1000);    
    },
    
    show: function() {
        if (!this._tweenRun && this._list && (this._list.length > 0))
            this._window.tween('top', this._window.getStyle('top').toInt(), 0);
    },
    
    hide: function() {
        if (!this._tweenRun && this._list && (this._list.length > 0)) {
            this._window.tween('top', this._window.getStyle('top').toInt(), this.getPSize().y);
        }
    },
    
    showCats: function() {
        this._cats.tween('margin-top', this._cats.getStyle('margin-top').toInt(), 6);
    },
    
    
    hideCats: function() {
        if (!this._checkMouseIn(this._cats))
            this._cats.tween('margin-top', this._cats.getStyle('margin-top').toInt(), 35);
    },
    
    _checkMouseIn: function (object) {
        object = object?object:this._window;
        return ((new Rectangle()).copy(object.getCoordinates())).containsPoint(this._curMousePos);
    },
    
    _doCatSelect: function(e) {
        var cat = e.target.options[e.target.selectedIndex].value;
        if (cat) this.loadTemplates(cat, this.show.bind(this));
    },
    
    loadTemplates: function(group, proc) {
        var url = this.tmplURL.replace('%s', group);
    
        if (this._count == 0) this._l_tmpl = [];
        
        this._count++;
        (new Asset.javascript(url, {
            onload: (function(e) {
                this.doLoadTmpls(tmpls, proc);
            }).bind(this)
        }));
    },
        
    doLoadTmpls: function(a_tmpls, proc) {
        this._l_tmpl = this._l_tmpl.concat(a_tmpls);
        this._count--;
        if (this._count == 0) {
            this._l_tmpl= this._l_tmpl.concat(_storage.templates);
            this._l_tmpl.sort(function(a,b){return b-a;});
            this.assign(this._l_tmpl, true);
            proc();
        }
    }  
});
