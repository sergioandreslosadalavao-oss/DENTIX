<?php
// index.php - Landing Page y Dashboard Principal con menús actualizados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCO - Sistema de Citas Odontológicas</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- HOJA DE ESTILOS DE LA LANDING -->
    <link rel="stylesheet" href="assests/css/index.css">
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
                    <small>Sistema Odontológico</small>
                </div>
            </a>

            <!-- Menú superior -->
            <nav class="site-nav">
                <a href="#modulos">Módulos</a>
                <a href="#beneficios">Beneficios</a>
            </nav>

            <!-- Botones de Acceso Rápido en la Barra Superior -->
            <div class="header__cta" style="display: flex; gap: 10px; align-items: center;">
                <!-- Botón de Administrador / Recepcionista -->
                <a href="view/recepcionista/lista_recepcionista.php" class="btn btn--ghost btn--small">
                     <i class="fa-solid fa-user-shield"></i> Administrador
                </a>
                
                <!-- Botón de Registro de Paciente -->
                <a href="view/paciente/form_paciente.php" class="btn btn--primary btn--small">
                    <i class="fa-solid fa-user-plus"></i> Registrarse
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
                <div class="eyebrow">Gestión Odontológica Inteligente</div>
                <h1 class="hero__title">
                    <span class="line"><span>Control total de</span></span>
                    <span class="line"><span>tu clínica con</span></span>
                    <span class="line"><span><em>diseño moderno</em></span></span>
                </h1>
                <p class="hero__lead">
                    Plataforma integral desarrollada para optimizar la administración de pacientes, especialistas, tratamientos y agendamiento de citas de manera eficiente.
                </p>
                <div class="hero__actions">
                    <!-- ACTUALIZADO: Envía al portal del paciente para validar cédula y agendar -->
                    <a href="view/paciente/portal_paciente.php" class="btn btn--primary btn--large">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar Cita
                    </a>
                    <a href="view/paciente/form_paciente.php" class="btn btn--ghost btn--large">
                        <i class="fa-solid fa-user-plus"></i> Registrarse como Paciente
                    </a>
                </div>
            </div>

            <div class="hero__visual">
                <div class="hero-card hero-card--main" data-tilt>
                    <div class="hero-card__top">
                        <div class="hero-card__badge">
                            <i></i> Sistema Activo
                        </div>
                        <span class="hero-card__time">SENA - ADSO</span>
                    </div>
                    <div class="hero-card__art">
                        <div class="hero-card__halo"></div>
                        <img src="assests/img/proyecto-placeholder.svg" alt="DENTIX" class="hero-card__tooth" style="width: 120px;">
                        <span class="sparkle sparkle--1"></span>
                        <span class="sparkle sparkle--2"></span>
                        <span class="sparkle sparkle--3"></span>
                    </div>
                    <div class="hero-card__info">
                        <div>
                            <span class="hero-card__label">Proyecto Colaborativo</span>
                            <span class="hero-card__name">DENTIX v2.0</span>
                        </div>
                        <div class="hero-card__avatar">DX</div>
                    </div>
                </div>

                <div class="floating-card floating-card--appointment">
                    <div class="floating-card__icon">
                        <i class="fa-solid fa-tooth"></i>
                    </div>
                    <div>
                        <strong>Odontología</strong>
                        <span>Especializada</span>
                    </div>
                </div>

                <div class="floating-card floating-card--stat">
                    <div class="floating-card__icon floating-card__icon--coral">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <strong>Pacientes</strong>
                        <span>Control en línea</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Módulos de Gestión -->
    <section class="section" id="modulos">
        <div class="container">
            <div class="section-head section-head--center">
                <div class="eyebrow">Navegación del Sistema</div>
                <h2>Explora los <em>Módulos de Gestión</em></h2>
                <p>Accede de forma rápida a cada sección administrativa del consultorio odontológico.</p>
            </div>

            <div class="modules__grid">
                <!-- Pacientes -->
                <article class="card-modulo" data-tilt>
                    <span class="card-modulo__number">01</span>
                    <div class="card-modulo__icon">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <span class="card-modulo__tag">Pacientes</span>
                    <h3>Gestión de Pacientes</h3>
                    <p>Administra historias clínicas, datos personales y registros generales de los pacientes del consultorio.</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="view/paciente/lista_pacientes.php" class="card-modulo__link">Ver lista <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="view/paciente/form_paciente.php" class="card-modulo__link text-primary">Registrarse <i class="fa-solid fa-user-plus"></i></a>
                    </div>
                </article>

                <!-- Odontólogos -->
                <article class="card-modulo card-modulo--odontologo" data-tilt>
                    <span class="card-modulo__number">02</span>
                    <div class="card-modulo__icon">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <span class="card-modulo__tag">Especialistas</span>
                    <h3>Cuerpo Médico</h3>
                    <p>Control detallado de los odontólogos activos, especialidades y horarios de disponibilidad profesional.</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="view/odontologo/lista_odontologos.php" class="card-modulo__link">Ver especialistas <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="view/odontologo/form_odontologo.php" class="card-modulo__link">Nuevo odontólogo <i class="fa-solid fa-user-plus"></i></a>
                    </div>
                </article>

                <!-- Servicios -->
                <article class="card-modulo card-modulo--servicio" data-tilt>
                    <span class="card-modulo__number">03</span>
                    <div class="card-modulo__icon">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <span class="card-modulo__tag">Tratamientos</span>
                    <h3>Catálogo de Servicios</h3>
                    <p>Visualiza y administra la lista completa de procedimientos odontológicos disponibles y sus tarifas.</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="view/servicio/lista_servicios.php" class="card-modulo__link">Ver servicios <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="view/servicio/form_servicio.php" class="card-modulo__link">Nuevo servicio <i class="fa-solid fa-plus"></i></a>
                    </div>
                </article>
            </div>
            
            <!-- Módulo de Citas -->
            <div class="mt-4">
                <article class="card-modulo" style="max-width: 100%;" data-tilt>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <span class="card-modulo__tag">Agenda</span>
                            <h3>Módulo de Citas y Programación de Turnos</h3>
                            <p class="mb-0">Control general de programación, asignación de turnos y sincronización con especialistas.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="view/cita/lista_citas.php" class="btn btn--ghost">Ver Agenda</a>
                            <a href="view/paciente/portal_paciente.php" class="btn btn--primary">Agendar Cita</a>
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
                <p>Sistema de Citas Odontológicas desarrollado para el programa ADSO del SENA.</p>
            </div>
            <div class="footer__nav">
                <a href="#modulos">Módulos</a>
                <a href="view/paciente/form_paciente.php">Registrarse</a>
                <a href="view/recepcionista/login_recepcionista.php" class="btn btn--ghost btn--small">
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