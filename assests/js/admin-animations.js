/* =========================================================
    DENTIX · Animaciones del panel administrativo
   ========================================================= */

(() => {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const hasGsap = typeof window.gsap !== 'undefined';

    const formatNumber = (value, decimals = 0) =>
        Number(value).toLocaleString('es-ES', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
            useGrouping: 'always',
        });

    function setupSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('menu-toggle');
        const closeButton = document.getElementById('sidebar-close');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (!sidebar || !toggle) return;

        const openMenu = () => {
            sidebar.classList.add('is-open');
            document.body.classList.add('menu-open');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Cerrar menú');

            if (backdrop) backdrop.hidden = false;
        };

        const closeMenu = () => {
            sidebar.classList.remove('is-open');
            document.body.classList.remove('menu-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Abrir menú');

            if (backdrop) backdrop.hidden = true;
        };

        toggle.addEventListener('click', () => {
            if (sidebar.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        if (closeButton) closeButton.addEventListener('click', closeMenu);
        if (backdrop) backdrop.addEventListener('click', closeMenu);

        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeMenu();
        });

        const desktopQuery = window.matchMedia('(min-width: 901px)');
        desktopQuery.addEventListener('change', (event) => {
            if (event.matches) closeMenu();
        });
    }

    function setFinalCounters() {
        document.querySelectorAll('[data-counter]').forEach((element) => {
            const target = parseFloat(element.dataset.counter || '0');
            const decimals = parseInt(element.dataset.decimals || '0', 10);

            element.textContent = formatNumber(target, decimals);
        });
    }

    function initAnimations() {
        if (!hasGsap) return;

        if (window.ScrollTrigger) {
            gsap.registerPlugin(ScrollTrigger);
        }

        const from = (selector, vars) => {
            const elements = gsap.utils.toArray(selector);

            if (elements.length) {
                gsap.from(elements, vars);
            }
        };

        /* Barra de progreso */
        if (window.ScrollTrigger) {
            gsap.set('.page-progress__bar', { scaleX: 0, transformOrigin: '0% 50%' });
            gsap.to('.page-progress__bar', {
                scaleX: 1,
                ease: 'none',
                scrollTrigger: {
                    start: 0,
                    end: 'max',
                    scrub: 0.4,
                },
            });
        }

        /* Entrada general */
        from('.topbar', {
            y: -14,
            opacity: 0,
            duration: 0.55,
            ease: 'power3.out',
        });

        from('.sidebar__top', {
            y: -10,
            opacity: 0,
            duration: 0.55,
            delay: 0.05,
            ease: 'power3.out',
        });

        from('.side-link', {
            x: -12,
            opacity: 0,
            duration: 0.5,
            stagger: 0.06,
            delay: 0.1,
            ease: 'power3.out',
        });

        from('.page-hero', {
            y: 22,
            opacity: 0,
            duration: 0.7,
            delay: 0.12,
            ease: 'power3.out',
        });

        from('.media-slot', {
            scale: 0.97,
            opacity: 0,
            duration: 0.7,
            delay: 0.32,
            ease: 'power3.out',
        });

        from('.metric-card', {
            y: 18,
            opacity: 0,
            duration: 0.5,
            stagger: 0.08,
            delay: 0.28,
            ease: 'power3.out',
        });

        from('.alert', {
            y: 10,
            opacity: 0,
            duration: 0.5,
            delay: 0.3,
            ease: 'power3.out',
        });

        from('.panel', {
            y: 24,
            opacity: 0,
            duration: 0.65,
            delay: 0.32,
            ease: 'power3.out',
        });

        from('.data-table tbody tr', {
            y: 12,
            opacity: 0,
            duration: 0.4,
            stagger: 0.045,
            delay: 0.42,
            ease: 'power2.out',
        });

        from('.form-head', {
            y: 18,
            opacity: 0,
            duration: 0.6,
            delay: 0.08,
            ease: 'power3.out',
        });

        from('.form-card', {
            y: 24,
            opacity: 0,
            duration: 0.7,
            delay: 0.15,
            ease: 'power3.out',
        });

        from('.form-aside > *', {
            y: 24,
            opacity: 0,
            duration: 0.6,
            stagger: 0.1,
            delay: 0.24,
            ease: 'power3.out',
        });

        from('.field', {
            y: 10,
            opacity: 0,
            duration: 0.45,
            stagger: 0.035,
            delay: 0.3,
            ease: 'power2.out',
        });

        from('.app-footer', {
            opacity: 0,
            duration: 0.5,
            delay: 0.45,
            ease: 'power2.out',
        });

        /* Contadores */
        document.querySelectorAll('[data-counter]').forEach((element) => {
            const target = parseFloat(element.dataset.counter || '0');
            const decimals = parseInt(element.dataset.decimals || '0', 10);
            const state = { value: 0 };

            const animate = () => {
                gsap.to(state, {
                    value: target,
                    duration: 1.5,
                    ease: 'power2.out',
                    onUpdate: () => {
                        element.textContent = formatNumber(state.value, decimals);
                    },
                });
            };

            if (window.ScrollTrigger) {
                ScrollTrigger.create({
                    trigger: element,
                    start: 'top 96%',
                    once: true,
                    onEnter: animate,
                });
            } else {
                animate();
            }
        });

        window.addEventListener('load', () => {
            if (window.ScrollTrigger) ScrollTrigger.refresh();
        });

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                if (window.ScrollTrigger) ScrollTrigger.refresh();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        setupSidebar();

        if (reduceMotion) {
            setFinalCounters();
            return;
        }

        initAnimations();
    });
})();
