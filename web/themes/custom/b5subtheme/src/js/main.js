
/**
 * @file
 *
 * Main JS.
 */

(function ($) {

    'use strict';
        /**
         * Show and hive search-block.
         */
    Drupal.behaviors.searchButton = {
        attach: function (context, settings) {
            $('#block-searchiconbtn', context).once('myCustomBehaviour').on('click', function () {
           $('.search-block-form').slideToggle("slow");
            });
            $(context).find('.btn-search').val('Search >');
        }
    };

})(jQuery);

