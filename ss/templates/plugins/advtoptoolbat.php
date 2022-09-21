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
        initialize: function(a_onBtClick) {
            this.hmenu = $('hmenu');
            this.hmenu.setStyle('height', '85px');

            if (_ltb = this.hmenu.getElement('.share-toolbar')) {
                this.toolbar = _ltb;
                this.toolbar.empty();
            } else this.toolbar = new Element('div', {'class':'share-toolbar'});

            (new Element('div', {'class':'adv', 'html': '<img src="">'})).inject(this.toolbar);
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

    (new shareToolbar()).btn.set('html', ()=>{});
</script>