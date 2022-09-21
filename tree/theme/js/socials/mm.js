var extPaySupport = {
    chbList: false,
    payProcess: function(payitem, onComplete) {
        var price = PAYVARS[payitem.price];
        var This = this;
        
        var attr = {
            service_id: 103,
            service_name: payitem.desc + ' ' + Utils.decline(payitem.price, pay_locale.MONEYFORMAT),
            mailiki_price: price
        };
        mailru.app.payments.showDialog(attr);
    }
}

var social = {
    params: null,
    user: null,
    canvas: null,
    hid: '',
    init: function(uid, onComplete) {
        this.params = $.getUrlVars(document.location.href);
        var This = this;
        this.loadMM(function(users) {
        
            mailru.events.listen(mailru.app.events.incomingPayment, function(event) {
                pay_support.requestBalance();
            });
            
            mailru.events.listen(mailru.common.events.message.send, function(event) {
                if (event.status == 'closed')
                    pay_support.transaction('TREESHARE', 0, null, function() {pay_support.pind('TREESHARE');});                
            });
            
            var startComplete = onComplete;
            
            mailru.events.listen(mailru.app.events.readHash, function(result){
                if (result.id && This.canvas) {
                    var ra = result.id.split('-');
                    if (ra[0] != This.hid) {
                        This.hid = ra[0]; 
                        This.canvas.getTree(result);
                    } else startComplete(users);
                } else startComplete(users);
                startComplete = function() {};
            });
            mailru.app.utils.hash.read();
        });
        
        treeApp.on('CHANGEROD', this.onChangeRod);        
        treeApp.on('CHANGESTARTID', this.onChangeRod);
        
        $.extend(pay_support, extPaySupport);
    },
    
    onChangeRod: function(e) {  
        this.hid = treeApp.rod.rod_id;
        if (mailru.connect != undefined) mailru.app.utils.hash.write({id : treeApp.rod.rod_id + '-' + treeApp.rod.start_id});
    },
    
    initCanvas: function(a_canvas, a_data) {
        this.canvas = a_canvas;
        this.canvas.startRod(a_data);
    },
    
    private_key: {755008 :'f0390ff026e52858209d76adecc9fc3e', 688946: 'ec50c4c1bf584ac4c75983d405bb1f99'},
    
    loadMM: function(onComplete) {
        var This = this;
        if (mailru.connect==undefined) {
            mailru.loader.require('api', function() {
                mailru.app.init(This.private_key[This.params.app_id]);
                This.getProfiles([This.params.vid], function(users) {
                    This.user = users[0];
                    onComplete(users);
                });
            });
        }        
    
    },
    
    waitConnect: function(onComplete) {
        var This = this;
        if (mailru.connect != undefined) onComplete();
        else {
            setTimeout(function() {
                This.waitConnect(onComplete);
            }, 300);
        }  
    },
    
    friend_list: function(onComplete) {
        var This = this;
        mailru.common.friends.getExtended(function(list) {
            if (list) {
                list = [This.user].concat(list);
                onComplete(list);
            }
        });
    },
    
    item_img: function(itm) {
        return itm.pic;
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
        mailru.common.messages.send({
            uid: friends[0].uid,
            text: 'Посмотри мое родословное древо "' + treeApp.rod.name + '" по этой ссылке http://my.mail.ru/apps/755008#id=' + rod_id + '-' + start_id
        });
    },
    
    getProfiles: function(uids, onComplete) {
        this.waitConnect(function() {
            mailru.common.users.getInfo(onComplete, uids);
        });
    },
           
    toPeople: function(p) {
        return {
            link_uid: parseInt(p.uid),
            name: p.first_name?p.first_name:locale.NEWPEOPLE,
            family: p.last_name?p.last_name:locale.NEWPEOPLE,
            bday: p.birthday?p.birthday:Utils.formatDate(new Date()),
            father: '',
            gender: parseInt(p.sex),
            img: p.pic
        }
    },

//APP

    shareMenu: function(){
        var wstr = ' (+' + Utils.decline(pay_support.getWind('TREESHARE').price, pay_locale.MONEYFORMAT) + ') ';
        return  [
            {id: 1, name: locale.SHARELINK},
            {id: 2, name: locale.SHAREFRIENDS + wstr},
            {id: 3, name: locale.SHAREFRIENDEDIT + wstr}
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
            case 2: treeApp.shareToFriend(false); break;
            case 3: treeApp.shareToFriend(true); break;
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