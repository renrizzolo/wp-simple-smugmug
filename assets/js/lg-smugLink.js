
/*! lg-smugLink
*  for lightgallery: http://sachinchoolur.github.io/lightGallery
* */

(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module unless amdModuleId is set
    define(['jquery'], function (a0) {
      return (factory(a0));
    });
  } else if (typeof exports === 'object') {
    // Node. Does not work with strict CommonJS, but
    // only CommonJS-like environments that support module.exports,
    // like Node.
    module.exports = factory(require('jquery'));
  } else {
    factory(jQuery);
  }
}(this, function ($) {

(function() {

var defaults = {
    smugLink: false,
    smugLinkIcon: 'lg-cart lg-icon'
};
 
var smugLink = function(element) {
 
 
    this.core = $(element).data('lightGallery');

    this.core.s = $.extend({}, defaults, this.core.s);

    if (this.core.s.smugLink) {
    	this.core.$outer.find('.lg-toolbar').append('<a id="lg-smugLink" target="_blank" class="'+this.core.s.smugLinkIcon+'"></a>');
    }
    this.init();
 
    return this;
};
 
smugLink.prototype.init = function() {
	var _this = this;
        if (_this.core.s.smugLink) {
        _this.core.$el.on('onAfterSlide.lg.tm', function(event, prevIndex, index) {

            setTimeout(function() {

                var _src;
                if (_this.core.s.dynamic) {
                    _src = _this.core.s.dynamicEl[index].smugLinkUrl !== false && (_this.core.s.dynamicEl[index].smugLinkUrl || _this.core.s.dynamicEl[index].src);
                } else {
                    _src = _this.core.$items.eq(index).attr('data-smug-url') !== 'false' && (_this.core.$items.eq(index).attr('data-smug-url') || _this.core.$items.eq(index).attr('href') || _this.core.$items.eq(index).attr('data-src'));

                }

                if (_src) {
                    $('#lg-smugLink').attr('href', _src);
                    _this.core.$outer.removeClass('lg-hide-smugLink');
                } else {
                    _this.core.$outer.addClass('lg-hide-smugLink');
                }
            })

       });
    }
};
 
/**
 * Destroy function must be defined.
 * lightgallery will automatically call your module destroy function 
 * before destroying the gallery
 */
smugLink.prototype.destroy = function() {
 
};

  $.fn.lightGallery.modules.smugLink = smugLink;

})();



}));