window.addEvent('domready', function() {
    var sharePanel = $$('.share-panel');
    var imageLayer = $('ds-image');
    imageLayer.addEvent('mouseover', function(e) {
        sharePanel.fade('in');
    });
    imageLayer.addEvent('mouseout', function(e) {
        sharePanel.fade('out');
    });
});