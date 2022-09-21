var WAITCALLBACK = 100;

var external = {
    getAlbums: function(callbackIndex, uid) {
        VK.Api.call('photos.getAlbums', {
            owner_id: app.user.uid
        }, function(data) {
            $('embed').callback(callbackIndex, data.response);
        });    
    },
    getFriends: function(callbackIndex, uid) {
       
    },
    getAllFriends: function(callbackIndex, uid) {
        
    },
    getPhotos: function(callbackIndex, uid, aid) {
        VK.Api.call('photos.get', {
            owner_id: app.user.uid,
            album_id: aid
        }, function(data) {
            $('embed').callback(callbackIndex, data.response);
        }, aid);
    }
}
