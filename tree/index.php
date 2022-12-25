<?
    $source = isset($_GET['source'])?$_GET['source']:'cap';
    $ispaysupport = !isset($_GET['nopayed']);
    
    include_once('data/config/tree_config_'.$source.'.php');
    
    $v = @$_GET['dev']?rand(1, 10000000):12;
    $uid = isset($_GET['uid'])?$_GET['uid']:DEFUID;
    $lang = isset($_GET['lang'])?$_GET['lang']:DEFLANG;
    $id = @$_GET['id'];
    
    $desc = 'Древо жизни. Генеологическое дерево моей семьи.';
    $title = 'Генеологическое дерево';
    
    $image = SHAREIMAGEURL.'default.jpg';
    
    $share_image = SHAREIMAGEURL.$id.'.jpg';

    if ($id && file_exists($share_image)) $image = $share_image;
    
    $scripts = array('theme/js/jquery-1.12.4.js', 'theme/language/'.$lang.'/locale.js', 'theme/language/'.$lang.'/'.$source.'.js');
    
    $ilist = file_get_contents('data/ilist.json');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru" dir="ltr" prefix="og: http://ogp.me/ns#">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?=$desc?>" />
    <meta name="keywords" content="<?=$desc?>" />
    <meta name="viewport" content="width=device-width, initial-scale=0.5">    
    <title><?=$title?></title>
<?
    include_once('include/'.$source.'.php');
    
    foreach ($scripts as $script) 
        echo('<script src="'.$script.'?v='.$v.'"></script>');
?>    
    
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="<?=$title?>" />
    <meta property="og:image" content="<?=$image?>"/>
    <meta property="og:title" content="<?=$title?>"/>
    <link rel="image_src" href="<?=$image?>" />
    <meta name="page-image" content="<?=$image?>"/>
    
    <style type="text/css">
        <?require('include/'.$source.'.css');?>
    </style>
    
    
    <script type="text/javascript">
        var tree, rel, treeApp, loadinfo, PAYVARS;
        
<?
    if ($_SERVER['HTTP_HOST'] == 'oformi-foto.ru') {
?>
        var APPID = { ok: 'CBAODNHIABABABABA', vk: 3323857, fb: '1626953174227585', mm: { id: '588137', key: 'dfc1eb613808539c17d80fbbf6af11ad' } }<?    
    } else {
?>
        var APPID = { ok: 'CBAODNHIABABABABA', vk: 3323857, fb: '1652843278305241', mm: { id: '588137', key: 'dfc1eb613808539c17d80fbbf6af11ad' } }<?    
    };
?>        
        
        
        var MODELURL = '<?=MODELURL?>';
        var MODELMODULE = '<?=MODELMODULE?>';
        var PLIMAGEPATH = '<?=PEOPLEIMAGEURL?>';
        var SHAREURL = '<?=SHAREURL?>';    
        var PAYVARS = <?=PAYVARS?>;
        
        var ldo = new (function(v) {
            var scInd = 1;
            var loaded = 0;
            var loadcount = 0;
            var This = this;
            
            this.isLoaded = function() {
                return loaded == loadcount;
            }
            
            this.refreshPercent = function() {
                if (loadinfo) 
                    loadinfo.find('span').text(Math.round(loaded / loadcount * 100) + '%');
            }
            
            this.loadScript = function(url, onComplete) {
                loadcount++;
                (function(d, s){
                    var js, fjs = d.getElementsByTagName(s)[0];
                    js = d.createElement(s); js.id = 'sc_i' + scInd; js.src = url + '?v=' + v;
                    js.onload = function(e) {
                        loaded++;
                        This.refreshPercent();
                        if (onComplete) onComplete(js);
                        if (This.isLoaded() && This.complete) This.complete();
                    }
                    scInd++;
                    fjs.parentNode.insertBefore(js, fjs);
                })(document, 'script');
            }
            
            this.loadStyle = function(url, onComplete, media="screen") {
                loadcount++;
                (function(d, s){
                    var css, fcss = d.getElementsByTagName(s)[0];
                    css = d.createElement(s); 
                    css.rel = 'stylesheet'; 
                    css.media = media;
                    css.type = 'text/css'; 
                    css.href = url + '?v=' + v;
                    css.onload = function(e) {
                        loaded++;
                        This.refreshPercent();
                        if (onComplete) onComplete(css);
                        if (This.isLoaded() && This.complete) This.complete();
                    }
                    scInd++;
                    fcss.parentNode.insertBefore(css, fcss);
                })(document, 'link');                
            }
                                    
            this.imgToCache = function(url) {
                loadcount++;
                (function(d, s){
                    img = d.createElement(s); img.id = 'img_' + scInd; img.src = url;
                    img.onload = function(e) {
                        loaded++;
                        This.refreshPercent();
                        if (This.isLoaded() && This.complete) This.complete();
                    }
                    scInd++;
                })(document, 'img');
            }
            
            this.loadScripts = function(list) {
                for (var i=0; i<list.length; i++) 
                    if (list[i]) This.loadScript(list[i]);                            
            }
            
            this.loadStyles = function(list) {
                for (var i=0; i<list.length; i++) 
                    if (list[i]) This.loadStyle(list[i]);                            
            } 
            
            this.loadImages = function(pathURL, list) {
                for (var i=0; i<list.length; i++) 
                    if (list[i]) This.imgToCache(pathURL + list[i]);                            
            }
        })(<?=$v?>);
        
        var slist = 
        [
        'theme/js/jcanvas.min.js'
        ,'theme/js/vector.js'
        ,'theme/js/controls.js'
        ,'theme/js/tree_view.js'
        ,'theme/js/validator.js' 
        ,'theme/js/main.js'
        ,'theme/js/right-panel.js'  
        ,'theme/js/dom-to-image.js'
        ,'theme/js/pay-support.js'
        ,'theme/js/history.js'  
        ,'theme/js/socials/<?=$source?>.js'  
        ,'theme/js/socials/def_canvas.js'  
        ,'theme/language/<?=$lang?>/lesson.js'  
        ];
        
        var ilist = <?=$ilist?>;    
        
        ldo.loadScripts(slist);
        ldo.loadImages('theme/tree/images/', ilist);
        ldo.loadStyle('theme/tree/styles.css');
        ldo.loadStyle('theme/tree/mobile.css', null, '(max-width: 840px)');
        ldo.loadStyle('theme/tree/vin.css');
        
        var dom_ready = false;
        function checkLoaded() {
            if (ldo.isLoaded() && dom_ready) {
                $('#page').css('display', 'block');
                loadinfo.remove();
                new canvasTree('<?=$uid?>', '<?=$id?>');   
                //window.asddddd();
            }
        }

        $(window).on('error', function(ev) {
            
            var e = ev.originalEvent;
            if (e.filename) {
                var fn = e.filename.split('?')[0].split('/');
                var line = fn[fn.length - 1] + ': ' + e.lineno + '/' + e.colno + ' - ' + e.message;
                $.main.query('addJSError', {line: line, uid: treeApp.user?treeApp.user.uid:'<?=$uid?>', browser: $.browser});
            } 
            return true;
        });        
        
        ldo.complete = checkLoaded;
        
        $(window).ready(function() {
            dom_ready = true;
            loadinfo = $('#loadinfo');
            checkLoaded();
        });      
    </script>
</head>
<body onselectstart="return false;">
    <table id="loadinfo">
        <tr><td><span></span></td></tr>
    </table>
    <div id="page">
        <div id="wrapper">
            <div id="content"<?=$uid?"":' class="noauth"'?>>    
                <div class="history">
                    <span><a class="button hint back" data-button="back" title="back"></a></span>
                    <span><a class="button hint forward" data-button="forward" title="forward"></a></span>
                </div>
                <div id="tree-content">
                    <div class="frame">
                        <div class="back"></div>
                        <div class="tree"></div>
                        <div class="frame-top">
                            <div class="frame-tc">
                                <div class="frame-title"></div>
                            </div>
                            <div class="frame-tr"></div>
                        </div>
                        <div class="frame-center">
                            <div class="frame-cl"></div>
                            <div class="frame-cr"></div>
                        </div>  
                        <div class="frame-bottom">
                            <div class="frame-bc"></div>
                            <div class="frame-br"></div>
                        </div>
                    </div>
                    <div class="family-title hint" title="ftitle" data-ifhint="app.tree.getMode()==MODE_EDIT"><span></span></div>
                    <div class="tv_container">                    
                        <div class="tree_view"></div>
                    </div>                    
                </div>
                <div class="menu menu-left">
                    <div class="flg button hint" data-id="rods" title="rods"></div>
                    <div class="save button hint" data-id="save" title="save"></div>
                    <div class="share button hint" data-id="share" title="share"></div>
                    <div class="submenu round"></div>
                </div>
                <div class="menu menu-right">
                    <div class="edit button hint assist" data-id="edit" title="edit"></div> 
                    <div class="lesson button hint" data-id="lesson" title="lesson"></div>
                    <div class="money button hint" data-id="money" title="money"></div>
                    <div class="submenu round"></div>
                </div>
            </div>
            <div class="right_panel">
                <div class="edit_panel">
                    <div class="inline">
                        <a class="button btn-add hint" data-button="add" title="add_persone"></a>
                    </div>
                    <div class="list_wrapper">
                        <div class="edit_close"><div></div></div>
                        <div class="pl_list_cont hint" title="ipersone"> 
                            <div class="pl_list">
                            </div>
                        </div>
                    </div> 
                    <div class="footer">
                        <a class="button btn-trash hint" data-button="delete" title="trash"></a>
                    </div>      
                </div>
            </div>
        </div>
        <div id="toplayer">
        </div>
        <div id="templates">
            <div class="uv_buttons focus_panel">
                <a class="button sbtn hint add-child" data-button="sadd" data-event="add-child" title="sadd"></a>
                <div class="center">
                    <a class="button dlgbtn hint" data-button="iedit" title="iedit"></a>
                    <a class="button dlgbtn hint" data-button="idelete" title="idelete"></a>
                </div>
                <a class="button sbtn hint add-parent" data-button="sadd" data-event="add-parent" title="padd"></a>
            </div>
            <div class="uv_buttons drop_panel">
                <a class="button dlgbtn" data-button="childs"></a>
                <a class="button dlgbtn" data-button="parents"></a>
            </div>
            <div class="assistant">
                <div class="arrow"></div>
                <div class="page">
                    <div class="hclose button" data-button="hclose"></div>
                    <div class="text"></div>
                </div>
            </div>
            <div class="persona vin-sv">
                <div class="vin_img_cont">
                    <div class="crop">
                        <img class="vin_image">
                    </div>
                </div>
                <div class="vin">
                    <div class="person_name">
                    </div>
                    <div class="person_type"></div>
                </div>           
                <div class="ppl_up">
                </div>            
                <div class="ppl_down">
                </div>
            </div>
            <div class="dialog">
                <div class="vm_view">
                    <div class="vm_text"><p></p></div>
                </div>
                <div class="title">
                    <div class="title_text"></div>
                    <a class="button close" data-button="close"></a>
                </div>
                <div class="dlg_content">
                </div>
                <div class="buttons">
                    <a class="button dlgbtn yes" data-button="yes"></a>
                    <a class="button dlgbtn apply" data-button="apply"></a>
                    <a class="button dlgbtn" data-button="cancel"></a>
                </div>
            </div>
            <div class="profile">
                <a class="button profile hint" data-button="profile" title="profile"></a>
            </div> 
            <div class="fio">
                <input type="text" name="family" class="family">
                <input type="text" name="name" class="name">
                <input type="text" name="father" class="father">
            </div>                
            <div class="new_tree">
                <input type="text" name="new_tree">
            </div>                
            <div class="bday">
                <input type="text" name="bday">
            </div>                 
            <div class="find">
                <input type="text" name="text">
            </div>                 
            <div class="message">
                <div>
                    <p></p>
                </div>
            </div>
            <div class="dlg_list">
                <div class="container">
                    <div class="list">
                    </div>
                </div>
            </div> 
            <div class="friend_item">
                <div class="container">
                    <div class="img"></div>
                    <span></span>
                </div>
            </div>
            <div class="user_image dlg-image">
                <input type="file">
                <canvas></canvas>
                <img src="">
                <div class="image_frame"></div>
                <div class="imglib">
                    <div class="list">
                    </div>
                </div>
                <div class="buttons">
                    <a class="button" data-button="imglib"></a>
                    <a class="button" data-button="imgload"></a>
                </div>
            </div>
            <div class="gender frame_small btn-cb">
                <a class="button" data-button="gender_male"></a>
                <a class="button" data-button="gender_female"></a>
            </div>
            <div class="vintitle btn-cb">
                <a class="button" data-button="rel"></a>
                <a class="button" data-button="bday"></a>
            </div>
            <div class="combobox cb_child">
                <div class="label"></div>
                <div class="cb_image">
                    <canvas></canvas>
                    <img>
                    <div class="image_frame"></div>
                </div>
                <a class="button dlgbtn" data-button="combobox"></a>
                <div class="cb-list"></div>
            </div> 
            <div class="combobox cb_mother">
                <div class="label"></div>
                <div class="cb_image">
                    <canvas></canvas>
                    <img>
                    <div class="image_frame"></div>
                </div>
                <a class="button dlgbtn" data-button="mother"></a>
                <div class="cb-list"></div>
            </div> 
            <div class="combobox cb_father">
                <div class="label"></div>
                <div class="cb_image">
                    <canvas></canvas>
                    <img>
                    <div class="image_frame"></div>
                </div>
                <a class="button dlgbtn" data-button="father"></a>
                <div class="cb-list"></div>
            </div>
            <div class="tips">
                <div class="tip-top"></div>
                <div class="tip">
                    <div class="tip-title"></div>
                    <div class="tip-text"></div>
                </div>
                <div class="tip-bottom"></div>
            </div>
            <div class="win_ind"></div>
            <div class="pay_ind"></div>
            <div class="payment">
                <a class="button" data-button="pay100" data-event="100"></a>
                <a class="button" data-button="pay60" data-event="60"></a>
                <a class="button" data-button="pay30" data-event="30"></a>
            </div>
        </div>
    </div>    
</body>
</html>