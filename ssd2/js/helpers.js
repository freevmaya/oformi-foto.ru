Element.implement({
    $: function(selector) {
        return this.getElement(selector);
    },
    
    $$: function(selector) {
        es = this.getElements(selector);
        return es?es:[];
    },
    
    tw: function(prop, val) {
        this.tween(prop, this.getStyle(prop), val);
    },
    
    addInt: function(addValue) {
        this.set('text', this.get('text').toInt() + addValue);
    },
    
    containsAll: function(elem) {
        return this.contains(elem) || (elem.getParents().indexOf(this) > -1);
    }
});

window.addEvent('domready', function() {          
    initMagnets.delay(500);
    
	$$('.tipz').each(function(element, index) {
        if (title = element.get('title')) {
    		var content = title.split('::');
    		element.store('tip:title', content[0]);
    		element.store('tip:text', content[1]);
        }
	});
             
	var tipz = new Tips('.tipz',{
		className : 'tipz',
		hideDelay: 50,
		showDelay: 50
	});
});

function initMagnets() {
    var bottom = $('bodyArea').getCoordinates().bottom;
    $$('.magnetBottom').each(function(elem) {                    
        var rect = elem.getCoordinates();
        function updatePos() {
            var ny = window.getSize().y + window.getScroll().y - rect.height;
            if (ny < bottom - rect.height) elem.setStyle('top', ny + 'px');
        }
        elem.setStyles({
            position: 'absolute'
        });
        
        updatePos();
        window.addEvents({
            scroll: updatePos,
            resize: updatePos                    
        });     
    });

    $$('.magnetTop').each(function(elem) {         
        var rect = elem.getCoordinates();
        function updatePos() {
            var b = window.getSize().y + window.getScroll().y;
            if (b > rect.bottom) {
                if (b < bottom)
                    elem.setStyle('margin-top', b - rect.bottom);
            } else elem.setStyle('margin-top', 0);
        }
        updatePos();
        window.addEvents({
            scroll: updatePos,
            resize: updatePos                    
        });
    });  
}        

function translit(str) {
    var arr = {
        'а' : 'a',   'б' : 'b',   'в' : 'v',
        'г' : 'g',   'д' : 'd',   'е' : 'e',
        'ё' : 'yo',   'ж' : 'zh',  'з' : 'z',
        'и' : 'i',   'й' : 'j',   'к' : 'k',
        'л' : 'l',   'м' : 'm',   'н' : 'n',
        'о' : 'o',   'п' : 'p',   'р' : 'r',
        'с' : 's',   'т' : 't',   'у' : 'u',
        'ф' : 'f',   'х' : 'h',   'ц' : 'c',
        'ч' : 'ch',  'ш' : 'sh',  'щ' : 'sch',
        'ь' : '\'',  'ы' : 'y',   'ъ' : '\'\'',
        'э' : 'e\'',   'ю' : 'yu',  'я' : 'ya',
        
        'А' : 'A',   'Б' : 'B',   'В' : 'V',
        'Г' : 'G',   'Д' : 'D',   'Е' : 'E',
        'Ё' : 'Yo',   'Ж' : 'Zh',  'З' : 'Z',
        'И' : 'I',   'Й' : 'J',   'К' : 'K',
        'Л' : 'L',   'М' : 'M',   'Н' : 'N',
        'О' : 'O',   'П' : 'P',   'Р' : 'R',
        'С' : 'S',   'Т' : 'T',   'У' : 'U',
        'Ф' : 'F',   'Х' : 'H',   'Ц' : 'C',
        'Ч' : 'Ch',  'Ш' : 'Sh',  'Щ' : 'Sch',
        'Ь' : '-',  'Ы' : 'Y',   'Ъ' : '-',
        'Э' : 'E\'',   'Ю' : 'Yu',  'Я' : 'Ya'
    }
     
    var replacer=function(a){return arr[a]||a};
    return str.replace(/[А-яёЁ]/g,replacer)
}

function removeSheme(url) {
    return url.substr(url.indexOf('://') + 3);
}


function parseLink(text) {
    var reg =  /(((http:\/\/)|(https:\/\/))+([^\s$]|[.])+)/igm;
    pregMatch = text.match(reg);
    return text.replace(reg, function(s){
        var str = (/:\/\//.exec(s) === null ? "http://" + s : s );
        return "<a href=\""+ str + "\">" + str /*s*/ + "</a>"; 
    });
}

function parseEmail(text) {
    var reg =  /([a-z0-9\-_]+@[a-z0-9\-_]+\.[a-z]+)/igm;
    pregMatch = text.match(reg);
    return text.replace(reg, function(s){
        var str = (/:\/\//.exec(s) === null ? "mailto:" + s : s );
        return "<a href=\""+ str + "\">" + s + "</a>"; 
    });
}

function parseText(text) {
    return parseLink(parseEmail(text))
}

function empty(text) {
    return text?text:'';
}