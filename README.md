# DENTIX — Sistema de Gestión y Citas Odontológicas (Versión 3.0)

**DENTIX** es una aplicación web integral para la gestión de clínicas y consultorios odontológicos, desarrollada con **PHP 8 (PDO)** y **PostgreSQL** sobre un entorno local XAMPP/LAMPP. Conecta en tiempo real a los tres roles clave de la clínica: **Pacientes**, **Recepción (Administración)** y **Odontólogos (Cuerpo Médico)**.

---

## 🚀 Registro Detallado de Cambios y Novedades (v3.0)

### 1. Portal Exclusivo e Independiente para Odontólogos (Cuerpo Médico)
Se construyó un sistema completo de autenticación y gestión clínica para cada especialista:
* **Acceso Independiente ([`login_odontologo.php`](view/odontologo/login_odontologo.php)) y Seguridad ([`app/odontologo_auth.php`](app/odontologo_auth.php))**:
  * Inicio de sesión con correo institucional y contraseña cifrada (`password_verify`), regeneración de sesión, cookies seguras y tokens CSRF.
  * Auditoría automática de acceso actualizando la columna `ultimo_acceso` al iniciar sesión.
  * Accesos directos añadidos en la barra de navegación superior y en la tarjeta de **Cuerpo Médico** de [`index.php`](index.php).
* **Panel Personal de Agenda ([`panel_odontologo.php`](view/odontologo/panel_odontologo.php))**:
  * Cada odontólogo visualiza **únicamente las citas confirmadas asignadas a su nombre**, junto con tarjetas de resumen (*Citas por Atender*, *Citas Finalizadas* y *Estado Actual*).
  * Muestra información crítica para la consulta: cédula, teléfono, tratamiento, duración estimada y alertas rojas de **alergias del paciente**.
  * **Botón `Finalizada` con Modal de Registro Clínico (`detalle_cita`)**: Al finalizar una cita, se abre un formulario modal para registrar obligatoriamente:
    * **Fecha y Hora de Salida de la Cita** (`fecha_salida`, `hora_salida`).
    * **Resumen breve de cómo fue la cita** (`detalle`).
    * **Procedimiento realizado** (`procedimiento`).
    * **Extras, recomendaciones o problemáticas presentadas** (`nota_clinica`).
  * El guardado se ejecuta mediante una transacción atómica en PostgreSQL (`UPDATE cita` a estado `'completada'` + `INSERT INTO detalle_cita`).
* **Nube Histórica Dedicada ([`historial_finalizadas.php`](view/odontologo/historial_finalizadas.php))**:
  * Vista independiente enlazada desde el menú lateral (*"Historial Finalizadas"*).
  * Funciona como archivo clínico permanente por odontólogo, mostrando todas las citas completadas con ingreso, salida, resumen, procedimiento y nota clínica, además de un **buscador en vivo** por nombre, cédula o tratamiento.
* **Cierre de Sesión Seguro ([`cerrar_sesion_odontologo.php`](view/odontologo/cerrar_sesion_odontologo.php))**:
  * Destrucción completa de sesión y cookies mediante petición `POST` protegida por CSRF.

---

### 2. Portal del Paciente y Solicitud de Citas
* **Registro y Activación de Cuentas ([`form_paciente.php`](view/paciente/form_paciente.php))**:
  * Registro público con validación de **Nombre**, **Cédula (única)**, **Teléfono**, **Correo electrónico** y **Contraseña cifrada con Bcrypt** (mínimo 8 caracteres).
  * Soporte para activación de cuenta si el paciente ya estaba pre-registrado por recepción sin contraseña.
* **Autenticación y Perfil Personal ([`login_paciente.php`](view/paciente/login_paciente.php), [`perfil_paciente.php`](view/paciente/perfil_paciente.php))**:
  * Inicio de sesión por **Cédula y Contraseña**.
  * Panel con tarjetas estadísticas (*Total de citas*, *Pendientes*, *Confirmadas*), ficha personal con alergias e historial actualizado en tiempo real con el odontólogo asignado.
* **Solicitud Autónoma de Citas ([`portal_paciente.php`](view/paciente/portal_paciente.php))**:
  * Permite al paciente elegir el tratamiento, fecha y hora (entre `08:00` y `19:30` en bloques de 30 minutos).
  * Las citas nacen en estado `'pendiente'` y con `odontologo_id = NULL` hasta que recepción las confirme.

---

### 3. Reglas de Negocio y Control de Agenda (Frontend + PostgreSQL)
* **RN-07 — Frecuencia de Citas por Paciente**:
  * Bloquea que un paciente agende más de **1 cita el mismo día**.
  * Exige un intervalo mínimo de **4 días completos** entre citas activas/completadas (ejemplo: cita el `12/12/2026` $\rightarrow$ siguiente cita permitida desde el `17/12/2026`).
* **RN-08 — Límite Diario por Odontólogo**:
  * Impide que recepción asigne más de **1 cita por día** (`confirmada` o `completada`) al mismo odontólogo ([`lista_citas.php`](view/cita/lista_citas.php)).
* Ambas reglas están protegidas a nivel de código PHP y a nivel de base de datos mediante la función y trigger **`trg_validar_espaciado_citas`**.

---

### 4. Panel Administrativo de Recepción y Rediseño UI/UX
* **Tarjeta de Perfil de Recepción en Barra Lateral ([`boton_cerrar_sesion.php`](view/recepcionista/boton_cerrar_sesion.php))**:
  * Diseño con estética *glassmorphism*, avatar con inicial en degradado esmeralda, indicador LED de estado activo, nombre completo del recepcionista autenticado (ej. *Jose Antonio*, *María López*) y botón de cierre de sesión seguro.
* **Disponibilidad de Odontólogos en Tiempo Real ([`lista_odontologos.php`](view/odontologo/lista_odontologos.php))**:
  * Consulta dinámica vinculada mediante `LEFT JOIN cita`: muestra **`🔴 Ocupado / En Cita`** únicamente a los odontólogos que tienen citas confirmadas pendientes por atender y **`🟢 Activo / Disponible`** a los demás.
* **Catálogo Público y Administración de Servicios**:
  * Catálogo público de tratamientos con iconos dinámicos, descripción clínica, duración y tarifa en COP ([`catalogo_publico.php`](view/servicio/catalogo_publico.php)).
  * Soporte completo para editar la **Descripción Clínica** en [`app/logica_servicio.php`](app/logica_servicio.php).
* **Rediseño Visual de Formularios de Edición**:
  * [`editar_servicio.php`](view/servicio/editar_servicio.php) y [`editar_odontologo.php`](view/odontologo/editar_odontologo.php) renovados con la línea gráfica oficial de DENTIX (Bootstrap 5, Font Awesome, tipografías *Manrope* y *Sora*, iconos en inputs y tarjetas centradas).

---

### 5. Base de Datos PostgreSQL ([`database/citas_odontologicas`](database/citas_odontologicas))
El script maestro oficial fue reconstruido, documentado y sincronizado con toda la aplicación:
* **Nuevas Columnas**:
  * `paciente`: `cedula VARCHAR(30) NOT NULL UNIQUE`, `password_hash VARCHAR(255) NOT NULL` e índice `ix_paciente_cedula`.
  * `servicio`: `descripcion TEXT`.
  * `cita`: `odontologo_id INT` (ahora admite `NULL` para solicitudes desde el portal del paciente).
  * `detalle_cita`: `fecha_salida DATE` y `hora_salida TIME`.
* **Triggers y Funciones PL/pgSQL**:
  * `fn_validar_espaciado_citas_paciente()` y `trg_validar_espaciado_citas` para garantizar **RN-07** y **RN-08** directamente en el motor de base de datos.
* **Vistas Actualizadas**:
  * `v_agenda_dia` y `v_historial_paciente` actualizadas con `LEFT JOIN odontologo` e inclusión de `fecha_salida` y `hora_salida`.
* **Datos de Prueba Completos con `pgcrypto`**:
  * Extensión `CREATE EXTENSION IF NOT EXISTS pgcrypto;` con hashes Bcrypt reales (`crypt('12141214', gen_salt('bf', 10))`).
  * **5 Pacientes**, **3 Recepcionistas**, **5 Odontólogos**, **5 Servicios** y **2 Citas iniciales**.

---

## 🔑 Credenciales de Prueba (Contraseña universal: `12141214`)

| Rol | Usuario / Identificador | Contraseña | URL de Acceso |
|---|---|---|---|
| **Recepción** | `jose.recepcion@clinica.com` / `maria.recepcion@clinica.com` | `12141214` | `/view/recepcionista/lista_recepcionista.php` |
| **Odontólogos** | `luis.mendoza@clinica.com` / `ana.torres@clinica.com` *(y demás del cuerpo médico)* | `12141214` | `/view/odontologo/login_odontologo.php` |
| **Pacientes** | Cédulas: `1001112233`, `1004445566`, `1076907716`, `1005556677`, `1007778899` | `12141214` | `/view/paciente/login_paciente.php` |

---

## 🛠️ Tecnologías Utilizadas
* **Backend:** PHP 8 (PDO, sesiones seguras, protección CSRF, `password_hash` / `password_verify`).
* **Base de Datos:** PostgreSQL 12+ (Triggers PL/pgSQL, Vistas, Índices, `pgcrypto`).
* **Frontend:** HTML5, CSS3, Bootstrap 5.3, Font Awesome 6.4, Google Fonts (*Manrope* & *Sora*), GSAP 3.12.
* **Entorno:** Apache / XAMPP / LAMPP (`http://localhost:8080/DENTIX/`).

---
*Proyecto desarrollado para el programa Análisis y Desarrollo de Software (ADSO) — SENA.*
