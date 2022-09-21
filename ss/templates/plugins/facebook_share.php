<?
    include(TEMPLATES_PATH.'squeeze_box.php');
    GLOBAL  $root;
    
    $main_url = $this->getLink();
    $desc = $this->getDescription('', 55);
    $ssource = $root->getSocialSource();
?>
<script type="text/javascript">
    var shareToolbar = new Class({
        hmenu: null,
        toolbar: null,
        btn: null,
        initialize: function(addClass, a_onBtClick) {
            this.hmenu = $('hmenu');
            this.hmenu.setStyle('height', '85px');
            if (_ltb = this.hmenu.getElement('.share-toolbar')) {
                this.toolbar = _ltb;
                this.toolbar.empty();
            } else this.toolbar = new Element('div', {'class':'share-toolbar'});
            this.btn = new Element('div', {'id': 'shareTBButton', 'class': 'share-tbbt sb' + addClass, events: {
                click: (function() {
                    if (a_onBtClick) a_onBtClick();
                    this.close();
                }).bind(this)
            }});
            this.btn.inject(this.toolbar); 
            (new Element('div', {'class':'share-text st' + addClass, 'html': '<table><tr><td><?=$desc?></td></tr></table>'})).inject(this.toolbar);
            (new Element('span', {
                'class':'close_button', 
                text:'закрыть это',
                events: {
                    'click': this.close.bind(this)
                } 
            })).inject(this.toolbar);  
            this.toolbar.inject(hmenu);
        },
        close: function() {
            this.toolbar.fade(0);
            (function() {
                this.hmenu.tween('height', 85, 30); 
                this.toolbar.dispose();
            }).delay(500, this);        
        }
    });

<?
if (($ssource == 'vk') || !$ssource) {?>
    (new shareToolbar('vk')).btn.set('html', VK.Share.button({
        image: '<?=$this->getMeta('page-image')?>', 
        title: 'Фоторамки для Вас!',
        description: '<?=$desc?>'
    }, {
        text: '<div class="share-vk">Поделиться</div>',
        type: 'custom'
    }));
//    share_vk();
<?} else if ($ssource == 'fb') {?>
    function share_fb(bt_class, bt_text, on_click) {
        (new Element('div', {'class':bt_class, text: bt_text})).inject((new shareToolbar('fb', on_click)).btn);
    }
  
    window.addEvent('login_status', function(e) {
        function shareButtonInit() {
            share_fb('share-facebook', 'Поделиться в Facebook', function() {
                FB.ui({
                    display     : 'popup',
                    method      : 'share',
                    href        : "<?=$main_url?>"
                }, function(response){})    
            })        
        }
        if (e.source == 'fb') {
            if (e.status) 
                shareButtonInit();
            else app.loadFB(shareButtonInit);
        }
    });
<?} else if ($ssource == 'ok') {?>
    (new shareToolbar('ok')).btn.set('html', '<div id="ok_shareTBButton" style="margin:0 auto;"></div>');
    
    var js = document.createElement("script");
    js.src = "<?=$sheme?>connect.ok.ru/connect.js";
    js.onload = js.onreadystatechange = function () {
        if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
            if (!this.executed) {
                this.executed = true;
                setTimeout(function () {
                    OK.CONNECT.insertShareWidget("ok_shareTBButton", document.URL, "{width:310,height:50,st:'rounded',sz:30,ck:2}");
                }, 0);
            }
        }
    };
    document.documentElement.appendChild(js);    
<?} else if ($ssource == 'mm') {
    $this->scripts[] = 'http://cdn.connect.mail.ru/js/loader.js';
?>
    function mm_shareClick() {
        var newWin = window.open('http://connect.mail.ru/share?url=<?=str_replace("'", "\'", ss::currentURL())?>&screenshot=<?=$this->getMeta('page-image')?>', 
        'Нравиться', "width=420,height=230,menubar=no,location=no,scrollbars=no,status=no");
        newWin.focus();
    }
    (new shareToolbar('mm')).btn.set('html','<span class="share-mm" onclick="mm_shareClick(); return false;">Нравится</span>');
<?}?>                                       
</script>