    <script stype="text/javascript">
        var _app, _cats;
        var _loadCount = 0;
        var DATA_MODEL = '<?=$dataModel?>';
        var LANGUAGE = '<?=$language?>';       
        
        //MOVECTRLEVENT['down'] = MOUSE_EVENTS.MOUSEDOWN;
        
        function _doInjectPhoto() {
            _app.openImageDialog();
            return false;
        }         
        
        function doStartCatsLoad() {
            _cats.removeEvent('load', doStartCatsLoad);
            _cats.setSelected(_app._groups?_app._groups:Cookie.read('group'));
            
            document.body.fade('in');
            var ctrl = $('categories');
            var bid = (function() {Utils.beat(ctrl);bid=0;}).delay(5000);
            ctrl.addEvent('click', function() {if (bid) clearTimeout(bid)});            
        }
        
        function doLoad() {

            _app = new leftPJApp(100);
            byrass.localization(LOCALE);
            
            _cats = new partsList($('categories'));
            _cats.load(TMPLSOURCEURL);
            _cats.addEvent('change', function(groups) {
                _app.setGroups(groups);
            });
            _cats.addEvent('load', doStartCatsLoad);
/*    
            $('canvas').addEvent('pointerdown', function(e) {
                alert(e);
            });;
*/
            new clickPanel($('func_panel'), _app);
            new colorPanel(_app, $('colorPanel'));
            new dragImplement($$('.head'));
            
            toastInit($$('body')[0]);
            
//---VIDOOM ADV------------
            (function() {                
                var vd = $('viboom');
                var pl, cht, canclose = false, after, tsec = vd.getElement('.viboom-title span'), ADVSHOWSEC = 16;
                
                function close() {
                    if (canclose) {
                        vd.removeClass('vdshow');
                        if (pl) pl.dispose();
                        if (cht) clearInterval(cht);
                        after();
                    } 
                }
                
                function getv() {
                    return vd.getElement('div.rtp-wrapper');
                }
                
                _app.showAdv = function(a_after) {
                    after = a_after;
                    if (pl = getv()) {
                        vd.addClass('vdshow');
                        var sz = vd.getSize();
                        pl.setStyle('margin-top', Math.round((sz.y - pl.getElement('div').getSize().y)/ 2));

                        var sec = ADVSHOWSEC;                        
                        cht = setInterval(function() {
                            if (!(pl = getv())) close();
                            sec--;
                            if (sec >= 0) {
                                tsec.set('text', sec);                                
                                if (sec == 0) {
                                    canclose = true;
                                    vd.addClass('canclose');
                                }
                            }
                        }, 1000);
                    } else after();
                }
                
                vd.addEvent('click', close);
            })();         
        }                                                                                                                                  
        
        <?=$script?>
    </script>
    <link href="styles/styles-viboom.css" rel="stylesheet" />
</head>
<body onload="doLoad();" style="visibility:hidden;opacity:0;" onselectstart="return false;" <?=!$isDev?'oncontextmenu="return false;"':''?>>
<div id="viboom">
    <script type='text/javascript' id="s-8408dfd9bab7aca3">!function(t,e,n,o,a,c,s){t[a]=t[a]||function(){(t[a].q=t[a].q||[]).push(arguments)},t[a].l=1*new Date,c=e.createElement(n),s=e.getElementsByTagName(n)[0],c.async=1,c.src=o,s.parentNode.insertBefore(c,s)}(window,document,"script","//puipui.ru/player/","vbm"); vbm('get', {"platformId":32448,"format":2,"align":"top","width":"550","height":"350","sig":"8408dfd9bab7aca3"});</script>
    <div class="viboom-title">{ADVTITLE}</div>
    <div class="viboom-footer"><a>{CLOSE}</a></div>
</div>
<div id="toolbar">
    <div class="cat-l">
    </div>
     <div class="cat-c">
        <select id="categories">
            <option selected="selected" style="display: none">{SELECTCATEGORY}</option>
        </select>
    </div>
    <div class="cat-r">
    </div>
</div>
<div id="editor"></div>
<?=$body?>
<div class="toolPanel" id="colorPanel">
    <a class="close"></a>
    <h3 class="head">{COLORPANEL}</h3>
    <div class="advanced slider grayscale">
      <div class="knob Tips1" rel="tips.GRAYSCALE"></div>
    </div>
    <div class="advanced slider bright">
      <div class="knob Tips1" rel="tips.BRIGHT"></div>
    </div>
    <div class="advanced slider contrast">
      <div class="knob Tips1" rel="tips.CONTRAST"></div>
    </div>
    <div class="advanced slider red">
      <div class="knob Tips1" rel="tips.RED"></div>
    </div>
    <div class="advanced slider green">
      <div class="knob Tips1" rel="tips.GREEN"></div>
    </div>
    <div class="advanced slider blue">
      <div class="knob Tips1" rel="tips.BLUE"></div>
    </div>
    <a class="button">{RESET}</a>
</div>
<div id="listWindow">
    <div id="listLayer">
    </div>
</div>
<div id="wrapper">
    <div id="shareButtons" class="buttons">
        <?=$shareButtons;?>
    </div>
    <div class="buttons" style="bottom: 0px">
        <a id="saveButton" accesskey="s" class="Tips1" rel="tips.SAVETOFILE" title="titles.HINT"></a>
        <a id="question" href="help.php" target="_blank" class="Tips1" rel="tips.CANVASHELP" title="titles.HINT"></a>
    </div>
    <div id="centerBlock">
        <div id="func_panel">
            <div class="fp-m Tips1" rel="tips.MOVEDESC"></div>
            <div class="fp-r Tips1" rel="tips.ROLLDESC"></div>
            <div class="fp-s Tips1" rel="tips.SCALEDESC"></div>
            <div class="fp-c Tips1" rel="tips.COLORDESC"></div>
        </div>
        <canvas id="canvas"></canvas>
    </div>
    <div id="injectPhoto"><p class="round" onclick="_doInjectPhoto()">{INSERTYOUPHOTO}</p></div>
    <div id="loader"><p id="loaderText" class="round"></p></div>
</div>
<div class="block" style="position: absolute; visibility: hidden;">
    <input type="file" id="openFile" accept="image">
</div>
<?include('include/metrica.php');?>
</body>