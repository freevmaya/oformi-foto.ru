function postImageProc(item) {
    var b, img;
    if ((b = item.getChildren('.img-button')) && 
        (img = item.getChildren('img'))) {
        
        img=img[0];b=b[0];
        
        var imgInit = function() {
            var cd = img.getCoordinates();
            img.set('tween', {onComplete: rBt});
            
            function rBt() {
                b.setStyles({left: img.getCoordinates().right});
            }
            rBt();
            var bc = b.getCoordinates();
            var bh = bc.height - 4;
            
            item.toggle = function(value) {
                var ish = !b.hasClass('up');
                if (value != ish) {                        
                    img.tween('height',(ish?bh:cd.height));  
                    b.toggleClass('up');
                }
            }
        
            b.addEvent('click', function(e) {
                item.toggle();
                e.stopPropagation();
                return false;
            });
        }
        
        item.toggle = function(value) {};
        img.addEvent('load', imgInit);        
    }
}

window.addEvent('domready', function() {
   var items = $$(".post-image");
   if (items.length) {
        var pi_tollbar = new Element('div', {'class':'pi-toolbar'});
        function showImages(value) {
            items.each(function(item) {item.toggle(value)});
            pi_tollbar.set('show', value?1:0);
        }
        var pn = items[0].getParent();
        var pp = pn.getElement('p');
        pi_tollbar.inject(pp, 'before');
        
        var bt = new Element('a', {href:'#', text: locale.HIDEALLIMAGES});
        pi_tollbar.set('show', 1);
        bt.addEvent('click', function() {
            showImages(pi_tollbar.get('show') == '0');            
        });
        bt.inject(pi_tollbar);
                    
        items.each(postImageProc);
   }  
});