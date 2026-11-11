# DENTIX — Sistema Integral de Gestión de Citas Odontológicas

**DENTIX** es una plataforma web clínica, operativa y administrativa desarrollada en **PHP 8 (PDO)** y **PostgreSQL 12+** sobre entorno **Apache (XAMPP/LAMPP)**. Articula de extremo a extremo la operación de una clínica odontológica conectando en tiempo real a **Visitantes**, **Pacientes**, **Recepcionistas (Administradores)** y **Odontólogos (Cuerpo Médico)**, incorporando además cumplimiento legal colombiano (**Ley 1581 de 2012**, **Ley 35 de 1989** y **Resolución 1995 de 1999**), **módulo de reportes gerenciales imprimibles en PDF**, **traducción automática global Español (`ES`) ↔ Inglés (`EN`)** y **conversión monetaria en tiempo real Pesos Colombianos (`COP`) ↔ Dólares (`USD`)**.

---

## Tabla de Contenido

1. [Resumen de Evolución y Cambios Respecto a la Versión Anterior](#1-resumen-de-evolución-y-cambios-respecto-a-la-versión-anterior)
2. [Estructura Completa de Carpetas y Archivos](#2-estructura-completa-de-carpetas-y-archivos)
3. [Funcionalidades Detalladas por Módulo y Rol](#3-funcionalidades-detalladas-por-módulo-y-rol)
4. [Base de Datos PostgreSQL y Reglas de Negocio (`RN-01` a `RN-18`)](#4-base-de-datos-postgresql-y-reglas-de-negocio-rn-01-a-rn-18)
5. [Instalación y Puesta en Marcha](#5-instalación-y-puesta-en-marcha)
6. [Credenciales de Prueba (`database/citas_odontologicas`)](#6-credenciales-de-prueba-databasecitas_odontologicas)
7. [Documentación Oficial, Calidad y Licenciamiento](#7-documentación-oficial-calidad-y-licenciamiento)

---

## 1. Resumen de Evolución y Cambios Respecto a la Versión Anterior

Respecto a las versiones anteriores del proyecto (enfocadas en un CRUD básico con agenda simple), **DENTIX** fue reconstruido e integrado funcional, visual y documentalmente incorporando las siguientes mejoras clave:

| Área / Componente | Versión Anterior | Estado Actual en DENTIX |
| :--- | :--- | :--- |
| **Registro de Pacientes y Datos Clínicos** | Solo capturaba datos básicos (`nombre`, `cédula`, `teléfono`, `correo`, `contraseña`). Sin validación estricta de nombre ni datos clínicos al registrarse. | Captura obligatoria de **`fecha_nac`** (`ck_paciente_fecha_nac`) y bloque interactivo de **Alergias / Alertas Médicas** (selector, chips rápidos y descripción). Validación estricta **Nombre Propio (*Title Case*)** en JS, PHP y PostgreSQL (`ck_paciente_nombre_formato`). |
| **Cumplimiento Legal (Ley 1581 / Ley 35)** | Sin flujo formal de consentimiento informado previo al registro. | Ventana modal **`#termsModal`** con **5 cláusulas expandibles (`Ver más...`)** y **alerta sorpresa de bloqueo (`#termsLockBanner`)** que impide aceptar o crear la cuenta sin haber leído los 5 puntos completos (`acepto_terminos = 1`). |
| **Portal del Paciente y Solicitud de Citas** | Gestión limitada o dependiente de recepción. | Portal autenticado completo con cédula y contraseña, 4 tarjetas KPI, edición de perfil/alergias, solicitud autónoma de cita sin odontólogo (`odontologo_id = NULL`) e historial con estado y **motivo de cancelación**. |
| **Portal Exclusivo del Odontólogo** | Sin módulo independiente o con campos clínicos limitados. | Login propio ([`login_odontologo.php`](file:///opt/lampp/htdocs/DENTIX/view/odontologo/login_odontologo.php)), agenda filtrada por sesión con alertas rojas de alergias, modal **`Finalizada`** con **cierre clínico transaccional atómico** (`fecha_salida`, `hora_salida`, resumen, procedimiento y nota clínica) e historial buscable ([`historial_finalizadas.php`](file:///opt/lampp/htdocs/DENTIX/view/odontologo/historial_finalizadas.php)). |
| **Formularios de Edición y Cancelación en Recepción** | Formularios de edición sin precarga por ID, falta de interfaz para cancelar con motivo y sin botón de eliminar servicios en tabla. | Edición precargada directa en [`editar_odontologo.php`](file:///opt/lampp/htdocs/DENTIX/view/odontologo/editar_odontologo.php), [`editar_paciente.php`](file:///opt/lampp/htdocs/DENTIX/view/paciente/editar_paciente.php) y [`editar_cita.php`](file:///opt/lampp/htdocs/DENTIX/view/cita/editar_cita.php); modal obligatorio **`#cancelarCitaModal`** (`RN-06`); y eliminación segura de servicios (`RN-12`) en [`lista_servicios.php`](file:///opt/lampp/htdocs/DENTIX/view/servicio/lista_servicios.php). |
| **Módulo de Reportes Gerenciales** | No existía o figuraba fuera de alcance. | Nuevo módulo [`view/recepcionista/reportes.php`](file:///opt/lampp/htdocs/DENTIX/view/recepcionista/reportes.php) con filtro por rango de fechas, 4 KPIs, 3 tarjetas líderes, 4 rankings estadísticos (pacientes, tratamientos, productividad médica y horarios pico), conversión `COP/USD` e impresión limpia en **PDF** (`@media print`). |
| **Página Principal, Manual y Mega Pie de Página** | Footer oscuro tradicional con botón *"Volver al inicio"*, sin guía integrada en página y con clase de ícono faltante en catálogo público. | Nuevo **Manual Interactivo de Usuario (`#userManualModal`)** de 5 pestañas en [`index.php`](file:///opt/lampp/htdocs/DENTIX/index.php); **Mega Pie de Página corporativo (`.mega-footer`)** en blanco menta y aguamarina suave con horario en vivo (`08:00 a 19:30`), 15 accesos y 4 cajones legales; e iconografía corregida (`fa-hand-sparkles`, `fa-tooth`) en [`catalogo_publico.php`](file:///opt/lampp/htdocs/DENTIX/view/servicio/catalogo_publico.php). |
| **Internacionalización (`ES/EN`) y Moneda (`COP/USD`)** | Plataforma disponible únicamente en español y pesos colombianos. | Nuevo motor transversal [`assests/js/dentix-i18n.js`](file:///opt/lampp/htdocs/DENTIX/assests/js/dentix-i18n.js) inyectado en **`index.php` y las 22 vistas de `view/`**: traducción instantánea sin pérdida (`WeakMap` + `MutationObserver`), conversión automática `COP ↔ USD` (`1 USD = $4.000 COP`), vista previa USD en tiempo real (`#usdLivePreview`) en formularios de servicio y barra flotante global `#dentixFloatingLang`. |
| **Base de Datos y Reglas de Agenda** | Reglas básicas sin `fecha_salida`/`hora_salida`, sin formato `Title Case` en BD y con cargo líder/asociado obsoleto. | Script unificado [`database/citas_odontologicas`](file:///opt/lampp/htdocs/DENTIX/database/citas_odontologicas): 6 tablas, 2 vistas, 9 índices, trigger `trg_validar_espaciado_citas` (`RN-07`: 1 cita/día y mínimo 4 días completos entre citas del paciente; `RN-08`: máx. 1 cita/día por odontólogo) y `ck_paciente_nombre_formato`. |
| **Documentación SENA, QA y Licenciamiento** | Documentación parcial de versiones previas. | Incorporación de la carpeta **`Extras/`** y actualización integral de **`manuales/`**: SRS maestro con tabla de contenido interactiva y **Sección 10 de Licenciamiento Privativo, Derechos/Deberes y Valoración (`$ 11.200.000 COP`)**, PAC de 56 casos de prueba, Log de Errores (`DEF-001`–`DEF-008`) y Sugerencias (`SUG-01`–`SUG-10`), Diagrama ER `1:1` corregido y Manuales Técnico y de Usuario. |

---

## 2. Estructura Completa de Carpetas y Archivos

```text
DENTIX/
├── app/                                      # Capa de Lógica de Negocio, Seguridad y Sesiones
│   ├── odontologo_auth.php                   # Middleware de sesión segura, cookies HttpOnly y CSRF (Odontólogo)
│   ├── paciente_auth.php                     # Middleware de sesión segura, cookies HttpOnly y CSRF (Paciente)
│   ├── recepcionista_auth.php                # Middleware de sesión segura, cookies HttpOnly y CSRF (Recepción)
│   ├── logica_cita.php                       # CRUD completo de citas, confirmación, edición y cancelación (RN-06..08)
│   ├── logica_odontologo.php                 # CRUD de odontólogos, generación de slug_publico y cierre clínico
│   ├── logica_paciente.php                   # Registro (Title Case, Ley 1581, fecha_nac, alergias), login y edición
│   ├── logica_recepcionista.php              # Autenticación y creación de cuentas de recepción
│   └── logica_servicio.php                   # CRUD y eliminación segura de servicios odontológicos (RN-11, RN-12)
│
├── assests/                                  # Recursos Frontend Estáticos
│   ├── css/
│   │   ├── admin.css                         # Diseño de paneles de recepción, odontólogo y reportes (@media print)
│   │   ├── index.css                         # Landing page, Manual Interactivo, #termsModal y Mega Footer aguamarina
│   │   └── paciente.css                      # Interfaz visual del Portal y Perfil del Paciente
│   ├── img/
│   │   └── proyecto-placeholder.svg          # Recurso gráfico institucional
│   └── js/
│       ├── dentix-i18n.js                    # Motor global ES ↔ EN + Conversor COP ↔ USD + Widget #dentixFloatingLang
│       ├── index-animations.js               # Animaciones GSAP, lógica del Manual 5 pestañas y reloj en vivo del Footer
│       ├── admin-animations.js               # Interacciones y animaciones del panel administrativo
│       └── animaciones.js                    # Utilidades visuales complementarias
│
├── config/                                   # Configuración de Conexión a PostgreSQL
│   ├── database.example.php                  # Plantilla pública de conexión PDO (versionada)
│   └── database.php                          # Credenciales reales locales PDO (protegida en .gitignore)
│
├── database/                                 # Persistencia y Lógica en Motor Relacional
│   └── citas_odontologicas                   # Script oficial PostgreSQL: 6 tablas, ENUM, CHECKs, Trigger, 2 Vistas, 9 Índices y Seed
│
├── view/                                     # Capa de Presentación (22 vistas HTML5/PHP responsivas)
│   ├── cita/
│   │   ├── lista_citas.php                   # Solicitudes pendientes, asignación de odontólogo, confirmadas y modal cancelar
│   │   ├── form_cita.php                     # Creación manual de cita desde recepción
│   │   └── editar_cita.php                   # Reprogramación y edición de citas con motivo de cancelación
│   ├── odontologo/
│   │   ├── login_odontologo.php              # Inicio de sesión exclusivo para especialistas
│   │   ├── panel_odontologo.php              # KPIs, citas confirmadas con alerta de alergias y modal "Finalizada"
│   │   ├── historial_finalizadas.php         # Archivo histórico clínico de citas completadas con buscador en vivo
│   │   ├── lista_odontologos.php             # Directorio médico con estado dinámico (Activo / Ocupado / Inactivo)
│   │   ├── form_odontologo.php               # Registro de nuevo especialista
│   │   ├── editar_odontologo.php             # Edición completa por ?id=, estado de cuenta y enlace slug público
│   │   └── cerrar_sesion_odontologo.php      # Cierre de sesión por POST + CSRF
│   ├── paciente/
│   │   ├── form_paciente.php                 # Registro público/admin (Title Case, fecha_nac, chips alergias y #termsModal)
│   │   ├── login_paciente.php                # Acceso por cédula y contraseña
│   │   ├── perfil_paciente.php               # Tarjetas estadísticas, edición de perfil/alergias e historial de citas
│   │   ├── portal_paciente.php               # Solicitud autónoma de citas validando horario (RN-03) y regla de 4 días (RN-07)
│   │   ├── agendar_cita.php                  # Redirección autenticada al flujo de agendamiento
│   │   ├── lista_pacientes.php               # Directorio administrativo con insignias de alergias médicas y fecha_nac
│   │   ├── editar_paciente.php               # Edición completa de expediente del paciente desde recepción
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
├── manuales/                                 # Documentación Técnica y Operativa en Markdown
│   ├── MANUAL_TECNICO.md                     # Arquitectura, diccionario de datos, despliegue, seguridad y backups
│   └── MANUAL_USUARIO.md                     # Guía paso a paso por rol (Visitante, Paciente, Odontólogo y Recepción)
│
├── Extras/                                   # Documentación SENA, Aseguramiento de Calidad y Evidencias
│   ├── SRS_V 4.0.docx                        # Especificación de Requisitos (RF-001..036, RNF-001..014, Licenciamiento y Costos)
│   ├── PAC_v2.3.docx                         # Plan de Aseguramiento de Calidad (56 casos TC-01..TC-56 + Licenciamiento)
│   ├── LOG_ERRORES_Y_SUGERENCIAS.md          # Expediente QA: DEF-001..DEF-008 y Banco de Sugerencias SUG-01..SUG-10
│   ├── MER.png                               # Diagrama Entidad-Relación actualizado (cardinalidad 1:1 en cita ↔ detalle_cita)
│   └── Video.mp4                             # Evidencia audiovisual de funcionamiento del sistema
│
├── index.php                                 # Landing page pública, Manual Interactivo (5 pestañas) y Mega Footer
└── README.md                                 # Documentación principal del repositorio
```

---

## 3. Funcionalidades Detalladas por Módulo y Rol

### 3.1 Experiencia Pública, Manual Interactivo y Mega Footer ([`index.php`](file:///opt/lampp/htdocs/DENTIX/index.php))
* **Navegación por Roles**: Accesos directos hacia *Servicios*, *Agendar cita*, *Manual*, *Odontólogos*, *Administrador* e *Iniciar sesión* para pacientes.
* **Manual Interactivo (`#userManualModal`)**: Guía modal integrada con 5 pestañas (*Inicio Rápido*, *Soy Paciente*, *Soy Odontólogo*, *Recepción / Admin* y *Dudas y Estados*), sincronizada con el motor `ES ↔ EN`.
* **Mega Pie de Página Corporativo (`.mega-footer`)**: Diseñado en blanco menta y aguamarina suave con indicador de estado horario en vivo (*Abierto `08:00 a 19:30`* o *Agenda en línea 24/7*), directorio de 15 accesos directos, 4 cajones informativos legales (*Condiciones de uso*, *Ley 1581*, *Datos de Salud*, *Ley 35*) y selectores integrados de idioma y moneda.
* **Catálogo Público ([`catalogo_publico.php`](file:///opt/lampp/htdocs/DENTIX/view/servicio/catalogo_publico.php))**: Tarjetas informativas con íconos clínicos, descripción, duración estimada y tarifa convertible entre `COP` y `USD`.

### 3.2 Motor Transversal de Idioma (`ES ↔ EN`) y Moneda (`COP ↔ USD`) ([`dentix-i18n.js`](file:///opt/lampp/htdocs/DENTIX/assests/js/dentix-i18n.js))
* **Cobertura en toda la web**: Activo en [`index.php`](file:///opt/lampp/htdocs/DENTIX/index.php) y en las **22 vistas internas** de `view/`.
* **Traducción sin pérdida (`WeakMap` + `MutationObserver`)**: Traduce textos, encabezados, tablas, modales, atributos (`placeholder`, `title`, `aria-label`) y alertas del sistema al instante sin recargar la página ni perder el texto original en español.
* **Conversión Monetaria (`1 USD = $4.000 COP`)**: Convierte automáticamente tarifas de tratamientos e ingresos en [`catalogo_publico.php`](file:///opt/lampp/htdocs/DENTIX/view/servicio/catalogo_publico.php), [`lista_servicios.php`](file:///opt/lampp/htdocs/DENTIX/view/servicio/lista_servicios.php) y [`reportes.php`](file:///opt/lampp/htdocs/DENTIX/view/recepcionista/reportes.php), además de mostrar equivalencia en vivo (`#usdLivePreview`) al crear o editar servicios.
* **Persistencia y Widget Flotante**: Conserva la selección de idioma y divisa en `localStorage` y cookies entre todas las vistas mediante el control flotante `#dentixFloatingLang` (oculto automáticamente al imprimir PDF).

### 3.3 Portal del Paciente y Consentimiento Legal ([`view/paciente/`](file:///opt/lampp/htdocs/DENTIX/view/paciente/form_paciente.php))
* **Calidad de Datos Clínicos (`DEF-008` corregido)**: Registro obligatorio con **Nombre en formato propio (*Title Case*)**, cédula única, **Fecha de nacimiento (`fecha_nac <= hoy`)**, **Alergias médicas** (selector, botones rápidos y detalle manual), teléfono, correo y contraseña `bcrypt` (8–72 caracteres).
* **Modal Obligatorio de Términos y Condiciones (`#termsModal`)**: Presenta **5 cláusulas legales** (Ley 1581 de 2012, Ley 35 de 1989, Resolución 1995 de 1999 y políticas `RN-07`). Si el usuario intenta aceptar sin desplegar los 5 botones `"Ver más..."`, se dispara el aviso de bloqueo `#termsLockBanner` señalando los puntos pendientes; en PHP se exige `acepto_terminos = 1`.
* **Perfil e Historial ([`perfil_paciente.php`](file:///opt/lampp/htdocs/DENTIX/view/paciente/perfil_paciente.php))**: Muestra 4 tarjetas estadísticas, actualización de datos y alergias, y listado de citas con odontólogo asignado y motivo de cancelación cuando aplica.

### 3.4 Panel Administrativo de Recepción y Reportes ([`view/cita/`](file:///opt/lampp/htdocs/DENTIX/view/cita/lista_citas.php), [`view/recepcionista/reportes.php`](file:///opt/lampp/htdocs/DENTIX/view/recepcionista/reportes.php))
* **Gestión de Citas**: Solicitudes pendientes, asignación de odontólogo activo validando **`RN-08`**, creación manual, edición precargada y **modal `#cancelarCitaModal`** obligando a documentar el motivo (`RN-06`).
* **Gestión de Cuerpo Médico, Pacientes y Servicios**: Alta/edición precargada por `?id=`, `slug_publico` automático, estado del odontólogo calculado en tiempo real (`Ocupado / En cita`, `Activo / Disponible`, `Inactivo`), etiquetas visuales de alergias en pacientes y eliminación segura de servicios (`RN-12`).
* **Módulo de Reportes Clínicos y Estadísticos ([`reportes.php`](file:///opt/lampp/htdocs/DENTIX/view/recepcionista/reportes.php))**: Filtro por rango de fechas, 4 KPIs globales, 3 tarjetas líderes, 4 rankings con barras de porcentaje, ingresos convertibles `COP ↔ USD` y botón **Imprimir / Exportar PDF** con estilos `@media print`.

### 3.5 Panel Clínico del Odontólogo ([`view/odontologo/panel_odontologo.php`](file:///opt/lampp/htdocs/DENTIX/view/odontologo/panel_odontologo.php))
* **Agenda Exclusiva**: Lista únicamente las citas en estado `confirmada` asignadas al especialista autenticado, destacando alertas médicas de alergias en rojo.
* **Cierre Clínico Atómico (`Finalizada`)**: Modal que registra en una transacción PDO (`cita` + `detalle_cita`) la `fecha_salida`, `hora_salida`, resumen de la cita, procedimiento realizado y `nota_clinica` (con valor por defecto si se omite), pasando la cita a `completada`.
* **Historial de Finalizadas ([`historial_finalizadas.php`](file:///opt/lampp/htdocs/DENTIX/view/odontologo/historial_finalizadas.php))**: Buscador en tiempo real por paciente, cédula o tratamiento.

---

## 4. Base de Datos PostgreSQL y Reglas de Negocio (`RN-01` a `RN-18`)

El esquema físico se encuentra consolidado en [`database/citas_odontologicas`](file:///opt/lampp/htdocs/DENTIX/database/citas_odontologicas) e incluye:
* **6 Tablas**: `paciente`, `recepcionista`, `odontologo`, `servicio`, `cita`, `detalle_cita` (con relación `1:1` garantizada mediante `uq_detalle_cita`).
* **2 Vistas**: `v_agenda_dia` y `v_historial_paciente`.
* **9 Índices y Tipo ENUM**: `estado_cita` (`pendiente`, `confirmada`, `completada`, `cancelada`, `no_show`).
* **Trigger PL/pgSQL (`trg_validar_espaciado_citas`)**:
  * **`RN-07`**: Un paciente solo puede tener **1 cita por día** y deben transcurrir al menos **4 días completos** entre citas activas/completadas (`ABS(fecha - NEW.fecha) < 5`).
  * **`RN-08`**: Un odontólogo solo puede atender máximo **1 cita por día** (`confirmada` o `completada`).
* **Restricciones `CHECK` y `UNIQUE` destacadas**: `ck_paciente_nombre_formato` (*Title Case*), `ck_paciente_fecha_nac`, `ck_cita_horario` (`08:00`–`19:30` en bloques de `30 min`), `ck_cita_cancelacion` (motivo obligatorio al cancelar), `uq_agenda` y `uq_detalle_cita`.

---

## 5. Instalación y Puesta en Marcha

1. **Habilitar extensiones PHP (`php.ini`)**: Verificar que `pdo_pgsql`, `pgsql` y `mbstring` estén activas en XAMPP/LAMPP.
2. **Aprovisionar la base de datos en PostgreSQL**:
   ```bash
   psql -h 127.0.0.1 -p 5432 -U postgres -d citas_odontologicas -f /opt/lampp/htdocs/DENTIX/database/citas_odontologicas
   ```
3. **Configurar credenciales locales**:
   ```bash
   cp /opt/lampp/htdocs/DENTIX/config/database.example.php /opt/lampp/htdocs/DENTIX/config/database.php
   ```
   *(Editar `$host`, `$port`, `$dbname`, `$username` y `$password` en [`config/database.php`](file:///opt/lampp/htdocs/DENTIX/config/database.php)).*
4. **Iniciar servicios y abrir en el navegador**:
   ```bash
   sudo systemctl start postgresql && sudo /opt/lampp/lampp startapache
   ```
   Acceder a: `http://localhost:8080/DENTIX/`

---

## 6. Credenciales de Prueba (`database/citas_odontologicas`)

Todas las cuentas iniciales generadas con `pgcrypto` utilizan la contraseña de prueba **`12141214`**:

| Rol | Usuario / Identificador | Contraseña | Ruta de Acceso |
| :--- | :--- | :--- | :--- |
| **Recepción** | `jose.recepcion@clinica.com`, `maria.recepcion@clinica.com`, `sofia.recepcion@clinica.com` | `12141214` | `view/recepcionista/lista_recepcionista.php` |
| **Odontólogos** | `ana.torres@clinica.com`, `luis.mendoza@clinica.com`, `sofia.rios@clinica.com`, `carlos.vega@clinica.com`, `valentina.ortiz@clinica.com` | `12141214` | `view/odontologo/login_odontologo.php` |
| **Pacientes** | Cédulas: `1001112233`, `1004445566`, `1076907716`, `1005556677`, `1007778899` | `12141214` | `view/paciente/login_paciente.php` |

---

## 7. Documentación Oficial, Calidad y Licenciamiento

* **Especificación de Requisitos (SRS)**: [`Extras/SRS_V 4.0.docx`](file:///opt/lampp/htdocs/DENTIX/Extras/SRS_V%204.0.docx) (`RF-001`–`RF-036`, `RNF-001`–`RNF-014`, `RN-01`–`RN-18`, `US-01`–`US-30`, `UC-01`–`UC-13` y Sección 10 de Licenciamiento).
* **Plan de Aseguramiento de Calidad (PAC)**: [`Extras/PAC_v2.3.docx`](file:///opt/lampp/htdocs/DENTIX/Extras/PAC_v2.3.docx) (56 casos de prueba `TC-01`–`TC-56`, métricas y licenciamiento).
* **Log de Errores y Sugerencias (Expediente QA)**: [`Extras/LOG_ERRORES_Y_SUGERENCIAS.md`](file:///opt/lampp/htdocs/DENTIX/Extras/LOG_ERRORES_Y_SUGERENCIAS.md) (registro de `DEF-001`–`DEF-008` cerrados y 10 sugerencias evolutivas `SUG-01`–`SUG-10`).
* **Diagrama Entidad-Relación Oficial**: [`Extras/MER.png`](file:///opt/lampp/htdocs/DENTIX/Extras/MER.png).
* **Manuales del Sistema**: [`manuales/MANUAL_TECNICO.md`](file:///opt/lampp/htdocs/DENTIX/manuales/MANUAL_TECNICO.md) y [`manuales/MANUAL_USUARIO.md`](file:///opt/lampp/htdocs/DENTIX/manuales/MANUAL_USUARIO.md).
* **Modelo de Licenciamiento**: Software **Privativo** con cesión íntegra del código fuente para uso exclusivo de la clínica adquiriente (modificaciones sujetas a autorización previa escrita de los autores), **6 meses de garantía y soporte técnico** y administración de infraestructura en servidor/nube con **copias de seguridad periódicas automatizadas** (paquete llave en mano valorado en **$ 11.200.000 COP / $ 2.800 USD** según el SRS y PAC).

---
*Proyecto desarrollado para el programa **Tecnólogo en Análisis y Desarrollo de Software (ADSO — Ficha 3407799)** — **CIES SENA Neiva, Huila**.*
