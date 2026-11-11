/* =========================================================
    DENTIX · Animaciones de la landing (GSAP + ScrollTrigger)
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

    function setupHeader() {
        const header = document.querySelector('.site-header');
        const toggle = document.querySelector('.nav-toggle');
        const nav = document.querySelector('.site-nav');

        if (header) {
            const onScroll = () => {
                header.classList.toggle('is-scrolled', window.scrollY > 24);
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        }

        if (toggle && nav) {
            const closeMenu = () => {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Abrir menú');
            };

            toggle.addEventListener('click', () => {
                const isOpen = !nav.classList.contains('is-open');

                nav.classList.toggle('is-open', isOpen);
                toggle.classList.toggle('is-open', isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
            });

            nav.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', closeMenu);
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMenu();
            });
        }
    }

    function setupUserManualModal() {
        const modal = document.getElementById('userManualModal');
        if (!modal) return;

        const openTriggers = document.querySelectorAll('[data-manual-open]');
        const closeTriggers = modal.querySelectorAll('[data-manual-close]');
        const tabs = modal.querySelectorAll('[data-manual-tab]');
        const panels = modal.querySelectorAll('[data-manual-panel]');
        let lastFocusedElement = null;

        const activateTab = (tabName) => {
            tabs.forEach((tab) => {
                const isMatch = tab.dataset.manualTab === tabName;
                tab.classList.toggle('is-active', isMatch);
                tab.setAttribute('aria-selected', String(isMatch));
            });

            panels.forEach((panel) => {
                const isMatch = panel.dataset.manualPanel === tabName;
                panel.classList.toggle('is-active', isMatch);
                if (isMatch) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', '');
                }
            });

            const body = modal.querySelector('.manual-modal__body');
            if (body) body.scrollTop = 0;
        };

        const openModal = (event) => {
            if (event) {
                event.preventDefault();
                const targetTab = event.currentTarget && event.currentTarget.dataset
                    ? event.currentTarget.dataset.manualOpen
                    : '';
                if (targetTab) {
                    activateTab(targetTab);
                }
            }
            lastFocusedElement = document.activeElement;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('is-manual-open');

            const closeBtn = modal.querySelector('.manual-modal__close');
            if (closeBtn) closeBtn.focus();
        };

        const closeModal = () => {
            if (!modal.classList.contains('is-open')) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('is-manual-open');

            if (window.location.hash === '#manual-usuario') {
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        };

        openTriggers.forEach((trigger) => {
            trigger.addEventListener('click', openModal);
        });

        closeTriggers.forEach((trigger) => {
            trigger.addEventListener('click', closeModal);
        });

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activateTab(tab.dataset.manualTab || 'inicio');
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        if (window.location.hash === '#manual-usuario') {
            openModal();
        }
    }

    function setupMegaFooter() {
        const footer = document.getElementById('pie-dentix');
        if (!footer) return;

        // Estado dinámico del horario de la clínica (08:00 a 19:30)
        const liveBtn = document.getElementById('footerLiveScheduleBtn');
        const liveText = document.getElementById('footerLiveScheduleText');
        const now = new Date();
        const totalMinutes = now.getHours() * 60 + now.getMinutes();
        const isOpenHours = totalMinutes >= 8 * 60 && totalMinutes <= 19 * 60 + 30;

        if (liveBtn && liveText) {
            liveBtn.classList.toggle('is-after-hours', !isOpenHours);
            liveText.textContent = isOpenHours
                ? 'Colombia · Clínica abierta (08:00–19:30)'
                : 'Colombia · Agenda online 24/7';
        }

        // Panel interactivo para los botones superiores del pie
        const pillBtns = footer.querySelectorAll('[data-footer-pill]');
        const pillDrawer = document.getElementById('footerPillDrawer');
        const pillMsg = document.getElementById('footerPillMessage');
        const pillIcon = document.getElementById('footerPillIcon');
        const pillClose = document.getElementById('footerPillClose');
        let activePill = null;

        const pillContent = {
            idioma: {
                icon: 'fa-solid fa-globe',
                text: 'Plataforma configurada en Español (Colombia) para pacientes, odontólogos y personal de recepción de DENTIX.',
            },
            moneda: {
                icon: 'fa-solid fa-peso-sign',
                text: 'Todas las tarifas de referencia del catálogo de tratamientos se muestran en Pesos Colombianos (COP) y se confirman tras la valoración clínica.',
            },
            horario: {
                icon: 'fa-solid fa-clock',
                text: isOpenHours
                    ? 'Atención presencial activa hoy de 08:00 a. m. a 07:30 p. m. en bloques de 30 minutos. También puedes solicitar tu cita en línea en segundos.'
                    : 'En este momento nuestro horario presencial (08:00 a. m. – 07:30 p. m.) ha cerrado, pero puedes crear tu cuenta y enviar solicitudes de cita en línea las 24 horas.',
            },
        };

        const closePillDrawer = () => {
            activePill = null;
            pillBtns.forEach((b) => b.setAttribute('aria-expanded', 'false'));
            if (pillDrawer) pillDrawer.hidden = true;
        };

        pillBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.footerPill;
                if (activePill === key) {
                    closePillDrawer();
                    return;
                }
                const info = pillContent[key];
                if (!info || !pillDrawer || !pillMsg || !pillIcon) return;

                activePill = key;
                pillBtns.forEach((b) => b.setAttribute('aria-expanded', String(b === btn)));
                pillIcon.className = info.icon;
                pillMsg.textContent = info.text;
                pillDrawer.hidden = false;
            });
        });

        if (pillClose) {
            pillClose.addEventListener('click', closePillDrawer);
        }

        // Panel desplegable para los enlaces legales inferiores
        const legalBtns = footer.querySelectorAll('[data-legal-topic]');
        const legalDrawer = document.getElementById('footerLegalDrawer');
        const legalTitle = document.getElementById('footerLegalTitle');
        const legalBody = document.getElementById('footerLegalBody');
        const legalClose = document.getElementById('footerLegalClose');
        let activeLegal = null;

        const legalTopics = {
            condiciones: {
                title: 'Condiciones de Uso del Portal DENTIX',
                body: 'El agendamiento funciona en bloques de 30 minutos (08:00 a 19:30), con un máximo de una cita activa por día y al menos 4 horas entre citas activas. Las solicitudes en línea quedan en estado Pendiente hasta su confirmación por Recepción.',
            },
            privacidad: {
                title: 'Aviso de Privacidad · Ley Estatutaria 1581 de 2012',
                body: 'DENTIX recolecta tus datos personales (nombre, cédula, fecha de nacimiento, teléfono y correo) exclusivamente para gestionar tu atención odontológica, confirmar citas y cumplir tus derechos ARCO de acceso, rectificación y supresión.',
            },
            salud: {
                title: 'Privacidad de Datos Sensibles de Salud',
                body: 'Tus alertas de alergias médicas, diagnósticos y procedimientos realizados se tratan bajo acceso restringido por roles: solo el equipo odontológico autorizado y el titular pueden consultar la información clínica pertinente.',
            },
            historia: {
                title: 'Reserva Legal de la Historia Clínica · Ley 35 de 1989',
                body: 'De acuerdo con el Código de Ética del Odontólogo Colombiano y la Resolución 1995 de 1999, la historia clínica es un documento privado sometido a reserva y protegido mediante integridad referencial en nuestra base de datos.',
            },
        };

        const closeLegalDrawer = () => {
            activeLegal = null;
            legalBtns.forEach((b) => b.classList.remove('is-active'));
            if (legalDrawer) legalDrawer.hidden = true;
        };

        legalBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.legalTopic;
                if (activeLegal === key) {
                    closeLegalDrawer();
                    return;
                }
                const topic = legalTopics[key];
                if (!topic || !legalDrawer || !legalTitle || !legalBody) return;

                activeLegal = key;
                legalBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                legalTitle.textContent = topic.title;
                legalBody.textContent = topic.body;
                legalDrawer.hidden = false;
            });
        });

        if (legalClose) {
            legalClose.addEventListener('click', closeLegalDrawer);
        }
    }

    function setFinalCounters() {
        document.querySelectorAll('[data-counter]').forEach((element) => {
            const target = parseFloat(element.dataset.counter || '0');
            const decimals = parseInt(element.dataset.decimals || '0', 10);

            element.textContent = formatNumber(target, decimals);
        });

        const ring = document.querySelector('.ring');
        if (ring) ring.style.setProperty('--p', '86');
    }

    function setupMarquee() {
        if (!hasGsap) return;

        const strip = document.querySelector('.hero__strip-inner');
        if (!strip) return;

        const group = document.createElement('div');
        group.className = 'hero__strip-group';

        while (strip.firstChild) {
            group.appendChild(strip.firstChild);
        }

        const clone = group.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        strip.append(group, clone);

        if (reduceMotion) return;

        gsap.fromTo(
            strip,
            { xPercent: 0 },
            {
                xPercent: -50,
                duration: 34,
                ease: 'none',
                repeat: -1,
            }
        );
    }

    function initAnimations() {
        if (!hasGsap) return;

        gsap.registerPlugin(ScrollTrigger);

        const mm = gsap.matchMedia();

        /* -------------------------------------------------
           Barra de progreso del scroll
        ------------------------------------------------- */
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

        /* -------------------------------------------------
           Entrada del hero
        ------------------------------------------------- */
        if (document.querySelector('.hero')) {
            const heroTimeline = gsap.timeline({ defaults: { ease: 'power3.out' } });

            heroTimeline
                .from('.site-header', { y: -90, opacity: 0, duration: 0.9 })
                .from('.hero__eyebrow', { y: 22, opacity: 0, duration: 0.6 }, '-=0.55')
                .from(
                    '.hero__title .line > span',
                    { yPercent: 115, duration: 1, stagger: 0.12 },
                    '-=0.35'
                )
                .from('.hero__lead', { y: 22, opacity: 0, duration: 0.7 }, '-=0.65')
                .from(
                    '.hero__actions .btn',
                    { y: 20, opacity: 0, duration: 0.55, stagger: 0.1 },
                    '-=0.5'
                )
                .from('.hero__trust', { y: 16, opacity: 0, duration: 0.6 }, '-=0.4')
                .from(
                    '.hero-card--main',
                    {
                        y: 44,
                        opacity: 0,
                        scale: 0.92,
                        rotate: -5,
                        duration: 1.1,
                    },
                    0.35
                )
                .from(
                    '.floating-card',
                    {
                        y: 26,
                        opacity: 0,
                        scale: 0.86,
                        stagger: 0.18,
                        duration: 0.7,
                        ease: 'back.out(1.6)',
                    },
                    '-=0.55'
                )
                .from(
                    '.orb',
                    {
                        scale: 0,
                        opacity: 0,
                        duration: 1.4,
                        stagger: 0.15,
                        ease: 'power2.out',
                    },
                    0.25
                )
                .from('.hero__strip', { opacity: 0, y: 18, duration: 0.7 }, '-=0.45');

            /* Movimiento ambiental */
            gsap.to('.floating-card--appointment', {
                y: -14,
                rotate: -1.5,
                duration: 2.7,
                delay: 1.6,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });

            gsap.to('.floating-card--stat', {
                y: 12,
                duration: 3.1,
                delay: 1.9,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });

            gsap.to('.sparkle', {
                scale: 1.35,
                opacity: 0.55,
                duration: 1.8,
                stagger: 0.35,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });

            gsap.to('.orb--a', {
                x: 34,
                y: -22,
                duration: 7,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });

            gsap.to('.orb--c', {
                x: -26,
                y: 20,
                duration: 8,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });

            /* Parallax del hero al hacer scroll */
            gsap.to('.hero__visual', {
                yPercent: 10,
                ease: 'none',
                scrollTrigger: {
                    trigger: '.hero',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });

            gsap.to('.hero__copy', {
                yPercent: -6,
                ease: 'none',
                scrollTrigger: {
                    trigger: '.hero',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });
        }

        /* -------------------------------------------------
           Indicadores y contadores
        ------------------------------------------------- */
        gsap.from('.stats__grid', {
            y: 40,
            opacity: 0,
            duration: 0.9,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: '.stats__grid',
                start: 'top 88%',
                once: true,
            },
        });

        document.querySelectorAll('[data-counter]').forEach((element) => {
            const target = parseFloat(element.dataset.counter || '0');
            const decimals = parseInt(element.dataset.decimals || '0', 10);
            const state = { value: 0 };

            ScrollTrigger.create({
                trigger: element,
                start: 'top 92%',
                once: true,
                onEnter: () => {
                    gsap.to(state, {
                        value: target,
                        duration: 1.7,
                        ease: 'power2.out',
                        onUpdate: () => {
                            element.textContent = formatNumber(state.value, decimals);
                        },
                    });
                },
            });
        });

        const ring = document.querySelector('.ring');
        if (ring) {
            gsap.to(ring, {
                '--p': 86,
                duration: 1.7,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: ring,
                    start: 'top 86%',
                    once: true,
                },
            });
        }

        /* -------------------------------------------------
           Encabezados de sección
        ------------------------------------------------- */
        gsap.utils.toArray('.section-head').forEach((head) => {
            gsap.from(head.children, {
                y: 28,
                opacity: 0,
                duration: 0.7,
                stagger: 0.1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: head,
                    start: 'top 86%',
                    once: true,
                },
            });
        });

        /* -------------------------------------------------
           Tarjetas de módulos
        ------------------------------------------------- */
        gsap.from('.card-modulo', {
            y: 48,
            opacity: 0,
            duration: 0.8,
            stagger: 0.14,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: '.modules__grid',
                start: 'top 84%',
                once: true,
            },
        });

        /* -------------------------------------------------
           Beneficios
        ------------------------------------------------- */
        if (document.querySelector('.benefits__visual')) {
            gsap.from('.benefits__visual', {
                x: -40,
                opacity: 0,
                duration: 1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.benefits',
                    start: 'top 78%',
                    once: true,
                },
            });

            gsap.from('.benefit', {
                x: 34,
                opacity: 0,
                duration: 0.75,
                stagger: 0.12,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.benefits__list',
                    start: 'top 86%',
                    once: true,
                },
            });
        }

        /* -------------------------------------------------
           Proceso
        ------------------------------------------------- */
        gsap.from('.process__step', {
            y: 38,
            opacity: 0,
            duration: 0.8,
            stagger: 0.16,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: '.process__steps',
                start: 'top 82%',
                once: true,
            },
        });

        if (document.querySelector('.process__line-fill')) {
            mm.add('(min-width: 901px)', () => {
                gsap.fromTo(
                    '.process__line-fill',
                    { scaleX: 0 },
                    {
                        scaleX: 1,
                        transformOrigin: 'left center',
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.process__steps',
                            start: 'top 76%',
                            end: 'bottom 62%',
                            scrub: 0.8,
                        },
                    }
                );
            });

            mm.add('(max-width: 900px)', () => {
                gsap.fromTo(
                    '.process__line-fill',
                    { scaleY: 0 },
                    {
                        scaleY: 1,
                        transformOrigin: 'center top',
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.process__steps',
                            start: 'top 76%',
                            end: 'bottom 62%',
                            scrub: 0.8,
                        },
                    }
                );
            });
        }

        /* -------------------------------------------------
           Llamado a la acción
        ------------------------------------------------- */
        gsap.from('.cta__card', {
            y: 50,
            opacity: 0,
            scale: 0.97,
            duration: 1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: '.cta',
                start: 'top 84%',
                once: true,
            },
        });

        /* -------------------------------------------------
           Directorio del Mega Footer
        ------------------------------------------------- */
        if (document.querySelector('.mega-footer__directory')) {
            gsap.from('.mega-footer__item', {
                y: 16,
                opacity: 0,
                duration: 0.45,
                stagger: 0.03,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: '.mega-footer__directory',
                    start: 'top 92%',
                    once: true,
                },
            });
        }

        /* -------------------------------------------------
           Inclinación 3D de tarjetas
        ------------------------------------------------- */
        if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            document.querySelectorAll('[data-tilt]').forEach((card) => {
                const strength = 7;
                const rotateX = gsap.quickTo(card, 'rotationX', {
                    duration: 0.5,
                    ease: 'power2.out',
                });
                const rotateY = gsap.quickTo(card, 'rotationY', {
                    duration: 0.5,
                    ease: 'power2.out',
                });

                card.addEventListener('pointermove', (event) => {
                    const bounds = card.getBoundingClientRect();
                    const px = (event.clientX - bounds.left) / bounds.width - 0.5;
                    const py = (event.clientY - bounds.top) / bounds.height - 0.5;

                    rotateX(-py * strength);
                    rotateY(px * strength);
                });

                card.addEventListener('pointerleave', () => {
                    rotateX(0);
                    rotateY(0);
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        setupHeader();
        setupUserManualModal();
        setupMegaFooter();
        setupMarquee();

        if (reduceMotion) {
            setFinalCounters();
            return;
        }

        initAnimations();

        window.addEventListener('load', () => {
            if (hasGsap) ScrollTrigger.refresh();
        });

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                if (hasGsap) ScrollTrigger.refresh();
            });
        }
    });
})();
