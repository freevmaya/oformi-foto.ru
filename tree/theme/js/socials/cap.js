var social = {
    init: function(a_data) {
    },
    
    friend_list: function(onComplete) {
        var list = [
            {uid: 24321, first_name: 'Петров', last_name: 'Василий', avatar: 'images/cap/24321.jpg', birthday: '12.05.1689', gender: 0},
            {uid: 24322, first_name: 'Мамонова', last_name: 'Людмила', avatar: 'images/cap/24322.jpg', birthday: '12.05.1689', gender: 1},
            {uid: 24323, first_name: 'Перепелкина', last_name: 'Анастасия', avatar: 'images/cap/24323.jpg', birthday: '12.05.1689', gender: 1},
            {uid: 24324, first_name: 'Петров', last_name: 'Василий', avatar: 'images/cap/24324.jpg', birthday: '12.05.1689', gender: 0},
            {uid: 24325, first_name: 'Мамонова', last_name: 'Людмила', avatar: 'images/cap/24325.jpg', birthday: '12.05.1689', gender: 1},
            {uid: 24326, first_name: 'Перепелкина', last_name: 'Анастасия', avatar: 'images/cap/24326.jpg', birthday: '12.05.1689', gender: 1}
        ]
        onComplete(list);
    },
    
    item_img: function(itm) {
        return itm.avatar;
    },
    
    item_userName: function(itm) {
        return itm.first_name + ' ' + itm.last_name;
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
        var result = [];
        for (var i=0; i<uids.length; i++) {        
            result.push({uid: uids[i], first_name: 'Фролов', last_name: 'Вадим', avatar: 'images/cap/24323.jpg'});
        }
        onComplete(result);
    },
           
    toPeople: function(p) {
        return {
            link_uid: p.uid,
            name: p.first_name?p.first_name:locale.NEWPEOPLE,
            family: p.last_name?p.last_name:locale.NEWPEOPLE,
            bday: p.birthday?p.birthday:Utils.formatDate(new Date()),
            father: '',
            gender: p.gender,
            img: p.avatar?Utils.proxy(p.avatar):DEFAULT_USER_IMAGE
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