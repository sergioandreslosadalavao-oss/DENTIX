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
    <link rel="stylesheet" href="assests/css/index.css?v=20261110-footer2">
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
                <a href="#manual-usuario" data-manual-open aria-haspopup="dialog" aria-controls="userManualModal">Manual</a>
            </nav>

            <!-- Botones de Acceso Rápido en la Barra Superior -->
            <div class="header__cta" style="display: flex; gap: 10px; align-items: center;">
                <!-- Botón de Portal Médico (Odontólogos) -->
                <a href="view/odontologo/login_odontologo.php" class="btn btn--ghost btn--small">
                     <i class="fa-solid fa-user-doctor"></i> Odontólogos
                </a>

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
                    <div class="d-grid gap-2 mt-3">
                        <a href="view/odontologo/login_odontologo.php" class="card-modulo__link">Acceso especialistas <i class="fa-solid fa-arrow-right"></i></a>
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

    <!-- Mega Footer dinámico estilo corporativo orientado a DENTIX -->
    <footer class="mega-footer" id="pie-dentix">
        <!-- Banda superior aguamarina suave: Marca + Selectores interactivos -->
        <div class="mega-footer__upper">
            <div class="container mega-footer__upper-inner">
                <a href="#" class="brand brand--mega-footer" aria-label="DENTIX, ir al inicio">
                    <div class="brand__mark"><i class="fa-solid fa-tooth" aria-hidden="true"></i></div>
                    <div class="brand__text">
                        DENTIX
                        <small>Clínica odontológica</small>
                    </div>
                </a>

                <div class="mega-footer__controls" role="group" aria-label="Información de sede, idioma y tarifas">
                    <label class="mega-footer__pill mega-footer__pill--lang" for="dentixLangSelect" data-i18n-ignore="true">
                        <i class="fa-solid fa-globe" aria-hidden="true"></i>
                        <select id="dentixLangSelect" class="mega-footer__lang-select" aria-label="Idioma / Language">
                            <option value="es">Español · ES</option>
                            <option value="en">English · EN</option>
                        </select>
                        <i class="fa-solid fa-sort mega-footer__pill-caret" aria-hidden="true"></i>
                    </label>

                    <label class="mega-footer__pill mega-footer__pill--currency" for="dentixCurrencySelect" data-i18n-ignore="true">
                        <span class="mega-footer__pill-tag" id="dentixCurrencyTag">COP</span>
                        <select id="dentixCurrencySelect" class="mega-footer__lang-select" aria-label="Moneda / Currency">
                            <option value="COP">Peso colombiano (COP)</option>
                            <option value="USD">Dollar (USD)</option>
                        </select>
                        <i class="fa-solid fa-sort mega-footer__pill-caret" aria-hidden="true"></i>
                    </label>

                    <button type="button" class="mega-footer__pill mega-footer__pill--live" id="footerLiveScheduleBtn" data-footer-pill="horario" aria-expanded="false">
                        <span class="mega-footer__live-dot" aria-hidden="true"></span>
                        <span id="footerLiveScheduleText">Colombia · 08:00 a 19:30</span>
                    </button>
                </div>
            </div>

            <!-- Panel dinámico al pulsar cualquiera de las 3 opciones superiores -->
            <div class="container">
                <div class="mega-footer__pill-drawer" id="footerPillDrawer" hidden>
                    <div class="mega-footer__pill-drawer-inner">
                        <i class="fa-solid fa-circle-info" id="footerPillIcon" aria-hidden="true"></i>
                        <p id="footerPillMessage"></p>
                        <button type="button" class="mega-footer__pill-close" id="footerPillClose" aria-label="Cerrar información">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banda inferior oscura: Directorio completo DENTIX en cuadrícula de 5 columnas -->
        <div class="mega-footer__lower">
            <div class="container">
                <div class="mega-footer__directory" role="navigation" aria-label="Directorio completo de servicios y módulos DENTIX">
                    <a href="view/paciente/login_paciente.php" class="mega-footer__item">
                        <strong>Portal del Paciente</strong>
                        <span>Consulta tus citas, estado clínico y alertas</span>
                    </a>
                    <a href="view/paciente/form_paciente.php" class="mega-footer__item">
                        <strong>Registro de Pacientes</strong>
                        <span>Crea tu cuenta en línea de forma segura</span>
                    </a>
                    <a href="view/odontologo/login_odontologo.php" class="mega-footer__item">
                        <strong>Portal Odontológico</strong>
                        <span>Agenda médica y cierre de historias clínicas</span>
                    </a>
                    <a href="view/recepcionista/lista_recepcionista.php" class="mega-footer__item">
                        <strong>Panel de Recepción</strong>
                        <span>Confirmación de agenda, equipo y pacientes</span>
                    </a>
                    <a href="#manual-usuario" class="mega-footer__item" data-manual-open="inicio">
                        <strong>Manual Interactivo</strong>
                        <span>Guía rápida para pacientes y personal</span>
                    </a>

                    <a href="view/servicio/catalogo_publico.php" class="mega-footer__item">
                        <strong>Catálogo de Servicios</strong>
                        <span>Tratamientos, tiempos y tarifas COP</span>
                    </a>
                    <a href="view/servicio/catalogo_publico.php#tratamientos" class="mega-footer__item">
                        <strong>Limpieza Dental</strong>
                        <span>Profilaxis profunda y prevención periodontal</span>
                    </a>
                    <a href="view/servicio/catalogo_publico.php#tratamientos" class="mega-footer__item">
                        <strong>Blanqueamiento LED</strong>
                        <span>Aclarado dental estético y seguro</span>
                    </a>
                    <a href="view/servicio/catalogo_publico.php#tratamientos" class="mega-footer__item">
                        <strong>Ortodoncia Correctiva</strong>
                        <span>Brackets y alineadores para tu mordida</span>
                    </a>
                    <a href="view/servicio/catalogo_publico.php#tratamientos" class="mega-footer__item">
                        <strong>Calza y Restauración</strong>
                        <span>Resina de alta resistencia y estética oral</span>
                    </a>

                    <a href="view/servicio/catalogo_publico.php#tratamientos" class="mega-footer__item">
                        <strong>Consulta General</strong>
                        <span>Valoración diagnóstica y plan de cuidado</span>
                    </a>
                    <a href="#citas" class="mega-footer__item">
                        <strong>Agenda en Bloques</strong>
                        <span>Citas de 30 minutos entre 08:00 y 19:30</span>
                    </a>
                    <a href="view/paciente/form_paciente.php" class="mega-footer__item">
                        <strong>Alertas de Alergias</strong>
                        <span>Control preventivo antes de cada atención</span>
                    </a>
                    <a href="view/recepcionista/lista_recepcionista.php" class="mega-footer__item">
                        <strong>Reportes Gerenciales</strong>
                        <span>Indicadores de ingresos y demanda clínica</span>
                    </a>
                    <a href="#manual-usuario" class="mega-footer__item" data-manual-open="faq">
                        <strong>Dudas y Reglas</strong>
                        <span>Estados de cita, límites y reprogramación</span>
                    </a>
                </div>

                <!-- Panel desplegable para enlaces legales del pie -->
                <div class="mega-footer__legal-drawer" id="footerLegalDrawer" hidden>
                    <div class="mega-footer__legal-drawer-card">
                        <strong id="footerLegalTitle"></strong>
                        <p id="footerLegalBody"></p>
                        <button type="button" class="mega-footer__pill-close" id="footerLegalClose" aria-label="Cerrar aviso legal">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <!-- Barra legal final estilo corporativo -->
                <div class="mega-footer__legal">
                    <div class="mega-footer__legal-links">
                        <button type="button" class="mega-footer__legal-btn" data-legal-topic="condiciones">Condiciones de uso</button>
                        <button type="button" class="mega-footer__legal-btn" data-legal-topic="privacidad">Aviso de privacidad (Ley 1581)</button>
                        <button type="button" class="mega-footer__legal-btn" data-legal-topic="salud">Privacidad de Datos de Salud</button>
                        <button type="button" class="mega-footer__legal-btn" data-legal-topic="historia">Reserva Historia Clínica (Ley 35)</button>
                        <span class="mega-footer__privacy-badge" title="Protección activa de datos clínicos">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            Datos protegidos
                        </span>
                    </div>
                    <p class="mega-footer__copy">
                        &copy; 2026 DENTIX Clínica Odontológica · Sistema Integral de Gestión de Citas · Colombia
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mini Interfaz Interactiva: Manual de Usuario -->
    <div class="manual-modal" id="userManualModal" role="dialog" aria-modal="true" aria-labelledby="userManualTitle" aria-hidden="true">
        <div class="manual-modal__backdrop" data-manual-close></div>
        <div class="manual-modal__dialog" role="document">
            <!-- Cabecera fija de la mini interfaz -->
            <header class="manual-modal__header">
                <div class="manual-modal__title-wrap">
                    <span class="eyebrow"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i> Guía interactiva de uso</span>
                    <h2 id="userManualTitle">Manual de Usuario <em>DENTIX</em></h2>
                    <p>Aprende cómo navegar por la plataforma, gestionar tus citas odontológicas y aprovechar cada módulo paso a paso.</p>
                </div>
                <button type="button" class="manual-modal__close" data-manual-close aria-label="Cerrar manual de usuario">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </header>

            <!-- Barra de pestañas por sección/perfil -->
            <div class="manual-tabs" role="tablist" aria-label="Secciones del manual de usuario">
                <button type="button" class="manual-tab is-active" role="tab" aria-selected="true" aria-controls="manual-panel-inicio" id="tab-inicio" data-manual-tab="inicio">
                    <i class="fa-solid fa-compass" aria-hidden="true"></i> Inicio Rápido
                </button>
                <button type="button" class="manual-tab" role="tab" aria-selected="false" aria-controls="manual-panel-paciente" id="tab-paciente" data-manual-tab="paciente">
                    <i class="fa-solid fa-hospital-user" aria-hidden="true"></i> Soy Paciente
                </button>
                <button type="button" class="manual-tab" role="tab" aria-selected="false" aria-controls="manual-panel-odontologo" id="tab-odontologo" data-manual-tab="odontologo">
                    <i class="fa-solid fa-user-doctor" aria-hidden="true"></i> Soy Odontólogo
                </button>
                <button type="button" class="manual-tab" role="tab" aria-selected="false" aria-controls="manual-panel-recepcion" id="tab-recepcion" data-manual-tab="recepcion">
                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i> Recepción / Admin
                </button>
                <button type="button" class="manual-tab" role="tab" aria-selected="false" aria-controls="manual-panel-faq" id="tab-faq" data-manual-tab="faq">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i> Dudas y Estados
                </button>
            </div>

            <!-- Cuerpo desplazable del manual -->
            <div class="manual-modal__body">
                <!-- PANEL 1: INICIO RÁPIDO -->
                <section class="manual-panel is-active" id="manual-panel-inicio" role="tabpanel" aria-labelledby="tab-inicio" data-manual-panel="inicio">
                    <div class="manual-intro-banner">
                        <div class="manual-intro-banner__icon">
                            <i class="fa-solid fa-tooth" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3>¿Cómo funciona DENTIX?</h3>
                            <p>DENTIX conecta a pacientes, recepción y odontólogos en un solo lugar para que solicitar, confirmar y atender citas odontológicas sea rápido, claro y seguro.</p>
                        </div>
                    </div>

                    <h4 class="manual-section-subtitle">Recorrido básico en 4 pasos</h4>
                    <div class="manual-steps-grid">
                        <article class="manual-step-card">
                            <span class="manual-step-card__num">01</span>
                            <div class="manual-step-card__icon"><i class="fa-solid fa-notes-medical" aria-hidden="true"></i></div>
                            <h5>Consulta los Servicios</h5>
                            <p>Haz clic en <strong>Servicios</strong> o en <strong>Conocer tratamientos</strong> para ver cada procedimiento, su duración estimada en minutos y su tarifa de referencia.</p>
                        </article>

                        <article class="manual-step-card">
                            <span class="manual-step-card__num">02</span>
                            <div class="manual-step-card__icon"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></div>
                            <h5>Crea tu Cuenta o Ingresa</h5>
                            <p>Si es tu primera vez, pulsa <strong>Crear cuenta de paciente</strong>. Si ya estás registrado, usa el botón <strong>Iniciar sesión</strong> con tu número de cédula y contraseña.</p>
                        </article>

                        <article class="manual-step-card">
                            <span class="manual-step-card__num">03</span>
                            <div class="manual-step-card__icon"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i></div>
                            <h5>Solicita tu Cita</h5>
                            <p>Desde tu portal personal selecciona el tratamiento, la fecha y la hora disponible (bloques de 30 minutos entre <strong>08:00 a. m.</strong> y <strong>07:30 p. m.</strong>).</p>
                        </article>

                        <article class="manual-step-card">
                            <span class="manual-step-card__num">04</span>
                            <div class="manual-step-card__icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></div>
                            <h5>Confirmación y Atención</h5>
                            <p>Recepción revisará tu solicitud, te asignará el especialista ideal y cambiará tu cita a <strong>Confirmada</strong>. Al finalizar la consulta podrás ver tu resumen clínico.</p>
                        </article>
                    </div>

                    <div class="manual-quick-map">
                        <h4 class="manual-section-subtitle">Mapa rápido de botones de la barra superior</h4>
                        <div class="manual-map-list">
                            <div class="manual-map-item">
                                <span class="manual-pill manual-pill--teal"><i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión</span>
                                <p>Acceso directo para <strong>Pacientes</strong> con cédula y contraseña.</p>
                            </div>
                            <div class="manual-map-item">
                                <span class="manual-pill"><i class="fa-solid fa-user-doctor"></i> Odontólogos</span>
                                <p>Acceso exclusivo al <strong>Panel Clínico</strong> para especialistas de la clínica.</p>
                            </div>
                            <div class="manual-map-item">
                                <span class="manual-pill"><i class="fa-solid fa-user-shield"></i> Administrador</span>
                                <p>Acceso para <strong>Recepcionistas</strong> al panel de control de citas, catálogos y reportes.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PANEL 2: SOY PACIENTE -->
                <section class="manual-panel" id="manual-panel-paciente" role="tabpanel" aria-labelledby="tab-paciente" data-manual-panel="paciente" hidden>
                    <div class="manual-role-header">
                        <div class="manual-role-header__badge"><i class="fa-solid fa-hospital-user"></i> Guía para Pacientes</div>
                        <h3>Gestiona tus citas y tu perfil personal</h3>
                        <p>Todo lo que puedes hacer dentro de tu Portal del Paciente de forma autónoma.</p>
                    </div>

                    <div class="manual-guide-list">
                        <article class="manual-guide-item">
                            <div class="manual-guide-item__icon"><i class="fa-solid fa-id-card" aria-hidden="true"></i></div>
                            <div class="manual-guide-item__content">
                                <h5>1. Registro por primera vez</h5>
                                <p>Dirígete a <strong>Crear cuenta de paciente</strong>, completa tus datos (nombre en formato propio, número de cédula, fecha de nacimiento, teléfono, correo, alergias médicas y contraseña de 8 a 72 caracteres) y despliega los 5 puntos de <strong>Términos y Condiciones</strong> para activar tu cuenta.</p>
                            </div>
                        </article>

                        <article class="manual-guide-item">
                            <div class="manual-guide-item__icon"><i class="fa-solid fa-calendar-day" aria-hidden="true"></i></div>
                            <div class="manual-guide-item__content">
                                <h5>2. Cómo agendar una cita nueva</h5>
                                <p>Dentro de tu portal, haz clic en <strong>Solicitar nueva cita</strong>. Elige el tratamiento odontológico, selecciona la fecha deseada, escoge un horario en intervalos de 30 minutos (por ejemplo <code>08:00</code>, <code>08:30</code>, <code>09:00</code>) y escribe brevemente el motivo de tu consulta.</p>
                            </div>
                        </article>

                        <article class="manual-guide-item">
                            <div class="manual-guide-item__icon"><i class="fa-solid fa-user-pen" aria-hidden="true"></i></div>
                            <div class="manual-guide-item__content">
                                <h5>3. Actualizar mis datos o alergias</h5>
                                <p>En tu portal selecciona <strong>Editar perfil</strong>. Allí podrás mantener actualizados tu teléfono, correo, alergias médicas o cambiar tu contraseña cuando lo necesites.</p>
                            </div>
                        </article>
                    </div>

                    <div class="manual-alert manual-alert--info">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <div>
                            <strong>Reglas importantes al agendar tu cita:</strong>
                            <ul>
                                <li>El horario de atención clínica es de <strong>08:00 a. m. a 07:30 p. m.</strong> en bloques exactos de 30 minutos.</li>
                                <li>Por seguridad y organización, cada paciente puede tener máximo <strong>1 cita activa por día</strong>.</li>
                                <li>Entre dos citas de un mismo paciente deben transcurrir al menos <strong>4 días completos</strong> de diferencia (RN-07).</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- PANEL 3: SOY ODONTÓLOGO -->
                <section class="manual-panel" id="manual-panel-odontologo" role="tabpanel" aria-labelledby="tab-odontologo" data-manual-panel="odontologo" hidden>
                    <div class="manual-role-header">
                        <div class="manual-role-header__badge"><i class="fa-solid fa-user-doctor"></i> Guía para Especialistas</div>
                        <h3>Uso del Panel Clínico del Odontólogo</h3>
                        <p>Consulta tu agenda confirmada y documenta la evolución clínica de cada paciente atendido.</p>
                    </div>

                    <div class="manual-guide-list">
                        <article class="manual-guide-item">
                            <div class="manual-guide-item__icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
                            <div class="manual-guide-item__content">
                                <h5>1. Ingreso al portal médico</h5>
                                <p>Haz clic en el botón superior <strong>Odontólogos</strong> e inicia sesión con tu correo electrónico institucional y contraseña asignada por la clínica.</p>
                            </div>
                        </article>

                        <article class="manual-guide-item">
                            <div class="manual-guide-item__icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></div>
                            <div class="manual-guide-item__content">
                                <h5>2. Revisión de Citas Confirmadas</h5>
                                <p>En <strong>Citas Confirmadas</strong> verás únicamente los pacientes que recepción ya te asignó, organizados por fecha y hora, junto con sus datos de contacto, cédula y motivo de consulta.</p>
                            </div>
                        </article>

                        <article class="manual-guide-item">
                            <div class="manual-guide-item__icon"><i class="fa-solid fa-file-waveform" aria-hidden="true"></i></div>
                            <div class="manual-guide-item__content">
                                <h5>3. Registro clínico y cierre de consulta</h5>
                                <p>Al terminar la atención, completa en la tarjeta del paciente los campos de <strong>Diagnóstico</strong>, <strong>Procedimiento realizado</strong> y <strong>Nota clínica / recomendaciones</strong>, y pulsa <strong>Finalizar cita</strong>. El registro pasará automáticamente a tu <strong>Historial de Finalizadas</strong>.</p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- PANEL 4: RECEPCIÓN / ADMIN -->
                <section class="manual-panel" id="manual-panel-recepcion" role="tabpanel" aria-labelledby="tab-recepcion" data-manual-panel="recepcion" hidden>
                    <div class="manual-role-header">
                        <div class="manual-role-header__badge"><i class="fa-solid fa-user-shield"></i> Guía para Recepción y Administración</div>
                        <h3>Operación integral del Panel Administrativo</h3>
                        <p>Coordina la agenda de la clínica, administra los catálogos y consulta los reportes estadísticos.</p>
                    </div>

                    <div class="manual-steps-grid">
                        <article class="manual-step-card">
                            <div class="manual-step-card__icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></div>
                            <h5>Módulo de Citas</h5>
                            <p>En <strong>Catálogo de Citas</strong> asigna un odontólogo disponible a cada solicitud pendiente y pulsa <strong>Confirmar</strong>. También puedes crear una <strong>+ Nueva Cita</strong>, <strong>Editar</strong> fecha/hora o <strong>Cancelar</strong> indicando el motivo obligatorio.</p>
                        </article>

                        <article class="manual-step-card">
                            <div class="manual-step-card__icon"><i class="fa-solid fa-users-gear" aria-hidden="true"></i></div>
                            <h5>Pacientes y Odontólogos</h5>
                            <p>Registra nuevos pacientes u odontólogos, actualiza sus datos de contacto o consultorio y activa/inactiva profesionales cuando no estén disponibles en agenda.</p>
                        </article>

                        <article class="manual-step-card">
                            <div class="manual-step-card__icon"><i class="fa-solid fa-tooth" aria-hidden="true"></i></div>
                            <h5>Catálogo de Servicios</h5>
                            <p>En <strong>Servicios</strong> administra los tratamientos odontológicos, su descripción pública, duración mínima (desde 15 minutos) y tarifas actualizadas.</p>
                        </article>

                        <article class="manual-step-card">
                            <div class="manual-step-card__icon"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></div>
                            <h5>Módulo de Reportes</h5>
                            <p>Debajo de Citas encontrarás <strong>Reportes</strong>: filtra por fechas y visualiza el paciente con más citas, el servicio más solicitado, el odontólogo que más atiende y los horarios de mayor demanda.</p>
                        </article>
                    </div>
                </section>

                <!-- PANEL 5: DUDAS Y ESTADOS -->
                <section class="manual-panel" id="manual-panel-faq" role="tabpanel" aria-labelledby="tab-faq" data-manual-panel="faq" hidden>
                    <h4 class="manual-section-subtitle">Significado de los estados de una cita</h4>
                    <div class="manual-status-grid">
                        <div class="manual-status-card">
                            <span class="manual-badge manual-badge--pending"><i class="fa-regular fa-clock"></i> Pendiente</span>
                            <p>El paciente envió la solicitud de cita y está a la espera de que recepción le asigne un odontólogo y confirme el horario.</p>
                        </div>
                        <div class="manual-status-card">
                            <span class="manual-badge manual-badge--confirmed"><i class="fa-solid fa-circle-check"></i> Confirmada</span>
                            <p>Recepción ya validó la cita y asignó el odontólogo y consultorio. El paciente debe asistir en la fecha y hora programadas.</p>
                        </div>
                        <div class="manual-status-card">
                            <span class="manual-badge manual-badge--completed"><i class="fa-solid fa-clipboard-check"></i> Completada</span>
                            <p>El odontólogo atendió al paciente y registró el diagnóstico, procedimiento y nota clínica en el historial.</p>
                        </div>
                        <div class="manual-status-card">
                            <span class="manual-badge manual-badge--cancelled"><i class="fa-solid fa-ban"></i> Cancelada</span>
                            <p>La cita fue cancelada registrando el motivo correspondiente, liberando el espacio en la agenda médica.</p>
                        </div>
                    </div>

                    <h4 class="manual-section-subtitle" style="margin-top: 24px;">Preguntas frecuentes</h4>
                    <div class="manual-faq-list">
                        <details class="manual-faq-item" open>
                            <summary>¿Necesito crear una cuenta para ver los precios de los tratamientos?</summary>
                            <p>No. Puedes consultar libremente todos los tratamientos, su descripción, duración estimada y tarifa de referencia haciendo clic en <strong>Conocer tratamientos</strong> desde la página principal.</p>
                        </details>
                        <details class="manual-faq-item">
                            <summary>¿Por qué no puedo agendar dos citas el mismo día?</summary>
                            <p>Para garantizar una atención ordenada y disponibilidad justa para todos los usuarios, el sistema permite máximo una cita activa (pendiente o confirmada) por paciente en una misma fecha.</p>
                        </details>
                        <details class="manual-faq-item">
                            <summary>¿Qué hago si olvidé mis datos o necesito reprogramar mi cita?</summary>
                            <p>Comunícate con el equipo de recepción de la clínica; desde el panel administrativo podrán actualizar tus datos o reprogramar la fecha y hora de tu cita inmediatamente.</p>
                        </details>
                    </div>
                </section>
            </div>

            <!-- Pie de la mini interfaz con accesos directos -->
            <footer class="manual-modal__footer">
                <span class="manual-modal__footer-note">
                    <i class="fa-solid fa-shield-heart" aria-hidden="true"></i> Guía rápida integrada en DENTIX
                </span>
                <div class="manual-modal__footer-actions">
                    <a href="view/servicio/catalogo_publico.php" class="btn btn--ghost btn--small">
                        <i class="fa-solid fa-notes-medical" aria-hidden="true"></i> Ver tratamientos
                    </a>
                    <a href="view/paciente/login_paciente.php?next=portal_paciente.php" class="btn btn--primary btn--small">
                        <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Agendar cita ahora
                    </a>
                </div>
            </footer>
        </div>
    </div>

    <!-- GSAP Animations Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- SCRIPT DE ANIMACIONES Y TRADUCCIÓN GLOBAL -->
    <script src="assests/js/index-animations.js?v=20261110-footer2"></script>
    <script src="assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>