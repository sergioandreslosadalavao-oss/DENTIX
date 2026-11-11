# LOG DE ERRORES Y SUGERENCIAS DE MEJORA CONTINUA


## DENTIX — Sistema Integral de Gestión de Citas Odontológicas

Expediente QA de Gestión Estratégica de Defectos (Severidad vs. Prioridad), Análisis de Causa Raíz y Registro de Sugerencias Evolutivas

| Campo | Detalle del Expediente |
| --- | --- |
| Proyecto | DENTIX — Sistema Integral de Gestión de Citas Odontológicas |
| Documento | Log de Errores y Sugerencias de Mejora Continua (Expediente QA) |
| Equipo de Desarrollo y QA | Jose Angel Mejia Perez · Michael Steven Cardenas Ruiz · Sergio Andres Losada Lavao |
| Programa / Ficha | Tecnólogo en Análisis y Desarrollo de Software (ADSO) — Ficha 3407799 |
| Instructor | Jose de Jesus Motta Vargas |
| Centro de Formación | CIES SENA Neiva – Huila |
| Fecha de Consolidación | 10 de noviembre de 2026 |
| Referencia Metodológica | Gestión Estratégica de Defectos (Glosario ISTQB, Acciones ISO 9000, 5 Porqués, Ishikawa y Pareto 80/20) |
| Documentos Asociados | SRS DENTIX, Plan de Aseguramiento de Calidad (PAC), Manual Técnico y Manual de Usuario |





# Tabla de contenido

Índice interactivo del documento. Haz clic (o Ctrl + Clic en Microsoft Word) sobre cualquier sección o subcategoría para ir directamente a ese apartado:

1.  Introducción y Marco Metodológico de Gestión de Defectos [1.]

▸ 1.1  Propósito del Log de Errores y Sugerencias [1.1]

▸ 1.2  Anatomía del Problema (Glosario ISTQB aplicado a DENTIX) [1.2]

▸ 1.3  Ciclo de Vida del Defecto y Niveles de Respuesta (ISO 9000) [1.3]

2.  Criterios de Triaje: Severidad vs. Prioridad [2.]

▸ 2.1  Escala de Severidad Técnica [2.1]

▸ 2.2  Escala de Prioridad de Negocio [2.2]

▸ 2.3  Matriz de Triaje Aplicada al Proyecto [2.3]

3.  Log Detallado de Errores y Defectos Detectados (DEF-001 a DEF-008) [3.]

▸ 3.1  Resumen General del Registro Histórico de Defectos [3.1]

▸ 3.2  Defectos de Severidad Alta y Prioridad Alta (P1 — Críticos Operativos) [3.2]

▸ 3.3  Defectos de Severidad Media y Prioridad Media/Alta (P2 — Integración y Experiencia) [3.3]

▸ 3.4  Defectos de Severidad Baja y Prioridad Media (P3 — Presentación Visual) [3.4]

4.  Análisis Estratégico de Causa Raíz y Telemetría QA [4.]

▸ 4.1  Técnica de los 5 Porqués (Caso DEF-001) y Síntesis Diagnóstica [4.1]

▸ 4.2  Vectores de Causalidad (Ishikawa), Pareto (80/20) y Métricas QA [4.2]

5.  Banco de Sugerencias y Mejoras Evolutivas Futuras (SUG-01 a SUG-10) [5.]

▸ 5.1  Matriz Priorizada de Ideas y Sugerencias para Futuras Integraciones [5.1]

▸ 5.2  Detalle Técnico y Valor de Negocio de las Sugerencias Prioritarias [5.2]

6.  Protocolos de Excelencia en QA y Conclusión [6.]

▸ 6.1  Protocolos de Reporte, Validación Dual y Cierre [6.1]

▸ 6.2  Conclusión General [6.2]






# 1. Introducción y Marco Metodológico de Gestión de Defectos


## 1.1 Propósito del Log de Errores y Sugerencias

Los defectos documentados constituyen la evidencia central del aseguramiento de la calidad y la memoria técnica del proyecto. El presente Log de Errores y Sugerencias almacena y documenta detalladamente todas las fallas reales detectadas durante el desarrollo y pruebas de DENTIX —desde los 3 errores iniciales de conexión, rutas y comunicación de formularios (`DEF-001`, `DEF-002` y `DEF-003`) hasta los defectos de formularios vacíos, omisión de información clínica (alergias y fecha de nacimiento en registro de paciente), validaciones legales, iconografía e internacionalización (`DEF-004` a `DEF-008`)— clasificándolos sistemáticamente por **Severidad** y **Prioridad** **con el objetivo de dejar registro de su causa raíz y acción preventiva para que no se vuelvan a repetir en futuras entregas o proyectos**.

De forma complementaria, el documento incorpora un Banco de Sugerencias Evolutivas donde el equipo registra ideas estratégicas surgidas durante la operación y sustentación del software para ser integradas en próximas iteraciones productivas, consolidando a DENTIX como una solución clínica 100 % funcional, escalable y centrada en el usuario.

•  1. Documentar (El Hallazgo): registrar de forma reproducible cada anomalía detectada en pruebas funcionales o técnicas.

•  2. Priorizar (El Triaje): definir el orden de intervención combinando el impacto técnico (Severidad) y la urgencia operativa (Prioridad).

•  3. Rastrear (La Trazabilidad): monitorear el ciclo de vida del ticket desde su detección hasta la verificación y cierre.

•  4. Erradicar y Evolucionar (La Mejora): atacar la causa raíz para evitar reincidencias y documentar ideas futuras de evolución del producto.






## 1.2 Anatomía del Problema (Glosario ISTQB aplicado a DENTIX)

| Concepto ISTQB | Definición Técnica | Evidencia Típica en DENTIX |
| --- | --- | --- |
| Error (La Genética) | Equivocación humana cometida durante la configuración, diseño o programación. | Un desarrollador escribe mal un atributo 'name' en un formulario HTML o configura un puerto/credencial incorrecta. |
| Defecto / Bug (El Patógeno) | Resultado del error humano plasmado en el código fuente, consulta SQL o documento. | La vista PHP envía $_POST['servicio'] mientras logica_servicio.php espera $_POST['nombre_servicio'], o falta habilitar pdo_pgsql. |
| Falla (El Síntoma) | Comportamiento incorrecto visible cuando el software se ejecuta en el navegador. | Al pulsar Guardar servicio aparece pantalla de error de conexión con PostgreSQL o el registro no se almacena en la tabla servicio. |




## 1.3 Ciclo de Vida del Defecto y Niveles de Respuesta (ISO 9000)

Todo defecto reportado en DENTIX recorre el flujo estándar: Nuevo → Asignado → En corrección → Corregido → Verificado (Retest) → Cerrado. Si el fallo persiste tras el retest pasa a Reabierto; si no corresponde a un defecto funcional se clasifica como Rechazado, Duplicado o Diferido.

| Nivel de Profundidad (ISO 9000) | Objetivo Estratégico | Aplicación Real en DENTIX |
| --- | --- | --- |
| 1. Acción de Contención (El Torniquete) | Limitar temporalmente el impacto operativo mientras se diseña la solución definitiva. | Operar con respaldo local de la BD o registro directo de prueba en PostgreSQL mientras se corrige el archivo de conexión. |
| 2. Corrección (El Parche) | Resolver puntualmente el síntoma detectado en el componente afectado. | Ajustar credenciales/puerto en la conexión o corregir un enlace roto en la vista específica. |
| 3. Acción Correctiva (La Cirugía) | Eliminar la causa raíz técnica para impedir que el mismo fallo vuelva a producirse. | Centralizar la conexión PDO en un archivo único (config/database.php), estandarizar rutas relativas y alinear atributos 'name' HTML ↔ PHP. |
| 4. Acción Preventiva (La Vacuna) | Implantar estándares y controles de equipo para evitar que el patrón de error reaparezca en otros módulos. | Documentar la arquitectura y entorno antes de codificar, usar plantilla config/database.example.php, validar formularios aisladamente y exigir checklist QA. |









# 2. Criterios de Triaje: Severidad vs. Prioridad


## 2.1 Escala de Severidad Técnica

| Nivel de Severidad | Criterio Técnico de Clasificación | Ejemplo en DENTIX |
| --- | --- | --- |
| Crítica | Bloqueo total del sistema, pérdida o corrupción de datos clínicos, o vulnerabilidad grave de seguridad/autenticación. | Imposibilidad de iniciar sesión en toda la plataforma o fallo en la base de datos que impida operar cualquier módulo. |
| Alta | Falla grave en una funcionalidad principal sin alternativa sencilla en la interfaz, o incumplimiento de reglas de negocio, clínicas o legales. | Error de conexión BD al guardar servicios (DEF-001), formulario desconectado de su lógica (DEF-003), edición vacía (DEF-004), registro sin Términos Ley 1581 (DEF-006) o ausencia inicial de fecha de nacimiento y alergias en registro de paciente (DEF-008). |
| Media | Fallo parcial que afecta flujos de navegación, usabilidad o consistencia entre vistas, pero permite continuar trabajando mediante ruta alternativa. | Enlaces relativos rotos entre subcarpetas de servicios (DEF-002) o contraste oscuro abrupto en pie de página / ausencia de traductor global (DEF-007). |
| Baja | Anomalía visual o estética menor sin impacto en la lógica de negocio ni en la persistencia de datos. | Ícono faltante en una tarjeta del catálogo público por uso de clase Pro en lugar de Free (DEF-005). |


## 2.2 Escala de Prioridad de Negocio

| Nivel de Prioridad | Criterio de Urgencia para el Negocio | Tratamiento en el Equipo |
| --- | --- | --- |
| Alta (P1 — Inmediata) | Impide el flujo clínico/administrativo diario de pacientes, recepción u odontólogos o compromete requisitos obligatorios del proyecto. | Corrección obligatoria antes de avanzar de sprint o presentar entrega. |
| Media (P2 — Planificada) | Afecta la experiencia de navegación, estética corporativa o utilidades complementarias sin detener las citas clínicas. | Se programa su resolución dentro del mismo ciclo de entregas. |
| Baja (P3 — Evolutiva) | Mejora cosmética menor o ajuste no urgente que puede agruparse en el mantenimiento regular. | Se atiende tras estabilizar los tickets P1 y P2. |


## 2.3 Matriz de Triaje Aplicada al Proyecto

| Severidad (Impacto Técnico) | Prioridad (Urgencia Negocio) | Decisión de Triaje | Tickets Asociados en DENTIX |
| --- | --- | --- | --- |
| Alta | Alta (P1) | Atención inmediata / Paro de módulo (Bloqueante) | DEF-001, DEF-003, DEF-004, DEF-006, DEF-008 |
| Media | Alta / Media (P2) | Corrección prioritaria en la iteración | DEF-002, DEF-007 |
| Alta | Baja (P3) | Fallo técnico en función secundaria poco usada | — (Sin casos abiertos) |
| Baja | Media / Baja (P2-P3) | Corrección visual o de detalle en interfaz | DEF-005 |













# 3. Log Detallado de Errores y Defectos Detectados (DEF-001 a DEF-008)


## 3.1 Resumen General del Registro Histórico de Defectos

A continuación se consolida el registro histórico completo de defectos detectados y resueltos durante el desarrollo del proyecto DENTIX —integrando los 3 incidentes tempranos iniciales documentados al comienzo del desarrollo (`DEF-001`: conexión con BD en servicios, `DEF-002`: enlaces y rutas rotas en vistas de servicio y `DEF-003`: formulario sin comunicación con la lógica PHP) junto con los hallazgos funcionales, clínicos, legales y visuales resueltos hasta consolidar la web 100 % funcional (`DEF-004` a `DEF-008`)— con su respectiva causa raíz y medida preventiva para evitar su repetición:

| ID | Módulo Afectado | Título del Defecto / Bug | Severidad | Prioridad | Estado |
| --- | --- | --- | --- | --- | --- |
| DEF-001 | Conexión y Servicios | Error de conexión con PostgreSQL al intentar guardar servicio | Alta | Alta (P1) | Cerrado |
| DEF-003 | Servicios (HTML ↔ PHP) | Formulario de servicio sin comunicación con el archivo de lógica | Alta | Alta (P1) | Cerrado |
| DEF-004 | Recepción (Edición y Citas) | Formularios de edición vacíos y falta de interfaz de cancelación con motivo | Alta | Alta (P1) | Cerrado |
| DEF-006 | Registro Paciente / Legal | Validación débil de nombre, regla de 4 días en cláusula y omisión de lectura | Alta | Alta (P1) | Cerrado |
| DEF-008 | Registro Paciente / Info Clínica | Omisión inicial de captura de Fecha de Nacimiento y Alergias Médicas al registrarse | Alta | Alta (P1) | Cerrado |
| DEF-002 | Vistas de Servicio | Enlaces y rutas relativas rotas al navegar entre archivos y carpetas | Media | Alta (P1) | Cerrado |
| DEF-007 | Interfaz Global y Footer | Contraste oscuro abrupto en pie, botón redundante y falta de i18n/USD | Media | Media (P2) | Cerrado |
| DEF-005 | Catálogo Público | Ícono visual faltante en tarjeta de Blanqueamiento / Limpieza dental | Baja | Media (P2) | Cerrado |


## 3.2 Defectos de Severidad Alta y Prioridad Alta (P1 — Críticos Operativos)

| ID | Módulo | Descripción y Reproducción | Triaje y Estado | Causa Raíz, Acción Correctiva y Preventiva |
| --- | --- | --- | --- | --- |
| DEF-001 | Conexión y Servicios | Error de conexión con BD PostgreSQL al intentar guardar un servicio. • Pasos de reproducción: 1) Iniciar Apache (XAMPP/LAMPP). 2) Abrir el formulario de servicios. 3) Diligenciar los campos y pulsar Guardar. • Esperado vs. Obtenido: Se esperaba registrar el servicio en la tabla servicio; se obtuvo pantalla de error de conexión PDO. | Severidad: Alta Prioridad: Alta (P1) Responsable: Equipo Backend Estado: Cerrado | • Causa Raíz: Credenciales/puerto incorrectos en conexión local, extensión PHP pdo_pgsql no habilitada y ausencia de un archivo único estandarizado. • Corrección Inmediata: Ajustar host, puerto (5432), base de datos y habilitar extension=pdo_pgsql en php.ini. • Acción Correctiva: Centralizar la conexión PDO en un único archivo (config/database.php) usando plantilla config/database.example.php. • Acción Preventiva: Documentar el entorno y validar la conexión antes de codificar módulos CRUD. |
| DEF-003 | Servicios (Formulario ↔ Lógica) | Formulario de creación/edición de servicios sin comunicación con el controlador app/logica_servicio.php. • Pasos de reproducción: 1) Abrir form_servicio.php. 2) Completar datos y enviar por POST. • Esperado vs. Obtenido: Se esperaba procesar la inserción en BD; la lógica no recibía los datos esperados. | Severidad: Alta Prioridad: Alta (P1) Responsable: Desarrollador Full-Stack Estado: Cerrado | • Causa Raíz: Nombres de atributos 'name' en los inputs HTML distintos a las claves leídas en $_POST dentro del archivo PHP. • Corrección Inmediata / Acción Correctiva: Igualar estrictamente los atributos 'name' del formulario HTML con las variables procesadas en logica_servicio.php. • Acción Preventiva: Acordar convenciones de nomenclatura estrictas entre frontend y backend y probar cada formulario de forma aislada antes de integrarlo. |
| DEF-004 | Recepción (Edición / Citas / Servicios) | Formularios de edición (odontólogo, paciente, cita) abrían con campos vacíos al ingresar por ?id= y ausencia de modal UI para cancelación con motivo (RN-06) y eliminación de servicios. • Esperado vs. Obtenido: Al editar o cancelar desde las tablas, los formularios debían precargar los datos y exigir motivo de cancelación. | Severidad: Alta Prioridad: Alta (P1) Responsable: Desarrollador / QA Estado: Cerrado | • Causa Raíz: Falta de lectura directa de parámetros GET (?id=) en las vistas de edición y controles pendientes en lista_citas.php / lista_servicios.php. • Acción Correctiva: Implementar precarga PDO directa por ID en editar_odontologo.php, editar_paciente.php y editar_cita.php, añadir modal #cancelarCitaModal obligatorio y botón eliminar seguro en lista_servicios.php. • Acción Preventiva: Exigir pruebas de flujo completo (Crear → Listar → Editar → Cancelar/Eliminar) antes de aprobar cada módulo. |
| DEF-006 | Registro de Paciente / Cumplimiento Legal | Ausencia de validación estricta Title Case en nombres, inconsistencia de regla de 4 días en cláusula legal y omisión de lectura en Términos (Ley 1581). • Pasos de reproducción: Intentar confirmar registro sin desplegar 'Ver más...' o ingresar nombres todo en mayúsculas/minúsculas. | Severidad: Alta Prioridad: Alta (P1) Responsable: QA / Desarrollador Estado: Cerrado | • Causa Raíz: Validación inicial laxa en cliente/BD y badges que distraían sin bloquear por sorpresa al usuario que omitía leer las 5 cláusulas. • Acción Correctiva: Agregar restricción CHECK ck_paciente_nombre_formato en PostgreSQL + auto-formateo JS/PHP; unificar cláusula 4 a 4 días completos (RN-07); y programar alerta sorpresa #termsLockBanner exigiendo desplegar los 5 'Ver más...' (#termsModal). • Acción Preventiva: Sincronizar siempre reglas legales y de dominio entre JS, PHP y PostgreSQL. |
| DEF-008 | Registro de Paciente / Información Clínica | Omisión de captura de Fecha de Nacimiento y Alergias Médicas en la versión inicial del formulario de registro de paciente (form_paciente.php). • Tipo de defecto: Error de información / especificación clínica incompleta. • Pasos de reproducción: 1) Abrir 'Crear cuenta de paciente' en la primera versión del formulario. 2) Revisar los campos solicitados al usuario. • Esperado vs. Obtenido: Se esperaba capturar la fecha de nacimiento (fecha_nac) y las alergias/alertas médicas del paciente desde su registro inicial; anteriormente solo pedía nombre, cédula, teléfono, correo y contraseña, impidiendo al paciente reportar alergias críticas (ej. penicilina, anestesia local, látex). | Severidad: Alta Prioridad: Alta (P1) Responsable: Equipo Full-Stack / QA Estado: Cerrado | • Causa Raíz: Omisión durante el diseño inicial de los campos clínicos indispensables para la ficha odontológica y la atención segura en consulta. • Acción Correctiva: Se incorporaron las columnas fecha_nac (con restricción ck_paciente_fecha_nac <= CURRENT_DATE) y alergias en PostgreSQL; se añadió en form_paciente.php la sección 'INFORMACIÓN CLÍNICA IMPORTANTE' (selector categorizado de alergias, campo descriptivo y chips interactivos: Ninguna, Penicilina, Anestesia local, Ibuprofeno/AINEs, Látex y '+ Escribir otra') junto con el campo Fecha de Nacimiento obligatorio; y se propagó la alerta visual de alergias a perfil_paciente.php, lista_pacientes.php y panel_odontologo.php. • Acción Preventiva: Contrastar todo nuevo formulario contra la ficha clínica completa y el diccionario de datos del SRS antes de dar por cerrado el módulo, para no omitir información crítica del paciente en futuros desarrollos. |


## 3.3 Defectos de Severidad Media y Prioridad Media/Alta (P2 — Integración y Experiencia)

| ID | Módulo | Descripción y Reproducción | Triaje y Estado | Causa Raíz, Acción Correctiva y Preventiva |
| --- | --- | --- | --- | --- |
| DEF-002 | Vistas de Servicio (Navegación) | Enlaces y rutas rotas (Error 404 Not Found) al navegar entre los archivos de view/servicio/, view/cita/ y la raíz del proyecto. • Pasos de reproducción: Hacer clic en botones de volver o enlaces de acción desde subcarpetas de view/. | Severidad: Media Prioridad: Alta (P1) Responsable: Equipo Frontend / PHP Estado: Cerrado | • Causa Raíz: Rutas relativas mal calculadas (omisión o exceso de '../') al cruzar entre carpetas de vistas y controladores en app/. • Acción Correctiva: Editar y reparar todas las rutas relativas entre index.php, view/*/*.php y app/*.php. • Acción Preventiva: Definir desde el inicio la estructura formal de directorios del proyecto y una convención única de referencias relativas. |
| DEF-007 | Interfaz Global, Pie de Página y Multi-idioma | Contraste oscuro abrupto en el pie de página de index.php, botón redundante 'Volver al inicio' y ausencia de traducción automática al inglés o conversión a dólares (USD). • Esperado vs. Obtenido: La página debía ofrecer estética suave en tono aguamarina y permitir cambiar toda la web a Inglés y USD. | Severidad: Media Prioridad: Media (P2) Responsable: Desarrollador UI/JS Estado: Cerrado | • Causa Raíz: Diseño inicial estático de un solo idioma/moneda y banda superior oscura heredada. • Acción Correctiva: Rediseñar .mega-footer en blanco menta y aguamarina suave eliminando el botón redundante e implementar assests/js/dentix-i18n.js (WeakMap + MutationObserver + tasa 1 USD = $4.000 COP) en index.php y las 22 vistas. • Acción Preventiva: Centralizar traducciones y divisas en un motor único extensible. |


## 3.4 Defectos de Severidad Baja y Prioridad Media (P3 — Presentación Visual)

| ID | Módulo | Descripción y Reproducción | Triaje y Estado | Causa Raíz, Acción Correctiva y Preventiva |
| --- | --- | --- | --- | --- |
| DEF-005 | Catálogo Público (catalogo_publico.php) | Cuadro de ícono vacío en la tarjeta de Limpieza dental / Blanqueamiento dental en el catálogo público. • Pasos de reproducción: Abrir view/servicio/catalogo_publico.php y observar el recuadro superior izquierdo de las tarjetas. | Severidad: Baja Prioridad: Media (P2) Responsable: Desarrollador Frontend Estado: Cerrado | • Causa Raíz: Uso de clases exclusivas de pago/Pro (fa-sparkles, fa-shield-tooth) incompatibles con el CDN gratuito de Font Awesome 6.4 Free. • Acción Correctiva: Reemplazar las clases por equivalentes oficiales de Font Awesome Free (fa-hand-sparkles, fa-shield-halved y fa-tooth). • Acción Preventiva: Verificar todo ícono nuevo contra la versión instalada/CDN antes de publicar cambios visuales. |








# 4. Análisis Estratégico de Causa Raíz y Telemetría QA


## 4.1 Técnica de los 5 Porqués (Caso DEF-001) y Síntesis Diagnóstica

Aplicando la técnica de los 5 Porqués sobre el incidente crítico fundacional DEF-001 se identificó la verdadera causa raíz organizacional:

| Nivel de Análisis | Pregunta Diagnóstica | Hallazgo Obtenido |
| --- | --- | --- |
| Porqué 1 | ¿Por qué no se guardó el servicio en la base de datos? | Porque no existía conexión activa entre PHP y PostgreSQL. |
| Porqué 2 | ¿Por qué no hubo conexión? | Porque las credenciales, el puerto y la extensión pdo_pgsql estaban mal configurados. |
| Porqué 3 | ¿Por qué estaban mal configurados? | Porque cada integrante del equipo configuró su entorno local por su cuenta. |
| Porqué 4 | ¿Por qué lo hicieron por separado? | Porque no se había definido ni compartido un archivo único estándar de conexión. |
| Porqué 5 (Causa Raíz) | ¿Por qué no se definió ese archivo único? | CAUSA RAÍZ: Falta de estandarización inicial del entorno, nomenclatura y acuerdos técnicos de equipo. |

Patrón Oculto (Síntesis Diagnóstica): Al cruzar DEF-001 (Base de datos caída/conexión), DEF-002 (Enlaces rotos por rutas) y DEF-003 (Variables perdidas entre HTML y PHP), todos convergían en una misma raíz: la falta de estandarización y acuerdos de equipo. La solución definitiva no fue solo parchar código, sino establecer config/database.php + database.example.php, arquitectura de carpetas unificada y convenciones de nombres compartidas.


## 4.2 Vectores de Causalidad (Ishikawa), Pareto (80/20) y Métricas QA

Desde el diagrama de Ishikawa se controlaron los cinco vectores: Personas (comunicación y estándares compartidos), Métodos (revisión cruzada y pruebas aisladas de formularios), Herramientas (XAMPP/LAMPP + PostgreSQL + extensiones verificadas), Requisitos (SRS Maestro + PAC de 56 casos) y Entorno (unificación en /opt/lampp/htdocs/DENTIX). Bajo el principio de Pareto (80/20), atacar el 20 % vital (centralización de BD, convenciones HTML↔PHP y rutas) eliminó más del 80 % de los bloqueos operativos.

| Métrica de Telemetría QA | Resultado Medido en DENTIX |
| --- | --- |
| Total de Defectos Documentados (DEF-001 a DEF-008) | 8 expedientes técnicos formales (3 fundacionales iniciales + 5 de consolidación clínica, legal y funcional). |
| Distribución por Severidad | Alta: 5 (62,5 %) · Media: 2 (25,0 %) · Baja: 1 (12,5 %) · Crítica abierta: 0 (0 %). |
| Distribución por Prioridad | Alta (P1): 6 tickets · Media (P2): 2 tickets · Baja (P3): 0 tickets. |
| Eficiencia de Resolución | 100 % corregidos, verificados mediante retest y en estado Cerrado (0 defectos reabiertos). |
| Cobertura de Validación Cruzada (Regresión PAC) | 56 de 56 casos de prueba (TC-01 a TC-56) aprobados sin fallos. |










# 5. Banco de Sugerencias y Mejoras Evolutivas Futuras (SUG-01 a SUG-10)


## 5.1 Matriz Priorizada de Ideas y Sugerencias para Futuras Integraciones

Además de corregir los errores detectados, durante el desarrollo y la evaluación del proyecto surgieron ideas estratégicas de alto valor para seguir evolucionando DENTIX hacia una plataforma clínica comercial de nivel completo. Cada sugerencia se clasifica por su Prioridad de Negocio, Complejidad Técnica y Módulo Destino:

| Código | Sugerencia / Idea Evolutiva | Módulo Destino | Descripción y Valor Agregado | Prioridad · Complejidad · Estado |
| --- | --- | --- | --- | --- |
| SUG-01 | Notificaciones automáticas por Correo Electrónico y WhatsApp | Citas / Comunicación Externa | Enviar confirmación inmediata cuando recepción asigne odontólogo, recordatorios automáticos 24 horas antes de la cita y aviso inmediato si una cita es cancelada con su motivo. | Prioridad: Alta | Complejidad: Media | Propuesta / Backlog |
| SUG-02 | Recuperación de contraseña olvidada vía enlace seguro temporal | Autenticación (Todos los roles) | Incorporar opción '¿Olvidaste tu contraseña?' con token criptográfico de un solo uso y expiración de 15 minutos enviado al correo del paciente, odontólogo o recepcionista. | Prioridad: Alta | Complejidad: Media | Propuesta / Backlog |
| SUG-03 | Exportación PDF de comprobante de cita, consentimiento Ley 1581 e historia clínica | Portal Paciente / Odontólogo | Permitir descargar en PDF el comprobante de agendamiento, el acta de consentimiento aceptado en #termsModal y el certificado de atención clínica firmado digitalmente. | Prioridad: Alta | Complejidad: Baja | Propuesta / Backlog |
| SUG-04 | Odontograma clínico interactivo por pieza dental | Panel del Odontólogo | Integrar un gráfico dental interactivo (32 piezas) en el modal 'Finalizar cita' para registrar hallazgos por diente y superficie dentro de detalle_cita. | Prioridad: Alta | Complejidad: Alta | Propuesta / Backlog |
| SUG-05 | Pasarela de pagos en línea (PSE / Nequi / Tarjeta) y facturación | Servicios / Pagos | Permitir al paciente abonar o pagar el valor del tratamiento (COP o USD) al confirmar la cita generando recibo electrónico y estado de recaudo en reportes.php. | Prioridad: Media | Complejidad: Alta | Propuesta / Backlog |
| SUG-06 | Sincronización automática de TRM en tiempo real para el conversor COP ↔ USD | Motor Global (dentix-i18n.js) | Consultar diariamente la Tasa Representativa del Mercado (TRM) vía API pública cacheada en sesión, manteniendo 1 USD = $4.000 COP como respaldo automático sin conexión. | Prioridad: Media | Complejidad: Baja | Propuesta / Backlog |
| SUG-07 | Vista de calendario visual semanal y mensual en Recepción y Odontología | Agenda y Citas | Complementar las tablas de citas con un calendario gráfico interactivo que resalte bloques libres/ocupados y valide automáticamente RN-07 y RN-08. | Prioridad: Media | Complejidad: Media | Propuesta / Backlog |
| SUG-08 | Bitácora inmutable de auditoría (tabla audit_log) en PostgreSQL | Seguridad / Base de Datos | Registrar automáticamente mediante triggers quién creó, confirmó, reprogramó o canceló cada cita o expediente médico, con IP, rol y marca temporal. | Prioridad: Media | Complejidad: Baja | Propuesta / Backlog |
| SUG-09 | Encuesta de satisfacción del paciente (1 a 5 estrellas) post-consulta | Paciente / Reportes | Habilitar en perfil_paciente.php la calificación de la atención una vez la cita pasa a 'completada' y mostrar el promedio de calidad en reportes.php. | Prioridad: Baja | Complejidad: Baja | Propuesta / Backlog |
| SUG-10 | Modo oscuro clínico opcional y aplicación web progresiva (PWA) | Experiencia de Usuario (UI/UX) | Agregar conmutador de tema claro/oscuro junto al selector ES/EN y manifiesto PWA para instalar DENTIX en teléfonos y tabletas de consultorio. | Prioridad: Baja | Complejidad: Media | Propuesta / Backlog |


## 5.2 Detalle Técnico y Valor de Negocio de las Sugerencias Prioritarias

•  Prioridad Inmediata (SUG-01, SUG-02, SUG-03): Son las de mayor retorno operativo con baja/media complejidad. Integrar PHPMailer/SMTP y generación PDF elevaría la autonomía del paciente (recuperación de clave, recordatorios anti-inasistencia para reducir estados 'no_show' y soporte documental físico/legal de la Ley 1581).

•  Evolución Clínica Especializada (SUG-04, SUG-07, SUG-08): El odontograma gráfico (SUG-04) y la bitácora audit_log (SUG-08) profundizan el alcance de la historia clínica odontológica conforme a la Resolución 1995 de 1999 sin alterar la arquitectura MVC/PDO ni las reglas del trigger trg_validar_espaciado_citas.

•  Expansión Financiera y Experiencia (SUG-05, SUG-06, SUG-09, SUG-10): Aprovechan la base ya construida del catálogo COP/USD (dentix-i18n.js) y del módulo de reportes gerenciales (reportes.php) para convertir DENTIX en un ecosistema comercial completo.









# 6. Protocolos de Excelencia en QA y Conclusión


## 6.1 Protocolos de Reporte, Validación Dual y Cierre

•  Protocolo 1 — Aislamiento: Un defecto = Un reporte. Nunca combinar múltiples anomalías en un mismo ticket para mantener clara la causa raíz.

•  Protocolo 2 — Claridad Forense: Todo hallazgo debe redactarse con pasos reproducibles y contraste entre resultado esperado y obtenido, respaldado por logs o capturas.

•  Protocolo 3 — Validación Dual: Todo defecto corregido exige prueba de confirmación (retest) sobre el ticket puntual y prueba de regresión sobre el módulo completo.

•  Protocolo 4 — Estandarización Continua: Toda nueva idea o mejora futura pasa primero por evaluación en el Banco de Sugerencias (Sección 5) antes de modificar la base de datos o las vistas.


## 6.2 Conclusión General

El registro estratégico de errores no es una lista de reproches, sino la memoria técnica evolutiva que permite documentar las fallas reales, atacar su causa raíz y evitar que se repitan en futuras entregas. Gracias a este seguimiento se superaron tanto los tres incidentes iniciales de infraestructura y comunicación (`DEF-001`, `DEF-002` y `DEF-003`) como los hallazgos clínicos, de captura de información (fecha de nacimiento y alergias en `DEF-008`), cumplimiento legal (`DEF-006`) e interfaz global (`DEF-007`), consolidando una aplicación web estable, segura, bilingüe (ES/EN), multi-moneda (COP/USD) y alineada al 100 % con sus requisitos.

Con los 8 defectos documentados y cerrados (0 % de incidencias abiertas) y una hoja de ruta clara de 10 sugerencias evolutivas (`SUG-01` a `SUG-10`), el proyecto DENTIX cuenta con trazabilidad QA completa para su sustentación actual y su crecimiento futuro.
