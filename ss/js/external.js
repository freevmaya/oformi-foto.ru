var WAITCALLBACK = 100;
var callbackObject;

var vk_external = {
    getProfiles: function(callbackIndex, uids) {
    },
    getAlbums: function(callbackIndex, uid) {
        VK.Api.call('photos.getAlbums', {
            owner_id: uid?uid:app.user.uid
        }, function(data) {
            callbackObject.callback(callbackIndex, data.response);
        });    
    },
    getFriends: function(callbackIndex, uid) {
       
    },
    getAllFriends: function(callbackIndex, uid) {
        
    },
    getPhotos: function(callbackIndex, uid, aid) {
        VK.Api.call('photos.get', {
            owner_id: uid?uid:app.user.uid,
            album_id: aid?aid:'profile'
        }, function(data) {
            function imgToProxy(src) {
                var url = src.substr(src.indexOf('://') + 3);
                url = document.location.protocol + '//oformi-foto.ru/imageproxy/' + url;
                return url.replace('.jpg', '.htm');
            }
            var photos = data.response;
            if (photos) {
                for (var i=0; i<photos.length; i++) {
                    photos[i].src = imgToProxy(photos[i].src_big);
                    delete(photos[i].src_big);
                } 
                callbackObject.callback(callbackIndex, photos);
            }
        }, aid);
    }
}     

var mm_external = {
    getProfiles: function(callbackIndex, uids) {
    },
    getAlbums: function(callbackIndex, uid) {
        mailru.common.photos.getAlbums(function(albums_list) {
            var albums = [];
            for (var i=0;i<albums_list.length;i++) 
                if (albums_list[i].privacy == 2) albums.push(albums_list[i]); 
            callbackObject.callback(callbackIndex, albums);
        });    
    },
    getFriends: function(callbackIndex, uid) {
       
    },
    getAllFriends: function(callbackIndex, uid) {
        
    },
    getPhotos: function(callbackIndex, uid, aid) {
        function imgToProxy(src) {
            var url = src.substr(src.indexOf('://') + 3);
            url = document.location.protocol + '//oformi-foto.ru/imageproxy/' + url;
            
            return url.replace('.jpg', '.htm');
        } 
    
        mailru.common.photos.get((function(photos_list) {
            for (var i=0; i<photos_list.length; i++)
                photos_list[i].src = imgToProxy(photos_list[i].src);
        
            callbackObject.callback(callbackIndex, photos_list);
        }).bind(this), aid);
    }
}

     

var ok_external = {
    getProfiles: function(callbackIndex, uids) {
        OkClient.call({method:"users.getInfo", uids: uids, fields: 
            'first_name,last_name,name,gender,pic50x50,pic128x128,pic640x480,url_profile,private,registered_date,private'}, 
            function(status, data, error) {
                if (status == 'ok') {
                    callbackObject.callback(callbackIndex, data);
                }
            }); 
    },
    getAlbums: function(callbackIndex, uid) {
        OkClient.call({method:"photos.getAlbums"}, function(status, data, error) {
            if (status == 'ok') {
                var albums = [];
                for (var i=0; i<data.albums.length;i++) {
                    var album = data.albums[i]; 
                    if (album.type == 'PUBLIC') albums.push(album);
                } 
                callbackObject.callback(callbackIndex, albums);
            }
        }); 
    },
    getFriends: function(callbackIndex, uid) {
    },
    getAllFriends: function(callbackIndex, uid) {
    },
    getPhotos: function(callbackIndex, uid, aid) {
        function imgToProxy(src) {
            var url = src.substr(src.indexOf('://') + 3);
            url = document.location.protocol + '//oformi-foto.ru/imageproxy/' + url;
            url = url.replace('.me/', '.me_____');
            return url.replace('?', '___') + '.img';
        } 
    
        OkClient.call({method:"photos.getPhotos", aid: aid, count: 100}, function(status, data, error) {
            if (status == 'ok') {
                var photos = data.photos;
                for (var i=0; i<photos.length; i++) {
                    photos[i].src_small = photos[i].pic128x128;
                    photos[i].src = imgToProxy(photos[i].pic640x480);
                }
                callbackObject.callback(callbackIndex, photos);
            }
        });
    }
}

var fb_external = {
    getProfiles: function(callbackIndex, uids) {
    },
    getAlbums: function(callbackIndex, uid) {
        FB.api('/me/albums', (function(response) {
            var albums = response.data; 
            for (var i=0; i<albums.length; i++) {
                albums[i].aid = albums[i].id; 
                albums[i].title = albums[i].name;
            }
            callbackObject.callback(callbackIndex, albums);
        }).bind(this));
    },
    getFriends: function(callbackIndex, uid) {
        FB.api('/me/friends', (function(items) {
            console.log(items);
        }));
    },
    getAllFriends: function(callbackIndex, uid) {
    },
    getPhotos: function(callbackIndex, uid, aid) {
        FB.api(aid + "/photos","get",{fields:"images"}, (function(response) {
            var photos = [];
            for (var i=0; i<response.data.length; i++) {
                var item = response.data[i];
                var out = {};
                for (var n=0; n<item.images.length; n++) {
                    var image = item.images[n];
                    if (n == 0) out.src = image.source;
                    if ((Math.max(image.width, image.height) < 300) || (n == item.images.length - 1)) out.src_small = image.source;
                }
                photos.push(out);
            }
            callbackObject.callback(callbackIndex, photos);
        }).bind(this));
    }
}


var default_external =  {
    getProfiles: function(callbackIndex, uids) {
    },
    getAlbums: function(callbackIndex, uid) {
        window.setTimeout(function() {
            callbackObject.callback(callbackIndex, [
                {
                    aid: 1,
                    title: 'Демо'
                }
            ]);
        }, WAITCALLBACK);    
    },
    getFriends: function(callbackIndex, uid) {
        window.setTimeout(function() {
            callbackObject.callback(callbackIndex, [
                {
                    src_small: "asddd.jpg"
                }
            ]);
        }, WAITCALLBACK);
    },
    getAllFriends: function(callbackIndex, uid) {
        window.setTimeout(function() {
            callbackObject.callback(callbackIndex, [
                {
                    src_small: "asddd.jpg"
                }
            ]);
        }, WAITCALLBACK);
    },
    getPhotos: function(callbackIndex, uid, aid) {
        window.setTimeout(function() {
            callbackObject.callback(callbackIndex, [
                {
                    src_small: "//oformi-foto.ru/gifavt/images/default/01.jpg",
                    src: "//oformi-foto.ru/gifavt/images/default/01.jpg",
                },{
                    src_small: "//oformi-foto.ru/gifavt/images/default/02.jpg",
                    src: "//oformi-foto.ru/gifavt/images/default/02.jpg",
                },{
                    src_small: "//oformi-foto.ru/gifavt/images/default/03.jpg",
                    src: "//oformi-foto.ru/gifavt/images/default/03.jpg",
                },{
                    src_small: "//oformi-foto.ru/gifavt/images/default/04.jpg",
                    src: "//oformi-foto.ru/gifavt/images/default/04.jpg",
                }
            ]);
        }, WAITCALLBACK);
    }
}

var external = default_external;
var of_external = default_external;