# DENTIX

Sistema web para la gestión integral de una clínica odontológica: pacientes, recepción, odontólogos, servicios, citas, historial clínico y reportes.

El proyecto está construido con **PHP 8 procedural**, **PostgreSQL** y **PDO**, bajo una arquitectura **MVC mínima** con un único punto de entrada (`public/index.php`), rutas por query string (`index.php?ruta=...`) y separación estricta entre controladores, modelos y vistas.

---

## Tabla de contenido

1. [Descripción general](#1-descripción-general)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Arquitectura](#3-arquitectura)
4. [Estructura del proyecto](#4-estructura-del-proyecto)
5. [Flujo de una petición](#5-flujo-de-una-petición)
6. [Capas y archivos](#6-capas-y-archivos)
7. [Rutas y permisos](#7-rutas-y-permisos)
8. [Roles, sesiones y CSRF](#8-roles-sesiones-y-csrf)
9. [Base de datos](#9-base-de-datos)
10. [Instalación y configuración](#10-instalación-y-configuración)
11. [Pruebas y validación](#11-pruebas-y-validación)
12. [Seguridad](#12-seguridad)
13. [Convenciones de desarrollo](#13-convenciones-de-desarrollo)
14. [Documentación adicional](#14-documentación-adicional)
15. [Licenciamiento y créditos](#15-licenciamiento-y-créditos)

---

## 1. Descripción general

DENTIX cubre cuatro perfiles:

| Perfil | Qué puede hacer |
| :--- | :--- |
| **Visitante** | Ver la página principal, el catálogo público de tratamientos y los accesos de inicio de sesión/registro. |
| **Paciente** | Registrarse, iniciar sesión, solicitar citas, consultar su perfil y su historial de citas. |
| **Recepción / Administrador** | Gestionar pacientes, odontólogos, servicios y citas; confirmar solicitudes, asignar odontólogos, cancelar citas y generar reportes. |
| **Odontólogo** | Ver su agenda diaria, finalizar citas con detalle clínico y consultar su historial de citas completadas. |

Funcionalidades destacadas:

- Registro de pacientes con validación de nombre, cédula, fecha de nacimiento, alergias y consentimiento de datos.
- Catálogo público de tratamientos con precios en COP y conversión a USD.
- Agenda con reglas de negocio: horario de 08:00 a 19:30, intervalos de 30 minutos, máximo una cita por odontólogo al día y separación mínima entre citas de un mismo paciente.
- Confirmación de citas con asignación de odontólogo.
- Finalización de citas con cierre clínico transaccional: estado `completada` + registro en `detalle_cita`.
- Reportes de recepción con KPIs, rankings y filtros por rango de fechas.
- Internacionalización ES/EN y conversión COP/USD en el frontend.

---

## 2. Stack tecnológico

| Componente | Uso |
| :--- | :--- |
| **PHP 8.1+** | Lógica del servidor, controladores, modelos, helpers y vistas. |
| **PostgreSQL 12+** | Base de datos relacional, reglas de negocio, trigger y vistas. |
| **PDO + pdo_pgsql** | Acceso a datos con consultas preparadas. |
| **Apache + mod_rewrite** | Servidor web y reescritura hacia `public/`. |
| **HTML5 / CSS3** | Interfaz responsiva de las vistas. |
| **Bootstrap 5** | Componentes y utilidades de interfaz. |
| **Font Awesome** | Iconografía clínica y de navegación. |
| **JavaScript** | Animaciones, i18n ES/EN, conversión COP/USD y utilidades de interfaz. |

---

## 3. Arquitectura

El proyecto usa una arquitectura **MVC procedural**:

```text
Navegador
   │
   ▼
.htaccess  ──►  public/index.php  ──►  routes/web.php
                                             │
                                             ▼
                                  app/controllers/*.php
                                             │
                              ┌──────────────┴──────────────┐
                              ▼                             ▼
                     app/models/*.php                app/views/*.php
                              │                             │
                              ▼                             ▼
                        PostgreSQL                      HTML final
```

Principios:

- **Un solo punto de entrada:** todas las peticiones pasan por `public/index.php`.
- **Rutas declarativas:** `routes/web.php` asocia una ruta con un archivo de controlador y una función.
- **Controladores delgados:** validan la petición, llaman a los modelos y renderizan vistas.
- **Modelos procedurales:** concentran el SQL y usan `db()` para obtener la conexión PDO.
- **Vistas sin lógica de negocio:** reciben datos ya preparados y se encargan solo de la presentación.
- **Helpers compartidos:** URLs, renderizado, mensajes flash, sesión, autenticación y CSRF.

---

## 4. Estructura del proyecto

```text
DENTIX/
├── app/
│   ├── bootstrap.php                  # Carga configuración, helpers e inicia la sesión
│   ├── controllers/
│   │   ├── home_controller.php
│   │   ├── paciente_controller.php
│   │   ├── odontologo_controller.php
│   │   ├── recepcionista_controller.php
│   │   ├── cita_controller.php
│   │   ├── servicio_controller.php
│   │   └── reporte_controller.php
│   ├── models/
│   │   ├── paciente_model.php
│   │   ├── odontologo_model.php
│   │   ├── recepcionista_model.php
│   │   ├── cita_model.php
│   │   ├── servicio_model.php
│   │   └── reporte_model.php
│   ├── helpers/
│   │   ├── url.php
│   │   ├── view.php
│   │   ├── flash.php
│   │   └── auth.php
│   └── views/
│       ├── home/
│       ├── errors/
│       ├── cita/
│       ├── odontologo/
│       ├── paciente/
│       ├── recepcionista/
│       └── servicio/
│
├── config/
│   ├── app.php                        # Constantes y rutas base
│   ├── database.php                   # Conexión PDO real (ignorada por git)
│   └── database.example.php           # Plantilla de conexión
│
├── database/
│   └── citas_odontologicas            # Script PostgreSQL: tablas, reglas, trigger, vistas, índices y seed
│
├── public/
│   ├── index.php                      # Front controller
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
│
├── routes/
│   └── web.php                        # Mapa de rutas
│
├── manuales/
│   ├── MANUAL_TECNICO.md
│   └── MANUAL_USUARIO.md
│
├── Extras/                            # Documentación SENA, QA y evidencias
│
├── .htaccess                          # Rewrite y bloqueo de directorios internos
├── index.php                          # Redirección a public/index.php
└── README.md
```

---

## 5. Flujo de una petición

Ejemplo: `GET /DENTIX/public/index.php?ruta=citas`

1. Apache recibe la petición y aplica `.htaccess`.
2. `public/index.php` carga `app/bootstrap.php`.
3. `bootstrap.php` carga configuración, conexión, helpers e inicia la sesión.
4. `public/index.php` lee `$_GET['ruta']` (`citas`).
5. Busca la ruta en `routes/web.php` y obtiene `['cita_controller.php', 'cita_index']`.
6. Carga `app/controllers/cita_controller.php` y ejecuta `cita_index()`.
7. `cita_index()` verifica sesión de recepción, valida CSRF si hay POST, consulta modelos y renderiza la vista.
8. `render('cita/lista', $datos)` carga `app/views/cita/lista.php` con los datos extraídos.
9. La vista genera HTML y lo envía al navegador.

---

## 6. Capas y archivos

### 6.1 Punto de entrada

| Archivo | Función |
| :--- | :--- |
| `app/bootstrap.php` | Carga `config/app.php`, `config/database.php` y los cuatro helpers; después inicia la sesión. Es el arranque común de toda la aplicación. |
| `public/index.php` | Front controller. Carga el bootstrap, resuelve la ruta, valida que exista el controlador y ejecuta la función correspondiente. Devuelve 404 si la ruta no existe. |
| `index.php` | Redirecciona a `public/index.php`. Útil cuando se accede a la raíz del proyecto. |
| `.htaccess` | Activa `mod_rewrite`, redirige la raíz a `public/index.php`, bloquea el acceso web a `app/`, `config/`, `database/`, `routes/`, `manuales/` y `Extras/`, y desactiva el listado de directorios. |

### 6.2 Configuración

| Archivo | Función |
| :--- | :--- |
| `config/app.php` | Define `APP_NAME`, `APP_ENV`, `BASE_PATH`, `APP_PATH`, `VIEW_PATH`, `CONFIG_PATH`, `PUBLIC_PATH` y calcula `BASE_URL` a partir de `SCRIPT_NAME`. |
| `config/database.php` | Crea la conexión PDO a PostgreSQL usando variables de entorno (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`) con valores por defecto. Define `db(): PDO`. No se versiona. |
| `config/database.example.php` | Plantilla pública de `config/database.php`. Se copia y se ajustan credenciales. |

### 6.3 Rutas

| Archivo | Función |
| :--- | :--- |
| `routes/web.php` | Devuelve un arreglo `ruta => [archivo_controlador, función]`. Es el único lugar donde se declaran las rutas públicas. |

Para agregar una ruta:

1. Crear la función en el controlador correspondiente.
2. Agregar la entrada en `routes/web.php`.
3. Crear o reutilizar una vista en `app/views/`.
4. Generar enlaces con `url('nombre-ruta')`.

### 6.4 Helpers

#### `app/helpers/url.php`

| Función | Qué hace |
| :--- | :--- |
| `base_url(string $path = '')` | Construye una URL absoluta relativa a `BASE_URL`. |
| `url(string $ruta = 'inicio', array $parametros = [])` | Genera `BASE_URL/index.php?ruta=...` con parámetros adicionales. |
| `asset(string $path)` | Genera la URL de un recurso en `public/assets/`. |
| `redirect(string $ruta = 'inicio', array $parametros = [])` | Redirige a una ruta interna y termina la ejecución. |
| `redirect_to(string $destino)` | Redirige a una URL ya construida. |

#### `app/helpers/view.php`

| Función | Qué hace |
| :--- | :--- |
| `e(mixed $valor)` | Escapa HTML con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. |
| `render(string $vista, array $datos = [])` | Carga una vista desde `app/views/` y extrae los datos para usarlos como variables. |
| `view_path(string $vista)` | Devuelve la ruta física de una vista. |

#### `app/helpers/flash.php`

| Función | Qué hace |
| :--- | :--- |
| `flash_set(string $mensaje, string $tipo = 'exito')` | Guarda un mensaje flash en sesión. |
| `flash_get()` | Devuelve y elimina el mensaje flash (`[mensaje, tipo]`). |
| `error_set(string $mensaje)` | Guarda un error en sesión. |
| `error_get()` | Devuelve y elimina el error. |
| `paciente_flash_set(string $mensaje)` | Guarda un mensaje flash específico del portal del paciente. |
| `paciente_flash_get()` | Devuelve y elimina el mensaje flash del paciente. |

#### `app/helpers/auth.php`

| Función | Qué hace |
| :--- | :--- |
| `iniciar_sesion()` | Configura cookies de sesión (`HttpOnly`, `SameSite=Lax`, `Secure` si hay HTTPS) e inicia la sesión. |
| `iniciar_sesion_odontologo()` / `iniciar_sesion_paciente()` | Alias de compatibilidad para iniciar sesión. |
| `redirigir_login(string $destino)` | Redirige a una ruta de login o a una URL completa. |
| `recepcionista_autenticado()` | Verifica si existe `$_SESSION['recepcionista_id']`. |
| `odontologo_autenticado()` | Verifica si existe `$_SESSION['odontologo_id']`. |
| `paciente_autenticado()` | Verifica si existe `$_SESSION['paciente_id']`. |
| `requerir_recepcionista()` | Exige sesión de recepción y genera el token CSRF de recepción. |
| `requerir_odontologo()` | Exige sesión de odontólogo y genera el token CSRF de odontólogo. |
| `requerir_paciente()` | Exige sesión de paciente. |
| `token_csrf_recepcionista()` | Genera o devuelve el token CSRF de recepción. |
| `validar_csrf_recepcionista()` | Valida el token CSRF de recepción. |
| `token_csrf_odontologo()` | Genera o devuelve el token CSRF de odontólogo. |
| `validar_csrf_odontologo()` | Valida el token CSRF de odontólogo. |
| `token_csrf_paciente()` | Genera o devuelve el token CSRF de paciente. |
| `validar_csrf_paciente()` | Valida el token CSRF de paciente. |
| `cerrar_sesion_actual()` | Vacía `$_SESSION`, elimina la cookie de sesión y destruye la sesión. |

### 6.5 Controladores

#### `app/controllers/home_controller.php`

| Función | Propósito |
| :--- | :--- |
| `home_index()` | Renderiza la página principal (`home/index`). |

#### `app/controllers/paciente_controller.php`

| Función | Propósito |
| :--- | :--- |
| `paciente_catalogo_alergias()` | Devuelve el catálogo de alergias disponibles en el formulario de registro. |
| `paciente_login()` | Autentica al paciente por cédula y contraseña, regenera la sesión y redirige al portal o a `next`. |
| `paciente_registro()` | Registra un paciente nuevo o activa una cuenta existente sin contraseña. Valida nombre, cédula, teléfono, correo, fecha de nacimiento, alergias, contraseña y consentimiento. En modo administrador redirige a `pacientes`; en modo público inicia sesión y redirige al perfil. |
| `paciente_portal()` | Muestra el portal de solicitud de citas y procesa nuevas solicitudes: valida CSRF, servicio, fecha, hora, una cita por día y separación mínima de 4 días. |
| `paciente_perfil()` | Carga los datos del paciente y sus citas, y renderiza el perfil. |
| `paciente_agendar()` | Ruta de conveniencia que redirige al portal de agendamiento. |
| `paciente_logout()` | Cierra la sesión del paciente mediante POST + CSRF. |
| `paciente_index()` | Lista pacientes para recepción. |
| `paciente_editar()` | Carga un paciente por ID para edición desde recepción. |
| `paciente_procesar()` | Procesa acciones de recepción: `actualizar` (valida datos y actualiza) y `registrar` (alta rápida). |

#### `app/controllers/odontologo_controller.php`

| Función | Propósito |
| :--- | :--- |
| `odontologo_login()` | Autentica al odontólogo por correo y contraseña, regenera la sesión y redirige al panel. |
| `odontologo_datos_sesion()` | Devuelve los datos del odontólogo autenticado para las vistas. |
| `odontologo_filtrar_historial()` | Filtra citas finalizadas por paciente, cédula, servicio o procedimiento. |
| `odontologo_panel()` | Muestra la agenda del odontólogo y procesa la finalización de citas: valida CSRF, datos clínicos y llama a `odontologo_finalizar_cita()`. |
| `odontologo_historial()` | Muestra el historial de citas finalizadas con buscador. |
| `odontologo_logout()` | Cierra la sesión del odontólogo mediante POST + CSRF. |
| `odontologo_index()` | Lista odontólogos para recepción, incluyendo estado y citas asociadas. |
| `odontologo_crear()` | Muestra el formulario de alta de odontólogo. |
| `odontologo_editar()` | Carga un odontólogo por ID para edición. |
| `odontologo_procesar()` | Procesa acciones de recepción: `registrar`, `actualizar` y `eliminar`. Valida slug, correo y datos obligatorios. |

#### `app/controllers/recepcionista_controller.php`

| Función | Propósito |
| :--- | :--- |
| `recepcionista_login()` | Autentica a recepción por correo y contraseña, regenera la sesión y redirige a `citas`. |
| `recepcionista_registro()` | Crea nuevas cuentas de recepción. Requiere sesión de recepción y valida correo único. |
| `recepcionista_logout()` | Cierra la sesión de recepción mediante POST + CSRF. |

#### `app/controllers/cita_controller.php`

| Función | Propósito |
| :--- | :--- |
| `cita_index()` | Lista citas pendientes y confirmadas. Procesa la confirmación de una cita pendiente y la asignación de un odontólogo activo. |
| `cita_crear()` | Carga pacientes, odontólogos activos y servicios, y muestra el formulario de nueva cita. |
| `cita_editar()` | Carga una cita por ID junto con pacientes, odontólogos y servicios para su edición. |
| `cita_procesar()` | Procesa acciones de recepción: `cancelar` (exige motivo), `actualizar` (valida estado, odontólogo, fecha, hora y motivo de cancelación) y `registrar` (alta de cita). Traduce errores de PostgreSQL a mensajes claros. |

#### `app/controllers/servicio_controller.php`

| Función | Propósito |
| :--- | :--- |
| `servicio_validar_datos()` | Valida nombre, descripción, duración mínima de 15 minutos y precio no negativo. |
| `servicio_index()` | Lista servicios para recepción. |
| `servicio_crear()` | Muestra el formulario de nuevo servicio. |
| `servicio_editar()` | Carga un servicio por ID para edición. |
| `servicio_procesar()` | Procesa acciones de recepción: `registrar`, `actualizar` y `eliminar`. Maneja nombres duplicados y restricciones de llave foránea. |
| `servicio_icono_tratamiento()` | Asigna un icono de Font Awesome según palabras clave del nombre del servicio. |
| `servicio_catalogo()` | Renderiza el catálogo público de tratamientos. |

#### `app/controllers/reporte_controller.php`

| Función | Propósito |
| :--- | :--- |
| `reporte_index()` | Valida el rango de fechas, obtiene los datos del reporte, calcula líderes y máximos para las barras, y renderiza la vista de reportes. |

### 6.6 Modelos

#### `app/models/paciente_model.php`

| Función | Propósito |
| :--- | :--- |
| `paciente_buscar_por_id()` | Busca un paciente por ID. |
| `paciente_buscar_por_cedula()` | Busca un paciente por cédula. |
| `paciente_listar()` | Lista pacientes con sus datos principales. |
| `paciente_crear()` | Inserta un paciente y devuelve el ID generado. |
| `paciente_actualizar()` | Actualiza datos personales, fecha de nacimiento y alergias. No modifica la contraseña. |
| `paciente_activar_cuenta()` | Activa una cuenta existente solo si `password_hash` es `NULL`, asignando contraseña, fecha de nacimiento y alergias. |
| `paciente_cedula_duplicada()` | Verifica si una cédula ya existe, excluyendo opcionalmente un ID. |
| `paciente_contar()` | Devuelve el total de pacientes. |
| `paciente_citas()` | Devuelve las citas del paciente con odontólogo, servicio y detalle clínico. |

#### `app/models/odontologo_model.php`

| Función | Propósito |
| :--- | :--- |
| `odontologo_generar_slug()` | Genera un slug público a partir del nombre. |
| `odontologo_buscar_por_id()` | Busca un odontólogo por ID. |
| `odontologo_buscar_por_email()` | Busca un odontólogo por correo. |
| `odontologo_listar_activos()` | Lista odontólogos activos. |
| `odontologo_listar_con_citas()` | Lista odontólogos con conteo de citas y estado. |
| `odontologo_slug_existe()` | Verifica si un slug ya existe, excluyendo opcionalmente un ID. |
| `odontologo_email_existe()` | Verifica si un correo ya existe, excluyendo opcionalmente un ID. |
| `odontologo_guardar()` | Inserta un odontólogo con estado activo. |
| `odontologo_actualizar()` | Actualiza datos, slug, correo y estado activo/inactivo. |
| `odontologo_eliminar()` | Elimina físicamente un odontólogo. Si tiene citas, la llave foránea lo impide. |
| `odontologo_actualizar_ultimo_acceso()` | Actualiza la fecha de último acceso. |
| `odontologo_citas_activas()` | Devuelve las citas confirmadas del odontólogo. |
| `odontologo_citas_finalizadas()` | Devuelve las citas completadas del odontólogo con detalle clínico. |
| `odontologo_finalizar_cita()` | Transacción atómica: marca la cita como `completada` e inserta su registro en `detalle_cita`. Si algo falla, hace rollback. |

#### `app/models/recepcionista_model.php`

| Función | Propósito |
| :--- | :--- |
| `recepcionista_buscar_por_email()` | Busca una cuenta de recepción activa por correo. |
| `recepcionista_email_existe()` | Verifica si un correo ya está registrado. |
| `recepcionista_crear()` | Inserta una nueva cuenta de recepción con contraseña hasheada. |

#### `app/models/cita_model.php`

| Función | Propósito |
| :--- | :--- |
| `cita_traducir_error()` | Convierte errores de PostgreSQL en mensajes legibles para el usuario. |
| `cita_buscar_por_id()` | Busca una cita por ID con datos relacionados. |
| `cita_listar_pendientes()` | Lista citas pendientes con paciente, servicio y recepción. |
| `cita_listar_confirmadas()` | Lista citas confirmadas con odontólogo, paciente y servicio. |
| `cita_guardar()` | Inserta una cita. |
| `cita_actualizar()` | Actualiza paciente, odontólogo, servicio, fecha, hora, estado, motivo y motivo de cancelación. |
| `cita_cancelar()` | Cambia el estado a `cancelada`, registra el motivo y la recepcionista que canceló. |
| `cita_confirmar()` | Asigna odontólogo, cambia el estado a `confirmada` y registra la recepcionista. |
| `cita_odontologo_tiene_conflicto()` | Verifica si un odontólogo ya tiene cita en una fecha. |
| `cita_cercana_paciente()` | Busca una cita cercana del paciente para validar la separación mínima de días. |

#### `app/models/servicio_model.php`

| Función | Propósito |
| :--- | :--- |
| `servicio_listar()` | Lista todos los servicios. |
| `servicio_listar_ordenado()` | Lista servicios ordenados por nombre. |
| `servicio_listar_catalogo()` | Lista servicios para el catálogo público. |
| `servicio_buscar_por_id()` | Busca un servicio por ID. |
| `servicio_existe()` | Verifica si un servicio existe. |
| `servicio_nombre_existe()` | Verifica si un nombre de servicio ya existe. |
| `servicio_guardar()` | Inserta un servicio. |
| `servicio_actualizar()` | Actualiza un servicio. |
| `servicio_eliminar()` | Elimina un servicio. Si está asociado a citas, la llave foránea lo impide. |

#### `app/models/reporte_model.php`

| Función | Propósito |
| :--- | :--- |
| `reporte_obtener_datos()` | Devuelve el resumen de citas, ingresos estimados, totales de pacientes/odontólogos, rankings de pacientes, servicios y odontólogos, y horarios pico. |

### 6.7 Vistas

Todas las vistas están en `app/views/` y se cargan con `render()`.

#### Público y errores

| Archivo | Función |
| :--- | :--- |
| `app/views/home/index.php` | Página principal, manual interactivo y pie de página. |
| `app/views/errors/404.php` | Página de error para rutas no encontradas. |
| `app/views/servicio/catalogo.php` | Catálogo público de tratamientos. |

#### Paciente

| Archivo | Función |
| :--- | :--- |
| `app/views/paciente/login.php` | Inicio de sesión del paciente por cédula. |
| `app/views/paciente/form.php` | Registro público y registro administrativo de pacientes. |
| `app/views/paciente/portal.php` | Solicitud de citas por parte del paciente. |
| `app/views/paciente/perfil.php` | Perfil, datos personales e historial de citas del paciente. |
| `app/views/paciente/lista.php` | Listado administrativo de pacientes. |
| `app/views/paciente/editar.php` | Edición de un paciente desde recepción. |

#### Odontólogo

| Archivo | Función |
| :--- | :--- |
| `app/views/odontologo/login.php` | Inicio de sesión del odontólogo. |
| `app/views/odontologo/panel.php` | Agenda del odontólogo y finalización de citas. |
| `app/views/odontologo/historial.php` | Historial de citas finalizadas con buscador. |
| `app/views/odontologo/lista.php` | Listado administrativo de odontólogos. |
| `app/views/odontologo/form.php` | Alta de odontólogo. |
| `app/views/odontologo/editar.php` | Edición de odontólogo y estado de cuenta. |

#### Recepción

| Archivo | Función |
| :--- | :--- |
| `app/views/recepcionista/login.php` | Inicio de sesión de recepción. |
| `app/views/recepcionista/form.php` | Alta de nuevas cuentas de recepción. |
| `app/views/recepcionista/reportes.php` | Reportes, KPIs, rankings y filtros por fecha. |
| `app/views/recepcionista/boton_cerrar_sesion.php` | Fragmento reutilizable con el botón de cierre de sesión. |

#### Citas

| Archivo | Función |
| :--- | :--- |
| `app/views/cita/lista.php` | Citas pendientes, confirmadas, asignación de odontólogo y cancelación. |
| `app/views/cita/form.php` | Creación manual de una cita desde recepción. |
| `app/views/cita/editar.php` | Edición y reprogramación de una cita. |

#### Servicios

| Archivo | Función |
| :--- | :--- |
| `app/views/servicio/lista.php` | Gestión administrativa de servicios. |
| `app/views/servicio/form.php` | Alta de servicio con vista previa de precio. |
| `app/views/servicio/editar.php` | Edición de servicio con vista previa de precio. |

### 6.8 Assets

| Archivo | Función |
| :--- | :--- |
| `public/assets/css/index.css` | Estilos de la página principal, manual y footer. |
| `public/assets/css/admin.css` | Estilos de paneles administrativos, odontólogo y reportes. |
| `public/assets/css/paciente.css` | Estilos del portal y perfil del paciente. |
| `public/assets/js/dentix-i18n.js` | Motor de internacionalización ES/EN y conversión COP/USD. |
| `public/assets/js/index-animations.js` | Animaciones y comportamiento de la página principal. |
| `public/assets/js/admin-animations.js` | Interacciones de los paneles administrativos. |
| `public/assets/js/animaciones.js` | Utilidades visuales complementarias. |
| `public/assets/img/proyecto-placeholder.svg` | Recurso gráfico institucional. |

---

## 7. Rutas y permisos

Todas las rutas se invocan como:

```text
BASE_URL/index.php?ruta=nombre-de-la-ruta
```

### Públicas

| Ruta | Método | Acceso | Función |
| :--- | :--- | :--- | :--- |
| `inicio` | GET | Público | Página principal. |
| `tratamientos` | GET | Público | Catálogo público de tratamientos. |
| `login-paciente` | GET/POST | Público | Inicio de sesión de paciente. |
| `registro-paciente` | GET/POST | Público / Recepción | Registro de paciente. Con `desde=admin` requiere recepción. |
| `login-odontologo` | GET/POST | Público | Inicio de sesión de odontólogo. |
| `login-recepcionista` | GET/POST | Público | Inicio de sesión de recepción. |

### Paciente

| Ruta | Método | Acceso | Función |
| :--- | :--- | :--- | :--- |
| `portal-paciente` | GET/POST | Paciente | Solicitar una cita. |
| `perfil-paciente` | GET | Paciente | Ver perfil e historial. |
| `agendar-cita` | GET | Paciente | Redirige al portal de citas. |
| `logout-paciente` | POST | Paciente | Cerrar sesión con CSRF. |

### Recepción / Administrador

| Ruta | Método | Acceso | Función |
| :--- | :--- | :--- | :--- |
| `citas` | GET/POST | Recepción | Listar citas y confirmar asignando odontólogo. |
| `citas/nueva` | GET | Recepción | Formulario de nueva cita. |
| `citas/editar` | GET | Recepción | Editar cita por `id`. |
| `citas/procesar` | POST | Recepción | Registrar, actualizar o cancelar cita. |
| `pacientes` | GET | Recepción | Listar pacientes. |
| `pacientes/editar` | GET | Recepción | Editar paciente por `id`. |
| `pacientes/procesar` | POST | Recepción | Actualizar o registrar paciente. |
| `odontologos` | GET | Recepción | Listar odontólogos. |
| `odontologos/nuevo` | GET | Recepción | Formulario de alta de odontólogo. |
| `odontologos/editar` | GET | Recepción | Editar odontólogo por `id`. |
| `odontologos/procesar` | POST | Recepción | Registrar, actualizar o eliminar odontólogo. |
| `servicios` | GET | Recepción | Listar servicios. |
| `servicios/nuevo` | GET | Recepción | Formulario de nuevo servicio. |
| `servicios/editar` | GET | Recepción | Editar servicio por `id`. |
| `servicios/procesar` | POST | Recepción | Registrar, actualizar o eliminar servicio. |
| `reportes` | GET | Recepción | Reportes con filtros `desde` y `hasta`. |
| `registro-recepcionista` | GET/POST | Recepción | Crear nueva cuenta de recepción. |
| `logout-recepcionista` | POST | Recepción | Cerrar sesión con CSRF. |

### Odontólogo

| Ruta | Método | Acceso | Función |
| :--- | :--- | :--- | :--- |
| `panel-odontologo` | GET/POST | Odontólogo | Agenda y finalización de citas. |
| `historial-odontologo` | GET | Odontólogo | Historial de citas finalizadas. |
| `logout-odontologo` | POST | Odontólogo | Cerrar sesión con CSRF. |

---

## 8. Roles, sesiones y CSRF

### Variables de sesión

| Rol | Variables principales |
| :--- | :--- |
| Recepción | `recepcionista_id`, `recepcionista_nombre`, `recepcionista_correo` |
| Odontólogo | `odontologo_id`, `odontologo_nombre`, `odontologo_especialidad`, `odontologo_consultorio` |
| Paciente | `paciente_id`, `paciente_nombre` |

### Guards

- `requerir_recepcionista()` protege las rutas administrativas.
- `requerir_odontologo()` protege el panel y el historial clínico.
- `requerir_paciente()` protege el portal y el perfil del paciente.

### CSRF

- Cada rol tiene su propio token: `csrf_recepcionista`, `csrf_odontologo`, `csrf_paciente`.
- Los formularios de escritura incluyen `csrf_token`.
- Los cierres de sesión se hacen por POST y validan CSRF.
- La validación usa `hash_equals()` para comparación segura.

> Nota: el inicio de sesión de recepción no exige CSRF actualmente; es el único formulario de autenticación sin esa validación.

---

## 9. Base de datos

El script completo está en `database/citas_odontologicas`. Crea el esquema desde cero y carga datos de prueba.

### 9.1 Tablas

| Tabla | Función |
| :--- | :--- |
| `paciente` | Datos personales, cédula única, contraseña, fecha de nacimiento y alergias. |
| `recepcionista` | Cuentas de recepción con correo único, contraseña, estado activo y último acceso. |
| `odontologo` | Datos del odontólogo, especialidad, consultorio, slug público, correo único, contraseña y estado activo. |
| `servicio` | Nombre único, duración mínima, precio y descripción. |
| `cita` | Relación paciente-servicio-odontólogo, fecha, hora, estado, motivo y motivo de cancelación. |
| `detalle_cita` | Cierre clínico de una cita completada: detalle, procedimiento, nota clínica, fecha y hora de salida. |

Estados posibles de una cita (`estado_cita`):

- `pendiente`: solicitud creada por el paciente o por recepción sin odontólogo asignado.
- `confirmada`: cita con odontólogo asignado.
- `completada`: cita finalizada por el odontólogo; genera `detalle_cita`.
- `cancelada`: cita cancelada con motivo obligatorio.
- `no_show`: estado previsto para paciente que no asistió.

### 9.2 Reglas de negocio implementadas en la base de datos

| Regla | Implementación |
| :--- | :--- |
| Horario de atención | `ck_cita_horario`: entre 08:00 y 19:30, minutos 00 o 30, segundos 0. |
| Sin choques de agenda | `uq_agenda`: único por `odontologo_id`, `fecha`, `hora`. |
| Cancelación con motivo | `ck_cita_cancelacion`: si el estado es `cancelada`, el motivo es obligatorio. |
| Un detalle por cita | `uq_detalle_cita`: único por `cita_id`. |
| Nombre de paciente en formato propio | `ck_paciente_nombre_formato`: cada palabra inicia en mayúscula. |
| Fecha de nacimiento no futura | `ck_paciente_fecha_nac`. |
| Duración y precio de servicio | `ck_servicio_duracion` (mínimo 15 minutos) y `ck_servicio_precio` (no negativo). |
| Espaciado de citas por paciente | Trigger `trg_validar_espaciado_citas` y función `fn_validar_espaciado_citas_paciente()`: una cita por día y mínimo 4 días completos entre citas. |
| Máximo una cita por odontólogo al día | Trigger `trg_validar_espaciado_citas`. |

### 9.3 Vistas

| Vista | Función |
| :--- | :--- |
| `v_agenda_dia` | Agenda diaria con paciente, odontólogo, servicio, consultorio, recepcionista y estado. |
| `v_historial_paciente` | Historial clínico del paciente con cita, odontólogo, servicio y detalle clínico. |

### 9.4 Datos de prueba

Contraseña general para todos los usuarios de prueba: `12141214`.

| Rol | Usuarios |
| :--- | :--- |
| Recepción | `jose.recepcion@clinica.com`, `maria.recepcion@clinica.com`, `sofia.recepcion@clinica.com` |
| Odontólogos | `ana.torres@clinica.com`, `luis.mendoza@clinica.com`, `sofia.rios@clinica.com`, `carlos.vega@clinica.com`, `valentina.ortiz@clinica.com` |
| Pacientes | Cédulas `1001112233`, `1004445566`, `1076907716`, `1005556677`, `1007778899` |

---

## 10. Instalación y configuración

### Requisitos

- PHP 8.1 o superior con las extensiones `pdo_pgsql`, `pgsql` y `mbstring`.
- PostgreSQL 12 o superior.
- Apache con `mod_rewrite` habilitado (XAMPP/LAMPP).
- Permiso `AllowOverride All` en la configuración de Apache para que se aplique `.htaccess`.

### Pasos

1. **Ubicar el proyecto**

   ```text
   /opt/lampp/htdocs/DENTIX
   ```

2. **Crear la base de datos**

   ```bash
   createdb -h 127.0.0.1 -p 5432 -U postgres citas_odontologicas
   ```

3. **Cargar el esquema y los datos de prueba**

   ```bash
   psql -h 127.0.0.1 -p 5432 -U postgres -d citas_odontologicas \
     -f /opt/lampp/htdocs/DENTIX/database/citas_odontologicas
   ```

4. **Configurar la conexión**

   ```bash
   cp config/database.example.php config/database.php
   ```

   Editar `config/database.php`:

   ```php
   $host = '127.0.0.1';
   $port = '5432';
   $dbname = 'citas_odontologicas';
   $user = 'postgres';
   $password = 'tu_contraseña';
   ```

   También se pueden usar variables de entorno:

   ```bash
   export DB_HOST=127.0.0.1
   export DB_PORT=5432
   export DB_NAME=citas_odontologicas
   export DB_USER=postgres
   export DB_PASSWORD=tu_contraseña
   ```

5. **Iniciar servicios**

   ```bash
   sudo systemctl start postgresql
   sudo /opt/lampp/lampp startapache
   ```

6. **Abrir la aplicación**

   ```text
   http://localhost:8080/DENTIX/
   ```

   También se puede acceder directamente al front controller:

   ```text
   http://localhost:8080/DENTIX/public/index.php?ruta=inicio
   ```

---

## 11. Pruebas y validación

### Verificación de sintaxis PHP

```bash
find app config public routes -name '*.php' -print0 \
  | xargs -0 -n1 /opt/lampp/bin/php -l
```

### Prueba rápida con el servidor integrado de PHP

```bash
/opt/lampp/bin/php -S 127.0.0.1:8099 -t public
```

Rutas mínimas para verificar:

- `http://127.0.0.1:8099/index.php?ruta=inicio`
- `http://127.0.0.1:8099/index.php?ruta=tratamientos`
- `http://127.0.0.1:8099/index.php?ruta=login-paciente`
- `http://127.0.0.1:8099/index.php?ruta=login-odontologo`
- `http://127.0.0.1:8099/index.php?ruta=login-recepcionista`
- `http://127.0.0.1:8099/index.php?ruta=no-existe` (debe devolver 404)

### Flujos validados

- Inicio, catálogo, 404 y assets.
- Login y logout de paciente, odontólogo y recepción.
- Registro de paciente público y administrativo.
- Creación y edición de pacientes, odontólogos, servicios y citas.
- Confirmación de cita con asignación de odontólogo.
- Finalización de cita con creación de `detalle_cita`.
- Cancelación de cita con motivo.
- Reportes de recepción.
- Validación CSRF en formularios de escritura y logout.

---

## 12. Seguridad

Medidas implementadas:

- **PDO con consultas preparadas** en todos los modelos.
- **`password_hash()` y `password_verify()`** para contraseñas.
- **Regeneración de ID de sesión** al iniciar sesión.
- **Cookies de sesión** con `HttpOnly`, `SameSite=Lax` y `Secure` cuando hay HTTPS.
- **Tokens CSRF por rol** en formularios de escritura y logout.
- **Escapado de salida** con `e()` en las vistas.
- **Bloqueo de directorios internos** desde `.htaccess` (`app/`, `config/`, `database/`, `routes/`, `manuales/`, `Extras/`).
- **Listado de directorios desactivado** con `Options -Indexes`.
- **Mensajes de error controlados** y registro de excepciones con `error_log()`.

Recomendaciones para producción:

- Servir el sitio con HTTPS.
- Configurar un usuario de PostgreSQL con permisos mínimos.
- Mover `config/database.php` fuera del directorio público o usar variables de entorno.
- Agregar limitación de intentos de inicio de sesión.
- Validar CSRF también en el login de recepción.
- Programar copias de seguridad periódicas de la base de datos.

---

## 13. Convenciones de desarrollo

- **PHP procedural:** no se usan clases propias; solo funciones y PDO.
- **Nombres de funciones:** `modulo_accion` (`paciente_crear`, `cita_listar_pendientes`, `servicio_actualizar`).
- **Controladores:** reciben la petición, validan, llaman al modelo y renderizan.
- **Modelos:** solo acceso a datos y reglas de persistencia.
- **Vistas:** solo presentación; no deben contener consultas SQL ni lógica de negocio.
- **Rutas:** se declaran únicamente en `routes/web.php`.
- **URLs:** se generan con `url()` y los assets con `asset()`.
- **Salida HTML:** se escapa con `e()`.
- **Mensajes:** se usan helpers flash para éxito/error.
- **Base de datos:** los cambios de esquema se hacen en `database/citas_odontologicas`.

---

## 14. Documentación adicional

- **Manual técnico:** [`manuales/MANUAL_TECNICO.md`](manuales/MANUAL_TECNICO.md)
- **Manual de usuario:** [`manuales/MANUAL_USUARIO.md`](manuales/MANUAL_USUARIO.md)
- **Especificación de requisitos:** [`Extras/SRS_V 4.0.docx`](Extras/SRS_V%204.0.docx)
- **Plan de aseguramiento de calidad:** [`Extras/PAC_v2.3.docx`](Extras/PAC_v2.3.docx)
- **Log de errores y sugerencias:** [`Extras/LOG_ERRORES_Y_SUGERENCIAS.md`](Extras/LOG_ERRORES_Y_SUGERENCIAS.md)
- **Diagrama entidad-relación:** [`Extras/MER.png`](Extras/MER.png)

---

## 15. Licenciamiento y créditos

**Modelo de licenciamiento:** software **privativo** con cesión íntegra del código fuente para uso exclusivo de la clínica adquiriente. Las modificaciones están sujetas a autorización previa escrita de los autores. Incluye **6 meses de garantía y soporte técnico**, administración de infraestructura en servidor/nube y copias de seguridad periódicas automatizadas. Paquete llave en mano valorado en **$ 11.200.000 COP / $ 2.800 USD** según el SRS y el PAC.

Proyecto desarrollado para el programa **Tecnólogo en Análisis y Desarrollo de Software (ADSO — Ficha 3407799)** — **CIES SENA Neiva, Huila**.
