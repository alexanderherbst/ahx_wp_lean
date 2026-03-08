document.addEventListener("DOMContentLoaded", function() {
    const burgerButton = document.getElementById("burger-menu");
    const menuList = document.querySelector(".menu-list");

    if (burgerButton && menuList) {
        burgerButton.addEventListener("click", function() {
            menuList.classList.toggle("show");
        });
    }
});
