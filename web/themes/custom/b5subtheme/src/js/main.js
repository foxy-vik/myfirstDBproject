
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
     $('.region-nav-additional', context).once('myCustomBehaviour').each(function () {
        $(this).find('#block-searchiconbtn').on('click', function () {
          $('.search-block-form').slideToggle("slow");
            });
              $(this).find('.btn-search').val('Search >');
      });
        }
    };

})(jQuery);

