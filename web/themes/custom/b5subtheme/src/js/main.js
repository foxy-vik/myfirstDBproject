// $(document).find('#block-searchiconbtn').on('click', function () {
//     $('.search-block-form').toggleClass('active');
// });

let link = document.querySelector('#block-searchiconbtn');
let formLink = document.querySelector('.search-block-form');
let searchBtn = document.querySelector('.btn-search');

searchBtn.value = 'Search >';

link.addEventListener('click', addClass);

function addClass(){
    formLink.classList.toggle('active');
}



