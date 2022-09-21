var dragRectInitialize = function(item) {
    Object.merge(item, {
        initialize: function() {
            this.addEvent('mousedown', this.doMouseDown);
            this.addEvent('mouseup', this.doMouseUp);
            window.addEvent('mousemove', this.doMouseMove.bind(this));
            this.createControls();
            this.updateControls();
        },
        spos    : null,
        rect    : new Element('div', {
            'class': 'rect'
        }),
        
        click   : function() {
        },
        
        createControls: function() {
            this.rect.inject(this);
        },
        
        updateControls: function() {
            var size = this.getSize();
            this.rect.setStyle('height', size.y);
        },
        
        doResize: function() {
            this.updateControls();
        },
        
        doMouseDown: function (e) {
            this.spos = e.page;
        },
    
        doMouseUp: function () {
            this.click();
            this.spos = null;
        },
    
        doMouseMove: function (e) {
            if (this.spos) {
                var curPos = e.page;
                var p = this.getPosition();
                p.x += (curPos.x - this.spos.x);
                p.y += (curPos.y - this.spos.y);
                this.setPosition(p)
               // this.setStyles(cuPos);
                this.spos = curPos;
            }
        }
    })
    
    item.initialize();
};