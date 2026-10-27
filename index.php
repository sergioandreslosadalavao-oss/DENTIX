<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DENTIX, Sistema de Citas Odontológicas: gestiona pacientes, odontólogos y servicios en un solo lugar.">
    <title>DENTIX | Sistema de Citas Odontológicas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assests/css/index.css">
</head>
<body>

    <div class="page-progress" aria-hidden="true">
        <span class="page-progress__bar"></span>
    </div>

    <header class="site-header">
        <div class="header__inner">
            <a class="brand" href="index.php" aria-label="DENTIX, inicio">
                <span class="brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 448 512" role="img">
                        <path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/>
                    </svg>
                </span>
                <span class="brand__text">
                    DENTIX
                    <small>Sistema de Citas Odontológicas</small>
                </span>
            </a>

            <nav class="site-nav" id="menu-principal" aria-label="Navegación principal">
                <a href="#modulos">Módulos</a>
                <a href="#beneficios">Beneficios</a>
                <a href="#proceso">Cómo funciona</a>
                <a href="#contacto">Contacto</a>
            </nav>

            <a class="btn btn--primary btn--small header__cta" href="#modulos">
                Comenzar
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main>

        <section class="hero" id="inicio">
            <span class="orb orb--a" aria-hidden="true"></span>
            <span class="orb orb--b" aria-hidden="true"></span>
            <span class="orb orb--c" aria-hidden="true"></span>
            <span class="hero__grid" aria-hidden="true"></span>

            <div class="container hero__inner">
                <div class="hero__copy">
                    <span class="eyebrow hero__eyebrow">Odontología digital, sin complicaciones</span>

                    <h1 class="hero__title" aria-label="Sonrisas sanas, agenda sin estrés">
                        <span class="line"><span>Sonrisas sanas,</span></span>
                        <span class="line"><span>agenda <em>sin estrés</em></span></span>
                    </h1>

                    <p class="hero__lead">
                        Centraliza pacientes, especialistas y servicios en una plataforma
                        clara y rápida, pensada para que tu consultorio respire tranquilidad.
                    </p>

                    <div class="hero__actions">
                        <a class="btn btn--primary btn--large" href="view/paciente/lista_pacientes.php">
                            Gestionar pacientes
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a class="btn btn--ghost btn--large" href="#modulos">Explorar módulos</a>
                    </div>

                    <div class="hero__trust">
                        <div class="avatars" aria-hidden="true">
                            <span style="--i:0">MR</span>
                            <span style="--i:1">JP</span>
                            <span style="--i:2">AL</span>
                        </div>
                        <p>Un equipo odontológico <strong>conectado</strong> con cada detalle.</p>
                    </div>
                </div>

                <div class="hero__visual">
                    <div class="hero-card hero-card--main" data-tilt>
                        <div class="hero-card__top">
                            <span class="hero-card__badge"><i></i> Próxima cita</span>
                            <span class="hero-card__time">10:30 AM</span>
                        </div>

                        <div class="hero-card__art">
                            <span class="hero-card__halo" aria-hidden="true"></span>
                            <svg class="hero-card__tooth" viewBox="0 0 448 512" aria-hidden="true">
                                <defs>
                                    <linearGradient id="toothGradient" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#3fd9c5"/>
                                        <stop offset="55%" stop-color="#10a294"/>
                                        <stop offset="100%" stop-color="#0a6f66"/>
                                    </linearGradient>
                                </defs>
                                <path fill="url(#toothGradient)" d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/>
                            </svg>

                            <span class="sparkle sparkle--1" aria-hidden="true"></span>
                            <span class="sparkle sparkle--2" aria-hidden="true"></span>
                            <span class="sparkle sparkle--3" aria-hidden="true"></span>
                        </div>

                        <div class="hero-card__info">
                            <div>
                                <span class="hero-card__label">Paciente</span>
                                <strong class="hero-card__name">María Fernanda Ruiz</strong>
                            </div>
                            <span class="hero-card__avatar">MR</span>
                        </div>

                        <div class="hero-card__footer">
                            <span class="hero-card__doctor">Dra. Laura Pérez</span>
                            <span class="hero-card__tag">Limpieza dental</span>
                        </div>
                    </div>

                    <div class="floating-card floating-card--appointment">
                        <span class="floating-card__icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                        <div>
                            <strong>Cita confirmada</strong>
                            <span>Hoy, 10:30 AM</span>
                        </div>
                    </div>

                    <div class="floating-card floating-card--stat">
                        <span class="floating-card__icon floating-card__icon--coral">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </span>
                        <div>
                            <strong>98%</strong>
                            <span>Satisfacción</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero__strip" aria-hidden="true">
                <div class="hero__strip-inner">
                    <span>Agenda inteligente</span><i></i>
                    <span>Historias clínicas</span><i></i>
                    <span>Especialistas conectados</span><i></i>
                    <span>Servicios organizados</span><i></i>
                    <span>Datos seguros</span><i></i>
                </div>
            </div>
        </section>

        <section class="stats" aria-label="Indicadores del sistema">
            <div class="container">
                <div class="stats__grid">
                    <div class="stat">
                        <span class="stat__value"><span data-counter="1200">0</span>+</span>
                        <span class="stat__label">Pacientes gestionados</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value"><span data-counter="12">0</span>+</span>
                        <span class="stat__label">Especialistas activos</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value"><span data-counter="24">0</span>/7</span>
                        <span class="stat__label">Disponibilidad</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value"><span data-counter="98">0</span>%</span>
                        <span class="stat__label">Satisfacción</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="modules section" id="modulos">
            <div class="container">
                <header class="section-head">
                    <span class="eyebrow">Módulos del sistema</span>
                    <h2>Todo lo que tu consultorio necesita, <em>en un solo lugar</em></h2>
                    <p>Accede a cada área de DENTIX con un clic y mantén la información de tu equipo siempre al día.</p>
                </header>

                <div class="modules__grid">
                    <a class="card-modulo card-modulo--paciente" href="view/paciente/lista_pacientes.php" data-tilt>
                        <span class="card-modulo__number" aria-hidden="true">01</span>
                        <span class="card-modulo__icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <span class="card-modulo__tag">Pacientes</span>
                        <h3>Historias clínicas</h3>
                        <p>Registra, busca y actualiza la información de cada paciente de forma ordenada y sencilla.</p>
                        <span class="card-modulo__link">
                            Ver pacientes
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="card-modulo card-modulo--odontologo" href="view/odontologo/lista_odontologos.php" data-tilt>
                        <span class="card-modulo__number" aria-hidden="true">02</span>
                        <span class="card-modulo__icon">
                            <svg viewBox="0 0 448 512" aria-hidden="true"><path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/></svg>
                        </span>
                        <span class="card-modulo__tag">Odontólogos</span>
                        <h3>Especialistas</h3>
                        <p>Administra el equipo odontológico, sus especialidades y consultorios asignados.</p>
                        <span class="card-modulo__link">
                            Ver odontólogos
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="card-modulo card-modulo--servicio" href="view/servicio/lista_servicios.php" data-tilt>
                        <span class="card-modulo__number" aria-hidden="true">03</span>
                        <span class="card-modulo__icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
                        </span>
                        <span class="card-modulo__tag">Servicios</span>
                        <h3>Tratamientos</h3>
                        <p>Define servicios, duraciones y precios para tener siempre una oferta clara y actualizada.</p>
                        <span class="card-modulo__link">
                            Ver servicios
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <section class="benefits section" id="beneficios">
            <div class="container benefits__grid">
                <div class="benefits__visual">
                    <div class="care-card">
                        <div class="care-card__header">
                            <div>
                                <span class="care-card__eyebrow">Resumen del consultorio</span>
                                <strong>Todo bajo control</strong>
                            </div>
                            <span class="care-card__pulse" aria-hidden="true"></span>
                        </div>

                        <div class="ring" style="--p:0">
                            <div class="ring__content">
                                <span class="ring__value"><span data-counter="86">0</span>%</span>
                                <span class="ring__label">Organizado</span>
                            </div>
                        </div>

                        <div class="care-card__rows">
                            <div class="care-card__row">
                                <span class="care-card__icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span>Pacientes al día</span>
                                <strong>100%</strong>
                            </div>
                            <div class="care-card__row">
                                <span class="care-card__icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span>Servicios cargados</span>
                                <strong>24</strong>
                            </div>
                            <div class="care-card__row">
                                <span class="care-card__icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span>Especialistas activos</span>
                                <strong>12</strong>
                            </div>
                        </div>
                    </div>

                    <div class="benefits__badge">
                        <strong>+1.200</strong>
                        <span>registros seguros</span>
                    </div>
                </div>

                <div class="benefits__copy">
                    <span class="eyebrow">Hecho para tu día a día</span>
                    <h2>Menos papeleo, <em>más sonrisas</em></h2>
                    <p>
                        DENTIX reúne la información esencial de tu consultorio para que puedas
                        dedicar más tiempo a lo importante: tus pacientes.
                    </p>

                    <div class="benefits__list">
                        <div class="benefit">
                            <span class="benefit__icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                            </span>
                            <div>
                                <h3>Información centralizada</h3>
                                <p>Pacientes, especialistas y servicios en un mismo entorno.</p>
                            </div>
                        </div>
                        <div class="benefit">
                            <span class="benefit__icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                            <div>
                                <h3>Búsqueda instantánea</h3>
                                <p>Encuentra cualquier registro en segundos, sin perder el ritmo.</p>
                            </div>
                        </div>
                        <div class="benefit">
                            <span class="benefit__icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            </span>
                            <div>
                                <h3>Datos seguros</h3>
                                <p>Una base organizada que protege la información de tu consultorio.</p>
                            </div>
                        </div>
                    </div>

                    <a class="btn btn--primary" href="#modulos">
                        Conoce los módulos
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <section class="process section" id="proceso">
            <div class="container">
                <header class="section-head section-head--center">
                    <span class="eyebrow">Así de simple</span>
                    <h2>Tu consultorio, <em>en tres pasos</em></h2>
                    <p>Un flujo claro para mantener todo organizado desde el primer registro.</p>
                </header>

                <div class="process__steps">
                    <span class="process__line" aria-hidden="true"><span class="process__line-fill"></span></span>

                    <article class="process__step">
                        <span class="process__num">01</span>
                        <h3>Registra al paciente</h3>
                        <p>Crea su historia clínica con los datos clave desde el primer contacto.</p>
                    </article>

                    <article class="process__step">
                        <span class="process__num">02</span>
                        <h3>Suma a los especialistas</h3>
                        <p>Organiza especialidades y consultorios para asignar la atención ideal.</p>
                    </article>

                    <article class="process__step">
                        <span class="process__num">03</span>
                        <h3>Gestiona los servicios</h3>
                        <p>Define tratamientos, duraciones y precios para una atención sin sorpresas.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta section" id="contacto">
            <div class="container">
                <div class="cta__card">
                    <span class="cta__glow cta__glow--a" aria-hidden="true"></span>
                    <span class="cta__glow cta__glow--b" aria-hidden="true"></span>
                    <svg class="cta__tooth" viewBox="0 0 448 512" aria-hidden="true">
                        <path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/>
                    </svg>

                    <span class="eyebrow eyebrow--light">Empieza hoy</span>
                    <h2>Tu consultorio, más organizado que nunca</h2>
                    <p>Da el primer paso y descubre una forma más simple de gestionar tu clínica dental.</p>

                    <div class="cta__actions">
                        <a class="btn btn--white btn--large" href="view/paciente/form_paciente.php">
                            Registrar paciente
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a class="btn btn--outline-light btn--large" href="view/servicio/lista_servicios.php">Ver servicios</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="container footer__inner">
            <div class="footer__brand">
                <a class="brand brand--footer" href="index.php">
                    <span class="brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 448 512"><path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/></svg>
                    </span>
                    <span class="brand__text">
                        DENTIX
                        <small>Sistema de Citas Odontológicas</small>
                    </span>
                </a>
                <p>Una gestión clara para sonrisas más sanas.</p>
            </div>

            <nav class="footer__nav" aria-label="Enlaces del sistema">
                <a href="view/paciente/lista_pacientes.php">Pacientes</a>
                <a href="view/odontologo/lista_odontologos.php">Odontólogos</a>
                <a href="view/servicio/lista_servicios.php">Servicios</a>
            </nav>

            <p class="footer__copy">© <?= date('Y') ?> DENTIX. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js"></script>
    <script src="assests/js/index-animations.js"></script>
</body>
</html>
