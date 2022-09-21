    <script stype="text/javascript">
        var _app;
        var DATA_MODEL = '<?=$dataModel?>';
        var LANGUAGE = '<?=$language?>';
        
        function _doInjectPhoto() {
            _app.openImageDialog();
            return false;
        }         
        
        function doTemplateComplete() {
            $$('.view').fade('in');
            _app._canvas.removeEvent(PJEVENTS.COMPLETE, doTemplateComplete);
        }
        
        function doLoad() {
            _app = new leftPJApp(100);
            if (parent.app) _app.afterAdv = parent.app.afterAdv;
            _app._canvas.addEvent(PJEVENTS.COMPLETE, doTemplateComplete);
            byrass.localization(LOCALE);
            new clickPanel($('func_panel'), _app);
            new colorPanel(_app, $('colorPanel'));
            new dragImplement($$('.head'));
            
            toastInit($$('body')[0]);       
        }
        
        <?=$script?>
    </script>
</head>
<body onload="doLoad();" onselectstart="return false;" <?=!$isDev?'oncontextmenu="return false;"':''?>>
<div id="view">
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
    <div id="wrapper">
        <!--<div id="shareButtons" class="buttons">
            <?=$shareButtons;?>
        </div>-->
        <div class="buttons" style="bottom: 0px">
            <a id="saveButton" accesskey="s" class="Tips1" rel="tips.SAVETOFILE" title="titles.HINT">&#128190;</a>
            <!--<a id="question" href="help.php" target="_blank" class="Tips1" rel="tips.CANVASHELP" title="titles.HINT"></a>-->
        </div>
    
        <a id="saveButton" accesskey="s" href="#" class="Tips1" rel="tips.SAVETOFILE" title="titles.HINT" type="image/jpeg" download></a>
        <div id="centerBlock">
            <div id="func_panel">
                <div class="fp-m Tips1" rel="tips.MOVEDESC"></div>
                <div class="fp-r Tips1" rel="tips.ROLLDESC"></div>
                <div class="fp-s Tips1" rel="tips.SCALEDESC"></div>
                <div class="fp-c Tips1" rel="tips.COLORDESC"></div>
            </div>
            <canvas id="canvas" width="100%" height="100%"></canvas>
        </div>
        <div id="injectPhoto"><p class="round" onclick="_doInjectPhoto()">{INSERTYOUPHOTO}</p></div>
        <div id="loader"><p id="loaderText" class="round"></p></div>
    </div>
    <div class="block" style="position: absolute; visibility: hidden;">
        <input type="file" id="openFile" accept="image">
    </div>
</div>
<?include(dirname(__FILE__).'/metrica.php')?>
</body>