var canvasTree = function(uid, id) {
    $.main.initialize();
    
    var rodMenu, userData, rmenu;
    var assistant;
    var params = $.getUrlVars(document.location.href);
    params.uid = uid;
    var This = this;
    
    function updateAppMenu(onComplete) {
        $.main.query('getRods', {uid: userData.user.uid}, function(a_rods) {
            a_rods.push({id: 'new', name:locale.NEWROD});
            a_rods.push({id: 'import', name:locale.IMPORT});
            rodMenu.setData({
                rods: a_rods,
                save: social.saveMenu(),
                share: social.shareMenu()
            });
            if ($.type(onComplete) == 'function') onComplete(a_rods);
        });
    }
    
    function createRightMenu() {
        var rmprm = {lesson: lesson.menu};
        if (pay_support.menu) rmprm.money = pay_support.menu;
        
        rmenu = $('.menu-right').submenu(treeApp, rmprm);
        
        rmenu.release({
            lesson: function(itm) {
                if (itm.lesson) assistant.setTimeline(itm.lesson); 
                else This[itm.method](itm);
            },
            edit: function() {
                treeApp.tree.toggleMode();
            },
            money: pay_support.menuRelease
        })
    }
    
    this.getTree = function(rod) {
        if (treeApp.rod && (treeApp.rod.rod_id == rod.id)) return;
        else {
            $.main.query('getTree', {id: rod.id}, function(a_tree) {
                Utils.fieldsInt(a_tree[0], 'id,main_id,rod_id,options,haveAvatar');
                Utils.fieldsInt(a_tree[2], 'rod_id,main_id,start_id');
                treeApp.responseDBTree(a_tree[2], a_tree);
            });
        }    
    }
    
    function createLeftMenu(trees) {
        trees.push({id: 'new', name:locale.NEWROD});
        trees.push({id: 'import', name:locale.IMPORT});
    
        rodMenu = $('.menu-left').submenu(treeApp, {
           rods: trees,
           save: social.saveMenu(),
           share: social.shareMenu()
        });
        rodMenu.release({
            rods: function(rod) {
                if (rod.id == 'new') {
                    social.getProfiles([uid], function(suser) {
                        treeApp.createNewTree(social.toPeople(suser[0]));
                    });
                } else if (rod.id == 'import') {
                    treeApp.importFile();                
                } else if (rod.id != treeApp.rod.rod_id) {
                    This.getTree(rod);
                }
            },
            save: social.saveMenuRelease,
            share: social.shareMenuRelease
        });
    } 
    
    $('.family-title span').click(function() {
        if (treeApp.tree.getMode() == MODE_EDIT) treeApp.editTree();
    });
    
    treeApp = new TreeApp({
        share_size: 640
    });
    
    this.startRod = function(a_data) {
        treeApp.responseDBTree(a_data.rod, a_data.tree);
        new panelCreate(treeApp, $('.right_panel'));
        createLeftMenu(a_data.trees);
        createRightMenu();
    }
    
    this.inUser = function() {
        $.main.query('inUser', {uid: uid, id: id}, function(a_data) {
            function checkNullUsers(suser) {
                if (treeApp.rel.getList().length == 0) treeApp.editDialog(social.toPeople(suser[0]));
            }
        
        
            Utils.fieldsInt(a_data, 'balance');
            Utils.fieldsInt(a_data.rod, 'main_id,rod_id,options,start_id');
            Utils.fieldsInt(a_data.user, 'def_rod');  
            Utils.fieldsInt(a_data.trees, 'id,main_id,rod_id,options');
            Utils.fieldsInt(a_data.tree, 'id,rod_id,gender,haveAvatar');
            Utils.fieldsIntA(a_data.prices, 'id,price');
            Utils.fieldsIntA(a_data.wins, 'id,price');
            
            pay_support = new PaySupport(treeApp, !params.nopayed, a_data.balance, a_data.prices, a_data.wins);
            
//            a_data.created = 1; //DEV
            
            social.init(uid, function(suser) {
                if (a_data.created && !parseInt(id)) {
                    treeApp.alert(locale.WELCOMENEWUSER, function() {
                        This.loadDemoTree();
                        setTimeout(function() {
                            assistant.setTimeline(lesson.startTimeline2);
                        }, 1000);
                    }, 'dlg_welcome', function() {
                        checkNullUsers(suser);
                        setTimeout(function() {
                            assistant.setTimeline(lesson.startTimeline);
                        }, 1000);
                    });
                } else checkNullUsers(suser);
            });
            userData = a_data;
            
            
            treeApp.setUser(a_data.user);
            treeApp.on('createnewtree', updateAppMenu);
            treeApp.on('refreshrods', updateAppMenu);
            treeApp.on('deletetree', updateAppMenu);
            
            new vinDragable(treeApp);
            new History($('.history'), treeApp.getCommandManager());
            new Tips(treeApp);
            assistant = new Assistant(treeApp);
            
            if (!social.initCanvas) This.startRod(a_data);
            else social.initCanvas(This, a_data);
        });
    }
    
//PUBLIC
    this.loadDemoTree = function(itm) {
        $.main.query('shareTreeFriends', {uids: userData.user.uid, access: 'show', noclear: 1}, function(a_data) {
            updateAppMenu(function(a_rods) {
                $.each(a_rods, function(i, rod) {
                    if (rod.id == a_data.rod_id) This.getTree(rod); 
                });
            });
        });
    }   
    
    this.inUser(); 
}