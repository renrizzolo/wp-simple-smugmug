/*
* Name: Simple Smugmug
* Description: Smugmug gallery feed using the smugmug REST api.
* Author: Ren Rizzolo
* Uri: https://github.com/renrizzolo
*/

jQuery(function ($) {
  //options and gallery_x come from wordpress wp_localize_script()

  //object where we'll store properties later
  var smug = {};

  var albumHtml = '';
  var imageHtml = '';

  //cache variables
  var cached = localStorage.getItem('smugCacheAlbums')
  var whenCached = localStorage.getItem('smugCache:ts')
  var expiry = options.cache_expiry;
  //options
  var smugmugurl = "https://www.smugmug.com";
  var api_key = options.api_key;
  var smugmug_username = options.smugmug_username;
  var force_https = options.force_https;

  //blastoff
  var galleries = [];
  //this is dumb
    if (typeof gallery_0 !== 'undefined'){
      galleries.push(gallery_0);
    }
    if (typeof gallery_1 !== 'undefined'){
      galleries.push(gallery_1);
    }
    if (typeof gallery_2 !== 'undefined'){
      galleries.push(gallery_2);
    }
    if (typeof gallery_3 !== 'undefined'){
      galleries.push(gallery_3);
    }
    if (typeof gallery_4 !== 'undefined'){
      galleries.push(gallery_4);
    }
    if (typeof gallery_5 !== 'undefined'){
      galleries.push(gallery_5);
    }
    if (typeof gallery_6 !== 'undefined'){
      galleries.push(gallery_6);
    }
    if (typeof gallery_7 !== 'undefined'){
      galleries.push(gallery_7);
    }
    if (typeof gallery_8 !== 'undefined'){
      galleries.push(gallery_8);
    }
    if (typeof gallery_9 !== 'undefined'){
      galleries.push(gallery_9);
    }

  console.log(galleries);
  if ( galleries.length && galleries[0].gallery_id.length ) {
    $.each(galleries, function (i, gallery){
      retrieveSmug(gallery, i);
    })
  } else {
    retrieveSmug(false, 0);
  }



  //the main function
  function retrieveSmug(gallery, i){
    //set options based on shortcode or fall back to current settings
    if (gallery === false) {
      gallery = {};
    }

    gallery.gallery_id = gallery.gallery_id || '';
    gallery.display_in_lightgallery = gallery.display_in_lightgallery || options.display_in_lightgallery;
    gallery.show_gallery_buy_link = gallery.show_gallery_buy_link || options.show_gallery_buy_link;
    gallery.album_container_class = gallery.album_container_class || options.album_container_class;
    gallery.first_image_container_class = gallery.first_image_container_class || options.first_image_container_class;
    gallery.image_container_class = gallery.image_container_class || options.image_container_class;
    gallery.image_class = gallery.image_class || options.image_class;
    gallery.title_class = gallery.title_class || options.title_class;
    gallery.link_class = gallery.link_class || options.link_class;
    gallery.image_count = gallery.image_count || options.image_count;
    gallery.album_count = options.album_count;
    gallery.show_album_title = gallery.show_album_title || options.show_album_title;
    gallery.smug_link_icon = gallery.smug_link_icon || options.smug_link_icon;

    if ( $.type(smugmug_username) === 'undefined' || smugmug_username === 'your smugmug username' || !smugmug_username.length ) {
      renderError('Please set your smugmug username in WP admin > Settings > Simple Smugmug')
      return;
    }
    
    if ( $.type(api_key) === 'undefined' || api_key === 'your api key' || !api_key.length) {
      renderError('Please set your smugmug API key in WP admin > Settings > Simple Smugmug');
      return;
    }
    var el = $('#smug-shortcode-'+i);

    $(".smug-inner").addClass("loading-smug");

    //if there's a gallery key passed to the shortcode fetch it.
    //it's less likely the user is revisiting a gallery so disregard cacheing.
    if (gallery.gallery_id.length > 0) {
      retrieveAlbum(el, gallery);
      return;
    }

    if (cached !== null && whenCached !== null) {

      console.log('cached - ', cached, whenCached);

      // it's cached
      var age = (Date.now() - whenCached) / 1000
      if (age < expiry) {
        retrieveAlbumsFromCache(el, gallery);
      } else {
        // We need to clean up this expired key
        console.log('cache expired');
        invalidateCache();
        //don't forget to get all the new stuff!
      retrieveAlbums(el, gallery);
      }
    } else {
      console.log('not yet cached - retrieving albums');
      retrieveAlbums(el, gallery);
    }
  }

  function invalidateCache() {
    if (cached){
      for (var i = options.album_count - 1; i >= 0; i--) {
        var key = JSON.parse(localStorage.getItem('smugCacheAlbums'))[i].AlbumKey;
        localStorage.removeItem('smugCacheImages-'+key);
      }  
      localStorage.removeItem('smugCacheAlbums');
    }
    localStorage.getItem('smugCache:ts') && localStorage.removeItem('smugCache:ts');
  };

  function setHtml(images, gallery) {
    console.log(images, gallery);
    var html = '';
       $.each( images, function(i, image) {
          if (i < gallery.image_count) {
              html += '<a href="'+( gallery.display_in_lightgallery === '1' && gallery.gallery_id ? (image.LargeImageUrl || image.MediumImageUrl) : secureUrl(image.WebUri))+'"'+
              'data-sub-html="'+image.Caption+'"'+
              'data-smug-url="'+secureUrl(image.WebUri)+'"'+
              'target="_blank"'+
              'class="'+ (i < 1 ? gallery.first_image_container_class : gallery.image_container_class)+' '+(gallery.display_in_lightgallery === '1' ? 'lg-smug-item' : '')+'"'+
              'title="'+image.Caption+'">'+
              '<img src="'+image.ThumbnailUrl+'"class="'+gallery.image_class+'"/></a>';
          }
      });
      $(".smug-inner").removeClass('loading-smug');
      $(".smug-inner #simple-smugmug-loader").remove();

      return html;

  }

  function retrieveAlbumsFromCache(el, gallery){
    if (localStorage.getItem('smugCacheAlbums')){
      var albums = JSON.parse(localStorage.getItem('smugCacheAlbums'));
      if (albums.length && albums[0] && albums[0].WebUri) {
          $.each( albums, function(i, album) {
            console.log(i, album.WebUri, album.Name);
            if (i < gallery.album_count) {
              var album_title_html = (gallery.show_album_title === '1') ? '<a class"'+gallery.link_class+'" href="'+secureUrl(album.WebUri)+'"><h4 class="'+gallery.title_class+'">'+album.Name+'</h4></a>' : '';
              albumHtml += '<div class="'+gallery.album_container_class+'">'+album_title_html;
              albumHtml += retrieveImagesFromCache(album.AlbumKey, gallery);
              albumHtml += '</div>';

          } else {
            return;
          }
        });
        el.append(albumHtml);
      }
    } else {
      //something is wrong with the cached data
      retrieveAlbums(el, gallery);
    }
  }

  function retrieveImagesFromCache(AlbumKey, gallery){
    var images = JSON.parse(localStorage.getItem('smugCacheImages-'+AlbumKey))
    if ( images ) {
      return setHtml(images, gallery);
    } else {
      invalidateCache();
      renderError('Sorry, something went wrong. Please reload the page and try again.')
    }
  };

  function retrieveAlbums(el, gallery){

    retrieveApi('/api/v2/user/'+smugmug_username+'!albums').success(function (data) {
      if (data.Code === 200) {
      var albums = data.Response.Album;
        $.each( albums, function(i, album) {
          if (i < gallery.album_count) {
            retrieveImages(album.Uris.AlbumImages.Uri, album.AlbumKey, gallery)
              .then(function (data){ 

                //set up html
                var album_title_html = (gallery.show_album_title === '1') ? '<a class"'+gallery.link_class+'" href="'+secureUrl(album.WebUri)+'"><h4 class="'+gallery.title_class+'">'+album.Name+'</h4></a>' : '';
                albumHtml  = '<div class="'+gallery.album_container_class+'">'+album_title_html;
                albumHtml += data;
                albumHtml += '</div>';
                $(el).append(albumHtml);

                // add the albums to the cache
                var arr = JSON.parse(localStorage.getItem('smugCacheAlbums')) || [];
                arr.push({WebUri:album.WebUri, Name: album.Name, AlbumKey: album.AlbumKey});
                localStorage.setItem('smugCacheAlbums', JSON.stringify(arr));

                //set the cached time
                localStorage.setItem('smugCache:ts', Date.now())

     

              }).catch(function (err){
                console.log(err);
                renderError(err.Message);
              });
        } else {
          return;
        }
      });
      } else {
        renderError('Error getting Smugmug data')
      }
    }).error(function (err){
      console.log(err);
      renderError(err.Message);
    });
    
  }

  function retrieveImages(uri, key, gallery) {
  //this only gets called if there was nothing in the cache. populates the albums with their images.
    return new Promise (function(resolve, reject) {
      retrieveApi(uri).success(function (data) {
        console.log(data);
      if (data.Code === 200) {
        var images = data.Response.AlbumImage;
        retrieveImageUrls(images, gallery.image_count)
        .then(function (data){
          console.log(data);

          //cacheing 
          if ( !gallery.gallery_id.length > 0 ) {
            localStorage.setItem('smugCacheImages-'+key, JSON.stringify(data));
          }
          resolve(setHtml(data, gallery))
        })
      } else {
        renderError('Error getting Smugmug data');
        reject();
      }
      }).error(function (err) {
        console.log(err)
        reject();
      })
    })
  }

  function retrieveImageUrls(images, imageCount) {
    return new Promise (function(resolve, reject) {
      var array = [];
      var promises = [];
     $.each(images, function(i, image) {
      if (i < imageCount){
        promises.push(
          retrieveApi(image.Uris.ImageSizes.Uri).success(function (data) {
            if (data.Code === 200) {
            var MediumUrl = data.Response.ImageSizes.MediumImageUrl;
            var LargeUrl = data.Response.ImageSizes.LargeImageUrl;
            array.push({
              Caption: image.Caption, 
              MediumImageUrl: MediumUrl, 
              LargeImageUrl: LargeUrl,
              ThumbnailUrl: image.ThumbnailUrl,
              WebUri: image.WebUri
            })

          } else {
          reject();
          }

          }).error(function (err) {
            console.log(err);
            reject();
          })
        )
      }
 
      });

      Promise.all(promises).then(() => 
        resolve(array)
      );

    });
  }

  function retrieveAlbum(el, gallery) {

    retrieveApi('/api/v2/album/'+gallery.gallery_id).success(function (data) {
      console.log(data);
    if (data.Code === 200) {
      var album = data.Response.Album;
      retrieveImages(album.Uris.AlbumImages.Uri, album.AlbumKey, gallery)
        .then(function (data){ 
          var album_title_html = (gallery.show_album_title === '1') ? '<a class"'+gallery.link_class+'" href="'+secureUrl(album.WebUri)+'"><h4 class="'+gallery.title_class+'">'+album.Name+'</h4></a>' : '';
          albumHtml = '<div class="'+gallery.album_container_class+' '+(gallery.display_in_lightgallery === '1' ? 'lg-smug' : '')+'">'+album_title_html;
          albumHtml += data;
          albumHtml += '</div>';
          console.log('albumhtml: ', albumHtml);
          el.html(albumHtml);

          $('.lg-smug').lightGallery({
              download:false,
              smugLink:  gallery.show_gallery_buy_link === '1' ? true : false,
              smugLinkIcon: gallery.smug_link_icon ? gallery.smug_link_icon : 'lg-cart lg-icon',
              thumbnail:true,
              selector:'.lg-smug-item',
              showAfterLoad:true,
              subHtmlSelectorRelative: true,
              hideBarsDelay: 2000
            });

        }).catch(function (err){
          console.log(err);
          renderError(err.Message);
        });
      } else {
        renderError('Error getting Smugmug data')
      }
    }).error(function (err){
      console.log(err);
      renderError(JSON.parse(err.responseText).Code + ': '+JSON.parse(err.responseText).Message);
    });

  }

  //simple ajax function that we can pass a url to because we do recursive api requests ( album -> that album's images )
  function retrieveApi(uri){

    var req = smugmugurl + uri + '?APIKey=' + api_key;

      return $.ajax({
        url: req,
        method: "GET",
        cache: true,
        dataType: "json",
        // error: function(xhr, status, error) {
        //   renderError(JSON.parse(xhr.responseText).Message);
        // }
       })
  }

  // add https to the hardcoded http uri from smugmug
  function secureUrl(string){
    if ( force_https === '1' ) {
      var url = string.replace( 'http://', 'https://' );
      return url;
    } else {
     return string;
    }
  }

  //rendering error messages in the container
  function renderError(message) {
    albumHtml +='<div><p>'+message+'</p></div>';
    $(".smug-inner").append(albumHtml);
  }

});

