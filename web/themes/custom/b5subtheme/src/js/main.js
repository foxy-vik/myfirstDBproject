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

  Drupal.behaviors.listMenu = {
    attach: function (context, settings) {
      $('#views-exposed-form-dont-miss-block-2 .tabs', context).once('myCustomBehaviourListMenu').each(function () {
        const container = $(this);
        container.find('ul').addClass('primary');
        const primary = $(this).find('.primary');

        const secondary = primary.clone().appendTo(primary.last());
        secondary.removeClass('primary').addClass('secondary').hide();
        secondary.wrap(function () {
          return '<li class="more">' + '</li>';
        });
        $(this).find('.more').prepend('<button type="button" aria-haspopup="true" aria-expanded="false"> More</button>');
        const moreBtn = $(this).find('.more button').mouseover(function () {
          secondary.toggle();
          moreBtn.attr('aria-expanded', container.classList.contains('show-secondary'));
        });
      });
    }
  };

  Drupal.behaviors.addClassMenuDark = {
    attach: function (context, settings) {
      $('.navbar-light', context).once('darkClass').each(function () {
        let screenWidth = $(document).width();
        let colEl = $(this).find('.collapsed');
        colEl.addClass('navbar-dark');
      });
    }
  };


})(jQuery);