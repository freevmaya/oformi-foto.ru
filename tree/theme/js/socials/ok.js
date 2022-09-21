var social = {
    init: function(uid) {
        console.log(uid);
        this.loginOK(uid);
    },
    
    loginOK: function(uid) {
        pt = location.protocol;
        var redirect_uri = pt + '//oformi-foto.ru/ssd2/ok_auth.php';
        window.authSuccess = (function(s) {this._afterLogin_ok(s);}).bind(this);         
        window.open(pt + "//connect.ok.ru/dk?cmd=WidgetCtrl&st.cmd=OAuth2Permissions&st.scope=PUBLISH_TO_STREAM%3BSET_STATUS%3BPHOTO_CONTENT%3BVALUABLE_ACCESS%3BAPP_INVITE%3BVIDEO_CONTENT&st.response_type=token&st.redirect_uri=" + redirect_uri + "&st.client_id=" + uid + "&st.show_permissions=off",
                    'AuthWindow', 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=0,height:0');
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
        FAPI.UI.showNotification('Помоги мне составить генеалогическое дерево', "id=" + rod.rod_id + '-' + rod.start_id, Utils.uidsToStr(friends));
    },
    
    getProfiles: function(uids, onComplete) {
        var result = {};
        for (var i=0; i<uids.length; i++) {        
            result[uids[i]] = {uid: uids[i], first_name: 'Фролов', last_name: 'Вадим', avatar: 'images/cap/24323.jpg'};
        }
        onComplete(result);
    }       
}