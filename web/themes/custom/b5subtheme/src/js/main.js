// let link = document.querySelector('#block-searchiconbtn');
// let formLink = document.querySelector('.search-block-form');
let searchBtn = document.querySelector('.btn-search');
searchBtn.value = 'Search >';
//
// link.addEventListener('click', addClass);
//
// function addClass(){
//     formLink.classList.toggle('active');
// }


/**
 * @file
 */

(function ($) {

    'use strict';

    Drupal.behaviors.searchButton = {
        attach: function (context, settings) {
            $(context).find('#block-searchiconbtn').once('myCustomBehaviour').on('click', function () {
           $('.search-block-form').toggle(800);
            });
        }
    };

})(jQuery);

