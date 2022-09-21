function commanManager() {
    var list = [];
    var index = -1;
    var This = this;
    $.extend(This, new Events());
    
    this.execute = function(command) {
        index++;
        if (index < list.length) list.splice(index);
        list[index] = command;
        command.execute();
        index = list.length - 1;
        this.trigger($.Event('HISTORY_ADD'));
    }
    
    this.back = function() {
        if (this.isBack()) {
            list[index].restore();
            index--;
            this.trigger($.Event('HISTORY_BACK'));
        }
    }
    
    this.forward = function() {
        if (this.isForward()) {
            index++;
            list[index].execute();
            this.trigger($.Event('HISTORY_FORWARD'));
        }
    }
    
    this.isBack = function() {
        return index >= 0;
    }
    
    this.isForward = function() {
        return index < list.length - 1;
    }
    
    this.clear = function() {
        list.splice(0);
        index = -1;
        this.trigger($.Event('HISTORY_CLEAR'));
    }
}

function cmd_treeNavigator(app, sid) {
    var prevID = app.tree.getTree().id; 
    this.execute = function() {
        app.tree.assign(app.rel.toTreeFromId(sid));
        app.rod.start_id = sid;
    }
    
    this.restore = function() {
        app.tree.assign(app.rel.toTreeFromId(prevID));
        app.rod.start_id = prevID;
    } 
}