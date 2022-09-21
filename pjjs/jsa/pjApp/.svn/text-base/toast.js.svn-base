var TOASTTIME = 5000;
function toastInit(parent) {
    var toast, msg;
    var limites = {
        ROTATETOAST: [0, 2],
        MOVETOAST: [0, 2],
    }
    
    function init() {
        toast = new Element('div', {'class': 'toast', styles: {
            visibility: 'hidden', opacity: 0
        }});
        toast.inject(parent);        
        $(window).addEvent('TOAST', onToast);
    }
    
    function onToast(itext) {
        if (!msg) {    
            var text = itext;
            if (LOCALE[itext]) {
                if (limites[itext]) {
                    if (limites[itext][0] >= limites[itext][1]) return;
                    limites[itext][0]++;
                }
                text = LOCALE[itext];
            } 
            show(text);
        }
    }
    
    function show(text) {
        var timer;
        msg = new Element('p', {
            text: text,
            tween: {transition: 'bounce:out', duration: 'long'}, 
            styles: {
                width: parent.getSize().x,
                'margin-left': parent.getCoordinates().right
            }
        });
        msg.inject(toast);
        toast.fade('in');
        msg.tween('margin-left', 0);
        timer = removeMsg.delay(TOASTTIME);
    }
    
    function removeMsg() {
        toast.fade('out');
        msg.tween('margin-left', parent.getCoordinates().right);
        (function() {
            msg.destroy();
            msg = null;
        }).delay(500);
    }
    
    
    init();
}
