var WAITCALLBACK = 100;

var external = {
    getAlbums: function(callbackIndex, uid) {
        mailru.common.photos.getAlbums(function(albums_list) {
            $('embed').callback(callbackIndex, albums_list);
        });    
    },
    getFriends: function(callbackIndex, uid) {
       
    },
    getAllFriends: function(callbackIndex, uid) {
        
    },
    getPhotos: function(callbackIndex, uid, aid) {
        mailru.common.photos.get(function(photos_list) {
            $('embed').callback(callbackIndex, photos_list);
        }, aid);
    }
}