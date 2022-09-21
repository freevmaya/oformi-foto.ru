function popupViewCreate(data) {
	var DAYMLS = 1000 * 60 * 60 * 24; 

	var popupView = function() {
		
		var islink = false;
		var elem = new Element('div', {'class':'popup_adv', html: 
			'<div class="adv_move"><div class="adv_rotate"><a class="close"></a><div class="layer"><div class="text"><p></p><div class="price"></div></div><div><img></div></div></div></div>'
		});
		elem.inject($('bodyArea'), 'top');

		var count =  data.length;
		var index = Math.floor(Math.random() * count);

		var img = elem.getElement('img');
		var tlayer = elem.getElement('.text');
		var text = tlayer.getElement('p');
		var price = tlayer.getElement('.price');
		elem.getElement('.layer').addEvent('click', function() {
			window.open(data[index][3]);
			islink = true;
		});
		elem.getElement('.close').addEvent('click', close);

		img.src = data[index][0];
		var cover = img.getParent();

		function next() {
			index = (index + 1) % count;
			cover.setStyle('background-image', 'url(' + data[index][0] + ')');
			img.addClass('out_img');
			endAnim.delay(1200);
			var t = data[index][1], p = data[index][2];
			tlayer.setStyle('display', (t || p)?'block':'none');
			if (t) text.set('text', t);
			if (p) price.set('text', p);
		}

		function endAnim() {
			img.removeClass('out_img');
			img.set('src', data[index][0])
			next.delay(4000);
		} 

		function close() {
			elem.addClass('adv_move_out');
			(function() {
				elem.destroy();
			}).delay(900);
			if (islink) Cookie.write('popupAdvTime', Date.now());
		}

		next();
	}

	var lastAdv = Cookie.read('popupAdvTime');
	var delta = lastAdv?(1 - (Date.now() - lastAdv) / DAYMLS):0;
	var isDev = document.location.href.search('dev') > -1;

	if ((delta <= 0) || isDev) {
		window.addEvent('domready', ()=>{
			(function() {
				new popupView();
			}).delay(6000);
		});
	}
}