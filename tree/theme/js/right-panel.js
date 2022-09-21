function panelCreate(treeApp, elem) {
    var This = this;
    
    this.trigger = function(event) {
        This[event.type](event);
    }
    
    this.deleteRequire = function() {
        treeApp.deleteRod();
    }
    
    this.addRequire = function() {
        treeApp.newPeople();
    }
    
    elem.find('.edit_close').click(function() {
        treeApp.tree.setMode(MODE_VIEW);        
    });
    elem.find('.btn-add').button(this);
    elem.find('.btn-trash').on('dropitem', function(e) {
        treeApp.trigger(jQuery.Event('item_command', {
            command  : 'clearItem',
            source   : e.source,
            target   : elem,
            item     : e.item                                                
        }));
    }).button(this);
    
    /*
    var cb = elem.find('.btn-cb').bt_checkbox();
    cb.onChange(function(index) {
        treeApp.tree.setMode(index);
    });
    */
    
    var container = elem.find('.edit_panel');
    var listElem = elem.find('.pl_list');
    container.vtrans();
    
    function refreshSize() {
        var lc = elem.find('.pl_list_cont');
        var over = listElem.height() - lc.height();
        if (over > 0) lc.css('overflow-y', 'scroll');
        else lc.css('overflow-y', 'hidden');
    }
    
    function onClick(e) {
        var id = $(e.target).attr('data-id');
        treeApp.editDialog(treeApp.rel.find(id));
    }
    
    function onMouseDown(e) {
        var id = $(e.target).attr('data-id');
        $(window).trigger(jQuery.Event('dragitem', {id: id, source: 'list'}));
    }
    
    function createLI(item) {
        var li = $('<div class="item" data-id="' + item.id + '">' + item.displayName() + '</div>');
        listElem.append(li);
        li.mousedown(onMouseDown);
        li.click(onClick);
    }
    
    function Refresh(e) {
        var list = treeApp.rel.getList().concat([]);
        list.sort(function(a, b) {
            return (a.displayName() < b.displayName())?-1:1;
        });
        listElem.empty();
        for (var i=0;i<list.length;i++) createLI(list[i]);
        refreshSize();        
    }
    
    function onMode(e) {
        if (treeApp.tree.getMode() == MODE_EDIT) {
            container.vshow();
        } else {
            container.vhide();     
        }
    }
    
    treeApp.rel.on('UPDATEITEM', function(e) {
        listElem.find('.item').each(function(i, li) {
            li = $(li);
            if (li.attr('data-id') == e.item.id) li.text(e.item.displayName());
        });
    });    
    
    treeApp.rel.on('RELATIVE', Refresh);
    treeApp.rel.on('ADDITEM', Refresh);
    treeApp.tree.on('MODE', onMode);
    Refresh();
    
    $(window).resize(refreshSize);
}