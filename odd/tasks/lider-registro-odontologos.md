# Feature: Registro de odontólogos restringido al líder (DENTIX)

Objetivo: que solo un odontólogo autenticado con cargo='lider' pueda registrar/editar/deshabilitar odontólogos, cerrando el acceso anónimo de todo módulo administrativo.
Branch: main (sin commits aún — el usuario revisa con git diff) — Runner: php -l + SQLite-free tests manuales + PostgreSQL scratch vía psql.

## Specs

- S1: La columna se llama `cargo` (ENUM 'lider','asociado') en tabla `odontologo`. Regla RN-07: "no cualquier persona puede registrarse como odontologo" — solo el líder (activo=TRUE) registra a los demás.
- S2: Máximo UN líder activo, garantizado por índice único parcial en BD (regla al motor, no al PHP).
- S3: RN-08: el líder no puede ser deshabilitado/eliminado; la baja de odontólogos es soft-delete (activo=FALSE), porque fk_cita_odontologo es ON DELETE RESTRICT.
- S4: Hashes del seed: son datos de prueba ("el password-hash son datos quemado porque son de prueba"), pero deben ser hashes bcrypt reales que validen login; contraseña única de prueba documentada en el seed: test1234.
- S5: El flujo de login del script v2.1 (email + password_hash en recepcionista y odontologo) ya existe en el SQL; el backend debe guardar sesión por rol (recepcionista / odontólogo) y validar el cargo contra la BD en cada acción, no confiar en la sesión envejecida.
- S6: Los módulos administrativos (citas, servicios, pacientes, odontólogos, registro de recepcionistas) requieren sesión autenticada; el portal público del paciente (form_paciente, portal_paciente, agendar_cita) permanece público (RF-001).
- S7: El registro de odontólogo inserta todas las columnas NOT NULL del schema v2.1 (email + password_hash) — hoy falla siempre (bug de desincronización app↔BD).
- S8: `form_odontologo.php` hace su propio INSERT sin auth y con columna `telefono` inexistente — debe pasarse por la lógica central autenticada.

## Tasks

- T1 (S1,S2,S4) [inline] Script SQL v2.2: ENUM cargo_odontologo, columna, índice parcial uq_odontologo_lider, seed líder Ana Torres, hashes reales, DROP TYPE. — HECHO
- T2 (S8,S7) [inline] database/patch_v2_2_cargo.sql: parche ALTER para BD viva sin DROP. — HECHO (pendiente de aplicar por el usuario en su BD viva)
- T3 (S5) [inline] app/sesion.php (nuevo): guards requiere_recepcionista / requiere_odontologo_lider / requiere_autenticado con re-validación en BD. — HECHO
- T4 (S1,S3,S5,S7) [inline] logica_odontologo.php: login_odontologo, guard líder (RN-07), registro completo, soft-delete con RN-08. — HECHO
- T5 (S6) [inline] Guards en logica_servicio / logica_cita / logica_paciente / registro de logica_recepcionista. — HECHO
- T6 (S6,S8) [inline] Vistas: login_odontologo.php y cerrar_sesion_odontologo.php (nuevos), form_odontologo.php reescrito sin lógica inline, guards en vistas administrativas + flash y botón Deshabilitar en lista_odontologos. — HECHO
- T7 [inline] Verificación: php -l OK (11 archivos); script en BD scratch con PG 18: 2do líder → error 23505 uq_odontologo_lider; líder inactivo libera el cupo; e2e php -S + curl: T1–T14 todos pasan (guards anónimos 302, login recep OK, líder registra asociado con email+hash, email duplicado rechazado, soft-delete OK, RN-08 bloquea auto-desactivación, portal público 200, password incorrecta rechazada). — HECHO

## Propuesta para el nuevo SRS

Los siguientes requisitos describen el comportamiento del sistema, no nombres de archivos ni detalles de implementación. Los identificadores son provisionales y deben ajustarse a la numeración del SRS general.

### Requisitos funcionales

- **RF-SEG-001 — Inicio de sesión por rol:** El sistema deberá permitir que una recepcionista y un odontólogo inicien sesión con correo y contraseña, validar la cuenta activa y dirigir cada rol a las funciones autorizadas.
- **RF-SEG-002 — Cierre y vigencia de sesión:** El sistema deberá invalidar la sesión al cerrar sesión y comprobar que la cuenta continúe activa al acceder a operaciones protegidas.
- **RF-SEG-003 — Protección de administración:** El sistema deberá requerir autenticación para gestionar citas, pacientes, servicios, odontólogos y cuentas de recepción. Las solicitudes sin autorización deberán ser rechazadas, aunque se invoque directamente la ruta de procesamiento.
- **RF-ODO-001 — Gestión restringida al líder:** El sistema deberá permitir que únicamente un odontólogo autenticado, activo y con cargo de líder registre, edite o deshabilite odontólogos.
- **RF-ODO-002 — Registro de asociado:** El sistema deberá permitir al líder registrar odontólogos asociados con nombre, especialidad, correo y contraseña; el correo deberá ser válido y único, y la contraseña deberá cumplir la política mínima definida por el sistema.
- **RF-ODO-003 — Deshabilitación sin pérdida de historial:** El sistema deberá deshabilitar odontólogos asociados sin borrar sus datos ni las citas relacionadas. El odontólogo deshabilitado no podrá recibir nuevas asignaciones.
- **RF-ODO-004 — Protección del líder:** El sistema no deberá permitir deshabilitar al único odontólogo líder activo.
- **RF-PAC-001 — Acceso público del paciente:** El sistema deberá mantener públicos el registro de pacientes y el flujo de solicitud de citas, sin exigir una sesión administrativa.
- **RF-CIT-001 — Revisión de solicitudes:** El sistema deberá permitir a recepción consultar citas pendientes, asignar un odontólogo activo y confirmar la cita. Las citas deberán respetar las reglas de disponibilidad y horario configuradas.

### Reglas de negocio

- **RN-ODO-001:** En una instalación de una sola clínica deberá existir como máximo un odontólogo con cargo de líder activo.
- **RN-ODO-002:** Un odontólogo asociado no podrá gestionar cuentas de odontólogos ni acceder a funciones clínicas hasta que ese módulo exista y se le autorice explícitamente.
- **RN-ODO-003:** La baja de un odontólogo será lógica (`activo = false`); no se borrará físicamente un odontólogo referenciado por citas.
- **RN-SEG-001:** El sistema almacenará contraseñas mediante hashes seguros y nunca en texto claro.
- **RN-SEG-002:** Los permisos se determinarán con el estado y cargo vigentes en la base de datos, no únicamente con valores guardados en la sesión.
- **RN-SEG-003:** Las cuentas, correos y contraseñas incluidos en los scripts SQL serán exclusivamente datos de demostración para desarrollo; no serán credenciales predeterminadas de producción.

### Criterios de aceptación sugeridos

- Una persona sin sesión no puede abrir módulos administrativos ni ejecutar sus acciones; el portal público del paciente sí responde sin autenticación.
- Una cuenta incorrecta o inactiva no inicia sesión; una cuenta válida inicia sesión con el rol correspondiente.
- Una recepcionista o un odontólogo asociado no puede registrar, editar ni deshabilitar odontólogos, incluso enviando una solicitud directa al backend.
- El líder activo puede registrar un asociado con credenciales válidas; el sistema rechaza correo duplicado y datos inválidos.
- La base de datos rechaza un segundo líder activo. Al desactivar al líder debe aplicarse primero el procedimiento de transferencia de liderazgo que defina el SRS.
- Deshabilitar un asociado conserva sus datos e historial y evita nuevas asignaciones.
- Cambiar en la base de datos el estado activo o el cargo de una cuenta invalida sus permisos en las siguientes operaciones protegidas.

### Pendientes que el SRS debe resolver

- Definir el flujo de transferencia del cargo de líder. El código protege al líder de ser deshabilitado, pero no se identificó una pantalla o acción para transferir el cargo.
- Definir las reglas completas de citas: estados permitidos, cancelación, motivo obligatorio, anticipación mínima y cómo se presentan los horarios disponibles al paciente.
- Definir un único contrato de datos para el paciente. Las vistas públicas usan `cedula`, mientras que los esquemas SQL incluidos no declaran esa columna; por tanto, el registro y la búsqueda por cédula no deben marcarse como funcionalidad verificada con esos scripts.
- Definir si el teléfono del odontólogo forma parte del modelo. El listado lo consulta, pero el esquema y el formulario de registro actuales no lo proveen.
- Aclarar que el inicio de sesión de odontólogos asociados existe, pero el acceso está bloqueado hasta que se implemente su módulo clínico.
- Mantener las credenciales del seed fuera de los requisitos funcionales del producto; documentarlas únicamente en instrucciones de desarrollo y pruebas.

## Log

- L1 (2026-10-29): "resulta que tenemos un detalle y es que no cualquier persona puede registrarse como odontologo, como pensamos solventar eso? aplicando un tipo de cargo en los odontologos para que el odontologo lider de el consultorio (u instalacion) pueda registrar los demas odontologo" → escenario decidido: UNA clínica; líder global. "bueno, el sistema solo cubre una clinica". "primero, la columna la llamare cargo, segundo el password-hash son datos quemado porque son de prueba". "mejor haz las correcciones, pero espero que esten bien" (modo enseñanza: cada cambio se explica al final).
- L2 (2026-10-29): Hallazgos del relevamiento — logica_odontologo.php sin validación de sesión; form_odontologo.php con INSERT inline (bypass, sin auth, columna telefono inexistente); form_cita/lista_citas/form_servicio/form_paciente con lógica inline en vistas; form_recepcionista + acción 'registro' permiten crear admins públicamente; PostgreSQL online con credenciales del .env (funciona en vivo).
- L3 (2026-10-29): Implementación vía edits (sin commits: el usuario revisa con git diff; si aprueba, armamos work-unit commits).
- L4 (2026-10-31): Verificación completa en entorno scratch (PG 18 en /var/tmp/pg-e2e puerto 55432 + php -S + curl): 14 escenarios T1–T14 TODOS OK (lista en T7). Evidencia SQL: intentar segundo líder activo → ERROR 23505 «llave duplicada viola restricción de unicidad uq_odontologo_lider»; líder inactivo libera el cupo (UPDATE permite segundo líder). Regresión: portal público (form_paciente, portal_paciente) sigue 200 sin sesión. Limpieza: scratch PG eliminado, servidor php detenido, /tmp/opencode vacío.
- L5 (2026-10-31): INCIDENTE DE ENTORNO: la instancia de PostgreSQL del usuario (puerto 5432, base citas_odontologicas, usuario sergio) estaba corriendo con datos en una ubicación volátil (probablemente /tmp); al limpiarse /tmp, el servidor y su data desaparecieron. Al reportar al usuario: su BD viva quedó caída y puede haber perdido datos; el parche v2.2 no se aplicó (pendiente de decisión del usuario) y tampoco se commitó nada (pendiente del usuario).
- L6 (2026-10-31): RDD assess: risk=medium, review_due=false (under_budget), 26 paths / 788 líneas — el review queda pendiente en el slice hasta que un work-unit commit alcance el presupuesto. Boundary de review: ninguna aún (primera ronda del slice).
- L7 (2026-10-31, pedido verbatim): "quiero poner un usuario con credenciales de admin para pruebas, no quiero que se ana torres, ella es una odontologa mas de la instalacion, quiero que sea un usuario llama DR. Checho Cardenas y pon las demas credenciales" → seed v2.2: Checho Cardenas (checho.cardenas@clinica.com / test1234) nace como ÚNICO líder; Ana pasa a 'asociado'; parche v2.2 bootstrap también apunta a Checho (lo da de alta si falta y le asigna cargo solo si no hay líder). Verificado en scratch PG: seed OK, 2do líder → 23505 uq_odontologo_lider, password_verify("test1234") → true.
- L8 (2026-10-31, pedido verbatim): "quiero que pongas el script de sco en sco patch para que sea solo ponerlo" → nuevo `database/sco_patch.sql` ÚNICO parche (reemplaza y se borra `patch_v2_2_cargo.sql`): idempotente, sirve para BD nueva (crea todo) y BD v2.1 existente (agrega cargo sin tocar datos), sin DROPs, semillas con guardas por email/estado. Dos bugs propios encontrados y corregidos en la verificación: (1) CASE de cargo sobre 4 filas evaluaba contra snapshot previo → en base vacía las 4 salían 'lider' (violaba el índice) → split en dos INSERTs; (2) guarda "tabla sin citas" por statement → solo sembraba 1 de 2 citas → unificado en un solo INSERT. Tests scratch PG: A base nueva (2 cit, líder Checho) / B simula v2.1 drop cargo y re-aplica (recupera + promueve) / C re-ejecución pura (idempotente) / D otro líder activo (Ana) NO es robado — todos OK. Incidente operativo: un pkill -f autoreferente mató la propia shell (usar patrón con clase `[-]` para evitar self-match).
- L9 (2026-11-01): Usuario pregunta "QUE PASO CON LA CARPETA config?" — hallazgo: `config/database.php` estaba BORRADO del disco y staged como `D` en git (existía en HEAD con credenciales hardcodeadas: dbname `sco`, user `sergio`, password `162025Checho`); 10+ archivos lo requerían → fatal. `.env` existía pero nada lo leía. Propuse A (restaurar tal cual) o B (migrar a `.env`); el usuario elige **B**. Recreado `config/database.php` con parser mínimo de `.env`, mismo contrato `$conexion` PDO y mismos mensajes de error. `.env` NO editable por política del agente (deny edit `**/.env`): el usuario debe poner la contraseña real él; DB_NAME actual `citas_odontologicas` vs `sco` del config viejo (elegir y ser consistente). E2E scratch PG 5432 con los valores ACTUALES del `.env` (password literal con ñ, scram real): parser→PDO→query OK (4 odontólogos, líder Checho); password mala rechazada. `sco.sql` con header de advertencia (DROP CASCADE, solo base vacía); borrado `sco_patch_v2_2_cargo.sql` (redundante con `sco_patch.sql`).
- L10 (2026-11-01, pedido verbatim): "quiero que modifiques el readme y que le agreuges lo nuevo que se hizo y con las nuevas funcionalidades de cada cosa" → README actualizado: Funcionalidades con RN-07/RN-08, revalidación BD, sesión por rol, portal público, login asociado denegado; tabla de accesos de prueba completa (5 cuentas con comportamiento esperado); ruta de gestión de odontólogos; fix de ".gitignore ya excluye .env" (verificado); borrada la referencia al parche v2.2 ya inexistente; pendiente nuevo "transferencia de líder sin pantalla". Verificación previa: telefono (lista_odontologo:138) y cedula (4 vistas paciente) siguen vigentes como pendientes.
- L11 (2026-11-01): Push a pedido del usuario ("quiero subir todo"). Pre-push reparado el índice (`git add config/database.php`: estaba staged D + archivo suelto). Commit `364e551` en main (40 archivos, +7928/-1325, sin .env) y push OK; development merged desde main y push OK. Remoto actualizado: el repo GitHub fue renombrado de `SGO` a `DENTIX` (git remote set-url). INCIDENTE: development tenía el `.env` trackeado (commit 6ae63b6) → el merge main→development lo borró del disco; restaurado con `git show 6ae63b6:.env > .env` (contenido idéntico, placeholder). En main `.gitignore` ya lo excluye. Recomendación pendiente al usuario: `.env` sigue con DB_PASSWORD=TU_NUEVA_CONTRASEÑA (placeholder) — completarlo antes de conectar en vivo.
