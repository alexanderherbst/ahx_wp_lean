document.addEventListener("DOMContentLoaded", function() {
    const burgerButton = document.getElementById("burger-menu");
    const menuList = document.getElementById('primary-menu') || document.querySelector('.menu-list');

    if (burgerButton && menuList) {
        burgerButton.addEventListener("click", function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            menuList.classList.toggle("show");
        });
    }
});
