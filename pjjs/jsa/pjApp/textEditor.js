var EDITORURL = 'editor/';
var FONTS = 'Arial=Arial,Tahoma,Verbana,helvetica,sans-serif;Beer=Beer;Aaargh=Aaargh;' + 
            'AC Line=AC Line;Adine Kirnberg=Adine Kirnberg;Asia=Asia;Bickham Script Two=Bickham Script Two;' +
            'Boom Boom=Boom Boom;Alexander=Alexander;Anfisa Grotesk=Anfisa Grotesk';

var TextEditor = new Class({
    Extends     : Events,
    ta          : null,
    instance    : null,
    size        : null,
    container   : null,
    initialize  : function(element, size) {
        this.container = element; 
        this.ta = new Element('textarea', {id: 'editorText', html: TextEditor.text});
        this.ta.inject(this.container);
        this.size = size;   
        this.container.setStyle('display', 'block');
        if (!this._checkInitEditor()) TextEditor.loadResources(this._checkInitEditor.bind(this));
    },
    
    _checkInitEditor: function() {
        if (TextEditor.isLoaded()) {
            this._createEditor();
            return true;
        } else return false;
    },
    
    _createEditor: function() {
        var This = this;
        var subh = (this.size.y < 750)?70:40;
        tinymce.init({ 
            body_id     : 'text-editor',
            selector    : 'textarea#editorText',
            language    : 'ru',
            content_css : EDITORURL + 'content.css',
            menubar     : false,
            statusbar   : false,
            toolbar     : "removeformat bold italic | alignleft aligncenter alignright alignjustify | formatselect fontselect fontsizeselect | styleselect | apply",
            body_class  : 'meditor',
            font_formats: FONTS,
            fontsize_formats: '10pt 12pt 14pt 18pt 24pt 36pt 48pt 64pt',
            style_formats: [
                {title: 'Высота строки', items: [
                    {title: 'Малая', inline: 'span', styles: {'line-height': '0.2em'}},
                    {title: 'Нормальная', inline: 'span', styles: {'line-height': '1.0em'}},
                    {title: 'Большая', inline: 'span', styles: {'line-height': '1.8em'}}
                    ]
                }
            ],
            style_formats_merge: true,
            width       : this.size.x,
            height      : this.size.y - subh,
            setup: function (editor) {
                editor.addButton('apply', {
                    text: 'Готово',                    
                    icon: false,
                    onclick: function () {
                        This.apply();
                    }
                });
            },
            init_instance_callback : this._doAfterInit.bind(this)
        });    
    },
    
    apply: function() {
        var html = this.instance.getContent();
        if (html) {
            TextEditor.text = html;
            
            var style = this.instance.getBody().style; 
            var size = this.instance.getBody().clientWidth;
            
            Cookie.write('html-text', html);
            Cookie.write('html-size', size);
            
            TextEditor.htmlToCanvas(html, size, (function(canvas) {
                this._doComplete(html, canvas);
                this.destroy();
            }).bind(this));
        } else {
            this._doComplete(html, null);
            this.destroy();
        }
    },
    
    _doComplete: function(html, canvas) {
        this.fireEvent('apply', {html:html, canvas:canvas});
    },
    
    _doAfterInit: function(editor) {
         this.instance = editor;
    },
    
    destroy: function() {
        this.ta.destroy();
        this.instance.destroy();
        this.container.setStyle('display', 'none');
    }
});


var defText = '<h1><span style="text-align:center">Введите свой текст</span></h1>';
if (ctext = Cookie.read('html-text')) defText = ctext;

TextEditor = Object.merge(TextEditor, {
    text: defText,
    editorLoad: 0,
    htmlToCanvas: function(html, width, doComplete) {
        var result = new Element('div', {'class':'meditor', html: html, styles:{
            width: width,
            margin: '0px auto !important'
        }});
        
        result.inject(document.body);
        
        html2canvas(result, {
            onrendered: function(canvas) {
                result.destroy();
                doComplete(canvas);
            }
        });
    },
    
    restoreTextCanvas: function(doComplete) {
        if (size = Cookie.read('html-size')) {
            TextEditor.loadResources(function() {
                TextEditor.htmlToCanvas(TextEditor.text, size.toInt(), doComplete);  
            });
        } else doComplete(null);
    },
    
    isLoaded: function() {
        return TextEditor.editorLoad == 4;
    },
    
    loadResources: function(onLoadComplete) {
        if (TextEditor.isLoaded()) onLoadComplete();
        else {
            function a_onLoadComplete() {
                TextEditor.editorLoad++;
                if (TextEditor.isLoaded()) onLoadComplete();
            }
            
            Utils.addScript({src: EDITORURL + 'tinymce/tinymce.min.js' + VER,onLoad: a_onLoadComplete});
            Utils.addScript({src: EDITORURL + 'html2canvas/html2canvas.min.js' + VER,onLoad: a_onLoadComplete});
            Asset.css(EDITORURL + 'main.css' + VER, {onLoad: a_onLoadComplete});
            Asset.css(EDITORURL + 'content.css' + VER, {onLoad: a_onLoadComplete});
        }    
    }    
});
