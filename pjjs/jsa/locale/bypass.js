var byrass = {
    localization: function(locale) {
        byrass.parseChild(document.getElement('body'), locale);
    },
    
    parseProp: function(elem, locale, prop) {
        var value = elem.get(prop);
        if (value) {
            var v = value.trim().match(/^{([\w\-\.]+)}$/gi);
            if (v) elem.set(prop, locale[v[0].substr(1, v[0].length - 2)]);
        }
    },
    
    parseChild: function(elem, locale) {
        elem.getChildren().each(function(elem) {
            byrass.parseProp(elem, locale, 'html');
            byrass.parseProp(elem, locale, 'rel');
            byrass.parseProp(elem, locale, 'title');
            byrass.parseProp(elem, locale, 'alt');
            byrass.parseChild(elem, locale);
        });
    }
};
