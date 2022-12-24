/******************************************************************************/
/*********************************** EASING ***********************************/
/******************************************************************************/

( function() {

// Based on easing equations from Robert Penner (http://www.robertpenner.com/easing)

var baseEasings = {};

$.each( [ "Quad", "Cubic", "Quart", "Quint", "Expo" ], function( i, name ) {
	baseEasings[ name ] = function( p ) {
		return Math.pow( p, i + 2 );
	};
} );

$.extend( baseEasings, {
	Sine: function( p ) {
		return 1 - Math.cos( p * Math.PI / 2 );
	},
	Circ: function( p ) {
		return 1 - Math.sqrt( 1 - p * p );
	},
	Elastic: function( p ) {
		return p === 0 || p === 1 ? p :
			-Math.pow( 2, 8 * ( p - 1 ) ) * Math.sin( ( ( p - 1 ) * 80 - 7.5 ) * Math.PI / 15 );
	},
	Back: function( p ) {
		return p * p * ( 3 * p - 2 );
	},
	Bounce: function( p ) {
		var pow2,
			bounce = 4;

		while ( p < ( ( pow2 = Math.pow( 2, --bounce ) ) - 1 ) / 11 ) {}
		return 1 / Math.pow( 4, 3 - bounce ) - 7.5625 * Math.pow( ( pow2 * 3 - 2 ) / 22 - p, 2 );
	}
} );

$.each( baseEasings, function( name, easeIn ) {
	$.easing[ "easeIn" + name ] = easeIn;
	$.easing[ "easeOut" + name ] = function( p ) {
		return 1 - easeIn( 1 - p );
	};
	$.easing[ "easeInOut" + name ] = function( p ) {
		return p < 0.5 ?
			easeIn( p * 2 ) / 2 :
			1 - easeIn( p * -2 + 2 ) / 2;
	};
} );

} )();
//---------END EASING-------------------

$.main = new (function() {
    var This = this;
    
    var toplayer;
    
    this.initialize = function() {
        toplayer = $('#toplayer');
        toplayer.vtrans();
    }
    
    function onError(error) {
        if (error) alert(locale.ERROR + '\n' + (error.statusText?error.statusText:error));
        else alert(locale.WENTWRONG);
    }
    
    this.ajax = function(action, method, formData, onSuccess) {
        This.loading(true);
        $.ajax({
            url: action,
            type: method,
            dataType: "json",
            success: function(data) {
                onSuccess(data);
                This.loading(true);
            },
            error: onError,
            data: formData,
            contentType: false,
            processData: false,
            cache: false
        });         
    }
    
    this.loading = function(show) {
    }
    
    this.toFormData = function(obj) {
        var formData = new FormData();
        $.each(obj, function(key, val) {
            formData.append(key, val);
        });
        return formData;
    }
    
    this.curtain = function(show) {
        if (show) toplayer.vshow();
        else toplayer.vhide(); 
    }
    
    this.query = function(model_method, params, onComplete) {
        setTimeout(function() {
            This.ajax(MODELURL + '?model=' + MODELMODULE + '&format=json&method=' + model_method, 'POST', $.main.toFormData(params), function(a_data) {
                if (onComplete) onComplete(a_data);
            });    
        }, 10);
    }
})();

var TreeApp = function(options){
    var This = this;
    
    $.extend(this, new Events());
    
    this.tree = null;
    this.rel = null;
    this.rod = null;
    this.main_index;
    this.start_index;
    this.user;            
    this.FILEINDENT = 'gt.v.1';           
    
    var commng = new commanManager();
    
    var input = $('<input type="file" style="display: none">');
    var _doInput;
    $('body').append(input);
    input.on('change', function(e) {
        _doInput();
    });
    
    function openFile(onComplete) {
        _doInput = function() {
            onComplete(input[0].files[0])
        };
        input.trigger($.Event('click'));
    }
    
    this.getCommandManager = function() {
        return commng;
    }
    
    this.sendPeopleData = function(item, onComplete) {
        if (!item.rod_id) item.rod_id = this.rod.rod_id;
        if (typeof(item.id) == 'undefined') item.id = 0;
        if (!item.link_uid) item.link_uid = 0;
        var params = Utils.filterClone(item, 'name,family,father,id,link_uid,rod_id,bday,gender,img,haveAvatar');
        if (this.rel.getList().length == 0) params.set_main_id = 1;
        $.main.query('applyPeopleData', params, onComplete);
    }
    
    this.updateRelations = function() {
        This.rel.updateRelations(This.main_index, This.user.uid);
    }
    
    this.Reassign = function() {
        This.tree.assign(This.rel.toTreeFromId(This.tree.getTree().id));
    }
    
    this.sendTreeData = function() {
        var sendData = {};
        var list = This.rel.getList();
        $.each(list, function(i, item) {
            var it = 0;
            if (item.childIds.length > 0) it = item.childIds;
            sendData[item.id] = it;
        });
        if (sendData) {
            $.main.query('updateTree', {list: JSON.stringify(sendData)}, function(a_data) {
                if (a_data.result == 1) {
                }    
            });
        }
    }
    
    this.deleteRod = function() {
        This.alert(locale.QUESTION_DELROD, function() {
            $.main.query('deleteRod', {rod_id: This.rod.rod_id}, function(a_data) {
                if (a_data.result) {
                    This.firstRod();
                    This.trigger($.Event('deletetree'));
                }                
            });
        }, '', null, true);
    } 
    
    this.firstRod = function() {
        $.main.query('getFirstRod', {uid: This.user.uid}, function(a_rod) {
            if (a_rod.rod) {
                rod = a_rod.rod;
                This.responseDBTree(a_rod.rod, a_rod.tree);
            }
        });        
    }
    
    this.vinTitle = function(people) {
    }
    
    this.alert = function(text, onRelease, dlgclass, onCancel, yesBtn) {
        $('.content').dialog({
            message: controlMsg(text)            
        }, function(result) {
            if (onRelease) onRelease();
        }, {
        }, dlgclass?dlgclass:'dlg_alert', onCancel, yesBtn).toCenter().show();
    }
    
    this.dev = function() {
        this.alert(locale.DEVELOPMENT, null, '');
    }
    
    this.friendsDialog = function(onComplete, selected, oneSelect) {
        var find = controlInput('');
        var dlg_list = controlListFriends(selected, function() {
            if (oneSelect) dlg.apply();
        });
        var dlg = $('.content').dialog({
            find: find,
            dlg_list: dlg_list
        }, function(result) {
            onComplete(result.dlg_list);
        }, {
        }, 'dlg_friends').toCenter().show();
        
        var fi = find.elem.find('input'); 
        fi.on('keyup', function(e) {
            dlg_list.filter(fi.val());        
        });
    }
    
    this.editDialog = function(a_item, onComplete, dlg_class) {
        var fio = a_item.name.split(' ');
        var dlg = $('.content').dialog({
            profile: controlProfile(This, function(a_user) {
                var people = social.toPeople(a_user);
                for (var n in dlg.controls) {
                    var ctrl = dlg.controls[n];
                    if (ctrl.fromPeople) ctrl.fromPeople(people);
                }
                a_item.link_uid = people.link_uid;
            }),
            fio: controlFIO({
                family: a_item.family,
                name: a_item.name,
                father: a_item.father
            }),
            bday: controlInput(a_item.bday, 'bday'),
            user_image: controlIMAGE(a_item.img),
            gender: controllCB(a_item.gender, 'gender')
        }, function(result) {
            var item      = a_item.id?This.rel.find(a_item.id):($.extend({}, treeItem));
            item.link_uid = a_item.link_uid;
            item.name     = result.fio.name;
            item.family   = result.fio.family;
            item.father   = result.fio.father;
            item.bday     = result.bday;
            item.gender   = result.gender;
//            item.dname    = item.displayName(item);
            item.haveAvatar = 1;
            if (result.user_image.modified || !item.id) {
                item.img = result.user_image.value;
            } else if ((item.img == DEFAULT_FEMALE_IMAGE) || (item.img == DEFAULT_USER_IMAGE)) {
                item.haveAvatar = 0;
            }
            
            function sendPeopleData() {
                This.sendPeopleData(item, function(result) {
                    if (result.image) item.img = PLIMAGEPATH + result.image;
                    if (result.is_new)  {
                        item.parentIds = [0, 0];
                        item.childIds = [];
                        item.id = result.id;
                        This.rel.addItem(item);
                        if (!This.tree.getTree()) {
                            This.main_index = 0;
                            This.updateRelations();
                            This.tree.assign(This.rel.toTree(This.main_index));
                        }
                    } else {
                        This.rel.updateItem(item);
                    }
                    
                    if (onComplete) onComplete(item);
                });
            }
            
            if (!item.id) pay_support.possibly('NEWPEOPLE', sendPeopleData);
            else sendPeopleData();
            
        }, {
            fio: validatorFIO({
                family: 'Неверно введена фамилия',
                name: 'Неверно введено имя',
                father: 'Неверно введено отчество' 
            }),
            bday: validatorBDAY('Формат даты должен быть 02.01.1989')/*,
            user_image: validatorIMAGE('Выберите фотографию')*/
        }, dlg_class?dlg_class:(a_item.id?'personal_data':'new_persone')).toCenter().show();
    }
    
    this.relDialog = function(item_id) {
        var This = this;
        var item = This.rel.find(item_id);
        var ps = item.parentIds;
        $('.content').dialog({
            cb_child: conrollPCB(item_id, this.rel),
            cb_father: conrollPCB(ps[0], this.rel, 0, item_id),
            cb_mother: conrollPCB(ps[1], this.rel, 1, item_id)
        }, function(result) {
            This.resetParents(result.cb_child, result.cb_father, result.cb_mother);
        }, {
        }, 'dlg_parents').toCenter().show();
    }
    
    this.parseDBTree = function(a_data) {
        function find(list, id) {
            var result = [];
            for (var i=0; i<list.length; i++) {
                if (list[i].pid == id) result.push(list[i].cid)
            }               
            return result;     
        }
        
        var childs = a_data[1];
        var result = [];
        $.each(a_data[0], function(i, item) {
            var chs = [];
            var prs = [];
            Utils.fieldsInt(item, 'id,rod_id,gender');
            item.img = item.haveAvatar?(PLIMAGEPATH + item.id + '.jpg?v=' + item.modified):Utils.defImg(item); 
            for (var i=0; i<childs.length; i++) {
                Utils.fieldsInt(childs[i], 'pid,cid');
                if (childs[i].cid == item.id) prs.push(childs[i].pid);
                if (childs[i].pid == item.id) chs.push(childs[i].cid);
            }
            item.parentIds = prs;
            item.childIds = chs; 
                
            result.push(item);
        });
        
        return result;
    } 
    
    this.isEditPermission = function() {
        return (This.rod.uid == This.user.uid) || (This.rod.access == 'edit');
    }
    
    this.setStartID = function(cid) {
        if (this.rod.start_id != cid) {
            this.rod.start_id = cid;
            $.main.query('setTopID', {uid: this.user.uid, rod_id: this.rod.rod_id,id: this.tree.getTree().id});
            
            this.trigger($.Event('CHANGESTARTID'));
        }
    }
    
    this.setDisplayData = function(data) {
        this.setTitle(this.rod);
        
        if (!this.tree) {
        
            var tv = $('.tree_view');
            this.tree = new treeView(this, tv, $('#content'));
                      
            this.tree.onSelect(function(tview) {
                var sid = tview.getData().id; 
                if (sid != This.tree.getTree().id) {
                    This.getCommandManager().execute(new cmd_treeNavigator(This, sid))
                }       
            });
            
            this.tree.onUpdate(function() {
            });
            
            this.tree.on('ASSIGN', function() {
                if (!This.isEditPermission()) This.tree.setMode(MODE_VIEW);
                var titem = This.tree.getTree();
                if (titem) This.setStartID(titem.id);
            });            
        }                   
        
        setTimeout(function() {
            This.tree.assign(data.tree);  
        }, 100); 
        
        return this.tree;
    }
    
    this.responseDBTree = function(rod, a_tree) {
        this.main_index = 0;
        this.start_index = 0;
        if (a_tree.length > 1) { 
            var ppl = a_tree[0];
            for (var i=0;i<ppl.length; i++)
                if (ppl[i].link_uid == this.user.uid) {
                    this.main_index = i;
                    break;
                }
            
            for (var i=0;i<ppl.length; i++) {
                if ((this.main_index == 0) && (ppl[i].id == rod.main_id)) this.main_index = i;
                if (ppl[i].id == rod.start_id) this.start_index = i;
            }
            $.main.query('setRodDefault', {uid: this.user.uid, rod_id: rod.rod_id});
        }
        if (!This.rel) This.rel = new Relatives(this.parseDBTree(a_tree), rod.options);
        else This.rel.assign(this.parseDBTree(a_tree), rod.options);
        
        this.rod = rod;
        this.trigger($.Event('CHANGEROD'));
        This.updateRelations();
        
        commng.clear();
       
        return this.setDisplayData({
            tree: This.rel.toTree(this.start_index)
        });
    }
    
    this.resetParent = function(c, p) {
        var gender = p.gender;
        if ((p.id != c.id) && (c.parentIds[gender] != p.id) && (p.childIds.indexOf(c.id) == -1)) {
            
            var pl = p.parentIds;
            for (var i=0; i<pl.length; i++) 
                if (pl[i] > 0) {
                    var pt = this.rel.find(pl[i]);
                    var lv = this.rel.findBottom(pt, c.id);
                    if (lv > -1) {
                        p.parentIds[i] = 0;
                        var cl = pt.childIds;
                        for (var n=0; n<cl.length; n++) {
                            if (cl[n] == p.id) pt.childIds.splice(n, 1);
                        }
                    } 
                }
            c.parentIds[gender] = p.id;
            p.childIds.push(c.id);
            
            This.sendTreeData();
            This.updateRelations();
            This.Reassign();
        }
    }
    
    this.resetChild = function(p, c) {
        var gender = p.gender;
        if ((p.id != c.id) && (c.parentIds[gender] != p.id) && (p.childIds.indexOf(c.id) == -1)) {
            this.rel.removeParentLink(c.id);
            //this.rel.removeChildLink(c);
            
            var i=0;
            while (i<c.childIds.length) {
                var ch = this.rel.find(c.childIds[i]);
                if (ch) {
                    var lv = this.rel.findTop(ch, p.id);
                    if (lv > -1) {
                        c.childIds.splice(i, 1);
                        var pl = ch.parentIds;
                        for (var n=0; n<pl.length; n++) {
                            if (pl[n] == c.id) pl[n] = 0;
                        }
                    } else i++;
                } else i++; 
            }      
            c.parentIds[gender] = p.id;
            p.childIds.push(c.id);
            
            This.sendTreeData();
            This.updateRelations();
            This.Reassign();
        }
    }
    
    this.newPeople = function(data, onComplete, dlg_Class) {
        this.editDialog($.extend({
            family: locale.NEWPEOPLE,
            name: locale.NEWPEOPLE,
            father: locale.NEWPEOPLE,
            img: DEFAULT_USER_IMAGE,
            bday: Utils.formatDate(new Date()),
            rod_id: This.rod.rod_id
        }, data), onComplete, dlg_Class);    
    }
    
    this.setTitle = function(rod) {
        if (rod) {
            var ft = $('.family-title span');
            ft.text(rod.name);
            ft.attr('class', rod.access);
            social.getProfiles([rod.uid], function(result) {
                ft.attr('data-title', rod.access=='main'?'':locale.AUTOR.replace('%s', social.item_userName(result[0])));
            });
        }
    } 
    
    this.editTree = function() {
        var This = this;
        $('.content').dialog({
            new_tree: controlInput(this.rod.name),
            vintitle: controllCB(this.rod.options & OPT_VINTITLE)        
        }, function(result) {
            var a_options = result.vintitle;
            $.main.query('updateRod', {name: result.new_tree, rod_id: This.rod.rod_id, options: a_options}, function(a_data) {
                if (a_data.result) {
                    This.rod.options = a_options;
                    This.rod.name = result.new_tree;
                    This.setTitle(This.rod);
                    This.rel.assign(This.rel.getList(), a_options);
                    This.updateRelations();
                    This.Reassign();
                    This.trigger($.Event('refreshrods'));
                }
            });
        }, {
            new_tree: validatorName('Допустимы только буквы и пробел')
        }, 'dlg_new_tree').toCenter().show();
    }
    
    this.createNewTree = function(sitem) {
        var This = this;
        if (this.user.uid) {
            $('.content').dialog({
                new_tree: controlInput(''),
                vintitle: controllCB(0)        
            }, function(result) {
                pay_support.possibly('NEWTREE', function() {
                    $.main.query('newRod', {name: result.new_tree, uid: This.user.uid, options: result.vintitle}, function(a_data) {
                        if (a_data.rod_id) { 
                            This.responseDBTree({rod_id: a_data.rod_id, name: result.new_tree, uid: This.user.uid}, []);
                            This.trigger($.Event('createnewtree'));
                            This.tree.setMode(MODE_EDIT);     
                            This.newPeople(sitem);    
                        }
                    });
                });
            }, {
                new_tree: validatorName('Допустимы только буквы и пробел')
            }, 'dlg_new_tree').toCenter().show();
        } else This.alert(locale.REQUIREAUTH);
    }
    
    this.setUser = function(a_user) {
        this.user = a_user;
    }
    
    this.clearItem = function(item) {
        This.rel.removeLink(item.id);
        This.sendTreeData();
        This.updateRelations();
        This.Reassign();
    }
    
    this.removeItem = function(item) {
        This.alert(locale.REMOVEITEMDEC, function() {
            This.clearItem(item);
            $.main.query('removeItem', {id: item.id}, function(a_data) {
                if (a_data.result == 1) {
                    var list = This.rel.getList();
                    for (var i=0;i<list.length;i++) 
                        if (list[i].id == item.id) {
                            list.splice(i, 1);
                            This.updateRelations();
                            break;
                        }
                    
                }
            });
        }, 'dlg_question', null, true);
    }
    
    this.onCommand = function(e) {
        if (e.command == 'parents') This.resetParent(e.target.getData(), e.item);
        else if (e.command == 'childs') This.resetChild(e.target.getData(), e.item);
        else if ((e.command == 'clearItem') && (e.source == 'tree')) This.clearItem(e.item); 
        else if ((e.command == 'clearItem') && (e.source == 'list')) This.removeItem(e.item);
    }
    
    this.saveToFile = function() {
        var cnt = $('#tree-content');
        //var max = This.rel.getList().
        
        var ccount = This.tree.getElement().find('.persona').length;
        
        var max = Math.min(ccount, 20) / 20;
        
        domtoimage.toJpeg(cnt[0], { 
            quality: 0.80,
            maxsize: Math.round(1000 + 4000 * max)             
        })
            .then(function (dataUrl) {
                pay_support.possibly('SAVETOFILE', null, null, function(onPayComplete) {
                    try {
                        This.saveData(dataUrl, 'jpg');
                        onPayComplete();
                    } catch (e) {
                    }
                });
            }); 
    }
    
    this.saveToText = function() {
        var text = '';
        var list = this.rel.getList();
        
        function outChild(a_item, depth) {
            if (a_item) {
                var pred = '   '.repeat(depth); 
                text += pred + a_item.displayName() + "\n";
                if (a_item.childIds.length > 0) {
                    text += '   '.repeat(depth + 1) + locale.CHILDSTITLE + "\n";
                    $.each(a_item.childIds, function(i, chid) {
                        outChild(This.rel.find(chid), depth + 1);
                    });
                }
            }
        }
        
        $.each(list, function(i, itm) {
            if (!itm.isParents()) outChild(itm, 0);
        });
        this.saveData('data:application/octet-stream;base64,' + Base64.encode(text), 'txt');
    }
    
    this.saveToData = function() {
        var text = '';
        var header = this.FILEINDENT + ';' + this.rod.name;
        var list = this.rel.getList();
        $.each(list, function(i, itm) {text += itm.text() + "\n";});
        this.saveData('data:application/octet-stream;base64,' + Base64.encode(header + "\n" + text), 'txt');
    }
    
    this.saveData = function(value, ext) {
        var link = document.createElement('a');
        var d = new Date();
        link.download = 'generic-tree' + d.toLocaleDateString() +  '.' + ext;
        link.href = value;
        link.click();    
    }
    
    this.importFile = function() {
        openFile(function(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var s = e.target.result;
                var data = Base64.decode(s.substr(s.indexOf(',') + 1));
                var lines = data.split("\n");
                if (lines[0].indexOf(This.FILEINDENT) == 0) {
                    $.main.query('import', {
                        uid: This.user.uid,  
                        data: data
                    }, function(a_result) {
                        if (a_result.rod_id) This.alert(locale.IMPORTSUCCESS);
                        else This.alert(locale.IMPORTERROR);
                        This.trigger($.Event('refreshrods'));
                    });
                } else This.alert(locale.UNKNOWNFORMAT);
            }
            reader.readAsDataURL(file);
        });
    }

    this.toClipboard = function(elem) {
        let toclipboard = $(elem).parent().find(".toclipboard");
        if (toclipboard.length > 0) {
            toclipboard.select();
            document.execCommand("copy");
        }
    }
    
    this.shareTree = function() {
        var url = SHAREURL.replace('%s', This.rod.rod_id);  
        This.alert(locale.SHARECOMPLETE.replace('%s', url) + 
            "<button class\"copytobuffer\" onclick=\"treeApp.toClipboard(this);\">" + locale.TOCLIPBOARD + "</button>");
        /*
        domtoimage.toCanvas(cnt[0], {maxsize: options.share_size || 300})
            .then(function(canvas) {
                var shparam = This.rod.rod_id;
                var titem = This.tree.getTree();
                if (titem) shparam += '-' + titem.id;
                 
                var params = {
                    id: shparam,
                    image: canvas.toDataURL()
                }
                $.main.query('shareTree', params, function(a_data) {
                    var url = SHAREURL.replace('%s', shparam);  
                    This.alert(locale.SHARECOMPLETE.replace('%s', url));
                });
            });
        */
    }
    
    this.shareToFriend = function(editmode) {
        if (!editmode || this.isEditPermission()) {
            $.main.query('getShareTreeFriends', {id: This.rod.rod_id}, function(a_data) {
                This.friendsDialog(function(select) {
                    var params = {
                            id: This.rod.rod_id,
                            uids: Utils.uidsToStr(select),
                            access: editmode?'edit':'show'
                        }
                    $.main.query('shareTreeFriends', params, function(a_data) {
                        social.shareTree(This.rod.rod_id, This.tree.getTree().id, select);
                    });
                }, Utils.uids(Utils.fieldsInt(a_data.users, ['uid'])));
            });
        } else This.alert(locale.NOACCESS);
    }
    
    this.showSupport = function() {
        This.alert(locale.SUPPORTDESC.replace('%s', This.user.uid));
    }
    
    this.on('item_command', this.onCommand);
    
    this.on('add-parentRequire', function(e) {
        var pt = e.getData();
        This.newPeople($.extend({family: pt.family}, locale.DEFPARENT), function(item) {
            This.resetParent(pt, item);
        }, 'dlg-parent');
    });
    
    this.on('add-childRequire', function(e) {
        var chl = e.getData();
        This.newPeople($.extend({family: chl.family}, locale.DEFCHILD), function(item) {
            This.resetChild(chl, item);
        }, 'dlg-child');
    });
    
    this.on('ieditRequire', function(e) {
        This.editDialog(e.getData());
    }); 
    
    this.on('ideleteRequire', function(itm) {
        This.clearItem(itm.getData());
    });
}