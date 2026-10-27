# DENTIX — Sistema de Citas Odontológicas

## Descripción

DENTIX (Sistema de Citas Odontológicas) es un proyecto web desarrollado para apoyar la gestión de un consultorio odontológico. Su objetivo es facilitar el registro, consulta, actualización y eliminación de información de pacientes y odontólogos mediante una aplicación desarrollada con PHP y PostgreSQL.

El proyecto organiza la conexión a la base de datos, la lógica de procesamiento y las interfaces de usuario en directorios separados, facilitando su mantenimiento y comprensión.

## Tecnologías utilizadas

- **PHP 8.2:** procesamiento de formularios y lógica de la aplicación.
- **PostgreSQL 17:** almacenamiento y administración de los datos.
- **PDO:** conexión entre PHP y PostgreSQL.
- **XAMPP:** entorno local para ejecutar Apache y PHP.
- **HTML5:** estructura de las páginas y formularios.
- **Git y GitHub:** control de versiones y colaboración.

## Estructura del proyecto

```text
DENTIX/
├── app/
│   ├── logica_odontologo.php
│   └── logica_paciente.php
├── assests/
├── config/
│   └── database.php
├── database/
│   └── citas_odontologicas
├── index.php
├── README.md
└── view/
    ├── odontologo/
    │   ├── editar_odontologo.php
    │   ├── form_odontologo.php
    │   └── lista_odontologos.php
    └── paciente/
        ├── editar_paciente.php
        ├── form_paciente.php
        └── lista_pacientes.php
```

**Nota:** el árbol representa los directorios y archivos identificados en la estructura actual. La carpeta `assests/` se conserva con su nombre original. El elemento `database/citas_odontologicas` aparece sin extensión en el listado; verifica su tipo de archivo antes de determinar cómo importarlo.

### `app/` — Lógica del sistema

Contiene los archivos responsables de procesar las operaciones relacionadas con las entidades principales del sistema.

- **`logica_paciente.php`:** centraliza las operaciones y validaciones relacionadas con los pacientes, como registrar, consultar, actualizar y eliminar información, según las funciones implementadas.
- **`logica_odontologo.php`:** centraliza las operaciones relacionadas con los odontólogos, incluyendo el procesamiento de datos y las operaciones de mantenimiento de sus registros.

Separar esta lógica de las vistas evita duplicar consultas y facilita el mantenimiento del código.

### `assests/` — Recursos del proyecto

Directorio destinado a los recursos complementarios de la interfaz, como hojas de estilos, imágenes, iconos o archivos JavaScript, según los elementos que contenga actualmente.

Se conserva el nombre `assests/` para coincidir con la estructura real del repositorio.

### `config/` — Configuración

Contiene los parámetros de configuración necesarios para conectar la aplicación con PostgreSQL.

- **`database.php`:** establece la conexión mediante PDO. Los archivos que necesitan consultar o modificar la base de datos pueden reutilizar esta conexión.

#### Configuración de la base de datos

Cada colaborador debe adaptar la configuración a su propio entorno local antes de ejecutar el proyecto.

En `config/database.php`, revisa los siguientes parámetros:

| Parámetro | Descripción |
|---|---|
| `host` | Dirección del servidor PostgreSQL. Si está instalado en el mismo equipo, normalmente puede utilizarse `127.0.0.1`. |
| `dbname` | Nombre exacto de la base de datos creada para el proyecto. |
| `user` | Usuario de PostgreSQL con los permisos necesarios. |
| `password` | Contraseña correspondiente al usuario configurado. |

Los valores incluidos por el autor corresponden a su entorno de desarrollo y pueden no funcionar en otros equipos.

**Importante:** no compartas contraseñas personales ni credenciales reales en el repositorio. Si utilizas variables de entorno, conserva sus valores privados fuera del control de versiones.

### `database/` — Archivos de base de datos

Contiene los recursos relacionados con la base de datos del proyecto.

- **`citas_odontologicas`:** elemento identificado dentro de esta carpeta. Antes de importarlo, comprueba si es un script SQL, un respaldo u otro tipo de archivo, y utiliza el procedimiento correspondiente.

La base de datos debe disponer de las tablas y relaciones que necesita la aplicación antes de utilizar sus funcionalidades.

### `view/` — Interfaces de usuario

Contiene las páginas PHP que permiten al usuario interactuar con el sistema. Está organizada por entidad: pacientes y odontólogos.

#### `view/paciente/`

- **`form_paciente.php`:** presenta el formulario de registro de pacientes y envía los datos para su procesamiento.
- **`lista_pacientes.php`:** muestra los pacientes registrados y sus datos disponibles, junto con las opciones de interacción implementadas.
- **`editar_paciente.php`:** permite cargar y modificar la información de un paciente existente.

#### `view/odontologo/`

- **`form_odontologo.php`:** presenta el formulario para registrar odontólogos.
- **`lista_odontologos.php`:** muestra los odontólogos registrados y las opciones disponibles para interactuar con sus registros.
- **`editar_odontologo.php`:** permite cargar y modificar la información de un odontólogo existente.

Las vistas se encargan principalmente de la presentación y la interacción. Las operaciones de negocio y las consultas correspondientes deben mantenerse en los archivos de lógica de `app/`.

### `index.php` — Punto de entrada

Es el archivo ubicado en la raíz del proyecto. Puede funcionar como punto de entrada de la aplicación y dirigir al usuario hacia las secciones correspondientes, según su implementación actual.

### `README.md` — Documentación

Este archivo explica el propósito del proyecto, sus tecnologías, la organización de los directorios y los pasos necesarios para preparar el entorno de desarrollo.

## Requisitos previos

Antes de ejecutar DENTIX, necesitas:

- XAMPP con Apache y PHP 8.2, o una versión compatible.
- PostgreSQL 17, o una versión compatible con el esquema del proyecto.
- La extensión `pdo_pgsql` habilitada en PHP.
- Git para clonar el repositorio.
- El archivo de base de datos o script SQL necesario para crear las tablas y relaciones.

## Instalación y configuración

### 1. Clonar el repositorio

Clona el repositorio privado de GitHub utilizando su URL real:

```bash
git clone URL_DEL_REPOSITORIO
```

Debes tener acceso autorizado al repositorio para completar la clonación.

### 2. Ubicar el proyecto

En XAMPP para Linux, coloca la carpeta del proyecto dentro de:

```text
/opt/lampp/htdocs/SCO
```

Si clonaste el repositorio directamente en esa ubicación, no necesitas moverlo nuevamente.

### 3. Iniciar PostgreSQL

Si PostgreSQL está instalado como servicio del sistema en Linux, puedes iniciarlo con:

```bash
sudo systemctl start postgresql
```

En otros sistemas operativos, utiliza el procedimiento correspondiente a tu instalación.

### 4. Crear la base de datos

Crea la base de datos que utilizará la aplicación y prepara sus tablas y relaciones con el archivo o respaldo disponible en `database/`.

El nombre de la base de datos debe coincidir con el valor configurado en `dbname` dentro de `config/database.php`.

### 5. Configurar la conexión

Abre:

```text
config/database.php
```

Adapta los parámetros de conexión a tu instalación de PostgreSQL. Comprueba que el servidor esté activo, que el usuario tenga los permisos necesarios y que la base de datos exista.

### 6. Iniciar Apache

En XAMPP para Linux, ejecuta:

```bash
sudo /opt/lampp/lampp startapache
```

### 7. Abrir el proyecto

Abre el navegador y accede al punto de entrada:

```text
http://localhost/SCO/
```

Si necesitas acceder directamente a una vista de pacientes, puedes utilizar:

```text
http://localhost/SCO/view/paciente/lista_pacientes.php
```

La disponibilidad de cada ruta depende de la implementación actual del proyecto.

## Colaboración

DENTIX se mantiene mediante Git y GitHub. Los colaboradores autorizados pueden clonar el repositorio, configurar su propio entorno y proponer cambios.

Antes de trabajar, cada colaborador debe:

1. Clonar el repositorio.
2. Preparar PostgreSQL y la base de datos del proyecto.
3. Adaptar la configuración de conexión a su equipo.
4. Verificar que Apache y PHP funcionen correctamente.
5. Probar las funcionalidades antes de enviar cambios.

## Estado del proyecto

El desarrollo se centra en las funcionalidades CRUD de pacientes y odontólogos: crear, consultar, actualizar y eliminar registros.

La disponibilidad de cada operación debe verificarse en la implementación actual de sus archivos de lógica y vistas.
