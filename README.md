

## 📋 Descripción General
**DENTIX** (anteriormente SCO) es un sistema integral de gestión web orientado a consultorios odontológicos, desarrollado con **PHP** y **PostgreSQL** sobre un entorno local LAMPP. Su objetivo principal es optimizar los flujos de trabajo del rol de recepcionista y del cuerpo médico, facilitando la administración de expedientes, el control de citas en tiempo real, la asignación de especialistas y la gestión de tarifas y tratamientos.

---

## 🚀 Módulos Actualizados y Nuevas Características

### 1. Módulo de Autenticación y Redirección
* **Ruta de Acceso Normalizada**: Se corrigieron las redirecciones tras el inicio de sesión para que el acceso de los usuarios dirija de forma directa y limpia al panel principal de administración.

### 2. Módulo de Pacientes (`lista_pacientes.php` y formularios)
* **Expediente Clínico Completo**: Se sincronizaron las columnas de la base de datos para mostrar con precisión:
  * **Documento (Cédula)** de cada paciente.
  * **Correo electrónico** y **Teléfono de contacto**.
  * **Fecha de Nacimiento**.
* **Control de Alergias Inteligente**: Incorporación de etiquetas visuales dinámicas para identificar rápidamente restricciones o alergias médicas.
* **Portal privado del paciente**: Inicio de sesión con cédula y contraseña, registro/activación de cuenta, perfil personal y consulta del estado e historial de citas.
* **Migraciones**: En bases existentes, ejecutar `database/migrations/20261031_patient_accounts.sql` para habilitar las cuentas de paciente y `database/migrations/20261103_unassigned_patient_appointments.sql` para permitir solicitudes sin odontólogo hasta que recepción lo asigne.

### 3. Módulo de Odontólogos (`lista_odontologos.php` y `form_odontologo.php`)
* **Estado en Tiempo Real**: Indicador visual de disponibilidad para cada especialista (*"Disponible"* u *"Ocupado / En Cita"*).
* **Gestión de Contacto y Credenciales**: El formulario solicita obligatoriamente el **Teléfono**, el **Correo electrónico** y la **Contraseña** cifrada por hash (Bcrypt).

### 4. Módulo de Servicios (`lista_servicios.php` y `form_servicio.php`)
* **Catálogo Clínico Ampliado**: Estructuración para registrar:
  * **Descripciones clínicas detalladas** de cada tratamiento.
  * **Duración promedio estimada en minutos**.
  * **Tarifas y costos vigentes** en pesos colombianos (COP).
* **Nuevos Procedimientos**: Incorporación de *Blanqueamiento dental*, *Extracción dental* y *Endodoncia*.

### 5. Módulo de Citas y Asignación (`lista_citas.php`)
* **Asignación Dinámica**: Interfaz refactorizada que permite asociar citas pendientes con los odontólogos activos del consultorio.

### 6. Estandarización Visual (UI/UX)
* **Diseño Unificado**: Todos los módulos comparten la misma estructura de contenedores (`app-shell`, `panel`, `data-table`) y una barra lateral de navegación fija en tono esmeralda institucional.

---

## 🛠️ Tecnologías y Herramientas Utilizadas
* **Backend**: PHP (PDO y programación estructurada).
* **Base de Datos**: PostgreSQL.
* **Frontend**: HTML5, CSS3, Bootstrap 5, Font Awesome, Google Fonts (*Manrope* y *Sora*), y GSAP.
* **Control de Versiones**: Git & GitHub.

---
*Desarrollado para el programa Análisis y Desarrollo de Software (ADSO) — SENA.*
EOF