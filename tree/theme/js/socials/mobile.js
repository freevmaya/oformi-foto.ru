
var social = {
    user: null,
    _callbackIndex: 0,
    _callback: [],
    init: function(uid, onComplete) {
        
        
        this.user = $.getUrlVars(document.location.href);
        Utils.decodeURI(this.user, 'last_name,first_name');
        Utils.fieldsInt(this.user, 'gender');
        if (onComplete) onComplete([this.user]);
    },
    
    regCallback: function(proc) {
        this._callbackIndex++;
        this._callback[this._callbackIndex] = proc;
        return this._callbackIndex;
    },
    
    friend_list: function(onComplete) {
        this.getProfiles([this.user.uid], onComplete);
    },
    
    item_img: function(itm) {
        return itm.avatar;
    },
    
    item_userName: function(itm) {
        return itm?(itm.first_name + ' ' + itm.last_name):'';
    },
    
    friendFilter: function(str_filter) {
        return function(item) {
            if (str_filter.length > 1) {
                return (item.first_name.search(str_filter) > -1) || (item.last_name.search(str_filter) > -1);
            } else return true;
        }
    },
    
    shareTree: function(rod, friends) {
    },
    
    getProfiles: function(uids, onComplete) {
        var result = [this.user];
        onComplete(result);
    },
           
    toPeople: function(p) {
        return {
            link_uid: parseInt(p.uid),
            name: p.first_name?p.first_name:locale.NEWPEOPLE,
            family: p.last_name?p.last_name:locale.NEWPEOPLE,
            bday: p.birthday?p.birthday:Utils.formatDate(new Date()),
            father: '',
            gender: parseInt(p.gender),
            img: p.avatar
        }
    },

//APP

    shareMenu: function(){
        return  [
            {id: 1, name: locale.SHARELINK}
        ] 
    },
    saveMenu: function(){
        return  [
            {id: 1, name: locale.SAVEASIMAGE},
            {id: 2, name: locale.SAVEASTEXT},
            {id: 3, name: locale.SAVEASDATA}
       ]
    },
    shareMenuRelease: function(itm) {
        switch (itm.id) {
            case 1: treeApp.shareTree(); break;
        }
    },
    saveMenuRelease: function(itm) {
        switch (itm.id) {
            case 1: treeApp.saveToFile(); break;
            case 2: treeApp.saveToText(); break;
            case 3: treeApp.saveToData(); break;
        }
    }
}