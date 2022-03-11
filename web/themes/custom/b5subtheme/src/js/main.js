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
      $('.form-select', context).once('myCustomBehaviourListMenu').each(function () {
        let elem = $(this).find('ul');
        let parentEl = elem.parent();
        if (parentEl.width() < elem.width()) {
          elem.first().appendTo('#edit-tags-down ul')
        }
        console.log(parentEl.width());
        console.log(elem.width());
      });
    }
  };

  Drupal.behaviors.addClassMenuDark = {
    attach: function (context, settings) {
      $('.navbar-light', context).once('darkClass').each(function () {
        let screenWidth = $(document).width();
        let colEl = $(this).find('.collapsed');
        if (screenWidth <= 768) {
          colEl.addClass('navbar-dark');
        } else {
          colEl.removeClass('navbar-dark');
        }

        console.log(screenWidth);
      });
    }
  };


})(jQuery);
