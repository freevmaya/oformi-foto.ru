window.addEvent('domready', function() {
    var i=0;
    var move = false, focus=false;
    var grattis = $('grattis');
    var phone = $('phone');
    var promo = $('promo');
    var waitFocus = false;
    
    promo.setStyle('opacity', 0);
    phone.setStyle('opacity', 0);
    
    grattis.setStyle('display', 'block');
    grattis.addEvent('mouseout', function() {setFocus(false);});
    grattis.addEvent('mouseover', function() {setFocus(true);});
    var rect = grattis.getCoordinates();
    window.addEvent('scroll', function() {
        var ny = window.getScroll().y;
        if (ny < rect.top) ny = rect.top;
        grattis.setStyle('top', ny + 'px');
    });
    
    phone.set('tween', {duration: 'long'});
//    phone.addEvent('mouseout', function() {setFocus(false);});
    (function() {phone.tween('opacity', 0, 1);}).delay(5000);
    
    function setFocus(a_set) {
        if (focus = a_set) {
            stop();
            promo.setStyles({display: 'block', visibility: 'visible'});
            promo.tween('opacity', promo.getStyle('opacity'), 1);
            waitFocus = false; 
        } else {
            waitFocus = true;
            (function() {
                if (waitFocus) promo.tween('opacity', promo.getStyle('opacity'), 0);
                waitFocus = false;                
            }).delay(2000);
        }
    }
    
    function stop() {
        move = false;
        phone.setStyle('right', 0);
    };
    
    (function() {
        if (move && !focus) {
            var offset = move?((Math.sin(i) + 1) * 5):0;
            phone.setStyle('right', offset);
            i += 2;
        }
    }).periodical(50);
    
    (function() {
        if (!(move = !move)) stop(); 
    }).periodical(1000);
});