var FBUFIELDS = 'id,picture,birthday,gender,first_name,last_name';

var social = {
    params: null,
    user: null,
    connect: false,
    init: function(uid, onComplete) {
        this.params = $.getUrlVars(document.location.href);
        $('body').append($('<h1 id="fb-welcome"></h1>'));
        
        var This = this;
        this.loadFB(function(a_user) {
            This.user = a_user;
            This.user.uid = a_user.id;
            onComplete([This.user]);
        });
    },
    
    loadFB: function(onComplete) {
        var This = this;
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '467686096941786',
                xfbml      : true,
                version    : 'v2.9'
            });
            
            function onLogin() {
                This.connect = true;
                FB.api('/me?fields=' + FBUFIELDS, function(response) {
                    onComplete(response);
                });
            }
            
            FB.getLoginStatus(function(response) {
                if (response.status == 'connected') {
                    onLogin(response);
                } else {
                    FB.login(function(response) {
                        onLogin(response);
                    }, {scope: 'user_photos,public_profile,user_friends'});
                }
            });
        };
    
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); 
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    },
    
    waitConnect: function(onComplete) {
        var This = this;
        if (this.connect) onComplete();
        else {
            setTimeout(function() {
                This.waitConnect(onComplete);
            }, 300);
        }  
    },
    
    friend_list: function(onComplete) {
        var This = this;
        FB.api('/me/friends?fields=' + FBUFIELDS, function(response) {
            var list = response.data;
            for (var i=0; i<list.length; i++) list[i].uid = list[i].id;
                
            list = [This.user].concat(list);
            onComplete(list);
        }); 
    },
    
    item_img: function(itm) {
        return itm.picture?itm.picture.data.url:'';
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
        this.waitConnect(function() {
            FB.api('/me?fields=id,picture,birthday,gender,first_name,last_name', function(response) {
                onComplete(response);
            });        
        });
    },
           
    toPeople: function(p) {
        return {
            link_uid: p.id,
            name: p.first_name?p.first_name:locale.NEWPEOPLE,
            family: p.last_name?p.last_name:locale.NEWPEOPLE,
            bday: p.birthday?p.birthday:Utils.formatDate(new Date()),
            father: '',
            gender: (p.gender == 'male')?0:1,
            img: this.item_img(p)
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
            case 2: treeApp.dev(); break;
            case 3: treeApp.dev(); break;
        }
    }       
}