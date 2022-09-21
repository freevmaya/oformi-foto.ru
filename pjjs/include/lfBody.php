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
            _cats.setSelected(_app.getGroups() || Cookie.read('group'));
            
            console.log(document.body);
            document.body.fade('in');
            var ctrl = $('categories');
            var bid = (function() {Utils.beat(ctrl);bid=0;}).delay(5000);
            ctrl.addEvent('click', function() {if (bid) clearTimeout(bid)});            
        }
        
        function doLoad() {

            _app = new lfApp(100);
            if (parent.app) _app.afterAdv = parent.app.afterAdv;
            
            byrass.localization(LOCALE);
            
            _cats = new lfpartsList($('categories'));
            _cats.load(TMPLSOURCEURL);
            _cats.addEvent('change', function(groups) {
                _app.setGroups(groups, true);
            });
            _cats.addEvent('load', doStartCatsLoad);
            new colorPanel(_app, $('colorPanel'));
            new dragImplement($$('.head'));
            new lfMenu($('menu'), {
                list: [
                    {text: LOCALE.INSERTPHOTO, onClick:"_doInjectPhoto()", value: APPMODES.EDITOR},
                    {text: LOCALE.COLORPANEL, onClick:"_app.fireEvent(PJEVENTS.SHOWCOLORPANEL, _app._canvas)", value: APPMODES.EDITOR},
                    {text: LOCALE.HELP, onClick:"_app.help()", value: APPMODES.ANY},
                    {text: LOCALE.ABOUT, onClick:"_app.about()", value: APPMODES.ANY}
                ] 
            });
            
            toastInit($$('body')[0]);
        }
        <?=$script?>
    </script>
</head>
<body onload="doLoad();" style="visibility:hidden;opacity:0;" onselectstart="return false;" <?=!$isDev?'oncontextmenu="return false;"':''?>>
<div id="toolbar">
    <div><a id="menu"></a></div>
    <div><a id="saveButton" title="{SAVE}"></a></div>
    <div><a id="back" title="{BACK}"></a></div>
    <div>
        <select id="categories">
            <option selected style="display: none">{SELECTCATEGORY}</option>
        </select>
    </div>
    <div style="clear:both;"></div>
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
    <div id="centerBlock">
        <!--
        <div id="func_panel">
            <div class="fp-m Tips1" rel="tips.MOVEDESC"></div>
            <div class="fp-r Tips1" rel="tips.ROLLDESC"></div>
            <div class="fp-s Tips1" rel="tips.SCALEDESC"></div>
            <div class="fp-c Tips1" rel="tips.COLORDESC"></div>
        </div>
        -->
        <canvas id="canvas"></canvas>
    </div>
    <div id="injectPhoto"><p class="round" onclick="_doInjectPhoto()">{INSERTYOUPHOTO}</p></div>
    <div id="loader"><p id="loaderText" class="round"></p></div>
</div>
<iframe id="textLayer">
</iframe>
<div class="block" style="position: absolute; visibility: hidden;">
    <input type="file" id="openFile" accept="image">
</div>
<?include('include/metrica.php');?>
</body>
