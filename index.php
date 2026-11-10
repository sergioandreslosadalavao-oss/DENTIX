<?php
// index.php - Landing Page y Dashboard Principal con menús actualizados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENTIX | Clínica odontológica</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- HOJA DE ESTILOS DE LA LANDING -->
    <link rel="stylesheet" href="assests/css/index.css?v=20261103-appointment">
</head>
<body>

    <!-- Barra de progreso de lectura superior -->
    <div class="page-progress">
        <span class="page-progress__bar"></span>
    </div>

    <!-- Cabecera de navegación principal -->
    <header class="site-header">
        <div class="header__inner">
            <a href="#" class="brand">
                <div class="brand__mark">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div class="brand__text">
                    DENTIX
                    <small>Clínica odontológica</small>
                </div>
            </a>

            <!-- Menú superior -->
            <nav class="site-nav">
                <a href="#modulos">Servicios</a>
                <a href="#citas">Agendar cita</a>
            </nav>

            <!-- Botones de Acceso Rápido en la Barra Superior -->
            <div class="header__cta" style="display: flex; gap: 10px; align-items: center;">
                <!-- Botón de Administrador / Recepcionista -->
                <a href="view/recepcionista/lista_recepcionista.php" class="btn btn--ghost btn--small">
                     <i class="fa-solid fa-user-shield"></i> Administrador
                </a>
                
                <!-- Acceso al portal del paciente -->
                <a href="view/paciente/login_paciente.php" class="btn btn--primary btn--small">
                    <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
                </a>
            </div>

            <button class="nav-toggle" aria-label="Abrir menú" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Sección Hero Principal -->
    <section class="hero">
        <div class="hero__grid"></div>
        <div class="orb orb--a"></div>
        <div class="orb orb--b"></div>
        <div class="orb orb--c"></div>

        <div class="container hero__inner">
            <div class="hero__copy">
                <div class="eyebrow">Cuidamos la salud de tu sonrisa</div>
                <h1 class="hero__title">
                    <span class="line"><span>Tu sonrisa merece</span></span>
                    <span class="line"><span>atención de calidad</span></span>
                    <span class="line"><span><em>y confianza</em></span></span>
                </h1>
                <p class="hero__lead">
                    En DENTIX te acompañamos en el cuidado de tu salud oral. Solicita tu cita en línea y nuestro equipo de recepción te ayudará con la confirmación y asignación del profesional.
                </p>
                <div class="hero__actions">
                    <a href="view/paciente/login_paciente.php?next=portal_paciente.php" class="btn btn--primary btn--large">
                        <i class="fa-solid fa-calendar-plus"></i> Solicitar una cita
                    </a>
                    <a href="view/paciente/form_paciente.php" class="btn btn--ghost btn--large">
                        <i class="fa-solid fa-user-plus"></i> Crear cuenta de paciente
                    </a>
                </div>
            </div>

            <div class="hero__visual">
                <div class="hero-card hero-card--main" data-tilt>
                    <div class="hero-card__top">
                        <div class="hero-card__badge">
                            <i></i> Atención DENTIX
                        </div>
                        <span class="hero-card__time">Cuidado odontológico</span>
                    </div>
                    <div class="hero-card__art">
                        <div class="hero-card__halo"></div>
                        <img src="assests/img/proyecto-placeholder.svg" alt="Cuidado de la salud oral en DENTIX" class="hero-card__tooth" style="width: 120px;">
                        <span class="sparkle sparkle--1"></span>
                        <span class="sparkle sparkle--2"></span>
                        <span class="sparkle sparkle--3"></span>
                    </div>
                    <div class="hero-card__info">
                        <div>
                            <span class="hero-card__label">Compromiso con tu salud oral</span>
                            <span class="hero-card__name">Cuidamos tu sonrisa</span>
                        </div>
                        <div class="hero-card__avatar">DX</div>
                    </div>
                </div>

                <div class="floating-card floating-card--appointment">
                    <div class="floating-card__icon">
                        <i class="fa-solid fa-tooth"></i>
                    </div>
                    <div>
                        <strong>Atención odontológica</strong>
                        <span>Profesional y cercana</span>
                    </div>
                </div>

                <div class="floating-card floating-card--stat">
                    <div class="floating-card__icon floating-card__icon--coral">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <strong>Tu espacio personal</strong>
                        <span>Citas y seguimiento</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios y atención al paciente -->
    <section class="section" id="modulos">
        <div class="container">
            <div class="section-head section-head--center">
                <div class="eyebrow">Estamos aquí para cuidarte</div>
                <h2>Tu atención, <em>en un solo lugar</em></h2>
                <p>Conoce nuestros tratamientos, consulta la información de atención y gestiona tus citas.</p>
            </div>

            <div class="modules__grid">
                <!-- Pacientes -->
                <article class="card-modulo" data-tilt>
                    <span class="card-modulo__number">01</span>
                    <div class="card-modulo__icon">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <span class="card-modulo__tag">Tu espacio personal</span>
                    <h3>Perfil y citas</h3>
                    <p>Ingresa a tu cuenta para solicitar una cita y consultar su estado, tus próximas visitas y tu historial.</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="view/paciente/login_paciente.php" class="card-modulo__link text-primary">Ingresar a mi cuenta <i class="fa-solid fa-arrow-right-to-bracket"></i></a>
                    </div>
                </article>

                <!-- Odontólogos -->
                <article class="card-modulo card-modulo--odontologo" data-tilt>
                    <span class="card-modulo__number">02</span>
                    <div class="card-modulo__icon">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <span class="card-modulo__tag">Nuestro equipo</span>
                    <h3>Cuerpo Médico</h3>
                    <p>Un equipo odontológico comprometido con brindarte una atención respetuosa, clara y enfocada en tu bienestar.</p>
                    <div class="honor-card" aria-label="Reconocimiento al cuerpo médico">
                        <div class="honor-card__portrait" aria-hidden="true">
                            <i class="fa-solid fa-user-doctor"></i>
                            <span><i class="fa-solid fa-star"></i></span>
                        </div>
                    </div>
                </article>

                <!-- Servicios -->
                <article class="card-modulo card-modulo--servicio" data-tilt>
                    <span class="card-modulo__number">03</span>
                    <div class="card-modulo__icon">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <span class="card-modulo__tag">Nuestros tratamientos</span>
                    <h3>Servicios odontológicos</h3>
                    <p>Conoce las opciones de atención, sus características y tarifas antes de solicitar tu cita.</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="view/servicio/catalogo_publico.php" class="card-modulo__link">Conocer tratamientos <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
            
            <!-- Módulo de Citas -->
            <div class="appointment-cta-wrap" id="citas">
                <article class="appointment-cta">
                    <div class="appointment-cta__content">
                        <span class="appointment-cta__eyebrow"><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i> Tu bienestar, nuestra prioridad</span>
                        <h2>Es momento de cuidar <em>tu sonrisa</em></h2>
                        <p>Solicita tu cita de forma sencilla. Nuestro equipo de recepción se pondrá en contacto para confirmar el horario y asignarte el profesional indicado.</p>
                        <a href="view/paciente/login_paciente.php?next=portal_paciente.php" class="appointment-cta__button">
                            Solicitar mi cita <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                        <span class="appointment-cta__note"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i> Tu solicitud será revisada por recepción antes de quedar confirmada.</span>
                    </div>
                    <div class="appointment-cta__visual" aria-hidden="true">
                        <div class="appointment-cta__orbit appointment-cta__orbit--outer"></div>
                        <div class="appointment-cta__orbit appointment-cta__orbit--inner"></div>
                        <div class="appointment-cta__tooth"><i class="fa-solid fa-tooth"></i></div>
                        <div class="appointment-cta__mini-card appointment-cta__mini-card--top">
                            <span><i class="fa-solid fa-calendar-check"></i></span>
                            <div><strong>Tu próxima visita</strong><small>Empieza con una solicitud</small></div>
                        </div>
                        <div class="appointment-cta__mini-card appointment-cta__mini-card--bottom">
                            <span class="appointment-cta__online-dot"></span>
                            <div><strong>Atención coordinada</strong><small>Recepción confirma contigo</small></div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer__inner">
            <div class="footer__brand">
                <a href="#" class="brand brand--footer">
                    <div class="brand__mark"><i class="fa-solid fa-tooth"></i></div>
                    <div class="brand__text">DENTIX</div>
                </a>
                <p>Atención odontológica con calidez, claridad y compromiso con tu salud oral.</p>
            </div>
            <div class="footer__nav">
                <a href="#modulos">Nuestros servicios</a>
                <a href="view/paciente/form_paciente.php">Crear cuenta de paciente</a>
                <a href="view/recepcionista/lista_recepcionista.php" class="btn btn--ghost btn--small">
                    <i class="fa-solid fa-user-shield"></i> Administrador
                </a>
            </div>
            <div class="footer__copy">
                &copy; 2026 DENTIX. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- GSAP Animations Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- SCRIPT DE ANIMACIONES -->
    <script src="assests/js/index-animations.js"></script>
</body>
</html>