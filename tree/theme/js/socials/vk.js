var social = {
    init: function(a_data) {
    },
    
    friend_list: function(onComplete) {
        var list = [
            {uid: 24321, first_name: 'Петров', last_name: 'Василий', avatar: 'images/cap/24321.jpg'},
            {uid: 24322, first_name: 'Мамонова', last_name: 'Людмила', avatar: 'images/cap/24322.jpg'},
            {uid: 24323, first_name: 'Перепелкина', last_name: 'Анастасия', avatar: 'images/cap/24323.jpg'},
            {uid: 24324, first_name: 'Петров', last_name: 'Василий', avatar: 'images/cap/24324.jpg'},
            {uid: 24325, first_name: 'Мамонова', last_name: 'Людмила', avatar: 'images/cap/24325.jpg'},
            {uid: 24326, first_name: 'Перепелкина', last_name: 'Анастасия', avatar: 'images/cap/24326.jpg'}
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
        var result = {};
        for (var i=0; i<uids.length; i++) {        
            result[uids[i]] = {uid: uids[i], first_name: 'Фролов', last_name: 'Вадим', avatar: 'images/cap/24323.jpg'};
        }
        onComplete(result);
    }       
}