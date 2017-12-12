/*
* Name: Simple Smugmug
* Description: Smugmug gallery feed using the smugmug REST api.
* Author: Ren Rizzolo
* Uri: https://github.com/renrizzolo
*/

/*global jQuery:true*/
/*eslint no-undef: "error"*/

jQuery(function ($) {
  'use strict';
  /*global simple_smugmug_options:true*/
  /*eslint no-undef: "error"*/
  // simple_smugmug_options  and gallery_[x] are global variables from the wordpress plugin's wp_localize_script()


  // cache variables
  var cached = localStorage.getItem('smugCacheAlbums');
  var whenCached = localStorage.getItem('smugCache:ts');
  var expiry = simple_smugmug_options.cache_expiry;

  // options
  var smugmugurl = 'https://www.smugmug.com';
  var api_key = simple_smugmug_options.api_key;
  var smugmug_username = simple_smugmug_options.smugmug_username;
  var force_https = simple_smugmug_options.force_https;

  var galleries = [];
  // this is a dumb way to allow multiple (up to 10) shorcodes to be used on the same page
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

  if ( galleries.length ) {
    $.each(galleries, function (i, gallery){
      retrieveSmug(gallery, i);
    });
  }


  // the main function
  function retrieveSmug(gallery, i){

    // set options based on shortcode or fall back to current settings
    gallery.el = 'smug-shortcode-'+i;
    gallery.gallery_id = gallery.gallery_id || '';
    gallery.display_in_lightgallery = gallery.display_in_lightgallery || simple_smugmug_options.display_in_lightgallery;
    gallery.show_gallery_buy_link = gallery.show_gallery_buy_link || simple_smugmug_options.show_gallery_buy_link;
    gallery.album_container_class = gallery.album_container_class || simple_smugmug_options.album_container_class;
    gallery.first_image_container_class = gallery.first_image_container_class || simple_smugmug_options.first_image_container_class;
    gallery.image_container_class = gallery.image_container_class || simple_smugmug_options.image_container_class;
    gallery.image_class = gallery.image_class || simple_smugmug_options.image_class;
    gallery.title_class = gallery.title_class || simple_smugmug_options.title_class;
    gallery.link_class = gallery.link_class || simple_smugmug_options.link_class;
    gallery.image_count = gallery.image_count || simple_smugmug_options.image_count;
    gallery.album_count = simple_smugmug_options.album_count;
    gallery.show_album_title = gallery.show_album_title || simple_smugmug_options.show_album_title;
    gallery.smug_link_icon = gallery.smug_link_icon || simple_smugmug_options.smug_link_icon;

    // notify and return when required stuff is not set
    if ( $.type(smugmug_username) === 'undefined' || smugmug_username === 'your smugmug username' || !smugmug_username.length ) {
      renderError('Please set your smugmug username in WP admin > Settings > Simple Smugmug', gallery.el);
      return;
    }
    
    if ( $.type(api_key) === 'undefined' || api_key === 'your api key' || !api_key.length) {
      renderError('Please set your smugmug API key in WP admin > Settings > Simple Smugmug', gallery.el);
      return;
    }

    // the current shortcode's container

    // load 'er up
    $('.smug-inner').addClass('loading-smug');

    // if there's a gallery key passed to the shortcode fetch it.
    // it's less likely the user is revisiting a gallery so disregard caching.
    // I've sort of disregarded the caching after I extended the plugin to show single galleries etc.
    if (gallery.gallery_id.length > 0) {
      retrieveAlbum(gallery);
      return;
    }

    if (cached !== null && whenCached !== null) {

      console.log('cached - ', cached, whenCached);

      // it's cached
      var age = (Date.now() - whenCached) / 1000;
      if (age < expiry) {
        retrieveAlbumsFromCache(gallery);
      } else {
        // We need to clean up this expired key
        console.log('cache expired');
        invalidateCache();
        //don't forget to get all the new stuff!
        retrieveAlbums(gallery);
      }
    } else {
      console.log('not yet cached - retrieving albums');
      retrieveAlbums(gallery);
    }
  }

  function invalidateCache() {
    if (cached){
      for (var i = simple_smugmug_options.album_count - 1; i >= 0; i--) {
        var key = JSON.parse(localStorage.getItem('smugCacheAlbums'))[i].AlbumKey;
        localStorage.removeItem('smugCacheImages-'+key);
      }  
      localStorage.removeItem('smugCacheAlbums');
    }
    localStorage.getItem('smugCache:ts') && localStorage.removeItem('smugCache:ts');
  }

  // final destination
  function setHtml(images, gallery) {
    var html = '';
    $.each( images, function(i, image) {
      if (i < gallery.image_count) {
        html += '<a href="'+( gallery.display_in_lightgallery === '1' && gallery.gallery_id ? (image.LargeImageUrl || image.MediumImageUrl) : secureUrl(image.WebUri) )+'"'+
        'data-sub-html="'+image.Caption+'"'+
        'data-smug-url="'+secureUrl(image.WebUri)+'"'+
        'target="_blank"'+
        'class="'+ (i < 1 ? gallery.first_image_container_class : gallery.image_container_class)+' '+(gallery.display_in_lightgallery === '1' ? 'lg-smug-item' : '')+'"'+
        'title="'+image.Caption+'">'+
        '<img src="'+image.ThumbnailUrl+'"class="'+gallery.image_class+'"/></a>';
      }
    });

    removeLoader(gallery.el);

    return html;

  }

  function retrieveAlbumsFromCache(gallery){
    if (localStorage.getItem('smugCacheAlbums')){
      var albums = JSON.parse(localStorage.getItem('smugCacheAlbums'));
      if (albums.length && albums[0] && albums[0].WebUri) {
        $.each( albums, function(i, album) {
          if (i < gallery.album_count) {
            var album_title_html = (gallery.show_album_title === '1') ? '<a class="'+gallery.link_class+'" href="'+secureUrl(album.WebUri)+'"><h4 class="'+gallery.title_class+'">'+album.Name+'</h4></a>' : '';
            var albumHtml  = '<div class="'+gallery.album_container_class+'">'+album_title_html;
            albumHtml += retrieveImagesFromCache(album.AlbumKey, gallery);
            albumHtml += '</div>';
            $('#'+gallery.el).append(albumHtml);
          } else {
            return;
          }
        });
      }
    } else {
      //something is wrong with the cached data
      retrieveAlbums(gallery);
    }
  }

  function retrieveImagesFromCache(AlbumKey, gallery){
    var images = JSON.parse(localStorage.getItem('smugCacheImages-'+AlbumKey));
    if ( images ) {
      return setHtml(images, gallery);
    } else {
      invalidateCache();
      renderError('Sorry, something went wrong. Please reload the page and try again.', gallery.el);
    }
  }

  function retrieveAlbums(gallery){

    retrieveApi('/api/v2/user/'+smugmug_username+'!albums').success(function (data) {
      if (data.Code === 200) {
        var albums = data.Response.Album;
        $.each( albums, function(i, album) {
          if (i < gallery.album_count) {
            retrieveImages(album.Uris.AlbumImages.Uri, album.AlbumKey, gallery)
              .then(function (data){ 

                //set up html
                var album_title_html = (gallery.show_album_title === '1') ?
                  '<a class="'+gallery.link_class+'" href="'+secureUrl(album.WebUri)+'"><h4 class="'+gallery.title_class+'">'+album.Name+'</h4></a>' : 
                  '';
                var albumHtml  = '<div class="'+gallery.album_container_class+'">'+album_title_html;
                albumHtml += data;
                albumHtml += '</div>';
                $('#'+gallery.el).append(albumHtml);

                // add the albums to the cache
                var arr = JSON.parse(localStorage.getItem('smugCacheAlbums')) || [];
                arr.push({WebUri:album.WebUri, Name: album.Name, AlbumKey: album.AlbumKey});
                localStorage.setItem('smugCacheAlbums', JSON.stringify(arr));

                //set the cached time
                localStorage.setItem('smugCache:ts', Date.now());

     

              }).catch(function (err){
                console.log(err);
                renderError(err.Message, gallery.el);
              });
          } else {
            return;
          }
        });
      } else {
        renderError('Error getting Smugmug data', gallery.el);
        removeLoader(gallery.el);
      }
    })
      .error(function (err){
        console.log(err);
        removeLoader(gallery.el);
        renderError(JSON.parse(err.responseText).Code + ': '+JSON.parse(err.responseText).Message, gallery.el);
        if (JSON.parse(err.responseText).Code === 404) {
          renderError('The gallery was removed or the ID is incorrect.', gallery.el);
        }
      });
    
  }

  function retrieveImages(uri, key, gallery) {
  //this only gets called if there was nothing in the cache. populates the albums with their images.
    return new Promise (function(resolve, reject) {
      retrieveApi(uri).success(function (data) {
        if (data.Code === 200) {
          var images = data.Response.AlbumImage;
          retrieveImageUrls(images, gallery)
            .then(function (data){
              console.log(data);
              //caching 
              if ( !gallery.gallery_id.length > 0 ) {
                localStorage.setItem('smugCacheImages-'+key, JSON.stringify(data));
              }
              resolve(setHtml(data, gallery));
            });
        } else {
          renderError('Error getting Smugmug data', gallery.el);
          reject();
        }
      })
        .error(function (err){
          console.log(err);
          removeLoader(gallery.el);
          renderError(JSON.parse(err.responseText).Code + ': '+JSON.parse(err.responseText).Message, gallery.el);
          if (JSON.parse(err.responseText).Code === 404) {
            renderError('The gallery was removed or the ID is incorrect.', gallery.el);
          }
        });
    });
  }

  function retrieveImageUrls(images, gallery) {
    return new Promise (function(resolve, reject) {
      var array = [];
      var promises = [];
      $.each(images, function(i, image) {
        if (i < gallery.image_count){
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
                });

              } else {
                reject();
              }
            })
              .error(function (err){
                console.log(err);
                removeLoader(gallery.el);
                renderError(JSON.parse(err.responseText).Code + ': '+JSON.parse(err.responseText).Message, gallery.el);
                if (JSON.parse(err.responseText).Code === 404) {
                  renderError('The gallery was removed or the ID is incorrect.', gallery.el);
                }
              })
          );
        }
 
      });

      Promise.all(promises).then(function () { 
        resolve(array);
      });

    });
  }

  function retrieveAlbum(gallery) {
    console.log(gallery);
    retrieveApi('/api/v2/album/'+gallery.gallery_id).success(function (data) {
      if (data.Code === 200) {
        var album = data.Response.Album;
        retrieveImages(album.Uris.AlbumImages.Uri, album.AlbumKey, gallery)
          .then(function (data){ 
            var album_title_html = (gallery.show_album_title === '1') ? '<a class="'+gallery.link_class+'" href="'+secureUrl(album.WebUri)+'"><h4 class="'+gallery.title_class+'">'+album.Name+'</h4></a>' : '';
            var albumHtml = '<div class="'+gallery.album_container_class+' '+(gallery.display_in_lightgallery === '1' ? 'lg-smug' : '')+'">'+album_title_html;
            albumHtml += data;
            albumHtml += '</div>';

            $('#'+gallery.el).html(albumHtml);

            if ( gallery.display_in_lightgallery === '1' ) {

              $('.lg-smug').lightGallery({
                download: false,
                smugLink: gallery.show_gallery_buy_link === '1' ? true : false,
                smugLinkIcon: gallery.smug_link_icon ? gallery.smug_link_icon : 'lg-cart lg-icon',
                thumbnail: true,
                selector: '.lg-smug-item',
                showAfterLoad: true,
                subHtmlSelectorRelative: true,
                hideBarsDelay: 2000
              });
            }

          }).catch(function (err){
            console.log(err);
            renderError(err.Message, gallery.el);
          });
      } else {
        renderError('Error getting Smugmug data', gallery.el);
      }
    })
      .error(function (err){
        console.log(err);
        removeLoader(gallery.el);
        renderError(JSON.parse(err.responseText).Code + ': '+JSON.parse(err.responseText).Message, gallery.el);
        if (JSON.parse(err.responseText).Code === 404) {
          renderError('The gallery was removed or the ID is incorrect.', gallery.el);
        }
      });

  }

  function removeLoader(el) {
    $('#'+el).removeClass('loading-smug');
    $('#'+el+' #simple-smugmug-loader').remove();
  }

  // simple ajax function that we can pass a url to because we do recursive api requests ( album -> that album's images )
  function retrieveApi(uri){

    var req = smugmugurl + uri + '?APIKey=' + api_key;

    return $.ajax({
      url: req,
      method: 'GET',
      cache: true,
      dataType: 'json',
    });
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

  // rendering error messages in the container
  function renderError(message, el) {
    var error = '<div class="simple-smug-error"><p>'+message+'</p></div>';
    $('#'+el).append(error);
  }

});

// Promise aut polyfill https://github.com/stefanpenner/es6-promise
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.ES6Promise=e()}(this,function(){"use strict";function t(t){var e=typeof t;return null!==t&&("object"===e||"function"===e)}function e(t){return"function"==typeof t}function n(t){I=t}function r(t){J=t}function o(){return function(){return process.nextTick(a)}}function i(){return"undefined"!=typeof H?function(){H(a)}:c()}function s(){var t=0,e=new V(a),n=document.createTextNode("");return e.observe(n,{characterData:!0}),function(){n.data=t=++t%2}}function u(){var t=new MessageChannel;return t.port1.onmessage=a,function(){return t.port2.postMessage(0)}}function c(){var t=setTimeout;return function(){return t(a,1)}}function a(){for(var t=0;t<G;t+=2){var e=$[t],n=$[t+1];e(n),$[t]=void 0,$[t+1]=void 0}G=0}function f(){try{var t=require,e=t("vertx");return H=e.runOnLoop||e.runOnContext,i()}catch(n){return c()}}function l(t,e){var n=arguments,r=this,o=new this.constructor(p);void 0===o[et]&&k(o);var i=r._state;return i?!function(){var t=n[i-1];J(function(){return x(i,o,t,r._result)})}():E(r,o,t,e),o}function h(t){var e=this;if(t&&"object"==typeof t&&t.constructor===e)return t;var n=new e(p);return g(n,t),n}function p(){}function v(){return new TypeError("You cannot resolve a promise with itself")}function d(){return new TypeError("A promises callback cannot return that same promise.")}function _(t){try{return t.then}catch(e){return it.error=e,it}}function y(t,e,n,r){try{t.call(e,n,r)}catch(o){return o}}function m(t,e,n){J(function(t){var r=!1,o=y(n,e,function(n){r||(r=!0,e!==n?g(t,n):S(t,n))},function(e){r||(r=!0,j(t,e))},"Settle: "+(t._label||" unknown promise"));!r&&o&&(r=!0,j(t,o))},t)}function b(t,e){e._state===rt?S(t,e._result):e._state===ot?j(t,e._result):E(e,void 0,function(e){return g(t,e)},function(e){return j(t,e)})}function w(t,n,r){n.constructor===t.constructor&&r===l&&n.constructor.resolve===h?b(t,n):r===it?(j(t,it.error),it.error=null):void 0===r?S(t,n):e(r)?m(t,n,r):S(t,n)}function g(e,n){e===n?j(e,v()):t(n)?w(e,n,_(n)):S(e,n)}function A(t){t._onerror&&t._onerror(t._result),T(t)}function S(t,e){t._state===nt&&(t._result=e,t._state=rt,0!==t._subscribers.length&&J(T,t))}function j(t,e){t._state===nt&&(t._state=ot,t._result=e,J(A,t))}function E(t,e,n,r){var o=t._subscribers,i=o.length;t._onerror=null,o[i]=e,o[i+rt]=n,o[i+ot]=r,0===i&&t._state&&J(T,t)}function T(t){var e=t._subscribers,n=t._state;if(0!==e.length){for(var r=void 0,o=void 0,i=t._result,s=0;s<e.length;s+=3)r=e[s],o=e[s+n],r?x(n,r,o,i):o(i);t._subscribers.length=0}}function M(){this.error=null}function P(t,e){try{return t(e)}catch(n){return st.error=n,st}}function x(t,n,r,o){var i=e(r),s=void 0,u=void 0,c=void 0,a=void 0;if(i){if(s=P(r,o),s===st?(a=!0,u=s.error,s.error=null):c=!0,n===s)return void j(n,d())}else s=o,c=!0;n._state!==nt||(i&&c?g(n,s):a?j(n,u):t===rt?S(n,s):t===ot&&j(n,s))}function C(t,e){try{e(function(e){g(t,e)},function(e){j(t,e)})}catch(n){j(t,n)}}function O(){return ut++}function k(t){t[et]=ut++,t._state=void 0,t._result=void 0,t._subscribers=[]}function Y(t,e){this._instanceConstructor=t,this.promise=new t(p),this.promise[et]||k(this.promise),B(e)?(this.length=e.length,this._remaining=e.length,this._result=new Array(this.length),0===this.length?S(this.promise,this._result):(this.length=this.length||0,this._enumerate(e),0===this._remaining&&S(this.promise,this._result))):j(this.promise,q())}function q(){return new Error("Array Methods must be provided an Array")}function F(t){return new Y(this,t).promise}function D(t){var e=this;return new e(B(t)?function(n,r){for(var o=t.length,i=0;i<o;i++)e.resolve(t[i]).then(n,r)}:function(t,e){return e(new TypeError("You must pass an array to race."))})}function K(t){var e=this,n=new e(p);return j(n,t),n}function L(){throw new TypeError("You must pass a resolver function as the first argument to the promise constructor")}function N(){throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.")}function U(t){this[et]=O(),this._result=this._state=void 0,this._subscribers=[],p!==t&&("function"!=typeof t&&L(),this instanceof U?C(this,t):N())}function W(){var t=void 0;if("undefined"!=typeof global)t=global;else if("undefined"!=typeof self)t=self;else try{t=Function("return this")()}catch(e){throw new Error("polyfill failed because global object is unavailable in this environment")}var n=t.Promise;if(n){var r=null;try{r=Object.prototype.toString.call(n.resolve())}catch(e){}if("[object Promise]"===r&&!n.cast)return}t.Promise=U}var z=void 0;z=Array.isArray?Array.isArray:function(t){return"[object Array]"===Object.prototype.toString.call(t)};var B=z,G=0,H=void 0,I=void 0,J=function(t,e){$[G]=t,$[G+1]=e,G+=2,2===G&&(I?I(a):tt())},Q="undefined"!=typeof window?window:void 0,R=Q||{},V=R.MutationObserver||R.WebKitMutationObserver,X="undefined"==typeof self&&"undefined"!=typeof process&&"[object process]"==={}.toString.call(process),Z="undefined"!=typeof Uint8ClampedArray&&"undefined"!=typeof importScripts&&"undefined"!=typeof MessageChannel,$=new Array(1e3),tt=void 0;tt=X?o():V?s():Z?u():void 0===Q&&"function"==typeof require?f():c();var et=Math.random().toString(36).substring(16),nt=void 0,rt=1,ot=2,it=new M,st=new M,ut=0;return Y.prototype._enumerate=function(t){for(var e=0;this._state===nt&&e<t.length;e++)this._eachEntry(t[e],e)},Y.prototype._eachEntry=function(t,e){var n=this._instanceConstructor,r=n.resolve;if(r===h){var o=_(t);if(o===l&&t._state!==nt)this._settledAt(t._state,e,t._result);else if("function"!=typeof o)this._remaining--,this._result[e]=t;else if(n===U){var i=new n(p);w(i,t,o),this._willSettleAt(i,e)}else this._willSettleAt(new n(function(e){return e(t)}),e)}else this._willSettleAt(r(t),e)},Y.prototype._settledAt=function(t,e,n){var r=this.promise;r._state===nt&&(this._remaining--,t===ot?j(r,n):this._result[e]=n),0===this._remaining&&S(r,this._result)},Y.prototype._willSettleAt=function(t,e){var n=this;E(t,void 0,function(t){return n._settledAt(rt,e,t)},function(t){return n._settledAt(ot,e,t)})},U.all=F,U.race=D,U.resolve=h,U.reject=K,U._setScheduler=n,U._setAsap=r,U._asap=J,U.prototype={constructor:U,then:l,"catch":function(t){return this.then(null,t)}},U.polyfill=W,U.Promise=U,U.polyfill(),U});
