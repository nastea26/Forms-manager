const menuEL = document.querySelector('.menu');
const hamburgerEL = document.querySelector('.hamburger-menu');
hamburgerEL.addEventListener('click',()=>{
    menuEL.classList.toggle('menu-open');
});