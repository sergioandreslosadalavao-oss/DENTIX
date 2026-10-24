# SCO — Sistema de Citas Odontológicas

## Descripción

SCO es un sistema web desarrollado para apoyar la gestión de un consultorio odontológico. Su propósito es facilitar el manejo de la información de pacientes, odontólogos, servicios y citas, mediante una aplicación desarrollada con PHP y PostgreSQL.

## Tecnologías utilizadas

- **PHP 8.2:** procesamiento de formularios y lógica del sistema.
- **PostgreSQL 17:** almacenamiento y administración de la información.
- **PDO:** conexión entre PHP y PostgreSQL.
- **XAMPP:** entorno local para ejecutar Apache y PHP.
- **HTML5:** estructura de los formularios y páginas web.
- **Git y GitHub:** control de versiones y alojamiento del código.

## Estructura del proyecto

El proyecto está organizado en carpetas que separan la conexión a la base de datos, la lógica del sistema y las interfaces que utiliza el usuario.

```text
SCO/
├── app/
│   └── logica_paciente.php
├── config/
│   └── database.php
├── view/
│   ├── form_paciente.php
│   ├── lista_pacientes.php
│   └── editar_paciente.php
└── README.md
```

*Nota: este árbol muestra los archivos principales identificados del proyecto. Si el repositorio contiene otros archivos o carpetas, deben conservarse e incluirse en esta estructura según su ubicación real.*

### `app/` — Lógica del sistema

Esta carpeta contiene la lógica encargada de procesar las operaciones y aplicar las validaciones de los datos.

- **`logica_paciente.php`:** centraliza las operaciones relacionadas con los pacientes, como registrar, consultar, actualizar y eliminar registros. También permite mantener las validaciones y el procesamiento separados de las interfaces.

### `config/` — Configuración de la conexión

Esta carpeta contiene los parámetros necesarios para conectar la aplicación con la base de datos.

- **`database.php`:** configura la conexión PDO entre PHP y PostgreSQL. Los demás archivos que necesiten acceder a la base de datos pueden utilizar esta conexión, evitando repetir su configuración en cada archivo.

**Importante al clonar el repositorio:** los datos de conexión utilizados durante el desarrollo corresponden al entorno local del autor. Antes de ejecutar el proyecto, debes abrir `config/database.php` y modificar los parámetros para que coincidan con tu instalación de PostgreSQL.

Los campos principales que debes revisar son:

- **`host`:** dirección del servidor PostgreSQL. Si está instalado en el mismo equipo, normalmente puedes utilizar `127.0.0.1`.
- **`dbname`:** nombre de la base de datos que vas a utilizar. Debe coincidir exactamente con el nombre de la base de datos que creaste e importaste.
- **`user`:** usuario de PostgreSQL con permisos para acceder a esa base de datos.
- **`password`:** contraseña correspondiente al usuario configurado.

Si estos datos no coinciden con tu instalación, la aplicación no podrá conectarse correctamente a la base de datos.

### `view/` — Interfaces de usuario

Esta carpeta contiene las páginas PHP encargadas de presentar la información y permitir que el usuario interactúe con el sistema.

- **`form_paciente.php`:** presenta el formulario para registrar los datos de un paciente y enviar la información para su procesamiento.
- **`lista_pacientes.php`:** muestra los pacientes registrados, sus datos principales y las opciones disponibles para consultar o realizar operaciones sobre los registros.
- **`editar_paciente.php`:** permite cargar y modificar la información de un paciente existente, según la lógica implementada en el archivo.

La carpeta `view/` se encarga de la presentación. Las consultas y operaciones de negocio deben mantenerse en la lógica correspondiente, en lugar de duplicarse en los formularios.

### `README.md` — Documentación del proyecto

Este archivo explica el propósito de SCO, las tecnologías utilizadas, la organización de sus carpetas y los pasos necesarios para preparar el entorno de desarrollo.

## Requisitos previos

Antes de ejecutar el proyecto, necesitas:

- XAMPP con Apache y PHP.
- PostgreSQL 17 o una versión compatible.
- La extensión `pdo_pgsql` habilitada en PHP.
- Git para clonar el repositorio.
- El script SQL del proyecto para crear las tablas y estructuras de la base de datos.

## Instalación y configuración

### 1. Clonar el repositorio

```bash
git clone URL_DEL_REPOSITORIO
```

Reemplaza `URL_DEL_REPOSITORIO` por la dirección real del repositorio de GitHub.

### 2. Ubicar el proyecto

En el entorno XAMPP de Linux, coloca la carpeta `SCO` dentro de:

```text
/opt/lampp/htdocs/SCO
```

### 3. Preparar PostgreSQL

Inicia PostgreSQL si el servicio está instalado en tu equipo:

```bash
sudo systemctl start postgresql
```

Crea la base de datos que utilizarás e importa el script SQL del proyecto para generar las tablas necesarias.

### 4. Configurar la conexión

Abre el archivo:

```text
config/database.php
```

Modifica `host`, `dbname`, `user` y `password` con los datos de tu propio entorno. Comprueba que `dbname` corresponda al nombre exacto de la base de datos creada en el paso anterior.

### 5. Iniciar Apache

```bash
sudo /opt/lampp/lampp startapache
```

### 6. Abrir el proyecto

Desde el navegador, accede a la ruta de la aplicación correspondiente al archivo principal que deseas ejecutar. Por ejemplo:

```text
http://localhost/SCO/view/lista_pacientes.php
```

## Documentación adicional

Para conocer los requerimientos y el diseño general del sistema, consulta el documento de especificación de requisitos de software (SRS) del proyecto.
