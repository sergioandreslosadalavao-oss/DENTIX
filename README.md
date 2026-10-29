# DENTIX — Sistema de Citas Odontológicas

## 📋 Descripción General
**DENTIX** (anteriormente SCO) es un sistema de gestión web orientado a consultorios odontológicos, desarrollado con **PHP** y **PostgreSQL** sobre un entorno local LAMPP. Su objetivo principal es optimizar los flujos de trabajo del rol de recepcionista y el cuerpo médico, facilitando la administración de pacientes, control de citas, asignación de especialistas y catálogo de servicios.

---

## 🚀 Principales Modificaciones y Mejoras Implementadas

### 1. Estandarización Visual de Módulos (UI/UX)
* **Diseño Unificado**: Se refactorizaron todos los módulos (`Inicio`, `Pacientes`, `Odontólogos`, `Servicios` y `Citas`) para compartir la misma estructura de contenedores (`app-shell`, `panel`, `data-table`) y una barra lateral fija de navegación esmeralda.
* **Consistencia de Botones**: Estandarización de botones de acción y formularios utilizando la paleta de colores institucional.

### 2. Módulo de Citas y Asignación
* **Asignación Manual**: Interfaz rediseñada en `lista_citas.php` que permite a la recepcionista asignar dinámicamente odontólogos activos a las citas pendientes.

### 3. Módulo de Odontólogos y Disponibilidad
* **Control de Estado en Tiempo Real**: Se añadió la columna y lógica para visualizar si un especialista se encuentra **"Disponible"** u **"Ocupado / En Cita"**.
* **Credenciales y Contacto**: Integración de campos de teléfono, correo (`email`) y autenticación segura con hash de contraseñas tanto en la vista como en el formulario de registro (`form_odontologo.php`).

### 4. Módulo de Pacientes
* **Expediente Completo**: Se incorporaron columnas de **Documento (Cédula)**, **Correo**, **Fecha de Nacimiento** y **Alergias** (con alertas visuales inteligentes) para que el personal clínico tenga los datos de riesgo a la mano.

### 5. Catálogo de Servicios
* **Detalle Clínico Ampliado**: Se estructuró la base de datos y la interfaz para registrar **Descripciones clínicas**, **Duración promedio en minutos** y **Tarifas (COP)**.
* **Nuevos Procedimientos**: Incorporación de servicios avanzados como *Blanqueamiento dental*, *Extracción dental* y *Endodoncia*.

---

## 🛠️ Tecnologías Utilizadas
* **Backend**: PHP (Programación estructurada y PDO).
* **Base de Datos**: PostgreSQL.
* **Frontend**: HTML5, CSS3, Bootstrap 5, Font Awesome, Google Fonts (Manrope y Sora) y animaciones con GSAP.
* **Control de Versiones**: Git & GitHub.

---
*Desarrollado para el programa ADSO — SENA.*
