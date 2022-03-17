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
        let $container = $(this);
        $container.find('ul').addClass('primary');
        let $primary = $(this).find('.primary');
        let $primaryItems = $('.primary > li:not(.more)');
        $primary.find('li:first-child').addClass('tabs-first-child');
        let $secondary = $primary.clone().appendTo($container.last());
        $secondary.removeClass('primary').addClass('secondary').hide();
        $secondary.wrap(function () {
          return '<span class="more">' + '</span>';
        });
        $('<h2>Lifestyle news</h2>').prependTo($primary);
        $container.find('.more').prepend('<button type="button" aria-haspopup="true" aria-expanded="false"> More</button>');
        let $moreBtn = $container.find('.more button').click(function () {
          $secondary.toggle();
          $moreBtn.attr('aria-expanded');
        });
      });

        let $btnMoreW = $('.tabs .more').outerWidth();
        let $primary = $('.tabs .primary');
        let $primaryAllItems = $('.tabs .primary > li');
        let $primW = $primary.width();
        let $secondaryAllItems = $('.more .secondary > li');

        $primaryAllItems.each(function (index, value) {
          let $itemWidth = value.offsetWidth;
          if ($primW >= $btnMoreW + $itemWidth + 120) {
            $btnMoreW += $itemWidth;
            } else {
            $($secondaryAllItems.get(index)).addClass('show');
          }
        })
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