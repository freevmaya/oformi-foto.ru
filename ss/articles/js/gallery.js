var demoGallery = new Class({
    Implements  : [Events, Options],
    _layer      : null,
    _focus      : null,
    options: {
        focusMove: -10
    },
    
	initialize: function(options) {
		this.setOptions(options);
		this._layer = $(options.layer);
		if (this._layer) this._resetChilds();
	},
	
	_resetChilds: function() {
        var list = this._layer.getElements('.e_image');
        for (var i=0; i<list.length; i++) {
            list[i].addEvent('mouseover', this._doRollOver.bind(this));
            list[i].addEvent('mouseout', this._doRollOut.bind(this));
            list[i].set('px', parseInt(list[i].getStyle('margin-left')));
        }
    },
    
    _setFocus: function(current) {
        this._focus = current;
        current.tween('margin-left', (parseInt(current.get('px')) + this.options.focusMove) + 'px');
    },
    
    _unFocus: function() {
        if (this._focus) {
            this._focus.tween('margin-left', this._focus.get('px') + 'px');
            var list = this._layer.getElements('.e_image');
            for (var i=0; i<list.length; i++)
                list[i].setStyles({'z-index': i, 'opacity': 1});
        }
        this._focus = null;
    },
    
    _doRollOver: function(e) {
        var current = e.target;
        var list = this._layer.getElements('.e_image');
        for (var i=0; i<list.length; i++) {
            if (list[i] != current) list[i].setStyles({'z-index': i, 'opacity': 0.5});
            else list[i].setStyles({'z-index': list.length, 'opacity': 1});
        }
        this._setFocus(current);
    },

    _doRollOut: function(e) {
        this._unFocus();
    }
});

window.addEvent('domready', function() {
    var ib = $$('.imageBox');
    new demoGallery({
        layer: ib[0]
    });
    new demoGallery({
        layer: ib[1]
    });
    
    SqueezeBox.assign($$('a[rel=boxed]'));
});