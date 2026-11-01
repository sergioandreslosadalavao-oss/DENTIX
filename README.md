# DENTIX

Sistema web para la gestión de pacientes, odontólogos, servicios y citas de un consultorio odontológico. El proyecto también aparece como SCO (Sistema de Citas Odontológicas) en algunos nombres y comentarios heredados.

## Funcionalidades

- **Pacientes:** registro **público**, listado y edición, con datos de contacto y cédula en las vistas.
- **Citas:** portal de solicitud **público** para pacientes y panel de recepción para revisar solicitudes, asignar odontólogo y confirmar citas pendientes.
- **Odontólogos:** listado con estado, cargo y disponibilidad; alta, edición y deshabilitación (baja lógica, sin borrar historial) de asociados **solo por el odontólogo líder** (RN-07). El líder no puede deshabilitarse a sí mismo (RN-08) y solo puede existir **un líder activo a la vez**, garantizado por un índice único en la base de datos.
- **Servicios:** creación, edición, listado y eliminación del catálogo con duración y precio.
- **Acceso por rol:** inicio de sesión para recepción y odontólogo (`app/sesion.php`), con sesión por rol; las operaciones administrativas exigen sesión y **revalidan cargo y cuenta activa contra la base de datos** en cada acción (la sesión envejecida no es fuente de verdad). El login de odontólogos asociados queda denegado hasta que exista su módulo clínico.
- **Interfaz:** página de inicio y paneles adaptables, con estilos propios y recursos visuales cargados localmente y desde CDN.

La implementación clínica para que los odontólogos asociados atiendan citas no está disponible todavía. Por ahora, el acceso de odontólogo está limitado al líder y a la gestión del cuerpo médico.

## Tecnologías

- PHP con PDO y `pdo_pgsql`.
- PostgreSQL.
- Apache (el proyecto puede ejecutarse en XAMPP/LAMPP).
- HTML, CSS y JavaScript; Bootstrap 5.3.2, Font Awesome, GSAP y fuentes Manrope/Sora se cargan desde CDN en las vistas.

No hay manifiesto de Composer o npm en el repositorio. Las versiones mínimas de PHP y PostgreSQL no están declaradas como configuración del proyecto.

## Estructura y funcionalidad

```text
DENTIX/
├── app/                 # Procesamiento de citas, pacientes, odontólogos,
│                        # recepción, servicios y guardas de sesión
├── assests/             # CSS, JavaScript e imágenes (nombre original del directorio)
├── config/              # Conexión PDO a PostgreSQL (lee las credenciales de `.env`)
├── database/            # Esquema y scripts de preparación/migración SQL
├── error/               # Notas y material de diagnóstico
├── odd/tasks/           # Tareas y documentación de trabajo
├── view/                # Vistas agrupadas por módulo
│   ├── cita/
│   ├── odontologo/
│   ├── paciente/
│   ├── recepcionista/
│   └── servicio/
├── .atl/                # Registro de skills de los asistentes de desarrollo (tooling)
├── .env                 # Credenciales de la base de datos (no se commitea)
├── .gitignore           # Excluye `.env` del repositorio
├── index.php            # Página de inicio y accesos a módulos
└── README.md            # Esta documentación
```

### Raíz del proyecto

| Archivo | Funcionalidad |
|---|---|
| `index.php` | Página de inicio con accesos a los módulos (paciente, odontólogo, servicio, cita) y botones de acceso rápido. |
| `README.md` | Esta documentación. |
| `.env` | Credenciales de conexión (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`). Excluido de git: nunca se sube al repositorio. |
| `.gitignore` | Excluye únicamente `.env`. |

### `app/` — lógica de negocio

| Archivo | Funcionalidad |
|---|---|
| `sesion.php` | Guardas de sesión. Revalida contra la base de datos, en cada acción protegida, que el recepcionista esté activo y que el odontólogo sea líder activo: la sesión envejecida no es fuente de verdad. |
| `logica_paciente.php` | Alta de pacientes desde recepción (campos `telefono`, `email`, `fecha_nac`, `alergias`). |
| `logica_odontologo.php` | Login de odontólogos con bcrypt y CRUD de odontólogos con guarda de líder (alta, edición y deshabilitación con baja lógica `activo = FALSE`). El login de asociados queda denegado hasta que exista el módulo clínico. |
| `logica_cita.php` | Creación de citas; requiere sesión de recepción. |
| `logica_recepcionista.php` | Login de recepción (exige `activo = TRUE`) y creación de cuentas de recepción con sesión iniciada. |
| `logica_servicio.php` | CRUD del catálogo de servicios (registrar, actualizar, eliminar) con guarda de recepción. |

### `config/`

| Archivo | Funcionalidad |
|---|---|
| `database.php` | Lee las claves `DB_*` de `.env`, abre la conexión PDO con `pdo_pgsql` y termina el proceso si la conexión falla. |

### `database/` — scripts SQL

| Archivo | Funcionalidad |
|---|---|
| `sco.sql` | Esquema completo + datos de prueba para una instalación descartable. **Elimina objetos existentes** (`DROP ... CASCADE`). |
| `sco_patch.sql` | **Script recomendado en todos los casos.** Prepara una base vacía o actualiza una existente sin borrar datos; es idempotente. |

El detalle y las advertencias de ambos están en [Archivos de base de datos principales](#archivos-de-base-de-datos-principales).

### `view/` — vistas por módulo

Cada subcarpeta es un módulo. Las vistas usan Bootstrap 5.3, Font Awesome, GSAP y las fuentes Manrope/Sora, cargados desde CDN.

#### `view/paciente/`

| Archivo | Funcionalidad |
|---|---|
| `form_paciente.php` | Registro público de pacientes. |
| `portal_paciente.php` | Portal público: valida la cédula para consultar y agendar. |
| `agendar_cita.php` | Formulario público de solicitud de cita, identificado por cédula. |
| `lista_pacientes.php` | Listado de pacientes (recepción). |
| `editar_paciente.php` | Edición de datos del paciente (recepción). |

#### `view/odontologo/`

| Archivo | Funcionalidad |
|---|---|
| `login_odontologo.php` | Pantalla de inicio de sesión de odontólogos. |
| `lista_odontologos.php` | Listado con estado, cargo y disponibilidad; alta, edición y deshabilitación solo para el líder (RN-07). |
| `form_odontologo.php` | Alta de odontólogo (nombre, especialidad, consultorio, email, contraseña). |
| `editar_odontologo.php` | Edición de odontólogo; la guarda de líder también aplica al acceder por URL directa. |
| `cerrar_sesion_odontologo.php` | Destruye la sesión de odontólogo. |

#### `view/recepcionista/`

| Archivo | Funcionalidad |
|---|---|
| `login_recepcionista.php` | Pantalla de inicio de sesión de recepción. |
| `form_recepcionista.php` | Creación de cuentas de recepción (requiere sesión iniciada). |
| `lista_recepcionista.php` | Stub: solo redirige al login (pendiente de implementar). |
| `editar_recepcionista.php` | Formulario de edición, prácticamente placeholder (pendiente). |
| `cerrar_sesion.php` | Destruye la sesión de recepción. |

#### `view/cita/`

| Archivo | Funcionalidad |
|---|---|
| `lista_citas.php` | Agenda de recepción: revisa solicitudes, asigna odontólogo y confirma citas pendientes. |
| `form_cita.php` | Formulario de nueva cita (recepción). |
| `editar_cita.php` | Edición de una cita existente (recepción). |

#### `view/servicio/`

| Archivo | Funcionalidad |
|---|---|
| `lista_servicios.php` | Catálogo de servicios con duración y precio. |
| `form_servicio.php` | Formulario de alta de servicio. |
| `editar_servicio.php` | Edición de servicio (recepción). |

### `assests/` — recursos front-end

| Archivo | Funcionalidad |
|---|---|
| `css/index.css` | Estilos de la página de inicio. |
| `css/admin.css` | Estilos de los paneles administrativos. |
| `js/index-animations.js` | Animaciones GSAP de la página de inicio. |
| `js/admin-animations.js` | Animaciones GSAP de los paneles. |
| `js/animaciones.js` | Animaciones GSAP de las tarjetas de módulo (versión anterior). |
| `img/proyecto-placeholder.svg` | Imagen placeholder del hero. |

### Otras carpetas

| Carpeta o archivo | Funcionalidad |
|---|---|
| `error/test.md` | Checklist de casos de fallo a probar (QA). |
| `odd/tasks/` | Documentos de tareas y especificaciones del desarrollo. |
| `.atl/` | Registro de skills de los asistentes de desarrollo (tooling interno). |

### Archivos de base de datos principales

- `database/sco_patch.sql`: script **recomendado en todos los casos**. Prepara una base vacía (esquema completo + datos de prueba) y también actualiza una base existente de versiones anteriores (agrega el `cargo` de odontólogo y el líder de pruebas) sin eliminar tablas ni datos. Es idempotente: puedes ejecutarlo tantas veces como quieras.
- `database/sco.sql`: esquema completo para una instalación descartable. **Elimina objetos existentes** mediante `DROP ... CASCADE`; no lo ejecutes sobre una base con datos que quieras conservar.

## Preparar el entorno

Se requiere PHP con las extensiones PDO y `pdo_pgsql`, PostgreSQL y un servidor web como Apache.

1. Coloca el proyecto en el directorio público de tu servidor. En LAMPP suele ser `/opt/lampp/htdocs/DENTIX`.
2. Crea una base de datos PostgreSQL, por ejemplo `citas_odontologicas`.
3. Configura el archivo `.env` en la raíz del proyecto con los datos de tu instalación:

   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_NAME=citas_odontologicas
   DB_USER=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

   `config/database.php` lee estas claves y abre la conexión PDO. `DB_NAME` debe coincidir con la base que creaste en el paso 2. No publiques credenciales reales ni uses las credenciales de prueba en producción. `.env` está excluido en `.gitignore`; nunca lo subas al repositorio.

4. Prepara la base de datos. Por ejemplo, con `psql`:

   ```bash
   psql -U tu_usuario -d citas_odontologicas -f database/sco_patch.sql
   ```

   Si usas pgAdmin, selecciona la base creada y ejecuta el contenido de `database/sco_patch.sql`. Para una base que ya contiene datos, haz primero un respaldo y comprueba el esquema; el script no elimina tablas, pero no agrega automáticamente todas las columnas que puedan faltar en tablas preexistentes.

5. Inicia Apache y PostgreSQL, y abre `http://localhost/DENTIX/`.

## Accesos de prueba

El script `sco_patch.sql` incluye datos de demostración. En ese conjunto, la contraseña de prueba documentada es `test1234`:

| Rol | Correo | Comportamiento esperado |
|---|---|---|
| Recepción | `maria.recepcion@clinica.com` | Inicia sesión en el panel de recepción |
| Odontólogo **líder** | `checho.cardenas@clinica.com` | Inicia sesión y gestiona el cuerpo de odontólogos |
| Odontólogo asociado | `ana.torres@clinica.com` | Login **denegado**: el módulo clínico de asociados no existe aún |
| Odontólogo asociado | `luis.mendoza@clinica.com` | Login **denegado** (mismo motivo) |
| Odontólogo inactivo | `sofia.rios@clinica.com` | Login denegado: cuenta deshabilitada |

Estos accesos son únicamente para desarrollo local. Cámbialos o elimina los datos de prueba antes de desplegar el sistema.

## Rutas de acceso

- Inicio: `http://localhost/DENTIX/`
- Inicio de sesión de recepción: `http://localhost/DENTIX/view/recepcionista/login_recepcionista.php`
- Inicio de sesión de odontólogo: `http://localhost/DENTIX/view/odontologo/login_odontologo.php`
- Gestión de odontólogos (solo líder): `http://localhost/DENTIX/view/odontologo/lista_odontologos.php`
- Portal de citas para pacientes: `http://localhost/DENTIX/view/paciente/portal_paciente.php`
- Agenda de recepción: `http://localhost/DENTIX/view/cita/lista_citas.php`

La agenda administrativa y los módulos de gestión requieren autenticación. La página de registro de pacientes es pública.

## Observaciones pendientes

Hay diferencias entre el esquema SQL incluido y las columnas usadas por algunas vistas. Deben corregirse en el esquema y mediante una migración antes de considerar operativos esos flujos:

- Las vistas de paciente, el registro y el portal de citas consultan o escriben `paciente.cedula`; las tablas `paciente` de `sco.sql` y `sco_patch.sql` no definen esa columna. El registro público y la búsqueda por cédula no funcionarán con una base creada únicamente desde esos scripts.
- El listado de odontólogos consulta `odontologo.telefono`, pero la tabla del esquema no define esa columna y el formulario actual de registro de odontólogo tampoco solicita teléfono.
- `logica_paciente.php` usa el esquema de paciente con `telefono`, `email`, `fecha_nac` y `alergias`, distinto del formulario público, que usa cédula y correo. Conviene unificar ambos contratos antes de ampliar ese módulo.
- No existe una pantalla ni una acción para **transferir el cargo de líder**; por ahora el cargo se cambia directamente en la base de datos (recuerda que el índice único exige que solo un líder esté activo a la vez).

La base incluye `detalle_cita` y una vista de historial, pero el árbol actual no contiene un módulo de interfaz clínica para registrar notas o procedimientos.

## Cosas a tener en cuenta (administración de la base de datos)

- **Dónde administrarla:** además de `psql`, puedes ejecutar ambos scripts desde el **Query Tool de pgAdmin** (selecciona la base → Query Tool → abre el archivo → Execute). Es la vía gráfica si no usas la terminal.
- **Reglas que rechazan escrituras** (fallan en la base de datos, no en PHP): solo un odontólogo líder activo a la vez; sin horarios repetidos para el mismo odontólogo/fecha/hora; horas de cita válidas de 08:00 a 19:30 en punto o y media; emails y nombres de servicio únicos.
- **No se borra nada por SQL:** las foráneas de `cita` y `detalle_cita` usan `ON DELETE RESTRICT`; no puedes eliminar un odontólogo o paciente que tenga citas. Por eso la app deshabilita con `activo = FALSE` en vez de borrar filas.
- **Una base vieja no se completa sola:** `sco_patch.sql` no agrega automáticamente todas las columnas que falten en tablas preexistentes (solo las que trae programadas, como `cargo`). Revisa el esquema antes de usarla y haz un respaldo (`pg_dump`) previo.

## Colaboración

Antes de probar cambios, verifica la configuración local de PostgreSQL y PHP, usa una copia de la base de datos y evita subir `.env` con credenciales. No ejecutes `sco.sql` en una base con información que quieras conservar.
