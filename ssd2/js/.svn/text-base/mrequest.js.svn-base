var HttpRequest = {
 
	//
	// Метод get (базируется на script tag транспорте).
	//
 
	get : function(url, params, callback) {
		var process = true,
        sid = 'sid' + parseInt(Math.random()*1000000),
		cb = 'cb=HttpRequest.callback.' + sid,
		script = document.createElement('script');
		script.type = 'text/javascript';
		if(params) {
			var sep = ''; url += "?";
			for(var name in params) {
				url += sep + name + '=' + params[name];
				sep = '&';
			}
		}
		if(url.indexOf('?') == -1) script.src = url + '?' + cb;
		else if(url.match(/\?[\w\d]+/)) script.src = url + '&' + cb;
		else script.src = url + cb;
		HttpRequest.callback[sid] = function(response) {
			process = false;
			callback(response);
		};
		script.onerror = script.onload = script.onreadystatechange = function() {
			if(!this.loaded && (!this.readyState
				|| this.readyState == 'loaded'
				|| this.readyState == 'complete'))
			{
				this.loaded = 1;
				this.onerror = this.onload = this.onreadystatechange = null;
				if(process) { callback(false); } else { /* Ответ пришел */ }
				this.parentNode.removeChild(this);
				delete script;
				delete HttpRequest.callback[sid];
			}
		}
		if(document.getElementsByTagName('head').length) {
			document.getElementsByTagName('head')[0].appendChild(script);
		} else { document.appendChild(script); }
	},
 
	//
	// Метод post (базируется на window.name транспорте).
	//
 
	post : function(url, params, callback) {
		function add2body(html) {
			var b = document.body;
			var div = document.createElement('div');        
			div.innerHTML = html.join ? html.join('') : html;    
			while (div.childNodes.length > 0) b.appendChild(div.childNodes[0]);
			return b.lastChild;
		}
		var form, input, doc = document, fid = 'fid' + parseInt(Math.random()*1000000),
  			html = '<iframe style="display:none" onload="HttpRequest._onLoad(this)"'
		  		+ ' src="javascript:true" id="' + fid + '" name="' + fid + '"></iframe>';
		var frame = add2body(html);            
		HttpRequest.callback[fid] = callback;
		if(params) {
			if(params.nodeType) {
				form = params;
			} else {
				form = document.createElement('form');
				for(var name in params) {
					var value = params[name];
					input = document.createElement('input');
					input.name = name;
					input.value = (typeof(value)=='string')?value.replace(/\n/g, ""):value;			
					form.appendChild(input);
				}
			}
			if(form) {
				form.method = 'post';
				form.action = url;
				form.target = fid;
				form.acceptCharset = 'utf-8';
				form.style.display = 'none';
				document.body.appendChild(form);
				form.submit();
				form.parentNode.removeChild(form);
			}
		} else {
			frame.src = url;
			if(frame.contentWindow) {
				frame.contentWindow.location.replace(url);
			}	
		}
	},
 
	callback : {},
 
	_getData : function(frame) {
		if(frame.abort) return;
		var callback = HttpRequest.callback[frame.id];
		if(callback) {
			try { callback(eval("(" + frame.contentWindow.name + ")")); } catch (ex) {}
			delete HttpRequest.callback[frame.id];
		}
		setTimeout(function() { frame.parentNode.removeChild(frame); }, 0);				
	},
 
	_onLoad : function(frame) {
		var blank = 'about:blank',
			wnd = frame.contentWindow;
		try {    
        	if (!frame.state && (wnd.location == blank
				|| wnd.location == 'javascript:true')) return;
		} catch (ex) {}
		if(frame.state) {
			return this._getData(frame);
		} else wnd.location = blank;
		frame.state = 1;
	}
}