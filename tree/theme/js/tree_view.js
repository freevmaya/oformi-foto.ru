var LINE_PERCENT_TREE = 0.8;
var MALE = 0;
var FEMALE = 1;
var FOCUSWAIT = 300;
var TRANSITONSPEED = 500;
var MODE_VIEW = 0;
var MODE_EDIT = 1;
var LEVELLIMIT = 4;
var MINVIEWRATIO = 0.6;
var MINVINSIZE = 120;
var VINDEC = 23;

var OPT_VINTITLE = 1;

function calcLevels(a_items, field) {
    var count = 1;
    for (var n=0; n<a_items.length; n++) {
        var item = a_items[n];
        if (item[field] != undefined) count = Math.max(1 + calcLevels(item[field], field), count);
    }
    return count;
}

function calcLevelsAll(tree) {
    return {
        childs: calcLevels(tree.childs, 'childs') - 1, 
        parents: calcLevels(tree.parents, 'parents') - 1
    };
}

function Events() {
    this.listeners = {};
    
    this.trigger = function(event) {
        var list = this.getLists(event.type);
        for (var i=0; i<list.length; i++) list[i](event);
    }
    
    this.on = function(event, onListener) {
        this.getLists(event).push(onListener);
    }
    
    this.off = function(event, onListener) {
        var lst = this.getLists(event);
        var idx = lst.indexOf(onListener);
        if (idx > -1) lst.splice(idx, 1);    
    }
    
    this.getLists = function(elenType) {
        if (!this.listeners[elenType]) this.listeners[elenType] = [];
        return this.listeners[elenType];
    }
}

var treeItem = {
    json: function() {
        return JSON.stringify(this);
    },
    text: function() {
        var fields = ['id', 'name', 'family', 'father', 'bday', 'gender', 'haveAvatar', 'img', 'link_uid'];
        var res = '';
        for (var i=0; i<fields.length; i++) {
            res += this[fields[i]] + ';';
        }
        return res + this.childIds;
    },
    displayName: function() {
        return this.family + ' ' + this.name + ' ' + this.father;
    },
    isParents: function() {
        return this.parentIds && (this.parentIds[0] || this.parentIds[1]);
    }
}

function Relatives(a_rel, a_options) {
    var This = this;
    var rel = extendItems(a_rel);
    var options = a_options;
    $.extend(This, new Events());
    
    function extendItems(a_rel) {
        $.each(a_rel, function(i, itm) {
            a_rel[i] = $.extend(itm, treeItem);
        });
        return a_rel;
    }
    
    this.assign = function(a_rel, a_options) {
        rel = extendItems(a_rel);
        options = a_options;
        this.trigger($.Event('ASSIGN'));
    }
    
    this.find = function (id) {
        if (id > 0)
            for (var i=0; i<rel.length; i++) if (rel[i].id == id) return rel[i];
        return null;
    }
    
    this.indexOf = function (id) {
        for (var i=0; i<rel.length; i++) if (rel[i].id == id) return i;
        return -1;
    }  
    
    this.isOther = function(itm, filedID) {
        if (item[filedID])
            for (var i=0; i<item[filedID].length; i++)
                if (item[filedID][i] > 0) return true;
        return false;         
    }
    
    this.older = function() {
        var maxLevel = -1; 
        var older = null; 
        
        function passItem(item, level) {
            var nop = true;
            if (item)
                for (var i=0; i<item.parentIds.length; i++) {
                    var pid = item.parentIds[i];
                    if (pid) {
                        passItem(This.find(pid), level + 1);
                        nop = false;
                    }   
                } 
            if (nop && (maxLevel < level)) older = item;
        } 
        
        if (rel.length > 0) passItem(rel[0], 0);
        
        return older;
    }  
    
    this.clearCycle = function() {
    
        function passItem(item, filedID, a_passed) {
            var nop = true;
            var pid;
            if (item)
                for (var i=0; i<item[filedID].length; i++) {
                    if (pid = item[filedID][i]) {
                        if (a_passed.indexOf(pid) > -1) { 
                            if (filedID == 'parentIds') item[filedID][i] = 0
                            else item[filedID].splice(i, 1);
                            console.log('clear ' + filedID + '=' + pid);
                        } else { 
                            a_passed.push(pid);
                            if (pid) passItem(This.find(pid), filedID, a_passed);
                        }
                    }
                } 
        } 
        
        if (rel.length > 0) {
            for (var i=0; i<rel.length; i++) {
                passItem(rel[i], 'parentIds', []);
                passItem(rel[i], 'childIds', []);
            }
        }
    }
    
    this.updateRelations = function(baseIndex, autorUID) {
        var pased = [];
        var vinRelType = (options & OPT_VINTITLE) == 0;
        
        function updateItems(item, relations) {                  
            if (item && ((pased.indexOf(item.id) == -1) || !$.ck(item.type))) {
                if (relations && relations.type) {
                    item.type = vinRelType?relations.type:item.bday;
                    pased.push(item.id);
                }
                if (relations) {
                    var ci=0;
                    while (ci<item.childIds.length) {
                        var cid = item.childIds[ci];
                        if (item.parentIds.indexOf(cid) > -1) item.parentIds[ci] = 0;
                        ci++;
                    }
                    
                    if (item.childIds)
                        for (var i=0; i<item.childIds.length; i++) {
                            updateItems(This.find(item.childIds[i]), relations.childs);
                        }
                    if (item.parentIds)
                        for (var i=0; i<item.parentIds.length; i++) {
                            updateItems(This.find(item.parentIds[i]), relations.parents);
                        }
                }
            }
        }
        if (rel.length > 0) {
                    
            this.clearCycle();
            updateItems(rel[baseIndex], locale.relations);
        }   
        this.trigger($.Event('RELATIVE'));
    }
    
    this.toTreeFromId = function(id) {
        return this.toTree(this.indexOf(id));
    }          
    
    this.toTree = function(baseIndex) {
        var maxLevel = 0;
        function calcDepth(item, fieldID, level) {
            if (!level) maxLevel = 0;
            maxLevel = Math.max(level, maxLevel);            
            if (item && item[fieldID]) {
                for (var i=0; i<item[fieldID].length; i++) {
                    calcDepth(This.find(item[fieldID][i]), fieldID, level + 1);
                }
            }
            return maxLevel;
        } 
        function getItems(parent, field, level, maxDepth) {
            var items = [];                
            //if (level < maxDepth) {
                var fieldID = field + 'Ids';
                var listID = field + 's';
                if (parent[fieldID])
                    for (var i=0; i<parent[fieldID].length; i++) {
                        var ppl = This.find(parent[fieldID][i]);
                        if (ppl) {
                            var item = $.extend({}, ppl);
                            if (level + 1 < maxDepth) {
                                item[listID] = getItems(item, field, level + 1, maxDepth);
                            } else {
                                item[listID] = [];
                                var l = (typeof(item[fieldID]) != 'undefined') && (item[fieldID].length > 0);
                                item[field + '_next'] = l;
                            }       
                            items.push(item);
                        }
                    }
            //}                 
            return items;
        }
        
        var depth = [calcDepth(rel[baseIndex], 'childIds', 0), calcDepth(rel[baseIndex], 'parentIds', 0)];
        
        var i = 1;
        while (depth[0] + depth[1] > LEVELLIMIT) {
            if (depth[i] > 1) depth[i]--;
            i = (i + 1) % 2;
        }
        
        var res=null;        
        if (rel && rel[baseIndex]) {
            res = $.extend({
                childs: getItems(rel[baseIndex], 'child', 0, depth[0]), 
                parents: getItems(rel[baseIndex], 'parent', 0, depth[1])
            }, rel[baseIndex]); 
        } 
        
        this.trigger($.Event('TOTREE'));
        return res;
    }
    
    this.getList = function() {
        return rel;
    }
    
    this.addItem = function(a_item) {
        rel.push(a_item);
        this.trigger(jQuery.Event('ADDITEM')); 
    }
    
    this.updateItem = function(a_item) {
        var item = this.find(a_item.id);
        if (item) {
            item.name     = a_item.name;
            item.family   = a_item.family;
            item.father   = a_item.father;
            item.bday     = a_item.bday;
            item.gender   = a_item.gender;
            item.img      = a_item.img;
            this.trigger(jQuery.Event('UPDATEITEM', {item: item}));
        }
    }
    
    this.removeLink = function(id) {
        this.removeParentLink(id);
        this.removeChildLink(id);
    }
    
    this.removeParentLink = function(id) {
        var item = this.find(id);
        for (var i=0;i<item.parentIds.length;i++) {
            var n_pid = item.parentIds[i];
            if (n_pid) {
                var p = this.find(n_pid);
                for (var n=0;n<p.childIds.length;n++)
                    if (p.childIds[n] == item.id) p.childIds.splice(n, 1);
            }
        }
        item.parentIds = [0, 0];
    }
    
    this.removeChildLink = function(id) {
        var item = this.find(id);
        for (var i=0;i<item.childIds.length;i++) {
            var c = this.find(item.childIds[i]);
            for (var n=0;n<c.parentIds.length;n++)
                if (c.parentIds[n] == item.id) c.parentIds[n] = 0;
        }
        item.childIds = [];      
    } 
    
    this.findBottom = function(startItem, id) {
        function findStep(item, level) {
            if (item.id == id) return level;
            var pl = item.parentIds; 
            for (var i=0; i<pl.length; i++) {
                if (pl[i] != 0) {
                    var lv = findStep(This.find(pl[i]), level + 1);
                    if (lv > -1) return lv;  
                }
            }
            return -1;
        } 
        return findStep(startItem, 0);
    } 
    
    this.findTop = function(startItem, id) {
        function findStep(item, level) {
            if (item.id == id) return level;
            var cl = item.childIds; 
            for (var i=0; i<cl.length; i++) {
                let aitem = This.find(cl[i]);
                if (aitem) {
                    var lv = findStep(aitem, level + 1);
                    if (lv > -1) return lv;
                }
            }
            return -1;
        } 
        return findStep(startItem, 0);
    }   
    
    this.link_uids = function() {
        var uids = [];
        $.each(rel, function(i, item) {
            if (item.link_uid) uids.push(item.link_uid);        
        });
        return uids;
    }
}

function treeView(treeApp, elem, content) {
    var This = this;
    var selfView;
    var listeners = {};
    var levelCount;
    var relCanvas;
    var tree;
    var smoothly = false;
    var mode = MODE_VIEW;
    
    this.getElement = function() {
        return elem;
    }
    
    function calcItem(item, field) {
        var count = 0;             
        if (item.depth == undefined) item.depth = {}; 
        if (item[field] != undefined) count = item.depth[field] = 1 + calcItems(item[field], field);
        else item.depth[field] = 0;
        return count;
    }
    
    function calcItems(a_items, field) {
        var count = 0;
        for (var n=0; n<a_items.length; n++)
            count += calcItem(a_items[n], field);
        return count;
    }
    
    function appendUserView(item) {
        if (!item.view) {
            var uv = new userView(This, item);
            var ve = uv.getElement();
            elem.append(ve);
            uv.onClick(doElementClick);
            item.view = uv;
            if (smoothly) ve.css('transition', 'all ' + (TRANSITONSPEED / 1000) + 's ease-in-out');
            setTimeout(function() {
                uv.show();
            }, 50);
        } else uv = item.view;
        return uv;
    }
    
    function append(a_items, field) {
        for (n in a_items) {
            var item = a_items[n];
            appendUserView(item);
            if (item[field]) append(item[field], field);
        }
    }
    
    function findView(s_tree, id) {
        if (s_tree.id == id) return s_tree.view;
            
        var view;
        if (s_tree.childs)
            for (var i=0; i<s_tree.childs.length; i++) 
                if (view = findView(s_tree.childs[i], id)) return view;
        if (s_tree.parents)
            for (var i=0; i<s_tree.parents.length; i++) 
                if (view = findView(s_tree.parents[i], id)) return view;
            
        return null;
    }
    
    function treeToList(a_tree) {
        var list = [];
        if ($.type(a_tree) == 'array') {
            for (var i=0; i<a_tree.length; i++) list = list.concat(treeToList(a_tree[i]));
        } else {
            list.push(a_tree);
            if (a_tree.childs) list = list.concat(treeToList(a_tree.childs));
            if (a_tree.parents) list = list.concat(treeToList(a_tree.parents));
        }
        
        return list;
    }
    
    this.removeItem = function(elem) {
        elem.css({opacity: 0, 'transform': 'scale(0.01, 0.01)'});
        setTimeout(function() {elem.remove();}, Math.round(TRANSITONSPEED * 1.2));
    }
     
    this.assign = function(a_tree) {
        
        if (tree) {
            //var clearList = treeToList(tree);
            var clearList = elem.children('.persona');
            var lcl = {}; nch = [];
            clearList.each(function(i, itm) {
                var id = parseInt($(itm).attr('data-id'));
                if (!lcl[id]) lcl[id] = 1;
                else nch.push(id);
            });  
            
            function safeViews(sv_tree) {
                if (sv_tree) {
                    var i=0;
                    while (i<clearList.length) {
                        if ((nch.indexOf(sv_tree.id) == -1) && ($(clearList[i]).attr('data-id') == sv_tree.id)) {
                            if (sv_tree.view = findView(tree, sv_tree.id))
                                sv_tree.view.setData(sv_tree);
                                
                            clearList.splice(i, 1);
                        } else i++;
                    }
                    
                    if (sv_tree.childs)
                        for (i=0; i<sv_tree.childs.length; i++) safeViews(sv_tree.childs[i]);
                    if (sv_tree.parents)
                        for (i=0; i<sv_tree.parents.length; i++) safeViews(sv_tree.parents[i]);
                }        
            }        
            
            safeViews(a_tree);
            for (var i=0; i<clearList.length; i++) this.removeItem($(clearList[i]));
            clearList.splice(0);
        }
        
        if (tree = a_tree) {
            levelCount = calcLevelsAll(tree);
            calcItem(tree, 'childs');
            calcItem(tree, 'parents');
            
    //        this.clear();
            append(tree.childs, 'childs'); 
            append(tree.parents, 'parents');
            selfView = appendUserView(tree);
            this.updateLevels();
        } else if (relCanvas) relCanvas.remove(); 
        
        this.trigger($.Event('ASSIGN'));
    }
    
    this.getTree = function() {
        return tree;
    }
    
    this.clear = function() {
        elem.empty();
        //listeners = null;
        relCanvas.remove();
    }
    
    this.dispose = function() {
        this.clear();
    }   
    
    this.setMode = function(a_mode) {
        if (mode != a_mode) {
            if ((a_mode == MODE_EDIT) && (!treeApp.isEditPermission())) return;
            mode = a_mode;
            content.css('margin-right', (mode == MODE_EDIT)?190:0);
            if (levelCount)
                setTimeout(function() {
                    resetSize();
                    This.updateLevels();
                }, ACTION_SPEED);
            This.trigger(jQuery.Event('MODE'));
        }
    }

    this.toggleMode = function(a_mode) {
        this.setMode((mode == MODE_EDIT)?MODE_VIEW:MODE_EDIT);
    }
    
    this.getMode = function() {
        return mode;
    }
    
    var hs = [];
    
    function calcWidth(a_items, w, field) {
        var ws = [];
        var sum = 0;
        for (var i=0; i<a_items.length; i++) sum += a_items[i].depth[field] + 1;
        for (var i=0; i<a_items.length; i++) ws[i] = ((a_items[i].depth[field] + 1)/ sum) * w;
        return ws;
    }
    
    function calcTop(level) {
        var fcount = levelCount.childs + levelCount.parents + 1; 
        return ((level / fcount * LINE_PERCENT_TREE + Math.sqrt(level / fcount) * (2 - LINE_PERCENT_TREE * 2) / 2)) * elem.height();
    }
    
    function calcLayer(level) {
        var top = calcTop(level);
        var h = calcTop(level + 1) - top;
        return {c: top + h/2, h: h};        
    }

    function updateLevel(a_items, field, level, x, y, width, eh, toUp) {
//        var k = 1 / levelCount;
        var l = calcLayer(level);
        var ws = calcWidth(a_items, width, field);
        
        for (var i=0; i<a_items.length; i++) {
            var item = a_items[i];
            var em  = item.view.getElement();
            var v = item.view; 
            var scale = v.adaptiveSize(ws[i], l.h);
//            console.log(item.name + ' ' + scale);
            
            item.view.setPosition(new Vector(Math.round(x + ws[i] / 2), Math.round(y + (toUp?Math.round(eh - l.c):Math.round(l.c)))));
            if (item[field]) updateLevel(item[field], field, level + 1, x, y, ws[i], eh, toUp);
            x += ws[i];
        }
    } 
    
    this.updateLevels = function() {
        if (levelCount) {
            var pc = levelCount.childs + levelCount.parents;
            var sh = calcLayer(0);
            var ch = elem.height() - sh.h;
            var width = elem.width();     
            var h = {
                childs: levelCount.childs?(ch * levelCount.childs/pc):0, 
                parents: levelCount.parents?(ch * levelCount.parents/pc):0, 
                self: sh.h 
            }
            
            if (tree) {
                updateLevel(tree.childs, 'childs', levelCount.parents + 1, 1, 0, width, elem.height(), true);      
                updateLevel(tree.parents, 'parents', 1, 1, h.childs, width, elem.height(), false);
            }
            selfView.setPosition(new Vector(width/2, h.childs + sh.c));
            selfView.adaptiveSize(width, sh.h);
            
            if (relCanvas) relCanvas.remove(); 
            
            createRelCanvas();            
            updateRelCanvas();
                    
            doUpdate();
        }   
    }
    
    this.trigger = function(e) {
        var list = getLists((typeof(e)=='string')?e:e.type);
        for (var i=0; i<list.length; i++) list[i](e);
    }
    
    this.on = function(etype, onListener) {
        getLists(etype).push(onListener);
    }
    
    this.getSmoothly = function() {
        return smoothly;
    }
    
    this.setSmoothly = function(a_set) {
        if (a_set != smoothly) {
            smoothly = a_set;
            if (relCanvas) relCanvas.css('transition', smoothly?'all 0.2s ease-in-out':'initial');
            content.css('transition', smoothly?('all ' + (ACTION_SPEED/1000) + 's ease-in-out'):'initial');
            elem.children('.persona').each(function(i, ve) {
                $(ve).css('transition', smoothly?('all ' + (TRANSITONSPEED / 1000) + 's ease-in-out'):'initial');
            });                                                                                    
        }
    }
    
    function createRelCanvas() {
        var size = Utils.viewSize(elem);
        relCanvas = $('<canvas width="' + size.x + 'px", height="' + size.y + 'px"><canvas>');
        relCanvas.css('opacity', 0);
        if (smoothly) relCanvas.css('transition', 'all 0.2s ease-in-out');
        elem.prepend(relCanvas);
    }
    
    function drawRelCanvas(style) {
        function drawLine(s_item, e_item) {
            var sp  = s_item.view.getPosition();
            var ep  = e_item.view.getPosition();
            var ch  = sp.y + (ep.y - sp.y) / 2
            var rx  = sp.x - ep.x;
//          var rs  = elem.width() * 0.02;
            var rx2 = Math.abs(rx) / 2;
            var rs  = Math.min(rx2, elem.width() * 0.04);
            var ry  = sp.y - ep.y;
            var rsh = (ry > 1)?rs:-rs;
            var rsw = (rx > 1)?rs:-rs;
            
            if (rx2 <= rs) {
                relCanvas.drawLine($.extend({
                    x1: sp.x, y1: sp.y,
                    x2: ep.x, y2: ep.y
                }, style));
            } else { 
                relCanvas.drawPath($.extend({
                    p1: {
                        type: 'line',
                        x1: sp.x, y1: sp.y,
                        x2: sp.x, y2: ch + rsh
                    },
                    p2: {
                        type: 'quadratic',
                        x1: sp.x, y1: ch + rsh,
                        cx1: sp.x, cy1: ch,
                        x2: sp.x - rsw, y2: ch
                    },
                    p3: {
                        type: 'line',
                        x1: sp.x - rsw, y1: ch,
                        x2: ep.x + rsw, y2: ch
                    },
                    p4: {
                        type: 'quadratic',
                        cx1: ep.x, cy1: ch,
                        x2: ep.x, y2: ch - rsh
                    },
                    p5: {
                        type: 'line',
                        x1: ep.x, y1: ch - rsh,
                        x2: ep.x, y2: ep.y
                    }
                }, style));
            }  
        }
        
        function drawToList(item, field) {
            if (item[field] != undefined) {
                for (var i=0; i<item[field].length; i++) {
                    drawLine(item, item[field][i]);
                    drawToList(item[field][i], field);
                }
            }
        }
        
        if (tree) {
            drawToList(tree, 'childs');
            drawToList(tree, 'parents');
        }
    }
    
    function updateRelCanvas() {

        var lw = Math.max(1, Math.min(4, Utils.viewSize(elem).length() * 0.003));
        
        var color = elem.css('color');
        var size = lw
        
        drawRelCanvas({
            strokeStyle: '#000',
            strokeWidth: size * 1.2
        });
        
        drawRelCanvas({
            strokeStyle: color,
            strokeWidth: size
        });
        
        if (smoothly)
            setTimeout(function() {
                relCanvas.css('opacity', 1);
            }, TRANSITONSPEED);
        else relCanvas.css('opacity', 1);
    }
    
    function getLists(elenType) {
        if (!listeners[elenType]) listeners[elenType] = [];
        return listeners[elenType];
    }
    
    function doElementClick(tview) {
        var list = getLists('ELEMCLICK');
        for (var i=0; i<list.length; i++) list[i](tview);
    } 
    
    function doUpdate() {
        var list = getLists('UPDATE');
        for (var i=0; i<list.length; i++) list[i]();
    }
    
    this.onSelect = function(listener) {
        getLists('ELEMCLICK').push(listener);
    }
    
    this.onUpdate = function(listener) {
        getLists('UPDATE').push(listener);
    }
    
    this.getApp = function() {
        return treeApp;
    }
    
    function resetSize() {
        var ws = Utils.viewSize(elem.parent());
        var hw = ws.y / ws.x;
        if (hw < MINVIEWRATIO) {
            ws.x = ws.y / MINVIEWRATIO;
            elem.css('width', Math.round(ws.x));
        } else elem.css('width', '100%');
//        console.log(hw);
    }
    
    function wResize() {
        resetSize();
        This.updateLevels();    
    }
    
    $(window).resize(wResize);
    resetSize();
    
    if (!_userView.isInitialize()) _userView.Initialize(this);
    this.setSmoothly(true);
}

_userView = {
    _focus: null,
    _current: null,
    _dpanel: null,
    _fpanel: null,
    _treeView: null,
    _isDrag: false,
    _app: null,
    panelFocus: false,
    dropitem: function(btn, item) {
        if (_userView._current) {
            this._app.trigger(jQuery.Event('item_command', {
                command  : btn.attr('data-button'),
                source   : 'tree',
                target   : _userView._current,
                item     : item                                                
            }));
        }
    },
    trigger: function(e) {
        this._app.trigger($.Event(e.type, this._current));
    }, 
    isInitialize: function() {
        return this._dpanel != null;
    },
    Initialize: function(treeView) {
        function unfocusPanel() {
            _userView.panelFocus = false;
            _userView.requireHidepanel();
        }
        var dp = this._dpanel = $('#templates').children('.drop_panel').clone();
        var fp = this._fpanel = $('#templates').children('.focus_panel').clone();
        
        $('body').prepend(dp);
        $('body').prepend(fp);
        
        
        dp.find('.button').each(function(i, im) {
            var btn = $(im);
            btn.button(_userView);
            btn.on('dropitem', function(e) {_userView.dropitem(btn, e.item);});
        });
        fp.find('.button').each(function(i, im) {$(im).button(_userView);});
        fp.mouseenter(function() {_userView.panelFocus = true});
        fp.mouseleave(unfocusPanel); 
        dp.mouseenter(function() {_userView.panelFocus = true});
        dp.mouseleave(unfocusPanel);
        
        
        dp.vtrans();
        fp.vtrans();
        
        treeView.on('MODE', function(e) {
            if (treeView.getMode() == MODE_VIEW)  _userView.hidePanel();           
        });
        
        treeView.on('ASSIGN', function(e) {_userView.hidePanel();});
        
        this._treeView = treeView;
        this._app = treeView.getApp();
        this._app.on('begindrag', function(e) {_userView._isDrag = true});
        this._app.on('enddrag', function(e) {_userView._isDrag = false});
    },
    requireHidepanel: function() {
        setTimeout(function() {
            if (!_userView.panelFocus && !_userView._focus) _userView.hidePanel();
        }, 100);
    },
    hidePanel: function() {
        this._dpanel.vhide();
        this._fpanel.vhide();
    },
    setCurrent: function(a_current) {
        var c;
        if (c = this._current = a_current) {
            var e = c.getElement();
            var pos = e.offset();
            var cs = c.getScale();
            var size = new Vector(e.width() * cs, e.height() * cs);
            
            var p = this._isDrag?this._dpanel:this._fpanel;

            let item = c.getData();
            let delbtn = this._fpanel.find('a[data-button="idelete"]');
            delbtn.css('display', this._treeView.getTree().id == item.id ? 'none' : 'block');
            
            p.vshow();
            if (!this._isDrag) {
                var sbtn = p.find('.sbtn');
                $(sbtn[1]).css({top: size.y * 0.9});
                var cp = p.find('.center');
                cp.css({
                    'left': size.x * 0.3,
                    'top': (size.y - cp.height()) * 0.45  
                });
                p.css({left: pos.left + size.x * 0.5 - 8, top: pos.top});
            } else {
                p.css({left: pos.left + size.x * 0.5 - 8, top: pos.top});
            } 
        }
    }
} 

function baseUserView(data) {
    var scale = 1;
    
    this.element = $('#templates').children('.persona').clone();
    var This = this;
    This.pos = new Vector();
    This.element.css({opacity: 0, visibility: 'hidden'});
    
    this.elem = function(className) {
        return This.element.find('.' + className);
    }
    
    this.createWinMoney = function() {
        if (this.element.find('smoney').length == 0) {
            var em = $('<div class="smoney"></div>');
            em.css('transition', 'all ' + (TRANSITONSPEED / 1000) + 's ease-in-out');
            this.element.append(em);
        }
    }
     
    this.setData = function(a_data) {
        data = a_data;
        This.element.attr('data-id', data.id); 
        this.elem('vin_image').attr('src', $.ck(data.img, Utils.defImg(data)));
        this.elem('person_name').html('<span>' + data.displayName() + '</span>');
        this.elem('person_type').html('<span>' + $.ck(data.type?(($.type(data.type) == 'array')?data.type[data.gender]:data.type):'', locale.RELATIVE) + '</span>');
        
        setTimeout(function() {
            This.elem('ppl_up').css('visibility', data.child_next?'visible':'hidden');
            This.elem('ppl_down').css('visibility', data.parent_next?'visible':'hidden');
            //this.elem('ppl_down').css('visibility', isp?'visible':'hidden');
        }, 200);
        
        if (a_data.win) this.createWinMoney();
    }
    
    this.getData = function() {
        return data;
    }
    
    this.getElement = function() {
        return This.element;
    }
    
    this.setScale = function(a_scale) {
        scale = Math.min(a_scale, 1);
        This.element.css('transform', 'scale(' + scale + ', ' + scale + ')');
        return scale;
    } 
    
    this.getScale = function() {
        return scale;
    }
    
    this.setPosition = function(a_pos) {
        This.pos = a_pos;
        This.element.css({left: This.pos.x + VINDEC, top: This.pos.y});
    } 
    
    this.getPosition = function() {
        return This.pos;
    }
    
    this.adaptiveSize = function(w, h) {
        var a_scale = Math.min(h / (this.element.height() - VINDEC), w / (this.element.width() - VINDEC));
        return this.setScale(a_scale);
    }
    
    this.restoreState = function() {
        This.element.css({
            transform: 'scale(' + scale + ', ' + scale + ')',
            left: This.pos.x + 23, top: This.pos.y
        });
        setTimeout(function() {
            This.element.css('z-index', 45);        
        }, TRANSITONSPEED);
    }
    
    this.hide = function() {
        This.element.css({opacity: 0});
        setTimeout(function() {
            This.element.css('visibility', 'hidden');
        }, 500);
    }
    
    this.show = function(op) {
        This.element.css({opacity: op?op:1, visibility: 'visible'});
    }
}

function userView(p_view, data) {
    $.extend(this, new baseUserView(data));
    var This = this;
    var layer;
    var app = p_view.getApp();
    
    app.rel.on('UPDATEITEM', function(e) {
        if (e.item.id == data.id) {
            This.setData(e.item);
        }
    });
    
    this.element.on('dropitem', function(e) {
        app.trigger(jQuery.Event('item_command', {
            command  : 'childs',
            source   : 'tree',
            target   : This,
            item     : e.item                                                
        }));
    });
          
    this.focusEffect = function() {
        if (_userView._focus == This) { 
            This.element.css('z-index', 50);
            var height = This.element.height();
            var width = This.element.width();
            if (This.getScale() <= 1) { 
                var pheight = This.element.parent().height();
                var pwidth = This.element.parent().width();
                var f_scale = 1;
                This.element.css('transform', 'scale(' + f_scale + ', ' + f_scale + ')');
                setTimeout(TRANSITONSPEED, function() {
                    if (_userView._focus != This) this.restoreState();
                });
                
                /*
                var pos = This.getPosition();
                
                var tr = Math.round(pos.y - height * 0.8);
                var br = Math.round(pos.y + height * 0.8);
                var lr = Math.round(pos.x - width * 0.5);
                var rr = Math.round(pos.x + width * 0.5);
                if (tr < 0) This.element.css('top', -tr);
                if (br > pheight) This.element.css('top', pheight - height * 0.5);
                if (lr < 0) This.element.css('left', pos.x - lr);
                if (rr > pwidth) This.element.css('left', pwidth - width * 0.5);
                */
            }
        }
    }
    
    function unfocus() {
        if (_userView._focus == This) This.restoreState();
        _userView._focus = null;
        _userView.requireHidepanel();
    }
                                                                                                                                                   1
    this.focus = function(a_focus) {
        if (a_focus) {
            if (_userView._focus) {
                _userView._focus.getElement().css('z-index', 45);
                this.focus(false);
            }
            _userView._focus = This;
            if (p_view.getMode() == MODE_EDIT) {
                _userView.setCurrent(This);
            } else setTimeout(function() {
                        This.focusEffect(); 
                    }, FOCUSWAIT);
        } else {
            if (_userView._focus) unfocus();
        }
    }
    
    This.element.mouseenter(function(e) {
        This.focus(true);
    });
    
    This.element.mouseleave(function(e) {
        This.focus(false);
    });
    
    This.element.mousedown(function(e) {
        if (p_view.getMode() == MODE_EDIT)
            $(window).trigger(jQuery.Event('dragitem', {id: This.getData().id, source: 'tree'}));
    })
    
    function omMoney() {
        var mn = This.getElement().find('.smoney');
        var pos = mn.offset();
        mn.css({'transform': 'scale(0.01, 0.01)'});
        
        setTimeout(function() {
            pay_support.transaction('FIND', 0, {rod_id: app.rod.rod_id, people_id: data.id, extend: 'winTook'}, function() {            
                pay_support.pind('FIND', pos);
                app.rel.find(data.id).win = 0;
            });
            mn.remove();
        }, TRANSITONSPEED);
    }
    
    this.onClick = function(listener) {
        This.element.click(function(e) {
            if ($(e.target).hasClass('smoney')) omMoney();
            else listener(This);
        });
        _userView.hidePanel();       
    }
    
    this.setData(data);
}