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
  //       $(this).addClass('jsfied');
        let primaryUl = $(this).find('ul');
        primaryUl.addClass('primary');
  //
  //       let containerEl = primaryUl.parent();
  //       if (containerEl.width() < elem.width()) {
  //         elem.first().appendTo('#edit-tags-down ul')
  //       }
  //       console.log(parentEl.width());
  //       console.log(elem.width());
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

const container = document.querySelector('#views-exposed-form-dont-miss-block-2 .tabs');
container.firstElementChild.classList.add('primary');
const primary = container.querySelector('.primary');
const primaryItems = container.querySelectorAll('.primary > li:not(.more)');
container.classList.add('--jsfied');

// insert "more" button and duplicate the list


primary.insertAdjacentHTML('beforeend', `
  <li class="more">
    <button type="button" aria-haspopup="true" aria-expanded="false">
      More
    </button>
    <ul class="secondary">
      ${primary.innerHTML}
    </ul>
  </li>
`)
const secondary = container.querySelector('.secondary');
const secondaryItems = secondary.querySelectorAll('li');
const allItems = container.querySelectorAll('li');
const moreLi = primary.querySelector('.more');
const moreBtn = moreLi.querySelector('button');

moreBtn.addEventListener('click', (e) => {
  e.preventDefault()
  container.classList.toggle('show-secondary')
  moreBtn.setAttribute('aria-expanded', container.classList.contains('show-secondary'))
})