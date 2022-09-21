var lf_tmplList = new Class({
    Extends         : Events,
    _window         : null,
    _current        : 0,
    _area           : null,
    _lastSourceURL  : '',
    _list           : [],
    _nextParams     : '',
    _loadCount      : 0,
    
    initialize: function(a_window) {
        this.createComponents(a_window);
    },    
    
    createComponents: function(a_window) {
        this._window = a_window;
        this._window.addEvent('scroll', this.onScroll.bind(this));
    },
    
    createItem: function(a_tmplId, index) {
        var item = new Element('div', {
            tmplId  : a_tmplId,
            id      : 'i' + a_tmplId,
            'class' : 'listItem',
            html: '<img style="font-size:1px">'
        });
                
        var img = item.getElement('img');
        img.src = PJCONST.TMPLURLPATHPREVIEW + a_tmplId + '.jpg';
        
        this._loadCount++;
        if (img.complete) this.onItemLoaded.delay(20, this, item);
        else {
            img.addEvent('load', (function() {
                this.onItemLoaded(item);
            }).bind(this));
            
            img.addEvent('error', (function(event) {
                console.log('error load ' + img.src);
                item.destroy();
                this._loadCount--;
            }).bind(this));
        }
        
        item.addEvent('click', (function() {
             this.doItemClick(a_tmplId);
        }).bind(this)); 
               
        return item;
    },
    
    onItemLoaded: function(item) {
        item.loaded = true;
        item.addClass('loaded');
        this.toMinCol(item);
        this._loadCount--;
    },
    
    _checkEndPage: function() {
        var w_coord = this._window.getCoordinates();
        var offset = this._area.getCoordinates().bottom - w_coord.bottom;
        if (this._loadCount == 0) {
            if ((offset <= w_coord.height * 0.1) && (this._nextParams)) {
                this.loadTemplates(this._lastSourceURL, this._nextParams, false);
            }
        } else setTimeout(this._checkEndPage.bind(this), 500); 
    },
    
    onScroll: function() {
        this._checkEndPage();
    },
    
    setCurrent: function(a_current) {
        if (a_current != this._current) {
            var tc;
            if (tc = $('i' + this._current)) tc.removeClass('current');
            if (tc = $('i' + (this._current = a_current))) tc.addClass('current');            
        }
    },
    
    createArea: function() {
        this._area = new Element('div', {'class': 'listArea', html: '<table cellpadding="0"><tr></tr></table>'});
        this._area.inject(this._window);
        var cols = Math.floor(this._area.getSize().x / 120);
        var tr = this._area.getElement('tr');
        for (var i=0; i<cols; i++) 
            new Element('td', {'class': 'col'}).inject(tr);
        return cols;            
    },
    
    doItemClick: function(tmplId) {
        this.fireEvent(PJEVENTS.TMPLSELECT, tmplId);
    },
    
    toMinCol: function(item) {
        if (cols = this._area.getElement('tr').getChildren()) {
            var minEdge = 1000000;
            var minIndex = 0;
            cols.each(function(col, i) {
                edge = (last = col.getLast())?last.getPosition().y:0;
                if (edge < minEdge) {
                    minEdge = edge;
                    minIndex = i;
                }
            });
            item.inject(cols[minIndex], 'bottom');
        }
    },
    
    assign: function(list, clear) {
        if (clear) {
            if (this._area) this._area.destroy();
            this._area = null;
            this._list = [];
            this._loadCount = 0;
        }
        if (!this._area) this.createArea();
        
        var opera = Browser.name == 'opera';
        this._list = this._list.concat(list);
         
        list.each((function(itemData, i) {
            var item;
            var asInt = itemData.toInt();
            if (asInt) { 
                item = this.createItem(itemData, i);
                item.inject(this._area);
            } else this._nextParams = itemData;
            
        }).bind(this));
        this.fireEvent(PJEVENTS.COMPLETE);
        this._checkEndPage();
    },
    
    loadTemplates: function(tmplListURL, params, clear) {
        this._nextParams = '';
        (new Asset.javascript(tmplListURL + (params?('?' + params):''), {
            onload: (function(e) {
                this._lastSourceURL = tmplListURL;
                this.assign(result, clear);
            }).bind(this)
        }));
    },
    
    parseStorage: function(a_storage) {
        var result = new Array();
        a_storage.each(function(item) {
            if (typeOf(item.id) == 'array') {
                if (item.id.length > 2)
                    for (var i=0; i<item.id.length; i++)
                        result.push(parseInt(item.id[i]));
                else for (var i=item.id[0]; i<=item.id[1]; i++)
                        result.push(i);
            } else result.push(parseInt(item.id));
        });
        
        result.sort(function(a,b){return b-a;});
        return result;
    }
});