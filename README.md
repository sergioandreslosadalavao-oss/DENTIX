# DENTIX — Sistema Integral de Gestión de Citas Odontológicas

**DENTIX** es una plataforma web clínica, operativa y administrativa desarrollada en **PHP 8 (PDO)** y **PostgreSQL 12+** sobre entorno **Apache (XAMPP/LAMPP)**. Articula de extremo a extremo la operación de una clínica odontológica conectando en tiempo real a **Visitantes**, **Pacientes**, **Recepcionistas (Administradores)** y **Odontólogos (Cuerpo Médico)**, incorporando además cumplimiento legal colombiano (**Ley 1581 de 2012**, **Ley 35 de 1989** y **Resolución 1995 de 1999**), **módulo de reportes gerenciales imprimibles en PDF**, **traducción automática global Español (`ES`) ↔ Inglés (`EN`)** y **conversión monetaria en tiempo real Pesos Colombianos (`COP`) ↔ Dólares (`USD`)**.

---

## Tabla de Contenido

1. [Resumen de Evolución y Cambios Respecto a la Versión Anterior](#1-resumen-de-evolución-y-cambios-respecto-a-la-versión-anterior)
2. [Estructura Completa de Carpetas y Archivos](#2-estructura-completa-de-carpetas-y-archivos)
3. [Funcionalidades Detalladas por Módulo y Rol](#3-funcionalidades-detalladas-por-módulo-y-rol)
4. [Base de Datos PostgreSQL y Reglas de Negocio (`RN-01` a `RN-22`)](#4-base-de-datos-postgresql-y-reglas-de-negocio-rn-01-a-rn-22)
5. [Instalación y Puesta en Marcha](#5-instalación-y-puesta-en-marcha)
6. [Credenciales de Prueba (`database/citas_odontologicas.sql`)](#6-credenciales-de-prueba-databasecitas_odontologicas)
7. [Documentación Oficial, Calidad y Licenciamiento](#7-documentación-oficial-calidad-y-licenciamiento)

---

## 1. Resumen de Evolución y Cambios Respecto a la Versión Anterior

Respecto a las versiones anteriores del proyecto (enfocadas en un CRUD básico con agenda simple), **DENTIX** fue reconstruido e integrado funcional, visual y documentalmente incorporando las siguientes mejoras clave:

| Área / Componente | Versión Anterior | Estado Actual en DENTIX |
| :--- | :--- | :--- |
| **Registro de Pacientes y Datos Clínicos** | Solo capturaba datos básicos (`nombre`, `cédula`, `teléfono`, `correo`, `contraseña`). Sin validación estricta de nombre ni datos clínicos al registrarse. | Captura obligatoria de **`fecha_nac`** (`ck_paciente_fecha_nac`) y bloque interactivo de **Alergias / Alertas Médicas** (selector, chips rápidos y descripción). Validación estricta **Nombre Propio (*Title Case*)** en JS, PHP y PostgreSQL (`ck_paciente_nombre_formato`). |
| **Cumplimiento Legal (Ley 1581 / Ley 35)** | Sin flujo formal de consentimiento informado previo al registro. | Ventana modal **`#termsModal`** con **5 cláusulas expandibles (`Ver más...`)** y **alerta sorpresa de bloqueo (`#termsLockBanner`)** que impide aceptar o crear la cuenta sin haber leído los 5 puntos completos (`acepto_terminos = 1`). |
| **Portal del Paciente y Solicitud de Citas** | Gestión limitada o dependiente de recepción. | Portal autenticado completo con cédula y contraseña, 4 tarjetas KPI, **autogestión completa de datos personales y alergias (`#modalEditarPerfilPaciente`, `RF-037`) con sincronización automática en vivo en recepción**, solicitud autónoma de cita sin odontólogo (`odontologo_id = NULL`) e historial con estado y **motivo de cancelación**. |
| **Portal Exclusivo del Odontólogo** | Sin módulo independiente o con campos clínicos limitados. | Login propio ([`login_odontologo.php`](view/odontologo/login_odontologo.php)), agenda filtrada por sesión con alertas rojas de alergias, modal **`Finalizada`** con **cierre clínico transaccional atómico** (`fecha_salida`, `hora_salida`, resumen, procedimiento y nota clínica) e historial buscable ([`historial_finalizadas.php`](view/odontologo/historial_finalizadas.php)). |
| **Panel Administrativo, Citas, Especialidades y Consultorios** | Formularios sin precarga, creación/edición libre de citas en recepción y texto libre para especialidad/consultorio. | **Recepción direcciona citas sin crear ni editar citas (`RF-030` / `RF-038` / `RF-044` / `RN-21`)**: solo asigna un odontólogo **cuya especialidad coincida estrictamente con el servicio solicitado por el paciente**, confirma, marca `No asistió` o cancela con motivo (`#cancelarCitaModal`, `RN-06`). **Catálogo exclusivo de los 5 servicios como especialidades odontológicas y consultorios multi-sede (`RF-039`..`RF-041`, `RF-044`, `RN-19`..`RN-21`)**: en [`form_odontologo.php`](view/odontologo/form_odontologo.php) y [`editar_odontologo.php`](view/odontologo/editar_odontologo.php) el selector de especialidad lista únicamente los 5 servicios activos de la tabla `servicio`. La tabla Cuerpo Médico en [`lista_odontologos.php`](view/odontologo/lista_odontologos.php) conserva únicamente **Eliminar**; desde `#panel-consultorios` recepción puede **crear nuevos consultorios** (`#modalNuevoConsultorio`), **eliminar consultorios libres** (adaptándose a cualquier sede), registrar odontólogos (`+ Asignar aquí` -> [`form_odontologo.php`](view/odontologo/form_odontologo.php)) y editarlos (`Gestionar asignación` -> [`editar_odontologo.php`](view/odontologo/editar_odontologo.php)). En **Pacientes (`RF-042`)**, recepción únicamente consulta en vivo y elimina pacientes sin historial (edición exclusiva del titular); y eliminación segura de servicios (`RN-12`) en [`lista_servicios.php`](view/servicio/lista_servicios.php). |
| **Módulo de Reportes Gerenciales** | No existía o figuraba fuera de alcance. | Nuevo módulo [`view/recepcionista/reportes.php`](view/recepcionista/reportes.php) con filtro por rango de fechas, 4 KPIs (separando `Canceladas` de `No-Show`), 3 tarjetas líderes, 4 rankings estadísticos, conversión `COP/USD`, impresión **PDF general e individual por cada reporte** (`@media print`) y **exportación nativa en libros Excel (`.xlsx`) con diseño corporativo DENTIX** ([`app/xlsx_writer.php`](app/xlsx_writer.php), `RF-043`) consolidada (5 pestañas) e individual (Resumen KPIs, Servicios, Odontólogos, Pacientes y Horarios). |
| **Página Principal, Video, Manual y Mega Pie de Página** | Footer oscuro tradicional con botón *"Volver al inicio"*, video no embebido y clase de ícono faltante en catálogo público. | **Video comercial HD integrado** ([`assets/video/demo_dentix.mp4`](assets/video/demo_dentix.mp4)) en el **cuadro interactivo del Hero** (vista previa viva en bucle + reproductor cinematográfico a pantalla completa `#dentixHeroVideoModal` al hacer clic) y en la sección 2 columnas **`#video-demo`** con descarga directa en MP4; **Manual Interactivo (`#userManualModal`)** con descarga directa de [`manuales/MANUAL_USUARIO.docx`](manuales/MANUAL_USUARIO.docx); **Mega Pie de Página corporativo (`.mega-footer`)**; e iconografía corregida (`fa-hand-sparkles`, `fa-tooth`) en [`catalogo_publico.php`](view/servicio/catalogo_publico.php). |
| **Internacionalización (`ES/EN`) y Moneda (`COP/USD`)** | Plataforma disponible únicamente en español y pesos colombianos. | Nuevo motor transversal [`assets/js/dentix-i18n.js`](assets/js/dentix-i18n.js) inyectado en **`index.php` y las 21 vistas de `view/`**: traducción bidireccional instantánea sin pérdida (`WeakMap` + `withObserverPaused()` + respaldo inverso `EN → ES`), bloqueo del traductor automático del navegador (`translate="no"` + `<meta name="google" content="notranslate">`), conversión automática `COP ↔ USD` (`1 USD = $4.000 COP`), vista previa USD en tiempo real (`#usdLivePreview`) y barra flotante global `#dentixLangFloating`. |
| **Base de Datos y Reglas de Agenda** | Reglas básicas sin `fecha_salida`/`hora_salida`, script destructivo con `DROP ... CASCADE` y vistas sin uso en PHP. | Script seguro e idempotente [`database/citas_odontologicas.sql`](database/citas_odontologicas.sql) (sin `DROP ... CASCADE`; reinicio de laboratorio aislado en [`database/reset_laboratorio.sql`](database/reset_laboratorio.sql)): **7 tablas** (incluyendo catálogo dinámico `consultorio` para adaptabilidad multi-sede, `RF-041`), **2 vistas activas consumidas desde PHP** (`v_agenda_dia` y `v_historial_paciente`), **8 índices explícitos `CREATE INDEX` + índices `UNIQUE` (incluyendo `uq_consultorio_nombre_ci` y `uq_odontologo_consultorio_activo`, `RN-19`/`RN-20`)**, **2 triggers PL/pgSQL** (`trg_validar_espaciado_citas` con `pg_advisory_xact_lock`, `RN-01`, `RN-07`, `RN-08` y **`RN-21` que valida coincidencia estricta entre la especialidad del odontólogo y el servicio de la cita**; y `trg_validar_detalle_cita` para `RN-04`/`RN-13`) y `ck_paciente_nombre_formato` con soporte de conectores (`de, del, la`). |
| **Documentación SENA, QA y Licenciamiento (100 % `.docx`)** | Documentación parcial en Markdown e imágenes sueltas. | Todos los entregables oficiales en **Microsoft Word (`.docx`)**: [`Extras/SRS_V 4.0.docx`](Extras/SRS_V%204.0.docx) (`RF-001`–`RF-045`, `RN-01`–`RN-22`, `US-01`–`US-39`, Sección 10.5 de Licenciamiento Privativo y desglose de ingeniería de `$ 11.200.000 COP`), [`Extras/PAC_v2.3.docx`](Extras/PAC_v2.3.docx) (69 casos `TC-01`–`TC-69` ejecutados con evidencia en 9 columnas), [`Extras/LOG_ERRORES_Y_SUGERENCIAS.docx`](Extras/LOG_ERRORES_Y_SUGERENCIAS.docx) (`DEF-001`–`DEF-027` cerrados + `MEJ-001`–`MEJ-015` + `SUG-01`–`SUG-10`), [`Extras/MER.docx`](Extras/MER.docx), [`Extras/GUION_PITCH_Y_DEFENSA_DENTIX.docx`](Extras/GUION_PITCH_Y_DEFENSA_DENTIX.docx), [`manuales/MANUAL_TECNICO.docx`](manuales/MANUAL_TECNICO.docx) y [`manuales/MANUAL_USUARIO.docx`](manuales/MANUAL_USUARIO.docx). |

---

## 2. Estructura Completa de Carpetas y Archivos

```text
DENTIX/
├── app/                                      # Capa de Lógica de Negocio, Seguridad y Sesiones
│   ├── odontologo_auth.php                   # Middleware de sesión segura, cookies HttpOnly y CSRF (Odontólogo)
│   ├── odontologo_catalogos.php              # Catálogo dinámico de especialidades y consultorios multi-sede (RF-039 / RF-041 / RN-19 / RN-20)
│   ├── logica_consultorio.php                # Creación y eliminación segura de consultorios libres por sede (POST + CSRF, RF-041 / RN-20)
│   ├── paciente_auth.php                     # Middleware de sesión segura, cookies HttpOnly y CSRF (Paciente)
│   ├── recepcionista_auth.php                # Middleware de sesión segura, cookies HttpOnly y CSRF (Recepción)
│   ├── logica_cita.php                       # Direccionamiento de citas: confirmar, asignar odontólogo, No-Show y cancelar (RF-038, RN-06..08)
│   ├── logica_odontologo.php                 # CRUD de odontólogos, validación de consultorio libre (RN-19), slug_publico y cierre clínico
│   ├── logica_paciente.php                   # Registro (Title Case, Ley 1581, fecha_nac, alergias), autogestión de perfil (RF-037) y edición
│   ├── logica_recepcionista.php              # Autenticación y creación de cuentas de recepción
│   ├── logica_servicio.php                   # CRUD y eliminación segura de servicios odontológicos (RN-11, RN-12)
│   └── xlsx_writer.php                       # Generador nativo OOXML de libros Excel (.xlsx) con diseño DENTIX sin dependencias (RF-043)
│
├── assets/                                  # Recursos Frontend Estáticos
│   ├── css/
│   │   ├── admin.css                         # Diseño de paneles de recepción, odontólogo y reportes (@media print)
│   │   ├── index.css                         # Landing page, Manual Interactivo, #termsModal y Mega Footer aguamarina
│   │   └── paciente.css                      # Interfaz visual del Portal y Perfil del Paciente
│   ├── img/
│   │   ├── MER.png                           # Imagen en alta resolución del Diagrama Entidad-Relación
│   │   └── proyecto-placeholder.svg          # Recurso gráfico institucional
│   ├── js/
│   │   ├── dentix-i18n.js                    # Motor global ES ↔ EN + Conversor COP ↔ USD + Widget #dentixFloatingLang
│   │   ├── index-animations.js               # Animaciones GSAP, lógica del Manual 5 pestañas y reloj en vivo del Footer
│   │   ├── admin-animations.js               # Interacciones y animaciones del panel administrativo
│   │   └── animaciones.js                    # Utilidades visuales complementarias
│   └── video/
│       ├── comercial_dentix.mp4              # Comercial publicitario oficial HD en el cuadro interactivo del Hero
│       └── demo_dentix.mp4                   # Video explicativo de los aprendices SENA integrado en index.php (#video-demo)
│
├── config/                                   # Configuración de Conexión a PostgreSQL
│   ├── database.example.php                  # Plantilla pública de conexión PDO (versionada)
│   └── database.php                          # Credenciales reales locales PDO (protegida en .gitignore)
│
├── database/                                 # Persistencia y Lógica en Motor Relacional
│   ├── citas_odontologicas.sql               # Script oficial seguro e idempotente: 7 tablas, 2 vistas, 2 triggers, índices y seed
│   └── reset_laboratorio.sql                 # Script destructivo aislado solo para reinicios de laboratorio desde cero
│
├── view/                                     # Capa de Presentación (22 vistas HTML5/PHP responsivas)
│   ├── cita/
│   │   ├── lista_citas.php                   # Direccionamiento de citas: pendientes, asignar odontólogo, confirmar, No-Show y modal cancelar (RF-038)
│   │   ├── form_cita.php                     # Acceso bloqueado para recepción (agendamiento exclusivo del paciente, RF-038)
│   │   └── editar_cita.php                   # Acceso bloqueado para recepción (inmutabilidad de fecha/hora/servicio, RF-038)
│   ├── odontologo/
│   │   ├── login_odontologo.php              # Inicio de sesión exclusivo para especialistas
│   │   ├── panel_odontologo.php              # KPIs, citas confirmadas con alerta de alergias y modal "Finalizada"
│   │   ├── historial_finalizadas.php         # Archivo histórico clínico de citas completadas con buscador en vivo
│   │   ├── lista_odontologos.php             # Directorio médico + panel dinámico de consultorios (crear, eliminar libres, asignar/editar, RF-039..041)
│   │   ├── form_odontologo.php               # Registro con selector dinámico de especialidades y selector de consultorios libres (RF-039)
│   │   ├── editar_odontologo.php             # Edición por ?id= con catálogo de especialidades, consultorios libres, estado y slug público
│   │   └── cerrar_sesion_odontologo.php      # Cierre de sesión por POST + CSRF
│   ├── paciente/
│   │   ├── form_paciente.php                 # Registro público/admin (Title Case, fecha_nac, chips alergias y #termsModal)
│   │   ├── login_paciente.php                # Acceso por cédula y contraseña
│   │   ├── perfil_paciente.php               # Tarjetas estadísticas, edición de perfil/alergias e historial de citas
│   │   ├── portal_paciente.php               # Solicitud autónoma de citas validando horario (RN-03) y regla de 4 días (RN-07)
│   │   ├── agendar_cita.php                  # Redirección autenticada al flujo de agendamiento
│   │   ├── lista_pacientes.php               # Directorio en vivo con alergias y fecha_nac (solo Eliminar; edición exclusiva del titular, RF-037/RF-042)
│   │   ├── editar_paciente.php               # Acceso bloqueado para recepción (edición exclusiva del titular bajo Ley 1581, RF-042)
│   │   └── cerrar_sesion_paciente.php        # Cierre de sesión por POST + CSRF
│   ├── recepcionista/
│   │   ├── lista_recepcionista.php           # Inicio de sesión de recepción (Acceso Administrador)
│   │   ├── login_recepcionista.php           # Vista de autenticación administrativa
│   │   ├── form_recepcionista.php            # Alta de nuevas cuentas de recepción
│   │   ├── reportes.php                      # Módulo de Reportes Clínicos y Estadísticos (KPIs, rankings, COP/USD, PDF)
│   │   ├── boton_cerrar_sesion.php           # Tarjeta de perfil en barra lateral + botón seguro de salida
│   │   └── cerrar_sesion_recepcionista.php   # Cierre de sesión por POST + CSRF
│   └── servicio/
│       ├── catalogo_publico.php              # Catálogo público de tratamientos con íconos clínicos y tarifas COP/USD
│       ├── lista_servicios.php               # Gestión administrativa de servicios con eliminación segura y precios COP/USD
│       ├── form_servicio.php                 # Registro de servicio con vista previa en tiempo real #usdLivePreview
│       └── editar_servicio.php               # Edición de tratamiento/descripción con vista previa USD en tiempo real
│
├── manuales/                                 # Manuales Oficiales en Microsoft Word (.docx)
│   ├── MANUAL_TECNICO.docx                   # Arquitectura, diccionario 1:1, diagramas embebidos, despliegue, 13 credenciales y backups
│   └── MANUAL_USUARIO.docx                   # Guía paso a paso por rol con 6 capturas visuales embebidas
│
├── Extras/                                   # Documentación SENA, Aseguramiento de Calidad y Pitch (.docx)
│   ├── SRS_V 4.0.docx                        # Especificación de Requisitos IEEE 830 (RF-001..043, RNF-001..014, RN-01..20, Licenciamiento y Costos)
│   ├── PAC_v2.3.docx                         # Plan de Aseguramiento de Calidad (66 casos TC-01..TC-66 con evidencia en 9 columnas)
│   ├── LOG_ERRORES_Y_SUGERENCIAS.docx        # Expediente QA: DEF-001..DEF-026 cerrados, MEJ-001..MEJ-014 y Sugerencias SUG-01..SUG-10
│   ├── MER.docx                              # Modelo Entidad-Relación oficial en Word (diagrama embebido + especificación DDL)
│   └── GUION_PITCH_Y_DEFENSA_DENTIX.docx     # Guion cronometrado, defensa de arquitectura 4 capas y justificación $11.200.000 COP
│
├── index.php                                 # Landing page pública, Video Demo integrado, Manual Interactivo y Mega Footer
└── README.md                                 # Documentación principal del repositorio
```

---

## 3. Funcionalidades Detalladas por Módulo y Rol

### 3.1 Experiencia Pública, Manual Interactivo y Mega Footer ([`index.php`](index.php))
* **Navegación por Roles**: Accesos directos hacia *Servicios*, *Agendar cita*, *Manual*, *Odontólogos*, *Administrador* e *Iniciar sesión* para pacientes.
* **Manual Interactivo (`#userManualModal`)**: Guía modal integrada con 5 pestañas (*Inicio Rápido*, *Soy Paciente*, *Soy Odontólogo*, *Recepción / Admin* y *Dudas y Estados*), sincronizada con el motor `ES ↔ EN`.
* **Mega Pie de Página Corporativo (`.mega-footer`)**: Diseñado en blanco menta y aguamarina suave con indicador de estado horario en vivo (*Abierto `08:00 a 19:30`* o *Agenda en línea 24/7*), directorio de 15 accesos directos y 4 cajones informativos legales (*Condiciones de uso*, *Ley 1581*, *Datos de Salud*, *Ley 35*), mientras que el cambio de idioma (`ES ↔ EN`) y moneda (`COP ↔ USD`) se centraliza en la barra flotante permanente `#dentixLangFloating` en la esquina inferior derecha.
* **Catálogo Público ([`catalogo_publico.php`](view/servicio/catalogo_publico.php))**: Tarjetas informativas con íconos clínicos, descripción, duración estimada y tarifa convertible entre `COP` y `USD`.

### 3.2 Motor Transversal de Idioma (`ES ↔ EN`) y Moneda (`COP ↔ USD`) ([`dentix-i18n.js`](assets/js/dentix-i18n.js))
* **Cobertura en toda la web**: Activo en [`index.php`](index.php) y en las **22 vistas internas** de `view/`.
* **Traducción sin pérdida (`WeakMap` + `MutationObserver`)**: Traduce textos, encabezados, tablas, modales, atributos (`placeholder`, `title`, `aria-label`) y alertas del sistema al instante sin recargar la página ni perder el texto original en español.
* **Conversión Monetaria (`1 USD = $4.000 COP`)**: Convierte automáticamente tarifas de tratamientos e ingresos en [`catalogo_publico.php`](view/servicio/catalogo_publico.php), [`lista_servicios.php`](view/servicio/lista_servicios.php) y [`reportes.php`](view/recepcionista/reportes.php), además de mostrar equivalencia en vivo (`#usdLivePreview`) al crear o editar servicios.
* **Persistencia y Widget Flotante**: Conserva la selección de idioma y divisa en `localStorage` y cookies entre todas las vistas mediante el control flotante `#dentixFloatingLang` (oculto automáticamente al imprimir PDF).

### 3.3 Portal del Paciente y Consentimiento Legal ([`view/paciente/`](view/paciente/form_paciente.php))
* **Calidad de Datos Clínicos (`DEF-008` corregido)**: Registro obligatorio con **Nombre en formato propio (*Title Case*)**, cédula única, **Fecha de nacimiento (`fecha_nac <= hoy`)**, **Alergias médicas** (selector, botones rápidos y detalle manual), teléfono, correo y contraseña `bcrypt` (8–72 caracteres).
* **Modal Obligatorio de Términos y Condiciones (`#termsModal`)**: Presenta **5 cláusulas legales** (Ley 1581 de 2012, Ley 35 de 1989, Resolución 1995 de 1999 y políticas `RN-07`). Si el usuario intenta aceptar sin desplegar los 5 botones `"Ver más..."`, se dispara el aviso de bloqueo `#termsLockBanner` señalando los puntos pendientes; en PHP se exige `acepto_terminos = 1`.
* **Perfil e Historial ([`perfil_paciente.php`](view/paciente/perfil_paciente.php))**: Muestra 4 tarjetas estadísticas, actualización de datos y alergias, y listado de citas con odontólogo asignado y motivo de cancelación cuando aplica.

### 3.4 Panel Administrativo de Recepción y Reportes ([`view/cita/`](view/cita/lista_citas.php), [`view/recepcionista/reportes.php`](view/recepcionista/reportes.php))
* **Direccionamiento de Citas por Especialidad del Servicio (`RF-030` / `RF-038` / `RF-044` / `RN-21`)**: El agendamiento es exclusivo del paciente. Recepción visualiza solicitudes pendientes, y el selector de cada cita **filtra y permite asignar única y exclusivamente odontólogos activos cuya especialidad clínica coincida exactamente con el servicio solicitado por el paciente** (validado en interfaz, controlador PHP `logica_cita.php` y trigger PL/pgSQL `fn_validar_espaciado_citas_paciente()` junto con **`RN-08`**), confirma la cita, marca `No asistió` (`no_show`) en citas confirmadas o cancela con **modal `#cancelarCitaModal`** obligando a documentar el motivo (`RN-06`). La creación y edición manual de citas desde recepción están restringidas en UI y servidor.
* **Gestión de Cuerpo Médico, Especialidades Vinculadas a los 5 Servicios, Título Automático `Dr./Dra.` y Consultorios Dinámicos por Sede (`RF-039`..`RF-041` / `RF-044` / `RF-045` / `RN-19`..`RN-22`)**: En [`form_odontologo.php`](view/odontologo/form_odontologo.php) y [`editar_odontologo.php`](view/odontologo/editar_odontologo.php), el selector de **Especialidad Clínica** lista exclusivamente los **5 servicios oficiales registrados en la tabla `servicio`** (*Consulta general*, *Limpieza dental*, *Calza (resina)*, *Blanqueamiento dental* y *Ortodoncia correctiva*), y el campo **Nombre Completo** antepone automáticamente `Dr.` o `Dra.` según el género inferido del primer nombre si el recepcionista no lo escribió manualmente (`RN-22`). La tabla Cuerpo Médico en [`lista_odontologos.php`](view/odontologo/lista_odontologos.php) muestra únicamente la acción **Eliminar** por odontólogo. En el panel visual `#panel-consultorios` recepción puede **crear nuevos consultorios** (`#modalNuevoConsultorio`), **eliminar consultorios libres** (adaptando la clínica a cualquier número de consultorios según cada sede, protegiendo los ocupados bajo `RN-20`), registrar nuevos especialistas (`+ Asignar aquí` en consultorios libres) y editar los existentes (`Gestionar asignación` en consultorios ocupados o en la franja de inactivos). Incluye `slug_publico` automático, estado del odontólogo en tiempo real (`Ocupado / En cita`, `Activo / Disponible`, `Inactivo`), directorio de pacientes con sincronización automática en vivo (`RF-037`) y eliminación segura de servicios (`RN-12`).
* **Módulo de Reportes Clínicos y Estadísticos ([`reportes.php`](view/recepcionista/reportes.php), `RF-043`)**: Filtro por rango de fechas, 4 KPIs globales, 3 tarjetas líderes, 4 rankings con barras de porcentaje, ingresos convertibles `COP ↔ USD`, **exportación nativa en libros Excel (`.xlsx`) con diseño corporativo DENTIX** (libro consolidado de 5 pestañas coloreadas o descarga individual de *Resumen KPIs*, *Servicios*, *Odontólogos*, *Pacientes* y *Horarios* con formato moneda COP, porcentajes, autofiltro, fila congelada y totales) y botón **Imprimir / Exportar PDF** con estilos `@media print`.

### 3.5 Panel Clínico del Odontólogo ([`view/odontologo/panel_odontologo.php`](view/odontologo/panel_odontologo.php))
* **Agenda Exclusiva**: Lista únicamente las citas en estado `confirmada` asignadas al especialista autenticado, destacando alertas médicas de alergias en rojo.
* **Cierre Clínico Atómico (`Finalizada`)**: Modal que registra en una transacción PDO (`cita` + `detalle_cita`) la `fecha_salida`, `hora_salida`, resumen de la cita, procedimiento realizado y `nota_clinica` (con valor por defecto si se omite), pasando la cita a `completada`.
* **Historial de Finalizadas ([`historial_finalizadas.php`](view/odontologo/historial_finalizadas.php))**: Buscador en tiempo real por paciente, cédula o tratamiento.

---

## 4. Base de Datos PostgreSQL y Reglas de Negocio (`RN-01` a `RN-22`)

El esquema físico se encuentra consolidado en [`database/citas_odontologicas.sql`](database/citas_odontologicas.sql) e incluye:
* **7 Tablas**: `paciente`, `recepcionista`, `consultorio` (catálogo dinámico por sede, `RF-041`), `odontologo`, `servicio`, `cita`, `detalle_cita` (con relación `1:1` garantizada mediante `uq_detalle_cita`).
* **2 Vistas**: `v_agenda_dia` y `v_historial_paciente`.
* **Índices y Tipo ENUM**: 8 índices explícitos `ix_*`, índices `UNIQUE` incluyendo **`uq_consultorio_nombre_ci`** (`RN-20`: impide consultorios duplicados) y el índice único parcial **`uq_odontologo_consultorio_activo`** (`RN-19`: impide asignar un mismo consultorio simultáneamente a dos odontólogos activos), y tipo `estado_cita` (`pendiente`, `confirmada`, `completada`, `cancelada`, `no_show`).
* **2 Triggers PL/pgSQL (`trg_validar_espaciado_citas` y `trg_validar_detalle_cita`)**:
  * **`RN-07`**: Un paciente solo puede tener **1 cita por día** y deben transcurrir al menos **4 días completos** entre citas activas/completadas (`ABS(fecha - NEW.fecha) < 5`).
  * **`RN-08`**: Un odontólogo solo puede atender máximo **1 cita por día** (`confirmada` o `completada`).
  * **`RN-21`**: La especialidad del odontólogo asignado a una cita debe coincidir exactamente con el nombre del servicio solicitado por el paciente (`lower(trim(o.especialidad)) = lower(trim(s.nombre))`).
* **Restricciones `CHECK` y `UNIQUE` destacadas**: `ck_paciente_nombre_formato` (*Title Case*), `ck_paciente_fecha_nac`, `ck_cita_horario` (`08:00`–`19:30` en bloques de `30 min`), `ck_cita_cancelacion` (motivo obligatorio al cancelar), `uq_agenda`, `uq_detalle_cita` y `uq_odontologo_consultorio_activo` (`RN-19`).

---

## 5. Instalación y Puesta en Marcha

1. **Habilitar extensiones PHP (`php.ini`)**: Verificar que `pdo_pgsql`, `pgsql` y `mbstring` estén activas en XAMPP/LAMPP.
2. **Aprovisionar la base de datos en PostgreSQL**:
   ```bash
   psql -h 127.0.0.1 -p 5432 -U postgres -d citas_odontologicas -f /opt/lampp/htdocs/DENTIX/database/citas_odontologicas.sql
   ```
3. **Configurar credenciales locales**:
   ```bash
   cp /opt/lampp/htdocs/DENTIX/config/database.example.php /opt/lampp/htdocs/DENTIX/config/database.php
   ```
   *(Editar `$host`, `$port`, `$dbname`, `$username` y `$password` en [`config/database.php`](config/database.php)).*
4. **Iniciar servicios y abrir en el navegador**:
   ```bash
   sudo systemctl start postgresql && sudo /opt/lampp/lampp startapache
   ```
   Acceder a: `http://localhost:8080/DENTIX/`

---

## 6. Credenciales de Prueba (`database/citas_odontologicas.sql`)

Todas las cuentas iniciales generadas con `pgcrypto` utilizan la contraseña de prueba **`12141214`**:

| Rol | Usuario / Identificador | Contraseña | Ruta de Acceso |
| :--- | :--- | :--- | :--- |
| **Recepción** | `jose.recepcion@clinica.com`, `maria.recepcion@clinica.com`, `sofia.recepcion@clinica.com` | `12141214` | `view/recepcionista/lista_recepcionista.php` |
| **Odontólogos** | `ana.torres@clinica.com`, `luis.mendoza@clinica.com`, `sofia.rios@clinica.com`, `carlos.vega@clinica.com`, `valentina.ortiz@clinica.com` | `12141214` | `view/odontologo/login_odontologo.php` |
| **Pacientes** | Cédulas: `1001112233`, `1004445566`, `1076907716`, `1005556677`, `1007778899` | `12141214` | `view/paciente/login_paciente.php` |

---

## 7. Documentación Oficial, Calidad y Licenciamiento (100 % Word `.docx`)

* **Especificación de Requisitos (SRS)**: [`Extras/SRS_V 4.0.docx`](Extras/SRS_V%204.0.docx) (`RF-001`–`RF-045`, `RNF-001`–`RNF-014`, `RN-01`–`RN-22`, `US-01`–`US-39`, `UC-01`–`UC-14` y Sección 10.5 de Licenciamiento y Costos).
* **Plan de Aseguramiento de Calidad (PAC)**: [`Extras/PAC_v2.3.docx`](Extras/PAC_v2.3.docx) (69 casos de prueba `TC-01`–`TC-69` ejecutados con evidencia en 9 columnas, Exit Criteria y SLA QA).
* **Log de Errores y Sugerencias (Expediente QA)**: [`Extras/LOG_ERRORES_Y_SUGERENCIAS.docx`](Extras/LOG_ERRORES_Y_SUGERENCIAS.docx) (registro de `DEF-001`–`DEF-027` cerrados, mejoras evolutivas `MEJ-001`–`MEJ-015` y 10 sugerencias evolutivas `SUG-01`–`SUG-10`).
* **Modelo Entidad-Relación Oficial**: [`Extras/MER.docx`](Extras/MER.docx) (y gráfico fuente en [`assets/img/MER.png`](assets/img/MER.png)).
* **Manuales del Sistema**: [`manuales/MANUAL_TECNICO.docx`](manuales/MANUAL_TECNICO.docx) y [`manuales/MANUAL_USUARIO.docx`](manuales/MANUAL_USUARIO.docx).
* **Guion de Pitch y Defensa Técnica**: [`Extras/GUION_PITCH_Y_DEFENSA_DENTIX.docx`](Extras/GUION_PITCH_Y_DEFENSA_DENTIX.docx).
* **Video Demostrativo Integrado**: [`assets/video/demo_dentix.mp4`](assets/video/demo_dentix.mp4) (reproducible directamente desde [`index.php`](index.php) sección `#video-demo`).
* **Modelo de Licenciamiento**: Software **Privativo** con cesión íntegra del código fuente para uso exclusivo de la clínica adquiriente (modificaciones sujetas a autorización previa escrita de los autores), **6 meses de garantía y soporte técnico** y administración de infraestructura en servidor/nube con **copias de seguridad periódicas automatizadas** (paquete llave en mano valorado en **$ 11.200.000 COP / $ 2.800 USD**, desglosado en 340 h desarrollo + 36 h despliegue/capacitación + 6 meses nube/soporte según la Sección 10.5 del SRS v4.0).

---
*Proyecto desarrollado para el programa **Tecnólogo en Análisis y Desarrollo de Software (ADSO — Ficha 3407799)** — **CIES SENA Neiva, Huila**.*
