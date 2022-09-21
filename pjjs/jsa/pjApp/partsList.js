var partsList = new Class({ 
    Extends         : Events,
    _select         : null,
    _list           : [],
    initialize: function(a_select) {
        this._select = a_select;
        this._select.addEvent('change', this._doChange.bind(this));
    },
    
    _doChange: function(e) {
        var groups = '';
        for (var i=0; i<this._select.options.length; i++) {
            var option = this._select.options[i];
            if (option.get('selected'))
                groups += (groups?'-':'') + option.value;
        }
        this.fireEvent('change', groups);
    },
    
    load: function(serverURL, afertLoad) {
        (new Asset.javascript(serverURL + '?model=' + DATA_MODEL + '&method=getCats_rus&lang=' + LANGUAGE, {
            onload: (function(e) {
                this.assign(result);
                this.fireEvent('load', result);
            }).bind(this)
        }));        
    },
    
    assign: function(list) {
        var insertOption = (function(text, value) {
            var option = new Element('option');
            option.set('text', text);
            option.set('value', value);
            option.inject(this._select);
        }).bind(this);
        this._select.getChildren().destroy();
        
        insertOption(LOCALE.LASTTMPLS, 0);
        
        this._list = list;
        list.each((function(item) {
            item.group_id = parseInt(item.group_id);
            insertOption(this._getItemTitle(item), item.group_id);
        }).bind(this));
    },
    
    _getItemTitle: function(item) {
        return item.partName + '->' + item.name;
    },
    
    setSelected: function(a_groups) {
        if (typeOf(a_groups) != 'null') { 
            a_groups = a_groups.toString().split('-');
            a_groups.each(function(item, i){a_groups[i]= parseInt(item)});
            for (i=0;i<this._list.length;i++)            
                if (a_groups.indexOf(this._list[i].group_id) > -1) {
                    this._select[i + 1].set('selected', 'selected');
                    break;                    
                }                    
            this._doChange();
        }
    }
});
