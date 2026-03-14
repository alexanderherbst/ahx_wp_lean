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

    // Accessibility: mark parents and add keyboard handlers for submenu navigation
    const parentItems = document.querySelectorAll('.menu-item-has-children');
    parentItems.forEach(function(li) {
        const link = li.querySelector('a');
        if (!link) return;
        link.setAttribute('aria-haspopup', 'true');
        link.setAttribute('aria-expanded', 'false');

        // Open submenu on focus
        link.addEventListener('focus', function() {
            link.setAttribute('aria-expanded', 'true');
            li.classList.add('focus');
        });

        // Close when leaving
        link.addEventListener('blur', function() {
            // small timeout to allow focusing into submenu
            setTimeout(function() {
                if (!li.contains(document.activeElement)) {
                    link.setAttribute('aria-expanded', 'false');
                    li.classList.remove('focus');
                }
            }, 100);
        });

        // Key handling: Enter/Space toggles, ArrowDown focuses first submenu link, Escape closes
        link.addEventListener('keydown', function(e) {
            const submenu = li.querySelector('.sub-menu, .children');
            if (!submenu) return;
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const expanded = link.getAttribute('aria-expanded') === 'true';
                link.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                if (!expanded) {
                    const first = submenu.querySelector('a');
                    if (first) first.focus();
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const first = submenu.querySelector('a');
                if (first) first.focus();
            } else if (e.key === 'Escape') {
                link.setAttribute('aria-expanded', 'false');
                link.focus();
            }
        });
    });
});
