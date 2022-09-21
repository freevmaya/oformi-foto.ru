var lfpartsList = new Class({ 
    Extends: partsList,
    _getItemTitle: function(item) {
        return item.name;
    }
});