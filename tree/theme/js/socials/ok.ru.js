function processError(error) {
    console.log(error);
}

function FAPICall(params, callback) {
    FAPI.Client.call(params, function(method, result, error) {
        if (result) callback(result);
        else processError(error);
    });
}

function resetdate(date) {
    var d = date.split('-');
    return d[2] + '.' + d[1] + '.' + d[0];
}

var treeAppExtend = {
    shareToFriend: function() {
        var This = this;
        if (this.isEditPermission()) {
            $.main.query('getShareTreeFriends', {id: This.rod.rod_id}, function(a_data) {
                api_params = {
                    app: treeApp,
                    event: 'SHARETREE'
                }
                var uids = This.rel.link_uids();
                var arg = "id=" + This.rod.rod_id + '-' + This.rod.start_id;
                FAPI.UI.showNotification('Помоги мне составить генеалогическое дерево', arg, uids.join(';'));
                //social.shareTree(This.rod.rod_id, This.tree.getTree().id, select);
            });
        } else this.alert(locale.NOACCESS);    
    },
    shareTree: function() {
        var shparam = this.rod.rod_id;
        var titem = this.tree.getTree();
        if (titem) shparam += '-' + titem.id;
        this.alert(locale.SHARECOMPLETE.replace('%s', SHAREURL.replace('%s', shparam)));
    }
}

var extPaySupport = {
    chbList: false,
    payProcess: function(payitem, onComplete) {
        var price = PAYVARS[payitem.price];
        var This = this;
        
        var attr = JSON.stringify({
            count: payitem.price,
            price: price
        });
        FAPI.UI.showPayment(Utils.decline(payitem.price, pay_locale.MONEYFORMAT), payitem.desc, payitem.id, price, attr, attr, 'ok', 'true');
        
        if (!this.chbList) {
            var chbi = 0, tid;
            
            function clearWait() {
                clearInterval(tid);
                pay_support.off('CHANGEBALANCE', clearWait);
                This.chbList = false;
            }
            
            pay_support.on('CHANGEBALANCE', clearWait);
            
            function checkBalance() {
                chbi++;
                if (chbi > 150) clearWait();
                else pay_support.requestBalance();
            }
            
            tid = setInterval(checkBalance, 2000);
            this.chbList = true;
        }
    }
}

var social = {
    _initstatus: false,
    uid: '',
    init: function(uid, onComplete) {
    
        var a_data = $.getUrlVars(document.location.href);
        this.uid = a_data.logged_user_id;       
        This = this;        
        FAPI.init(a_data.api_server, a_data.apiconnection, function() {
            social._initstatus = true;
            FAPI.UI.getPageInfo();
            
            if (onComplete) This.getProfiles([This.uid], onComplete);
        });
        
        Utils.proxy = function(imgUrl) {
            imgUrl = imgUrl.replace('https://', '');
            imgUrl = imgUrl.replace(/\//g, '_____');
            imgUrl = imgUrl.replace(/&/g, '____');
            imgUrl = imgUrl.replace(/\?/g, '___');
            
            return '//oformi-foto.ru/imageproxy/' + imgUrl + '.img';
        }
        
        $.extend(treeApp, treeAppExtend);
        $.extend(pay_support, extPaySupport);
    },
    
    friend_list: function(onComplete) {
        FAPICall({"method": "friends.get"}, function(uids) {
            var users = [];
            var qcount = 0;
            uids.push(treeApp.user.uid);
            while (uids.length > 0) {
                var su = uids.splice(0, 100);
                social.getProfiles(su, function(a_users) {
                    users = $.merge(users, a_users);
                    qcount--;
                    if (qcount <= 0) onComplete(users);
                });
                qcount++;
            }
        });
    },
    
    item_img: function(itm) {
        return itm?itm.pic_full:'';
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
    
    shareTree: function(rod_id, start_id, friends) {
    },
    
    getProfiles: function(uids, onComplete) {
         FAPICall({"method": "users.getInfo", "fields": "first_name,last_name,gender,pic_full,birthday,uid", "uids": uids.join(',')}, function(users) {
            var result = [];
            for (var i=0; i<users.length; i++) {
                users[i].birthday = resetdate(users[i].birthday); 
                users[i].gender = (users[i].gender == 'male')?0:1;
                result.push(users[i]); 
            }
            onComplete(result);
         });
    },
    
    toPeople: function(p) {
        return {
            link_uid: p.uid,
            name: p.first_name?p.first_name:locale.NEWPEOPLE,
            family: p.last_name?p.last_name:locale.NEWPEOPLE,
            bday: p.birthday?p.birthday:Utils.formatDate(new Date()),
            father: '',
            gender: p.gender,
            img: p.pic_full?Utils.proxy(p.pic_full):DEFAULT_USER_IMAGE
        }
    },

//APP

    shareMenu: function(){
        return  [
            {id: 1, name: locale.SHARELINK},
            {id: 2, name: locale.SHAREFRIENDS}
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
            case 2: treeApp.shareToFriend(); break;
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

var api_params = {};
function API_callback(method, result, data) {
    if (method == 'showNotification') {
        if (api_params.event == 'SHARETREE') {
            if (result == 'ok') {
                var app = api_params.app; 
                sendData(true);
                /*                
                var tuids = app.rel.link_uids();
                 if (tuids) {
                    app.alert(locale.RELINKQUESTION, sendData, '', function() {
                        sendData(true);
                    }, true);
                } else sendData();
                */  
                
                function sendData(relink) {
                    var params = {
                        id: app.rod.rod_id,
                        uids: data,
                        relink: relink?1:0
                    }
                    $.main.query('shareTreeFriends', params, function(a_data) {});
                }
            }
        } 
    } else if (method == 'showPayment') {
        if (result == 'ok') 
            pay_support.requestBalance();
    } else if (method == 'getPageInfo') {
        if (result == 'ok') {
            data = $.parseJSON(data);
            FAPI.UI.setWindowSize(data.clientWidth, data.clientHeight - 76);
       }
    }
    
    console.log(method);
    
    api_params = {};
}