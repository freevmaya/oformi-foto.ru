var baseTmplList = new Class({
    Extends         : Events,
    _window         : null,
    _current        : 0,
    _area           : null,
    _lastSourceURL  : '',
    _list           : [],
    _invisible      : [],
    
    initialize: function(a_window) {
        this.createComponents(a_window);
    },    
    
    createComponents: function(a_window) {
        this._window = a_window;
        this.listenEvents();
    },
    
    listenEvents: function() {
        this._window.addEvent('scroll', this.doChangeWindow.bind(this));
        window.addEvent('resize', this.doChangeWindow.bind(this));
    },
    
    createItem: function(itemData, index) {
        var item = new Element('div', {
            id      : 'i' + itemData,
            'class' : 'listItem',
            html: '<table><tbody><tr><td><img style="font-size:1px;"></td></tr></tbody></table>'
        });
        item.inject(this._area);
        return item;
    },
    
    setCurrent: function(a_current) {
        if (a_current != this._current) {
            var tc;
            if (tc = $('i' + this._current)) tc.removeClass('current');
            if (tc = $('i' + (this._current = a_current))) tc.addClass('current');            
        }
    },
    
    createArea: function() {
        this._area = new Element('div', {
            'class': 'listArea'
        });
        this._area.inject(this._window);
    },
    
    doItemClick: function(tmpl) {
        this.fireEvent(PJEVENTS.TMPLSELECT, tmpl.tmplId);
    },
    
    assign: function(list, clear) {
        if (clear) {
            if (this._area) this._area.destroy();
            this._area = null;
            this._list = [];
            this._invisible = []
        }
        if (!this._area) this.createArea();
        
        var _this = this;
        function itemClick(e) {
            _this.doItemClick(this);
        }

        var opera = Browser.name == 'opera';
        var startIndex = this._list.length - 1;         
        this._list = this._list.concat(list);
        list.each((function(itemData, i) {
            var item;
            var asInt = itemData.toInt();
            if (asInt) { 
                item = this.createItem(itemData, i);
                startIndex ++;
                Object.append(item, {
                    tmplId  : itemData,
                    loaded  : false,
                    index   : startIndex,
                    checkEndLoad: function() {
                        var vis = this.loaded;
                        if (!vis) { 
                            vis = this.checkVisible(); 
                            if (vis) {
                                var img = this.getElement('img');
                                img.addEvent('load', (function() {
                                    this.loaded = true;
                                    this.getElement('td').addClass('loaded');
                                    var next = this.getNext();
                                    if (next && !next.loaded) {
                                        (function() {
                                            if (next && next.checkEndLoad) next.checkEndLoad();
                                        }).delay(300, this);
                                    }
                                }).bind(this));
                                
                                img.addEvent('error', (function(event) {
                                    /*
                                    var next = this.getNext();
                                    this.dispose();
                                    if (next && next.getProperty('checkEndLoad')) next.checkEndLoad();
                                    */
                                    console.log('error load ' + img.src);
                                }).bind(this));
                                img.src = PJCONST.TMPLURLPATHPREVIEW + itemData + '.jpg';
                            }
                        }
                        return vis;
                    },
                    
                    getLayer: function() {
                        var parent = this.getParent();
                        if (parent) return parent.getParent();
                        else return null; 
                    },
                    
                    checkVisible: function () {
                        var base = this.getLayer();
                        if (base) { 
                            var rect = this.getCoordinates(base);
                            var psize = base.getSize();
                            return (rect.bottom >= 0) && (rect.top <= psize.y);
                        } return false;
                    },
                    
                    rollOver: function() {
                        if (!this.loaded) this.checkEndLoad();
                    }                
                });
                
                item.addEvent('click', itemClick.bind(item));
                item.addEvent('mouseover', item.rollOver.bind(item));
                if (!item.checkEndLoad()) this._invisible.push(item);
            } else {
                item = new Element('div', {
                    'class' : 'next',
                    html    : LOCALE.NEXTPAGE,
                    events  : {
                        'click': (function(e) {
                            item.destroy();
                            this.loadTemplates(this._lastSourceURL, itemData);                            
                        }).bind(this)
                    }                    
                }); 
                item.inject(this._area);
            }
        }).bind(this));
        this.fireEvent(PJEVENTS.COMPLETE);
    },
    
    loadTemplates: function(tmplListURL, params, clear) {
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
    },    
    
    checkVisibleItems: function () {
        var i = 0;
        while (i < this._invisible.length) {
            var item = this._invisible[i]; 
            if (item.checkEndLoad()) this._invisible.splice(i, 1);
            else i++;
        }
    },    
    
    doChangeWindow: function() {
        this.checkVisibleItems();
    }
});