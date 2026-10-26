# SCO - Sistema de Citas Odontológicas (Rama: rama-maik)

Este repositorio contiene el desarrollo y la integración del **Sistema de Citas Odontológicas (SCO)**, un software web desarrollado en **PHP** y **PostgreSQL** para la gestión clínica, enfocado en optimizar el flujo de pacientes, especialistas y servicios.

## 🚀 Cambios y Mejoras Realizados en esta Rama

### 1. Integración y Backend
* **Fusión de ramas:** Integración exitosa del repositorio del colaborador con la rama local `rama-maik` resolviendo conflictos de concurrencia y configuraciones base.
* **Configuración de Base de Datos (`config/database.php`):** Ajuste de la conexión mediante **PDO** hacia **PostgreSQL** (puerto `5433`), conectando correctamente con la base de datos `citas_odontologicas` y adaptando las consultas a las tablas en singular (`paciente`, `odontologo`, `servicio`)[cite: 9].
* **Habilitación de Drivers:** Configuración y activación de las extensiones `pgsql` y `pdo_pgsql` en el entorno XAMPP de PHP.

### 2. Rediseño y Modernización del Frontend
Se estandarizó toda la interfaz gráfica de usuario utilizando **Bootstrap 5** y **FontAwesome** para lograr un diseño moderno, limpio y completamente responsivo:

* **Dashboard Principal (`index.php`):**
  * Creación de un banner superior (*hero banner*) institucional.
  * Tarjetas interactivas con efectos de elevación (*hover*) para acceder rápidamente a los módulos de Pacientes, Odontólogos y Servicios.
* **Módulo de Pacientes (`view/paciente/`):**
  * **Listado (`lista_pacientes.php`):** Tabla estilizada con diseño moderno, alertas de alergias personalizadas y botones de acción rápidos.
  * **Agendamiento de Citas (`form_cita.php`):** Formulario optimizado con selectores dinámicos para vincular pacientes y odontólogos.
* **Módulo de Odontólogos (`view/odontologo/`):**
  * **Registro (`form_odontologo.php`):** Formulario rediseñado con campos claros para nombre, especialidad y consultorio, acompañado de iconos descriptivos.
  * **Listado (`lista_odontologos.php`):** Visualización en tabla limpia de la planta de profesionales.
* **Módulo de Servicios (`view/servicio/`):**
  * **Registro (`form_servicio.php`):** Formulario adaptado para el ingreso de tratamientos, control de duración en minutos y tarifas en pesos colombianos (COP).
  * **Listado (`lista_servicios.php`):** Catálogo visual ordenado de procedimientos médicos.

## 🛠️ Tecnologías Utilizadas
* **PHP** (Lógica del lado del servidor y controladores).
* **PostgreSQL** (Gestión relacional de base de datos).
* **Bootstrap 5** (Framework CSS para diseño UI/UX).
* **FontAwesome** (Iconografía vectorial).
* **XAMPP / Apache** (Entorno de servidor local).