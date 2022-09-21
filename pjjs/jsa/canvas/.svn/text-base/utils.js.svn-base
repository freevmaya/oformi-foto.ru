var Utils = {
    urlrnd: function(url) {
        var url = new URI(url);
        var qdata = url.getData();
        if (!qdata['rnd']) {
            qdata['rnd'] = parseInt(Math.random() * 1024).toString(16);
            url.setData(qdata);
        } 
        return url.toString();
    },
    removeRND: function(url) {
        var uri = new URI(url);
        var data = uri.getData();
        delete(data['rnd']);
        uri.setData(uri);
        return uri.toString();
    },
    cmdurl: function(url1, url2) {
        if (url1 && url2) {
            return this.removeRND(url1) == this.removeRND(url2);
        } else return url1 == url2;
    },
    
    addScript: function(attr) {
		var scripts = document.getElement('head').getElements('script');
		attr.src = Utils.urlrnd(attr.src);
		for (var i in scripts) if (Utils.cmdurl(scripts[i].src, attr.src)) return scripts[i];
		return new Asset.javascript(attr.src, attr);
	},
    
    scaleImage: function(image, scale) {
        var canvas = new Element('canvas', {
            width: image.width * scale,
            height: image.height * scale
        });
        var context = canvas.getContext('2d');
        var mat = new Matrix();
        mat.scale(scale, scale);
        context.antialias = true;
        context.setTransform(mat.a, mat.b, mat.c, mat.d, mat.tx, mat.ty);
        context.drawImage(image, 0, 0);
        return canvas.toDataURL();      
    },
    
    localToGlobal: function(p, elem) {
        var v = new Vector(p);
        while (elem) {
            if (elem.offsetParent) {
                v.x += elem.offsetLeft;
                v.y += elem.offsetTop;
            }
            elem = elem.offsetParent;
        }
        
        return v;
    },
    
    globalToLocal: function(p, elem) {
        var v = new Vector(p);
        while (elem) {
            if (elem.offsetParent) {
                v.x -= elem.offsetLeft;
                v.y -= elem.offsetTop;
            }
            elem = elem.offsetParent;
        }
        
        return v;
    },
    
    rToR: function(p, sourceElem, toElem) {
        return Utils.globalToLocal(Utils.localToGlobal(p, sourceElem), toElem);
    },
    
    isTouchOnly: function() {
        return (Browser.Platform.name=='android') || (Browser.Platform.name=='ios');
    },
    
    baseURL: function(fullURL) {
        return fullURL.replace(/\/[\w\.\-_]+$/i, '')
    },
    
    beat: function(elem, style) {
        if (elem) {
            style = style?style:'margin-left';
            var fx = new Fx();
            fx.set = function(value) {
                sine = Math.sin(value * Math.PI * 2);
                elem.setStyle(style, Math.round(sine * 10));
                return value;
            }  
            fx.start(0, 1);
        }
    }      
};

function trace(msg) {
    alert(msg);
}