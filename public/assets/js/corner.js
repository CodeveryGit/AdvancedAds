/**
 * Process ads with cache busting 'On'
 */
var Advanced_Ads_Corner_cache_busting;
if ( ! Advanced_Ads_Corner_cache_busting ) {
	Advanced_Ads_Corner_cache_busting = {
        doc_loaded: false,
        bufferedAds: [],

        flush: function() {
            var _bufferedAds = this.bufferedAds;
            this.bufferedAds = [];

            for ( var i = 0; i < _bufferedAds.length; i++ ) {
                this._process_item( jQuery( _bufferedAds[i] ) );
            }
        },

        _process_item: function( banner ) {

            jQuery('<div class="corner-peel-shadow"></div>').insertAfter(banner);

            var banner_id = banner.attr('id'),
                name = 'timeout_placement_' + jQuery(banner).data('id'),
                value = '1',
                days = jQuery(banner).data('close_for');

            advads_corner_items.conditions[banner_id] = advads_corner_items.conditions[banner_id] || {};

            if (banner.hasClass(Advanced_Ads_Corner_settings.corner_class + '-onload')) {
                advads_corner_check_item_conditions(banner_id);
            }

            var img = banner.find('a img'),
                imgSrc = img.attr('src');
            if (imgSrc) {
                banner.css({backgroundImage: 'url("'+imgSrc+'")'});
            }

            if (jQuery(banner).hasClass('advads-corner-close-opened')) {

                jQuery(banner).hover(function () {

                }, function () {
                    jQuery(this).addClass('corner-peel-hide');
                    jQuery(this).fadeOut(500, function() { jQuery(this).remove(); });

                    createCookie(name,value,days);
                });
            }

            else if (jQuery(banner).hasClass('advads-corner-close-clicked')) {

                jQuery(banner).click(function () {
                    jQuery(this).addClass('corner-peel-hide');
                    jQuery(this).fadeOut(500, function() { jQuery(this).remove(); });

                    createCookie(name,value,days);
                });
            }

            if (jQuery(banner).hasClass('advads-corner-show-in-rectangle')) {
                jQuery(banner).addClass('corner-peel-hide-triangle');
            }

        },

        observe: function (event) {
            if ( event.event === 'postscribe_done' && event.ref && event.ad ) {
                var banner = jQuery( event.ref ).children( 'div' );
                if ( ! banner.hasClass( Advanced_Ads_Corner_settings.corner_class + '' ) ) {
                    return;
                }

                if ( Advanced_Ads_Corner_cache_busting.doc_loaded ) {
                    Advanced_Ads_Corner_cache_busting.bufferedAds.push( banner );
                    Advanced_Ads_Corner_cache_busting.flush();
                }
            }
        }
	}
}

// Advanced Ads Pro is enabled
if ( typeof advanced_ads_pro === 'object' && advanced_ads_pro !== null ) {
	// observe cache busting done event
    advanced_ads_pro.postscribeObservers.add( Advanced_Ads_Corner_cache_busting.observe );
}

/**
 * Process ads with cache busting 'Off'
 */
jQuery(document).ready(function ($) {
    Advanced_Ads_Corner_cache_busting.doc_loaded = true;

    jQuery( '.' + Advanced_Ads_Corner_settings.corner_class ).each(function () {
        Advanced_Ads_Corner_cache_busting.bufferedAds.push( jQuery( this ) );
    });

    Advanced_Ads_Corner_cache_busting.flush();
});

/**
 * check item conditions and display the ad if all conditions are true
 *
 * @param {string} id of the ad, without #
 * @returns {bool} true, if item can be displayed
 */
function advads_corner_check_item_conditions(id) {

    var item = jQuery('#' + id);
    if (item.length == 0) {
        return;
    }

    advads_corner_items.showed.push(id);
    var ad = jQuery('#' + id);
    ad.show();
}

function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}