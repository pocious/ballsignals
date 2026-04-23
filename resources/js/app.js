// Apply theme before first paint — manual override wins, otherwise auto day/night
(function () {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') {
        document.documentElement.classList.add('dark');
    } else if (saved === 'light') {
        document.documentElement.classList.remove('dark');
    } else {
        // Auto: dark between 8pm (20:00) and 6am (06:00)
        const hour = new Date().getHours();
        const isNight = hour >= 20 || hour < 6;
        document.documentElement.classList.toggle('dark', isNight);
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    // Dark mode — supports multiple toggle buttons (desktop + mobile)
    const toggles = document.querySelectorAll('#dark-toggle');

    function updateIcons() {
        const isDark = document.documentElement.classList.contains('dark');
        toggles.forEach(btn => {
            btn.querySelector('[data-icon="sun"]')?.classList.toggle('hidden', !isDark);
            btn.querySelector('[data-icon="moon"]')?.classList.toggle('hidden', isDark);
        });
    }

    updateIcons();

    toggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const saved = localStorage.getItem('theme');
            const isDark = document.documentElement.classList.contains('dark');
            if (!saved) {
                // Auto mode — manual override to opposite
                document.documentElement.classList.toggle('dark', !isDark);
                localStorage.setItem('theme', isDark ? 'light' : 'dark');
            } else {
                // Manual mode — toggle and save
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            }
            updateIcons();
        });
    });

    // Mobile menu
    const menuBtn   = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const iconHamburger = document.getElementById('icon-hamburger');
    const iconClose     = document.getElementById('icon-close');

    if (menuBtn && mobileMenu) {
        let open = false;

        menuBtn.addEventListener('click', () => {
            open = !open;
            mobileMenu.classList.toggle('menu-hidden', !open);
            mobileMenu.classList.toggle('menu-visible', open);
            iconHamburger?.classList.toggle('hidden', open);
            iconClose?.classList.toggle('hidden', !open);
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (open && !mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
                open = false;
                mobileMenu.classList.add('menu-hidden');
                mobileMenu.classList.remove('menu-visible');
                iconHamburger?.classList.remove('hidden');
                iconClose?.classList.add('hidden');
            }
        });
    }

    // Auto-scroll league pills — endless seamless loop
    const pills = document.getElementById('league-pills');
    if (pills && pills.scrollWidth > pills.clientWidth) {
        // Duplicate children for seamless loop
        [...pills.children].forEach(child => pills.appendChild(child.cloneNode(true)));

        const half = pills.scrollWidth / 2;
        let pos = 0;
        let pageScrolling = false;
        let scrollTimer;

        window.addEventListener('scroll', () => {
            pageScrolling = true;
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => { pageScrolling = false; }, 300);
        }, { passive: true });

        const step = () => {
            if (!pills.matches(':hover') && !pageScrolling) {
                pos += 0.4;
                if (pos >= half) pos -= half;
                pills.scrollLeft = pos;
            }
            requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    // Scroll shadow on header
    const header = document.getElementById('main-header');
    window.addEventListener('scroll', () => {
        header?.classList.toggle('shadow-2xl', window.scrollY > 8);
        header?.classList.toggle('shadow-black/60', window.scrollY > 8);
    }, { passive: true });
});
