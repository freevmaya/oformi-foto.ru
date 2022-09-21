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

var social = {
    _initstatus: false,
    uid: '',
    init: function(a_data) {
        this.uid = a_data.logged_user_id;               
        FAPI.init(a_data.api_server, a_data.apiconnection, function() {
            social._initstatus = true;
        });
    },
    
    friend_list: function(onComplete) {
        FAPICall({"method": "friends.get"}, function(uids) {
            var users = [];
            var qcount = 0;
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
        return itm.pic_full;
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
    }       
}

var api_params = {};
function API_callback(method, result, data) {
    if (method == 'showNotification') {
        if (api_params.event == 'SHARETREE') {
            if (result == 'ok') {
                var params = {
                    id: api_params.app.rod.rod_id,
                    uids: data
                }
                $.main.query('shareTreeFriends', params, function(a_data) {
                    
                });
            }
        } 
    }
    
    api_params = {};
}

function canvasCreate(uid, id) {
    var tree, rel, treeApp;
    var params = $.getUrlVars(document.location.href);
    
    Utils.proxy = function(imgUrl) {
        imgUrl = imgUrl.replace('https://', '');
        imgUrl = imgUrl.replace(/\//g, '_____');
        imgUrl = imgUrl.replace(/&/g, '____');
        imgUrl = imgUrl.replace(/\?/g, '___');
        
        return '//oformi-foto.ru/imageproxy/' + imgUrl + '.img';
    }
    
    social.init(params);
    $.main.initialize();
    
    var rodMenu, userData;
    
    function updateAppMenu() {
        $.main.query('getRods', {uid: userData.user.uid}, function(a_rods) {
            a_rods.push({id: 'new', name:locale.NEWROD});
            rodMenu.setData({rods: a_rods});
        });
    }
    
    function createPeopleFromUser(p) {
        var item = {
            name: p.first_name,
            family: p.last_name,
            bday: p.birthday,
            gender: p.gender,
            img: Utils.proxy(social.item_img(p))
        }
        
        treeApp.editDialog(item);
    } 
    
    function startNewUser() {
        treeApp.alert(locale.WELCOMENEWUSER, function() {
            console.log('click');
            assistant.setTimeline([
                {control: '.assist', text: 'Что бы редактировать нажмите сюда'},
                {control: '.btn-add', text: 'Жмите сюда что бы добавить персону', delay: 1000},
                {control: '.new_persone .dlgbtn', text: 'Введите необходиные данные и нажмите "Применить"', delay: 2000}
            ]);    
        }, 'dlg_welcome');
    }
    
    function createRightMenu() {
         rmenu = $('.menu-right').submenu(treeApp, {
           lesson: lesson.menu,
           money: []
        });
        
        rmenu.release({
            lesson: function(itm) {
                assistant.setTimeline(itm.lesson); 
            }
        })
    }
    
    function createLeftMenu(trees) {
        rodMenu = $('.menu-left').submenu(treeApp, {
           rods: trees,
           share: [
                {id: 1, name: locale.SHARELINK},
                {id: 2, name: locale.SHAREFRIENDS}
           ]
        });
        rodMenu.release({
            rods: function(rod) {
                if (rod.id == 'new')
                    treeApp.createNewTree();
                else if (rod.id != treeApp.rod.rod_id) {
                    $.main.query('getTree', {id: rod.id}, function(a_tree) {
                        Utils.fieldsInt(a_tree, 'id,main_id,rod_id,options,haveAvatar');
                        treeApp.responseDBTree(rod, a_tree);
                    });
                }
            },
            edit: function() {
                treeApp.tree.toggleMode();
            },
            save: function() {
                treeApp.saveToFile();
            },
            share: function(itm) {
                switch (itm.id) {
                    case 1: treeApp.shareTree(); break;
                    case 2: treeApp.shareToFriend(); break;
                }
            }
        });
    }
    
    $('.family-title span').click(function() {
        if (treeApp.tree.getMode() == MODE_EDIT) treeApp.editTree();
    });
    
    treeApp = new TreeApp({
        share_size: 640
    });
    
    treeApp.shareToFriend = function() {
        console.log(this);
        var This = this;
        if (this.isEditPermission()) {
            $.main.query('getShareTreeFriends', {id: This.rod.rod_id}, function(a_data) {
                api_params = {
                    app: treeApp,
                    event: 'SHARETREE'
                }
                FAPI.UI.showNotification('Помоги мне составить генеалогическое дерево', "id=" + This.rod.rod_id + '-' + This.rod.start_id);
                //social.shareTree(This.rod.rod_id, This.tree.getTree().id, select);
            });
        } else this.alert(locale.NOACCESS);
    }
    
    console.log(id);
    $.main.query('inUser', {uid: uid, id: id}, function(a_data) {
        userData = a_data;
        
        Utils.fieldsInt(a_data.rod, 'main_id,rod_id,options');
        Utils.fieldsInt(a_data.user, 'def_rod');  
        Utils.fieldsInt(a_data.trees, 'id,main_id,rod_id,options');
        Utils.fieldsInt(a_data.tree, 'id,rod_id,gender,haveAvatar');
        
        treeApp.setUser(a_data.user);
        treeApp.responseDBTree(a_data.rod, a_data.tree);
        treeApp.on('createnewtree', updateAppMenu);
        treeApp.on('deletetree', updateAppMenu);
        
        new panelCreate(treeApp, $('.right_panel'));
        new vinDragable(treeApp);
        
        a_data.trees.push({id: 'new', name:locale.NEWROD});
        createLeftMenu(a_data.trees);
        createRightMenu();
        
        if (a_data.tree[0].length == 0) {
            if (uid)  {
                social.getProfiles([uid], function(users) {
                    createPeopleFromUser(users[0]);
                });
            }
        }                
        new Tips(treeApp);
        assistant = new Assistant(treeApp, $('.assistant'));
        
        if (a_data.created) startNewUser();
    });
    
    
    $(window).on('parentsRequire', function(e) {
    });
    
    $(window).on('ieditRequire', function(e) {
        treeApp.editDialog(e.getData());
    }); 
    
    $(window).on('ideleteRequire', function(itm) {
        treeApp.clearItem(itm.getData());
    });
}
