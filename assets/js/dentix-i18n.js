/* =========================================================
   DENTIX · Motor Global de Traducción ES <-> EN (dentix-i18n.js)
   Traduce automáticamente todas las interfaces, formularios,
   tablas, modales, alertas y atributos en tiempo real.
   ========================================================= */

(() => {
    'use strict';

    const STORAGE_KEY = 'dentix_lang';
    const COOKIE_NAME = 'dentix_lang';
    const CURRENCY_STORAGE_KEY = 'dentix_currency';
    const CURRENCY_COOKIE_NAME = 'dentix_currency';
    const COP_PER_USD = 4000;

    const DICT = {
        // --- Idioma y controles del pie ---
        'Colombia · 08:00 a 19:30': 'Colombia · 08:00 AM to 07:30 PM',
        'Colombia · Clínica abierta (08:00–19:30)': 'Colombia · Clinic Open (08:00–19:30)',
        'Colombia · Agenda online 24/7': 'Colombia · 24/7 Online Booking',
        'Peso colombiano': 'Colombian Peso',
        'Datos protegidos': 'Protected data',
        'Condiciones de uso': 'Terms of Use',
        'Aviso de privacidad (Ley 1581)': 'Privacy Notice (Law 1581)',
        'Privacidad de Datos de Salud': 'Health Data Privacy',
        'Reserva Historia Clínica (Ley 35)': 'Medical Record Confidentiality (Law 35)',
        '© 2026 DENTIX Clínica Odontológica · Sistema Integral de Gestión de Citas · Colombia':
            '© 2026 DENTIX Dental Clinic · Comprehensive Appointment Management System · Colombia',
        'Condiciones de Uso del Portal DENTIX': 'DENTIX Portal Terms of Use',
        'El agendamiento funciona en bloques de 30 minutos (08:00 a 19:30), con un máximo de una cita activa por día y al menos 4 horas entre citas activas. Las solicitudes en línea quedan en estado Pendiente hasta su confirmación por Recepción.':
            'Scheduling works in 30-minute slots (08:00 AM to 07:30 PM), with a maximum of one active appointment per day and at least 4 full days between active appointments. Online requests remain Pending until confirmed by Reception.',
        'El agendamiento funciona en bloques de 30 minutos (08:00 a 19:30), con un máximo de una cita activa por día y al menos 4 días completos entre citas activas (RN-07). Las solicitudes en línea quedan en estado Pendiente hasta su confirmación por Recepción.':
            'Scheduling works in 30-minute slots (08:00 AM to 07:30 PM), with a maximum of one active appointment per day and at least 4 full days between active appointments (RN-07). Online requests remain Pending until confirmed by Reception.',
        'Aviso de Privacidad · Ley Estatutaria 1581 de 2012': 'Privacy Notice · Statutory Law 1581 of 2012',
        'DENTIX recolecta tus datos personales (nombre, cédula, fecha de nacimiento, teléfono y correo) exclusivamente para gestionar tu atención odontológica, confirmar citas y cumplir tus derechos ARCO de acceso, rectificación y supresión.':
            'DENTIX collects your personal data (name, ID number, birth date, phone, and email) exclusively to manage your dental care, confirm appointments, and fulfill your ARCO rights of access, rectification, and deletion.',
        'Privacidad de Datos Sensibles de Salud': 'Sensitive Health Data Privacy',
        'Tus alertas de alergias médicas, diagnósticos y procedimientos realizados se tratan bajo acceso restringido por roles: solo el equipo odontológico autorizado y el titular pueden consultar la información clínica pertinente.':
            'Your medical allergy alerts, diagnoses, and performed procedures are handled under role-restricted access: only authorized dental staff and the account holder can view relevant clinical information.',
        'Reserva Legal de la Historia Clínica · Ley 35 de 1989': 'Legal Confidentiality of Medical Records · Law 35 of 1989',
        'De acuerdo con el Código de Ética del Odontólogo Colombiano y la Resolución 1995 de 1999, la historia clínica es un documento privado sometido a reserva y protegido mediante integridad referencial en nuestra base de datos.':
            'In accordance with the Colombian Dentist Code of Ethics and Resolution 1995 of 1999, the dental record is a private, confidential document protected through referential integrity in our database.',
        'Plataforma configurada en Español (Colombia) para pacientes, odontólogos y personal de recepción de DENTIX.':
            'Platform configured for DENTIX patients, dentists, and reception staff.',
        'Todas las tarifas de referencia del catálogo de tratamientos se muestran en Pesos Colombianos (COP) y se confirman tras la valoración clínica.':
            'All reference fees in the treatment catalog are displayed in Colombian Pesos (COP) and confirmed after clinical evaluation.',
        'Atención presencial activa hoy de 08:00 a. m. a 07:30 p. m. en bloques de 30 minutos. También puedes crear tu cuenta y solicitar tu cita en línea en segundos.':
            'In-person clinic care is open today from 08:00 AM to 07:30 PM in 30-minute slots. You can also request your appointment online in seconds.',
        'Atención presencial activa hoy de 08:00 a. m. a 07:30 p. m. en bloques de 30 minutos. También puedes solicitar tu cita en línea en segundos.':
            'In-person clinic care is open today from 08:00 AM to 07:30 PM in 30-minute slots. You can also request your appointment online in seconds.',
        'En este momento nuestro horario presencial (08:00 a. m. – 07:30 p. m.) ha cerrado, pero puedes crear tu cuenta y enviar solicitudes de cita en línea las 24 horas.':
            'Our in-person hours (08:00 AM – 07:30 PM) are currently closed, but you can create your account and submit online appointment requests 24/7.',

        // --- Mega Footer Directory ---
        'Portal del Paciente': 'Patient Portal',
        'Portal del paciente': 'Patient portal',
        'Consulta tus citas, estado clínico y alertas': 'Check your appointments, clinical status, and alerts',
        'Registro de Pacientes': 'Patient Registration',
        'Crea tu cuenta en línea de forma segura': 'Create your online account securely',
        'Portal Odontológico': 'Dental Staff Portal',
        'Agenda médica y cierre de historias clínicas': 'Clinical schedule and medical record completion',
        'Panel de Recepción': 'Reception Dashboard',
        'Confirmación de agenda, equipo y pacientes': 'Schedule confirmation, staff, and patients',
        'Manual Interactivo': 'Interactive Manual',
        'Guía rápida para pacientes y personal': 'Quick guide for patients and staff',
        'Catálogo de Servicios': 'Service Catalog',
        'Tratamientos, tiempos y tarifas COP': 'Treatments, durations, and COP pricing',
        'Limpieza Dental': 'Dental Cleaning',
        'Limpieza dental': 'Dental Cleaning',
        'Profilaxis profunda y prevención periodontal': 'Deep prophylaxis and periodontal prevention',
        'Blanqueamiento LED': 'LED Teeth Whitening',
        'Blanqueamiento dental': 'Teeth Whitening',
        'Aclarado dental estético y seguro': 'Safe, advanced cosmetic teeth brightening',
        'Ortodoncia Correctiva': 'Corrective Orthodontics',
        'Ortodoncia correctiva': 'Corrective Orthodontics',
        'Brackets y alineadores para tu mordida': 'Braces and aligners for your bite',
        'Calza y Restauración': 'Dental Filling & Restoration',
        'Calza (resina)': 'Dental Filling (Resin)',
        'Resina de alta resistencia y estética oral': 'High-strength resin and natural aesthetics',
        'Consulta General': 'General Consultation',
        'Consulta general': 'General Consultation',
        'Valoración diagnóstica y plan de cuidado': 'Diagnostic assessment and care plan',
        'Agenda en Bloques': 'Slot Scheduling',
        'Citas de 30 minutos entre 08:00 y 19:30': '30-minute appointments between 08:00 AM and 07:30 PM',
        'Alertas de Alergias': 'Allergy Alerts',
        'Control preventivo antes de cada atención': 'Preventive check before every procedure',
        'Reportes Gerenciales': 'Management Reports',
        'Indicadores de ingresos y demanda clínica': 'Revenue indicators and clinical demand',
        'Dudas y Reglas': 'FAQs & Rules',
        'Estados de cita, límites y reprogramación': 'Appointment statuses, limits, and rescheduling',

        // --- Títulos de página (<title>) ---
        'DENTIX | Clínica odontológica': 'DENTIX | Dental Clinic',
        'Tratamientos odontológicos | DENTIX': 'Dental Treatments | DENTIX',
        'Iniciar sesión - DENTIX': 'Sign In - DENTIX',
        'Registrar Nuevo Paciente - DENTIX': 'Register New Patient - DENTIX',
        'Editar Paciente - DENTIX': 'Edit Patient - DENTIX',
        'Mi perfil - DENTIX': 'My Profile - DENTIX',
        'Agendar cita - DENTIX': 'Book Appointment - DENTIX',
        'Pacientes - SCO': 'Patients - DENTIX',
        'Odontólogos - DENTIX': 'Dentists - DENTIX',
        'Registrar Odontólogo - DENTIX': 'Register Dentist - DENTIX',
        'Editar Odontólogo - DENTIX': 'Edit Dentist - DENTIX',
        'Mi Agenda Médica - DENTIX': 'My Clinical Schedule - DENTIX',
        'Historial de Citas Finalizadas - DENTIX': 'Completed Appointments History - DENTIX',
        'Citas - SCO': 'Appointments - DENTIX',
        'Agendar Nueva Cita - DENTIX': 'Schedule New Appointment - DENTIX',
        'Editar Cita - DENTIX': 'Edit Appointment - DENTIX',
        'Servicios - SCO': 'Services - DENTIX',
        'Registrar Servicio - SCO': 'Register Service - DENTIX',
        'Editar Servicio - DENTIX': 'Edit Service - DENTIX',
        'Acceso Administrador - DENTIX': 'Administrator Login - DENTIX',
        'Acceso Cuerpo Médico - DENTIX': 'Medical Staff Login - DENTIX',
        'Registro Recepción - DENTIX': 'Receptionist Registration - DENTIX',
        'Reportes Clínicos - DENTIX': 'Clinical Reports - DENTIX',

        // --- Landing page (index.php) ---
        'Clínica odontológica': 'Dental clinic',
        'Servicios': 'Services',
        'Agendar cita': 'Book appointment',
        'Manual': 'Manual',
        'Odontólogos': 'Dentists',
        'Administrador': 'Administrator',
        'Iniciar sesión': 'Sign in',
        'Iniciar Sesión': 'Sign In',
        'Inicia sesión': 'Sign in',
        'Cuidamos la salud de tu sonrisa': 'We care for your smile',
        'Tu sonrisa merece': 'Your smile deserves',
        'atención de calidad': 'quality care',
        'y confianza': 'and trust',
        'En DENTIX te acompañamos en el cuidado de tu salud oral. Solicita tu cita en línea y nuestro equipo de recepción te ayudará con la confirmación y asignación del profesional.':
            'At DENTIX we guide you in caring for your oral health. Request your appointment online and our reception team will help confirm and assign your specialist.',
        'Solicitar una cita': 'Request an appointment',
        'Crear cuenta de paciente': 'Create patient account',
        'Atención odontológica': 'Dental care',
        'Profesional y cercana': 'Professional & caring',
        'Cuidado odontológico': 'Dental care',
        'Cuidamos tu sonrisa': 'We protect your smile',
        'Compromiso con tu salud oral': 'Committed to your oral health',
        'Tu espacio personal': 'Your personal space',
        'Citas y seguimiento': 'Appointments & tracking',
        'Estamos aquí para cuidarte': 'We are here to care for you',
        'Tu atención,': 'Your care,',
        'en un solo lugar': 'in one place',
        'Un equipo odontológico comprometido con brindarte una atención respetuosa, clara y enfocada en tu bienestar.':
            'A dental team committed to providing you with respectful, clear care focused on your well-being.',
        'Conoce nuestros tratamientos, consulta la información de atención y gestiona tus citas.':
            'Explore our treatments, view care details, and manage your appointments.',
        'Nuestros tratamientos': 'Our treatments',
        'Conocer tratamientos': 'Explore treatments',
        'Portal Especialistas': 'Specialists Portal',
        'Nuestro equipo': 'Our team',
        'Agenda Clínica': 'Clinical Schedule',
        'Horarios y Citas': 'Schedules & Appointments',
        'Solicita tu cita de forma sencilla. Nuestro equipo de recepción se pondrá en contacto para confirmar el horario y asignarte el profesional indicado.':
            'Request your appointment easily. Our reception team will reach out to confirm the time and assign the right specialist.',
        'Atención coordinada': 'Coordinated care',
        'Recepción confirma contigo': 'Reception confirms with you',
        'Es momento de cuidar': 'It is time to care for',
        'tu sonrisa': 'your smile',
        'Nuestros servicios': 'Our services',
        'Manual de usuario': 'User manual',

        // --- Manual Interactivo Modal ---
        'Guía interactiva de uso': 'Interactive user guide',
        'Manual de Usuario': 'User Manual',
        'Aprende cómo navegar por la plataforma, gestionar tus citas odontológicas y aprovechar cada módulo paso a paso.':
            'Learn how to navigate the platform, manage your dental appointments, and use each module step by step.',
        'Inicio Rápido': 'Quick Start',
        'Soy Paciente': 'I am a Patient',
        'Soy Odontólogo': 'I am a Dentist',
        'Recepción / Admin': 'Reception / Admin',
        'Dudas y Estados': 'FAQs & Statuses',
        '¿Cómo funciona DENTIX?': 'How does DENTIX work?',
        'DENTIX conecta a pacientes, recepción y odontólogos en un solo lugar para que solicitar, confirmar y atender citas odontológicas sea rápido, claro y seguro.':
            'DENTIX connects patients, reception, and dentists in one place so requesting, confirming, and completing dental appointments is fast, clear, and secure.',
        'Recorrido básico en 4 pasos': 'Basic 4-step walkthrough',
        'Consulta los Servicios': 'Browse Services',
        'Haz clic en': 'Click on',
        'o en': 'or on',
        'para ver cada procedimiento, su duración estimada en minutos y su tarifa de referencia.':
            'to view each procedure, its estimated duration in minutes, and its reference fee.',
        'Crea tu Cuenta o Ingresa': 'Create Your Account or Sign In',
        'Si es tu primera vez, pulsa': 'If it is your first time, click',
        '. Si ya estás registrado, usa el botón': '. If you are already registered, use the',
        'con tu número de cédula y contraseña.': 'button with your ID number and password.',
        'Solicita tu Cita': 'Request Your Appointment',
        'Desde tu portal personal selecciona el tratamiento, la fecha y la hora disponible (bloques de 30 minutos entre':
            'From your personal portal select the treatment, date, and available time (30-minute slots between',
        '08:00 a. m.': '08:00 AM',
        '07:30 p. m.': '07:30 PM',
        'Confirmación y Atención': 'Confirmation & Care',
        'Recepción revisará tu solicitud, te asignará el especialista ideal y cambiará tu cita a':
            'Reception will review your request, assign the ideal specialist, and change your appointment to',
        '. Al finalizar la consulta podrás ver tu resumen clínico.': '. Once the visit ends, you can view your clinical summary.',
        'Mapa rápido de botones de la barra superior': 'Quick map of top bar buttons',
        'Acceso directo para': 'Direct access for',
        'Pacientes': 'Patients',
        'con cédula y contraseña.': 'with ID number and password.',
        'Acceso exclusivo al': 'Exclusive access to the',
        'Panel Clínico': 'Clinical Panel',
        'para especialistas de la clínica.': 'for clinic specialists.',
        'Acceso para': 'Access for',
        'Recepcionistas': 'Receptionists',
        'al panel de control de citas, catálogos y reportes.': 'to the appointments, catalogs, and reports dashboard.',
        'Guía para Pacientes': 'Patient Guide',
        'Gestiona tus citas y tu perfil personal': 'Manage your appointments and personal profile',
        'Todo lo que puedes hacer dentro de tu Portal del Paciente de forma autónoma.':
            'Everything you can do independently inside your Patient Portal.',
        '1. Registro por primera vez': '1. First-time registration',
        'Dirígete a': 'Go to',
        ', completa tus datos (nombre en formato propio, número de cédula, fecha de nacimiento, teléfono, correo, alergias médicas y contraseña de 8 a 72 caracteres) y despliega los 5 puntos de':
            ', fill in your details (Proper Case full name, ID number, birth date, phone, email, medical allergies, and an 8–72 character password), and expand all 5',
        'Términos y Condiciones': 'Terms & Conditions',
        'para activar tu cuenta.': 'clauses to activate your account.',
        'y completa tus datos: nombre completo, número de cédula (será tu usuario de acceso), teléfono, correo electrónico, fecha de nacimiento, alergias médicas importantes y una contraseña de mínimo 6 caracteres.':
            'and fill in your details: full name, ID number (your login username), phone, email, birth date, important medical allergies, and a password.',
        '2. Cómo agendar una cita nueva': '2. How to schedule a new appointment',
        'Dentro de tu portal, haz clic en': 'Inside your portal, click',
        'Solicitar nueva cita': 'Request new appointment',
        '. Elige el tratamiento odontológico, selecciona la fecha deseada, escoge un horario en intervalos de 30 minutos (por ejemplo':
            '. Choose the dental treatment, pick your preferred date, choose a time in 30-minute intervals (for example',
        ') y escribe brevemente el motivo de tu consulta.': ') and briefly describe the reason for your visit.',
        '3. Actualizar mis datos o alergias': '3. Update my details or allergies',
        'En tu portal selecciona': 'In your portal select',
        'Editar perfil': 'Edit profile',
        '. Allí podrás mantener actualizados tu teléfono, correo, alergias médicas o cambiar tu contraseña cuando lo necesites.':
            '. There you can keep your phone, email, and medical allergies up to date or change your password whenever you need.',
        'Reglas importantes al agendar tu cita:': 'Important rules when booking your appointment:',
        'El horario de atención clínica es de': 'Clinic care hours are from',
        '08:00 a. m. a 07:30 p. m.': '08:00 AM to 07:30 PM',
        'en bloques exactos de 30 minutos.': 'in exact 30-minute blocks.',
        'Por seguridad y organización, cada paciente puede tener máximo':
            'For safety and organization, each patient may have at most',
        '1 cita activa por día': '1 active appointment per day',
        'Entre dos citas de un mismo paciente deben transcurrir al menos':
            'Between two appointments for the same patient there must be at least',
        '4 días completos': '4 full days',
        'de diferencia (RN-07).': 'in between (RN-07).',
        'Entre dos citas activas de un mismo paciente debe existir un intervalo mínimo de':
            'Between two active appointments for the same patient there must be a minimum gap of',
        '4 horas': '4 hours',
        'Guía para Especialistas': 'Specialists Guide',
        'Uso del Panel Clínico del Odontólogo': 'Using the Dentist Clinical Panel',
        'Consulta tu agenda confirmada y documenta la evolución clínica de cada paciente atendido.':
            'Review your confirmed schedule and document clinical progress for every patient seen.',
        '1. Ingreso al portal médico': '1. Medical portal sign-in',
        'e inicia sesión con tu correo electrónico institucional y contraseña asignada por la clínica.':
            'and sign in with your institutional email address and clinic-assigned password.',
        '2. Revisión de Citas Confirmadas': '2. Reviewing Confirmed Appointments',
        'En': 'In',
        'Mi Agenda': 'My Schedule',
        'verás únicamente los pacientes que recepción ya te asignó, organizados por fecha y hora, junto con sus datos de contacto, cédula y motivo de consulta.':
            'you will see only the patients reception has assigned to you, organized by date and time, along with their contact info, ID, and visit reason.',
        '3. Registro clínico y cierre de consulta': '3. Clinical record and visit completion',
        'Al terminar la atención, completa en la tarjeta del paciente los campos de':
            'When care finishes, complete on the patient card the fields for',
        'fecha/hora o': 'date/time or',
        'Diagnóstico': 'Diagnosis',
        'Procedimiento realizado': 'Procedure performed',
        'Novedades / Nota Clínica': 'Notes / Clinical Remarks',
        ', y pulsa': ', and click',
        'Finalizar cita': 'Complete appointment',
        '. El registro pasará automáticamente a tu': '. The record will automatically move to your',
        'Historial de Finalizadas': 'Completed History',
        'Guía para Recepción y Administración': 'Reception & Administration Guide',
        'Operación integral del Panel Administrativo': 'Full Admin Dashboard Operation',
        'Coordina la agenda de la clínica, administra los catálogos y consulta los reportes estadísticos.':
            'Coordinate clinic scheduling, manage catalogs, and review statistical reports.',
        'Módulo de Citas': 'Appointments Module',
        'asigna un odontólogo disponible a cada solicitud pendiente y pulsa':
            'assign an available dentist to each pending request and click',
        'Confirmar': 'Confirm',
        '. También puedes crear una': '. You can also create a',
        'Nueva Cita': 'New Appointment',
        'o cancelar citas indicando el motivo obligatorio.': 'or cancel appointments with a required reason.',
        'Pacientes y Odontólogos': 'Patients & Dentists',
        'Registra nuevos pacientes u odontólogos, actualiza sus datos de contacto o consultorio y activa/inactiva profesionales cuando no estén disponibles en agenda.':
            'Register new patients or dentists, update their contact or office info, and activate/deactivate professionals when unavailable.',
        'Administración de Tratamientos': 'Treatment Management',
        'administra los tratamientos odontológicos, su descripción pública, duración mínima (desde 15 minutos) y tarifas actualizadas.':
            'manage dental treatments, their public description, minimum duration (from 15 minutes), and updated pricing.',
        'Módulo de Reportes': 'Reports Module',
        ': filtra por fechas y visualiza el paciente con más citas, el servicio más solicitado, el odontólogo que más atiende y los horarios de mayor demanda.':
            ': filter by dates and view top patient by appointments, most requested service, busiest dentist, and peak demand hours.',
        'Significado de los estados de una cita': 'Meaning of appointment statuses',
        'El paciente envió la solicitud de cita y está a la espera de que recepción le asigne un odontólogo y confirme el horario.':
            'The patient submitted the appointment request and is waiting for reception to assign a dentist and confirm the slot.',
        'Recepción ya validó la cita y asignó el odontólogo y consultorio. El paciente debe asistir en la fecha y hora programadas.':
            'Reception has validated the appointment and assigned the dentist and office. The patient should attend at the scheduled date and time.',
        'El odontólogo atendió al paciente y registró el diagnóstico, procedimiento y nota clínica en el historial.':
            'The dentist treated the patient and recorded the diagnosis, procedure, and clinical note in the history.',
        'La cita fue cancelada registrando el motivo correspondiente, liberando el espacio en la agenda médica.':
            'The appointment was cancelled with its reason recorded, freeing up the medical schedule slot.',
        'Preguntas frecuentes': 'Frequently asked questions',
        '¿Necesito crear una cuenta para ver los precios de los tratamientos?':
            'Do I need to create an account to view treatment prices?',
        'No. Puedes consultar libremente todos los tratamientos, su descripción, duración estimada y tarifa de referencia haciendo clic en':
            'No. You can freely browse all treatments, descriptions, estimated duration, and reference pricing by clicking',
        'desde la página principal.': 'from the home page.',
        '¿Por qué no puedo agendar dos citas el mismo día?':
            'Why can I not book two appointments on the same day?',
        'Para garantizar una atención ordenada y disponibilidad justa para todos los usuarios, el sistema permite máximo una cita activa (pendiente o confirmada) por paciente en una misma fecha.':
            'To guarantee orderly care and fair availability for everyone, the system allows at most one active appointment (pending or confirmed) per patient on the same date.',
        '¿Qué hago si olvidé mis datos o necesito reprogramar mi cita?':
            'What if I forgot my details or need to reschedule my appointment?',
        'Comunícate con el equipo de recepción de la clínica; desde el panel administrativo podrán actualizar tus datos o reprogramar la fecha y hora de tu cita inmediatamente.':
            'Contact the clinic reception team; from the admin dashboard they can immediately update your info or reschedule your appointment date and time.',
        'Guía rápida integrada en DENTIX': 'Integrated quick guide in DENTIX',
        'Video Demo': 'Demo Video',
        'Ver video demostrativo': 'Watch demo video',
        'Ver Video Demo': 'Watch Demo Video',
        'Recorrido Audiovisual del Sistema': 'Audiovisual System Tour',
        'Conoce DENTIX': 'See DENTIX',
        'en acción': 'in action',
        'Observa en menos de 4 minutos cómo interactúan el Paciente, la Recepción y el Odontólogo dentro de la plataforma.':
            'Watch in under 4 minutes how the Patient, Reception, and Dentist interact inside the platform.',
        'Capítulos del recorrido:': 'Tour chapters:',
        '00:00 Inicio y Catálogo': '00:00 Home & Catalog',
        '00:45 Portal Paciente (Ley 1581)': '00:45 Patient Portal (Law 1581)',
        '01:45 Recepción y Reportes': '01:45 Reception & Reports',
        '02:30 Panel Odontólogo': '02:30 Dentist Panel',
        'Descargar Video (MP4)': 'Download Video (MP4)',
        'Editar mis datos': 'Edit my info',
        'Editar mi información personal': 'Edit my personal information',
        'Tus cambios se sincronizan automáticamente con recepción y tu odontólogo.':
            'Your changes automatically sync with reception and your dentist.',
        'Nombre completo': 'Full name',
        'Formato Nombre Propio (se permiten conectores como': 'Proper Case format (connectors allowed such as',
        'y apellidos compuestos).': 'and compound last names).',
        'Identificador único de acceso (no editable).': 'Unique login identifier (not editable).',
        'Teléfono de contacto': 'Contact phone',
        'Correo electrónico': 'Email address',
        'Fecha de nacimiento': 'Date of birth',
        'Alergias o alertas médicas': 'Allergies or medical alerts',
        'Detalle adicional o alergia específica (opcional)...': 'Additional detail or specific allergy (optional)...',
        'Seguridad: Cambiar contraseña (Opcional)': 'Security: Change password (Optional)',
        'Nueva contraseña (dejar vacío para mantener)': 'New password (leave blank to keep current)',
        'Confirmar nueva contraseña': 'Confirm new password',
        'Sincronización automática con Recepción': 'Automatic sync with Reception',
        'Guardar cambios': 'Save changes',
        'Sincronización en vivo': 'Live sync',
        'Datos actualizados en vivo': 'Data updated live',
        'Agendamiento exclusivo por el paciente': 'Booking exclusive to the patient',
        'Direccionamiento de solicitudes pendientes y asignación de odontólogo disponible.':
            'Routing of pending requests and assignment of an available dentist.',
        'Módulo de Citas (Direccionamiento)': 'Appointments Module (Routing)',
        'No asistió': 'No-show',
        'La cita fue marcada como inasistencia (No asistió).':
            'The appointment was marked as a no-show.',
        '¿Confirmas marcar esta cita como inasistencia (No asistió)?':
            'Confirm marking this appointment as a no-show?',
        'visualiza las solicitudes agendadas por los pacientes, direcciona cada cita asignando un odontólogo disponible y pulsa':
            'view requests booked by patients, route each appointment by assigning an available dentist, and click',
        'visualiza las solicitudes agendadas por los pacientes, direcciona cada cita asignando un odontólogo cuya especialidad coincida con el servicio solicitado (RN-21) y pulsa':
            'view requests booked by patients, route each appointment by assigning a dentist whose specialty matches the requested service (RN-21), and click',
        '. También puedes registrar': '. You can also record',
        'o': 'or',
        'indicando el motivo obligatorio (sin crear ni editar citas manualmente).':
            'providing the mandatory reason (without creating or editing appointments manually).',
        '-- Seleccionar especialidad --': '-- Select specialty --',
        '-- Seleccionar servicio / especialidad --': '-- Select service / specialty --',
        'Especialidad Clínica (Servicio que atiende)': 'Clinical Specialty (Service Provided)',
        'Solo se listan los servicios activos de la clínica; Recepción asignará citas de este servicio a este especialista.':
            'Only active clinic services are listed; Reception will assign appointments for this service to this specialist.',
        'La cita fue confirmada y el odontólogo especializado quedó asignado.':
            'The appointment was confirmed and the specialized dentist has been assigned.',
        'No hay odontólogos activos con la especialidad solicitada':
            'No active dentists with the requested specialty',
        '-- Seleccionar consultorio libre --': '-- Select free office --',
        'Consultorio Disponible': 'Available Office',
        'Listado actualizado automáticamente con las especialidades activas de la clínica.':
            'List automatically updated with active clinic specialties.',
        'Solo se muestran consultorios libres (los ocupados por otros odontólogos activos aparecen en el Mapa de Consultorios).':
            'Only free offices are shown (those occupied by active dentists appear in the Office Map).',
        'Consultorios de la Clínica': 'Clinic Offices',
        'Control en tiempo real de los 10 consultorios: ocupados por odontólogos activos y espacios libres para asignar.':
            'Real-time control of the 10 clinic offices: occupied by active dentists and free spaces available to assign.',
        'Ocupado': 'Occupied',
        'Libre': 'Free',
        'Gestionar asignación': 'Manage assignment',
        'Espacio disponible para asignar a un nuevo odontólogo.':
            'Space available to assign to a new dentist.',
        'Asignar aquí': 'Assign here',
        'Todos los consultorios (10 de 10) están ocupados': 'All offices (10 of 10) are occupied',
        'Ningún consultorio ocupado actualmente.': 'No offices currently occupied.',
        'No quedan consultorios libres (10 de 10 ocupados).': 'No free offices remaining (10 of 10 occupied).',
        'Tus datos personales fueron actualizados correctamente y ya están sincronizados con recepción.':
            'Your personal data was updated successfully and is now synced with reception.',
        'Tus datos personales y tu contraseña fueron actualizados y sincronizados con la clínica.':
            'Your personal data and password were updated and synced with the clinic.',
        'Descargar Manual (.docx)': 'Download Manual (.docx)',
        '1. Registro por primera vez (Titular — Ley 1581)': '1. First-time registration (Account Holder — Law 1581)',
        ', completa tus datos (nombre en formato propio con soporte de conectores como': ', fill in your details (Proper Case name supporting connectors such as',
        'de, del, la': 'de, del, la',
        'o apellidos compuestos, número de cédula, fecha de nacimiento, teléfono, correo, alergias médicas y contraseña de 8 a 72 caracteres) y despliega los 5 puntos de': 'or compound last names, ID number, birth date, phone, email, medical allergies, and 8-to-72-char password) and expand all 5 clauses of',
        '. Elige el tratamiento odontológico, selecciona la fecha deseada (si es para hoy, solo horas futuras del día), escoge un horario en intervalos de 30 minutos (por ejemplo': '. Choose the dental treatment, select your preferred date (if today, only future hours of the day), pick a 30-minute time slot (for example',
        '3. Actualizar mis alergias médicas en Mi Perfil': '3. Update my medical allergies in My Profile',
        'En tu portal entra a': 'In your portal go to',
        'y pulsa el botón': 'and click the',
        '. Podrás seleccionar alergias del catálogo clínico (Penicilina, Amoxicilina, Lidocaína, Ibuprofeno, Látex, etc.) o marcar': '. You can select allergies from the clinical catalog (Penicillin, Amoxicillin, Lidocaine, Ibuprofen, Latex, etc.) or select',
        'para que tu odontólogo reciba la alerta actualizada en tiempo real.': 'so your dentist receives the updated alert in real time.',
        'en bloques exactos de 30 minutos (si agendas para hoy, solo se permiten horas posteriores a la hora actual).': 'in exact 30-minute blocks (if booking for today, only times after the current hour are allowed).',
        'consulta, edita o elimina expedientes sin citas activas (el registro es exclusivo del titular por Ley 1581). En': 'view, edit, or delete records without active appointments (registration is exclusive to the holder under Law 1581). In',
        'administra especialistas y observa su estado real (': 'manage specialists and view their real-time status (',
        'hoy vs.': 'today vs.',
        'administra los tratamientos odontológicos, su descripción pública, duración mínima (desde 15 minutos) y tarifas actualizadas en COP con equivalente automático en USD.': 'manage dental treatments, their public description, minimum duration (from 15 minutes), and updated pricing in COP with automatic USD equivalent.',
        'Módulo de Reportes (PDF y CSV)': 'Reports Module (PDF & CSV)',
        'Módulo de Reportes (PDF y Excel)': 'Reports Module (PDF & Excel)',
        'filtra por fechas, revisa los 4 KPIs (con separación de': 'filter by dates, review the 4 KPIs (separating',
        'vs.': 'vs.',
        ') y descarga o imprime el informe completo o cada una de las 4 tablas individualmente en': ') and download or print the full report or each of the 4 tables individually in',
        'CSV (Excel)': 'CSV (Excel)',
        'Ver tratamientos': 'View treatments',
        'Agendar cita ahora': 'Book appointment now',

        // --- Catálogo público ---
        'Atención para cada sonrisa': 'Care for every smile',
        'Tratamientos para cuidar': 'Treatments to care for',
        'tu salud oral': 'your oral health',
        'Conoce nuestros servicios odontológicos, sus tiempos estimados y tarifas de referencia. Nuestro equipo puede orientarte para encontrar la atención adecuada.':
            'Explore our dental services, estimated times, and reference pricing. Our team can guide you to find the right care.',
        'Información clara': 'Clear information',
        'Atención profesional': 'Professional care',
        'Solicitud en línea': 'Online request',
        'Servicios DENTIX': 'DENTIX Services',
        'Conoce nuestras': 'Explore our',
        'opciones de atención': 'care options',
        'La duración y el valor pueden variar según la valoración odontológica y las necesidades de cada paciente.':
            'Duration and price may vary depending on the dental evaluation and each patient’s needs.',
        'Estamos actualizando el catálogo': 'We are updating the catalog',
        'Comunícate con nuestro equipo para recibir información sobre los tratamientos disponibles.':
            'Contact our team to receive information about available treatments.',
        'Consulta con nuestro equipo para conocer los detalles de este tratamiento y saber si es adecuado para ti.':
            'Ask our team for details about this treatment and whether it is right for you.',
        'Duración estimada': 'Estimated duration',
        'Tarifa de referencia': 'Reference fee',
        'Estamos para ayudarte': 'We are here to help',
        '¿Listo para cuidar tu sonrisa?': 'Ready to care for your smile?',
        'Solicita una cita y recepción te ayudará con la confirmación y asignación del profesional.':
            'Request an appointment and reception will help confirm and assign your specialist.',
        'Atención odontológica con calidez, claridad y compromiso con tu salud oral.':
            'Dental care with warmth, clarity, and commitment to your oral health.',
        'Inicio': 'Home',
        '© 2026 DENTIX. Todos los derechos reservados.': '© 2026 DENTIX. All rights reserved.',

        // --- Seed descriptions from DB ---
        'Evaluación diagnóstica completa del estado de salud oral y plan de tratamiento personalizado.':
            'Complete diagnostic evaluation of oral health status and personalized treatment plan.',
        'Profilaxis dental profunda para eliminar placa bacteriana y sarro, previniendo enfermedades periodontales.':
            'Deep dental prophylaxis to remove bacterial plaque and tartar, preventing periodontal disease.',
        'Restauración estética con resina de alta resistencia para devolver la función y forma natural al diente.':
            'Cosmetic restoration with high-strength resin to restore natural tooth function and shape.',
        'Tratamiento estético avanzado con luz LED y geles especializados para aclarar el tono dental de forma segura y eficaz.':
            'Advanced cosmetic treatment with LED light and specialized gels to safely and effectively brighten tooth shade.',
        'Alineación y corrección de la posición dental mediante brackets o alineadores para mejorar la estética y mordida.':
            'Alignment and correction of tooth position using braces or aligners to improve aesthetics and bite.',
        'Odontologia general': 'General Dentistry',
        'Odontología general': 'General Dentistry',
        'Ortodoncia': 'Orthodontics',
        'Endodoncia': 'Endodontics',
        'Periodoncia e Implantología': 'Periodontics & Implantology',
        'Periodoncia e Implantologia': 'Periodontics & Implantology',
        'Odontopediatría': 'Pediatric Dentistry',
        'Odontopediatria': 'Pediatric Dentistry',

        // --- Portal / Login / Registro Paciente ---
        'Qué bueno tenerte de vuelta': 'Welcome back',
        'Accede a tu perfil y consulta el estado de tus citas.': 'Access your profile and check the status of your appointments.',
        'Número de cédula': 'ID Number',
        'Número de Cédula': 'ID Number',
        'Contraseña': 'Password',
        '¿Aún no tienes una cuenta?': "Don't have an account yet?",
        'Regístrate aquí': 'Register here',
        'Volver al inicio': 'Back to home',
        'Volver': 'Back',
        'Recepción DENTIX': 'DENTIX Reception',
        'Registrar paciente': 'Register patient',
        'Crear mi cuenta': 'Create my account',
        'Completa tus datos personales y clínicos para brindarte una atención segura.':
            'Complete your personal and clinical details so we can provide safe care.',
        'Datos personales y de contacto': 'Personal and contact information',
        'Nombres y Apellidos': 'Full Name',
        'Cada palabra debe iniciar con mayúscula seguida de minúsculas (Ej.':
            'Each word must start with an uppercase letter followed by lowercase letters (e.g.',
        'Juan Steven': 'Juan Steven',
        'Fecha de Nacimiento': 'Date of Birth',
        'Fecha de nacimiento': 'Date of birth',
        'Teléfono / Celular': 'Phone / Mobile',
        'Correo Electrónico': 'Email Address',
        'Información clínica importante': 'Important clinical information',
        'Alergias o Alertas Médicas': 'Allergies or Medical Alerts',
        'Ninguna alergia conocida': 'No known allergies',
        'Ninguna registrada': 'None registered',
        'Penicilina / Amoxicilina (Antibióticos)': 'Penicillin / Amoxicillin (Antibiotics)',
        'Anestesia local (Lidocaína / Articaína)': 'Local Anesthesia (Lidocaine / Articaine)',
        'Ibuprofeno / AINEs / Aspirina (Analgésicos)': 'Ibuprofen / NSAIDs / Aspirin (Pain relievers)',
        'Látex (Guantes o insumos)': 'Latex (Gloves or supplies)',
        'Yodo / Clorhexidina (Antisépticos)': 'Iodine / Chlorhexidine (Antiseptics)',
        'Metales dentales (Níquel / Cromo / Cobalto)': 'Dental Metals (Nickel / Chrome / Cobalt)',
        'Otra alergia (Escribir manualmente...)': 'Other allergy (Type manually...)',
        'Otra': 'Other',
        'Ninguna': 'None',
        'Penicilina': 'Penicillin',
        'Anestesia local': 'Local anesthesia',
        'Ibuprofeno / AINEs': 'Ibuprofen / NSAIDs',
        'Látex': 'Latex',
        '+ Escribir otra': '+ Write other',
        'Seguridad de la cuenta': 'Account security',
        'Crea una contraseña': 'Create a password',
        'Confirma tu contraseña': 'Confirm your password',
        'Guardar paciente': 'Save patient',
        'Crear cuenta': 'Create account',
        '¿Ya tienes una cuenta?': 'Already have an account?',

        // --- Modal Términos y Condiciones ---
        'Protección de Datos y Ética Clínica · Ley 1581 de 2012': 'Data Protection & Clinical Ethics · Law 1581 of 2012',
        'Términos, Condiciones y Tratamiento de Datos de Salud': 'Terms, Conditions & Health Data Processing',
        'Tus datos han sido verificados. Antes de finalizar la creación de tu cuenta en DENTIX, revisa y acepta el consentimiento de uso clínico y protección de tu información personal.':
            'Your details have been verified. Before completing your DENTIX account registration, please review and accept the clinical use and personal data protection consent.',
        'Titular validado:': 'Validated holder:',
        'Documento:': 'Document:',
        '1. Autorización de Tratamiento de Datos Personales (Habeas Data)': '1. Personal Data Processing Authorization (Habeas Data)',
        'En cumplimiento de la': 'In compliance with',
        'Ley Estatutaria 1581 de 2012': 'Statutory Law 1581 of 2012',
        'y el': 'and',
        'Decreto 1377 de 2013': 'Decree 1377 of 2013',
        ', autorizas a': ', you authorize',
        'DENTIX Clínica Odontológica': 'DENTIX Dental Clinic',
        'para recolectar, almacenar y gestionar tus datos de identificación y contacto con fines asistenciales y administrativos.':
            'to collect, store, and manage your identification and contact data for clinical care and administrative purposes.',
        'Ver más...': 'Read more...',
        'Ver menos': 'Show less',
        'Alcance Constitucional y Legal:': 'Constitutional & Legal Scope:',
        'De conformidad con el Artículo 15 de la Constitución Política de Colombia, la Ley 1581 de 2012 y el Decreto Único Reglamentario 1074 de 2015, el titular otorga su consentimiento previo, expreso e informado para que sus datos personales (nombres y apellidos, número de cédula de ciudadanía, fecha de nacimiento, teléfono móvil y dirección de correo electrónico) sean incorporados en la base de datos institucional de':
            'Pursuant to Article 15 of the Colombian Constitution, Law 1581 of 2012, and Sole Regulatory Decree 1074 of 2015, the holder grants prior, express, and informed consent for their personal data (full name, ID number, birth date, mobile phone, and email address) to be stored in the institutional database of',
        'Finalidades Específicas Autorizadas:': 'Authorized Specific Purposes:',
        'Creación, autenticación y administración de tu cuenta personal dentro del Portal del Paciente DENTIX.':
            'Creation, authentication, and management of your personal account within the DENTIX Patient Portal.',
        'Programación, confirmación, reprogramación o cancelación de citas odontológicas por parte del área de Recepción.':
            'Scheduling, confirmation, rescheduling, or cancellation of dental appointments by Reception.',
        'Contacto telefónico o por correo electrónico para recordatorios de agenda, preparación previa a procedimientos y seguimiento postoperatorio.':
            'Phone or email contact for appointment reminders, pre-procedure preparation, and post-procedure follow-up.',
        'Generación de reportes estadísticos internos anonimizados para mejorar la calidad y oportunidad del servicio odontológico.':
            'Generation of anonymized internal statistical reports to improve dental care quality and timeliness.',
        'Soporte Normativo: Art. 15 Constitución Política · Ley 1581 de 2012 · Decreto 1377 de 2013':
            'Legal Basis: Art. 15 Colombian Constitution · Law 1581 of 2012 · Decree 1377 of 2013',
        '2. Uso Profesional de Información Clínica y Alergias Médicas': '2. Professional Use of Clinical Data & Medical Allergies',
        'Los datos sensibles de salud (alergias a medicamentos o insumos, motivos de consulta, diagnósticos y procedimientos) serán utilizados':
            'Sensitive health data (medication or supply allergies, consultation reasons, diagnoses, and procedures) will be used',
        'exclusivamente por el cuerpo odontológico autorizado': 'exclusively by authorized dental staff',
        'para tu seguridad clínica.': 'for your clinical safety.',
        'Tratamiento de Datos Sensibles de Salud (Art. 6 Ley 1581 de 2012):': 'Sensitive Health Data Processing (Art. 6 Law 1581 of 2012):',
        'Se informa al paciente que los datos relativos a su estado de salud oral, alergias medicamentosas (penicilina, amoxicilina, anestésicos locales como lidocaína/articaína, analgésicos AINEs, látex, yodo/clorhexidina o metales) constituyen datos sensibles recolectados con la única finalidad de salvaguardar su vida e integridad física durante los tratamientos odontológicos.':
            'The patient is informed that data related to oral health and drug allergies (penicillin, amoxicillin, local anesthetics like lidocaine/articaine, NSAIDs, latex, iodine/chlorhexidine, or metals) constitute sensitive data collected solely to protect life and physical safety during dental procedures.',
        'Protocolo de Seguridad Clínica y Farmacovigilancia:': 'Clinical Safety & Pharmacovigilance Protocol:',
        'Tu reporte de alergias estará visible de manera prioritaria para el odontólogo tratante antes de aplicar cualquier anestésico, biomaterial o prescripción farmacológica.':
            'Your allergy report will be prominently visible to your treating dentist before administering any anesthetic, biomaterial, or medication.',
        'El registro de cierre clínico (': 'The clinical closing record (',
        ': diagnóstico definitivo, descripción del procedimiento ejecutado y notas clínicas de evolución) es diligenciado exclusivamente por profesionales en Odontología con cuenta activa en el sistema.':
            ': final diagnosis, performed procedure description, and clinical progress notes) is completed exclusively by licensed dentists with an active system account.',
        'El paciente declara que la información de alergias y antecedentes suministrada es veraz y se compromete a actualizarla desde su perfil ante cualquier cambio médico.':
            'The patient declares that all provided allergy and medical history information is accurate and agrees to update it from their profile upon any medical change.',
        'Soporte Normativo: Art. 6 Ley 1581 de 2012 · Resolución 3100 de 2019 (Seguridad del Paciente)':
            'Legal Basis: Art. 6 Law 1581 of 2012 · Resolution 3100 of 2019 (Patient Safety)',
        '3. Reserva Legal de la Historia Clínica Odontológica': '3. Legal Confidentiality of the Dental Clinical Record',
        'Conforme a la': 'In accordance with',
        'Ley 35 de 1989': 'Law 35 of 1989',
        '(Código de Ética del Odontólogo Colombiano) y la': '(Colombian Dentist Code of Ethics) and',
        'Resolución 1995 de 1999': 'Resolution 1995 of 1999',
        'del Ministerio de Salud, tu historial clínico está sometido a estricta reserva legal.':
            'of the Ministry of Health, your clinical record is strictly confidential under the law.',
        'Carácter Privado y Secreto Profesional:': 'Private Nature & Professional Secrecy:',
        'En virtud de la Ley 35 de 1989, la Ley 23 de 1981 y la Resolución 1995 de 1999 expedida por el Ministerio de Salud, la historia clínica odontológica es un documento privado, obligatorio y sometido a reserva, en el cual se registran cronológicamente las condiciones de salud oral del paciente, los actos médicos y los procedimientos ejecutados.':
            'Under Law 35 of 1989, Law 23 of 1981, and Resolution 1995 of 1999 of the Ministry of Health, the dental clinical record is a private, mandatory, confidential document recording the patient’s oral health, medical acts, and performed procedures chronologically.',
        'Garantías de Confidencialidad en DENTIX:': 'Confidentiality Guarantees at DENTIX:',
        'Ningún dato clínico, diagnóstico o procedimiento será comercializado, cedido ni divulgado a terceros no autorizados.':
            'No clinical data, diagnosis, or procedure will ever be sold, transferred, or disclosed to unauthorized third parties.',
        'Únicamente tienen acceso al expediente clínico el paciente titular, el equipo odontológico y asistencial involucrado en su atención o las autoridades judiciales y sanitarias en los casos estrictamente previstos por la ley.':
            'Only the patient holder, involved dental/clinical staff, or judicial and health authorities in cases strictly mandated by law have access to the clinical record.',
        'El motor de base de datos de DENTIX aplica restricciones de integridad referencial que impiden la alteración o eliminación no autorizada de historias clínicas con atenciones registradas.':
            'DENTIX’s database enforces referential integrity constraints that prevent unauthorized alteration or deletion of clinical records with recorded care.',
        'Soporte Normativo: Ley 35 de 1989 · Ley 23 de 1981 · Resolución 1995 de 1999 MinSalud':
            'Legal Basis: Law 35 of 1989 · Law 23 of 1981 · Resolution 1995 of 1999 MinSalud',
        '4. Políticas de Agendamiento, Puntualidad y Cancelación': '4. Scheduling, Punctuality & Cancellation Policies',
        'El paciente se compromete a cumplir el reglamento de agenda clínica (horario de':
            'The patient agrees to comply with clinic scheduling rules (hours from',
        'en bloques de 30 minutos), respetando las reglas de disponibilidad y aviso oportuno.':
            'in 30-minute slots), respecting availability rules and timely notice.',
        'Reglas Operativas del Sistema de Citas (RN-01 a RN-08):': 'Appointment System Operating Rules (RN-01 to RN-08):',
        'Para garantizar una atención puntual, ordenada y equitativa para toda la comunidad de pacientes, la plataforma aplica las siguientes políticas de agendamiento:':
            'To guarantee punctual, orderly, and fair care for all patients, the platform enforces the following scheduling policies:',
        'Horario y Bloques de Atención:': 'Clinic Hours & Time Slots:',
        'Las citas se programan exclusivamente entre las': 'Appointments are scheduled exclusively between',
        'y las': 'and',
        'horas en intervalos exactos de 30 minutos (': 'hours in exact 30-minute intervals (',
        'Proceso de Confirmación:': 'Confirmation Process:',
        'Toda solicitud realizada en línea ingresa en estado': 'Every online request starts with status',
        'Pendiente': 'Pending',
        '. El área de Recepción verificará la agenda, asignará el odontólogo disponible y cambiará el estado a':
            '. Reception will check the schedule, assign an available dentist, and change the status to',
        'Confirmada': 'Confirmed',
        'Límite Diario e Intervalo Mínimo:': 'Daily Limit & Minimum Gap:',
        'Por seguridad clínica y control de agenda, un paciente solo puede mantener':
            'For clinical safety and schedule control, a patient may hold only',
        'una (1) cita activa por día': 'one (1) active appointment per day',
        'y debe existir un espaciado mínimo de': 'and there must be a minimum spacing of',
        'cuatro (4) días completos': 'four (4) full days',
        'entre citas activas del mismo titular.': 'between active appointments for the same holder.',
        'Asistencia y Cancelación:': 'Attendance & Cancellation:',
        'El paciente deberá presentarse 15 minutos antes de la hora confirmada con su documento de identidad. En caso de no poder asistir, deberá solicitar la cancelación o reprogramación indicando el motivo correspondiente.':
            'The patient must arrive 15 minutes before the confirmed time with their ID document. If unable to attend, they must request cancellation or rescheduling stating the reason.',
        'Soporte Normativo: Reglamento Interno de Agenda Clínica DENTIX · Reglas RN-01 a RN-08':
            'Legal Basis: DENTIX Internal Clinic Schedule Rules · Rules RN-01 to RN-08',
        '5. Derechos del Titular (ARCO) y Seguridad de la Cuenta': '5. Holder Rights (ARCO) & Account Security',
        'Como titular tienes derecho a conocer, actualizar y rectificar tus datos personales en cualquier momento. Tu contraseña se resguarda bajo cifrado criptográfico seguro (':
            'As the data holder, you have the right to know, update, and rectify your personal data at any time. Your password is stored using secure cryptographic hashing (',
        'Ejercicio de Derechos ARCO (Art. 8 Ley 1581 de 2012):': 'Exercise of ARCO Rights (Art. 8 Law 1581 of 2012):',
        'El titular de los datos personales cuenta en todo momento con los siguientes derechos garantizados por la plataforma:':
            'The personal data holder has the following rights guaranteed at all times by the platform:',
        'Acceso y Consulta:': 'Access & Inquiry:',
        'Consultar de forma gratuita sus datos personales, alergias registradas y el estado o historial de sus citas desde su Portal del Paciente.':
            'View their personal data, registered allergies, and appointment status or history free of charge from their Patient Portal.',
        'Actualización y Rectificación:': 'Update & Rectification:',
        'Modificar sus datos de contacto, correo electrónico, alergias o contraseña, o solicitar la corrección de información inexacta ante Recepción.':
            'Modify contact details, email, allergies, or password, or request correction of inaccurate data at Reception.',
        'Revocatoria y Supresión:': 'Revocation & Deletion:',
        'Solicitar la supresión de su cuenta cuando no exista un deber legal o clínico de conservación de historia médica vigente.':
            'Request deletion of their account when no active legal or clinical retention duty applies.',
        'Ciberseguridad y Custodia de Credenciales:': 'Cybersecurity & Credential Custody:',
        'Las contraseñas se almacenan mediante funciones de hash irreversibles (':
            'Passwords are stored using irreversible hash functions (',
        '), las sesiones están protegidas con cookies': '), sessions are protected with',
        'y tokens': 'cookies and',
        '. El paciente es responsable de no compartir su clave personal con terceros.':
            'tokens. The patient is responsible for not sharing their personal password with third parties.',
        'Soporte Normativo: Art. 8 Ley 1581 de 2012 · Estándares de Seguridad OWASP / PDO':
            'Legal Basis: Art. 8 Law 1581 of 2012 · OWASP / PDO Security Standards',
        'He leído íntegramente los 5 puntos desplegados y acepto los':
            'I have read all 5 expanded points in full and accept the',
        'Términos y Condiciones de Uso': 'Terms and Conditions of Use',
        ', autorizando de manera previa, libre y expresa el': ', providing prior, free, and express authorization for the',
        'Tratamiento de mis Datos Personales y Clínicos': 'Processing of My Personal and Clinical Data',
        'para fines profesionales de salud oral en DENTIX.': 'for professional oral health purposes at DENTIX.',
        'Revisar mis datos': 'Review my details',
        'Aceptar términos y crear cuenta': 'Accept terms & create account',
        'Creando cuenta...': 'Creating account...',
        'Por favor marca la casilla de aceptación para confirmar los términos y condiciones.':
            'Please check the acceptance box to confirm the terms and conditions.',
        'Cada palabra del nombre debe empezar con mayúscula seguida de minúsculas (ejemplo: Juan Steven). No se permite todo en mayúsculas ni en minúsculas.':
            'Each word of the name must start with an uppercase letter followed by lowercase (example: Juan Steven). All-uppercase or all-lowercase is not allowed.',
        'Por favor ingresa tus nombres y apellidos.': 'Please enter your full name.',
        'Por favor ingresa tu número de cédula.': 'Please enter your ID number.',
        'Por favor selecciona tu fecha de nacimiento.': 'Please select your birth date.',
        'Por favor ingresa tu número de teléfono o celular.': 'Please enter your phone or mobile number.',
        'Por favor ingresa un correo electrónico válido.': 'Please enter a valid email address.',
        'Por favor escribe cuál es tu alergia en el campo de texto.': 'Please write your allergy in the text field.',
        'La contraseña debe tener al menos 8 caracteres.': 'Password must be at least 8 characters long.',
        'Las contraseñas no coinciden. Verifícalas por favor.': 'Passwords do not match. Please verify them.',

        // --- Portal del Paciente & Perfil & Agendar ---
        'Perfil y citas': 'Profile & appointments',
        'Tu información personal': 'Your personal information',
        'Tu próxima visita': 'Your next visit',
        'Mi perfil': 'My profile',
        'Mi Perfil': 'My Profile',
        'Mis datos': 'My details',
        'Mis citas': 'My appointments',
        'Aún no tienes citas': 'You have no appointments yet',
        'Cuando agendes una cita, aquí podrás revisar su estado y los detalles de atención.':
            'Once you book an appointment, you can review its status and care details here.',
        'Solicitar mi primera cita': 'Request my first appointment',
        'Solicitar nueva cita': 'Request new appointment',
        'Solicitar mi cita': 'Request my appointment',
        'Solicitar cita': 'Request appointment',
        'Volver a mi perfil': 'Back to my profile',
        'Cerrar sesión': 'Sign out',
        'Salir de forma segura': 'Sign out safely',
        'Cédula': 'ID Number',
        'Teléfono': 'Phone',
        'Correo': 'Email',
        'Nacimiento': 'Birth date',
        'Alergias': 'Allergies',
        'Sin alergias': 'No allergies',
        'Estado': 'Status',
        'Odontólogo asignado': 'Assigned dentist',
        'Odontólogo por asignar por recepción': 'Dentist to be assigned by reception',
        'Motivo de consulta': 'Reason for visit',
        'Motivo de Consulta': 'Reason for Visit',
        'Diagnóstico': 'Diagnosis',
        'Procedimiento realizado': 'Procedure performed',
        'Nota clínica / recomendaciones': 'Clinical note / recommendations',
        'Tu solicitud será revisada por recepción antes de quedar confirmada.':
            'Your request will be reviewed by reception before being confirmed.',
        'Tu solicitud aparecerá como pendiente mientras recepción confirma la cita y asigna al odontólogo.':
            'Your request will appear as pending while reception confirms the appointment and assigns a dentist.',
        'Selecciona un tratamiento': 'Select a treatment',
        'Fecha': 'Date',
        'Hora (08:00 a 19:30, cada 30 min)': 'Time (08:00 AM to 07:30 PM, every 30 min)',
        'Hora (08:00 – 19:30)': 'Time (08:00 – 19:30)',
        'En este momento no hay servicios disponibles para agendar.':
            'There are currently no services available for booking.',
        'Total de Citas': 'Total Appointments',
        'Total de citas': 'Total appointments',
        'Pendientes': 'Pending',
        'Confirmadas': 'Confirmed',
        'Completadas': 'Completed',
        'Finalizadas': 'Completed',
        'Finalizada': 'Completed',
        'Canceladas': 'Cancelled',
        'Cancelada': 'Cancelled',
        'Completada': 'Completed',
        'Consulta tus datos y mantente al día con el cuidado de tu sonrisa.':
            'View your details and stay up to date with your smile care.',

        // --- Estados y etiquetas comunes ---
        'Pendiente': 'Pending',
        'Confirmada': 'Confirmed',
        'No Asistió (No Show)': 'No Show',
        'Activo': 'Active',
        'Inactivo': 'Inactive',
        'Activo (Habilitado)': 'Active (Enabled)',
        'Inactivo (Deshabilitado)': 'Inactive (Disabled)',
        'Activo / Disponible': 'Active / Available',
        'Ocupado / En Cita': 'Busy / In Appointment',
        'Ocupado': 'Busy',
        'Disponible': 'Available',
        'En atención': 'In care',

        // --- Panel Administrador / Recepción / CRUDs ---
        'Acceso Recepción': 'Reception Access',
        'Ingresa tus datos para gestionar la agenda de citas': 'Enter your credentials to manage the appointment schedule',
        'Correo Institucional': 'Institutional Email',
        'Contraseña de Acceso': 'Access Password',
        'Registro Recepción': 'Reception Registration',
        'Crea una cuenta para administrar el panel de DENTIX': 'Create an account to manage the DENTIX dashboard',
        'Crear Cuenta y Entrar': 'Create Account & Sign In',
        'Ir al Inicio': 'Go to Home',
        'Panel Admin': 'Admin Panel',
        'Citas': 'Appointments',
        'Pacientes': 'Patients',
        'Reportes': 'Reports',
        'Recepcionista Activo': 'Active Receptionist',
        'Agenda de Citas': 'Appointment Schedule',
        '+ Nueva Cita': '+ New Appointment',
        'Buscar': 'Search',
        'Filtrar': 'Filter',
        'Limpiar': 'Clear',
        'Paciente': 'Patient',
        'Servicio': 'Service',
        'Servicio / Tratamiento': 'Service / Treatment',
        'Servicio Odontológico': 'Dental Service',
        'Servicio odontológico': 'Dental service',
        'Odontólogo': 'Dentist',
        'Odontólogo Asignado': 'Assigned Dentist',
        'Fecha y Hora': 'Date & Time',
        'Acciones': 'Actions',
        'Acción': 'Action',
        'Editar': 'Edit',
        'Eliminar': 'Delete',
        'Confirmar': 'Confirm',
        'Cancelar': 'Cancel',
        'Cancelar Cita': 'Cancel Appointment',
        'Confirmar cancelación': 'Confirm cancellation',
        'Motivo de cancelación (obligatorio)': 'Cancellation reason (required)',
        'Motivo de cancelación:': 'Cancellation reason:',
        'Motivo de Cancelación (obligatorio si el estado es Cancelada)':
            'Cancellation Reason (required if status is Cancelled)',
        'No hay solicitudes pendientes de confirmar.': 'No pending requests to confirm.',
        'Todavía no hay citas confirmadas.': 'There are no confirmed appointments yet.',
        'Seleccione servicio...': 'Select service...',
        'Seleccione un paciente...': 'Select a patient...',
        '-- Seleccionar --': '-- Select --',
        'Por asignar (queda pendiente)': 'To be assigned (stays pending)',
        'Por asignar (sin odontólogo)': 'Unassigned (no dentist)',
        'Guardar Cita': 'Save Appointment',
        'Guardar Cambios': 'Save Changes',
        'Guardar cambios': 'Save changes',
        'Cancelar y volver': 'Cancel and go back',
        'Cancelar y volver al listado': 'Cancel and return to list',
        'Cancelar y volver al catálogo': 'Cancel and return to catalog',
        'Estado de la Cita': 'Appointment Status',
        'Agendar Nueva Cita - DENTIX': 'Schedule New Appointment - DENTIX',
        'Selecciona el paciente, el servicio requerido y, opcionalmente, el odontólogo activo para confirmarla de inmediato.':
            'Select the patient, required service, and optionally an active dentist to confirm immediately.',
        'Nuevo Paciente': 'New Patient',
        'Listado de Pacientes': 'Patient Directory',
        'Paciente / Contacto': 'Patient / Contact',
        'Alergias / Antecedentes': 'Allergies / Medical History',
        'No hay pacientes registrados en este momento.': 'No patients registered at this time.',
        'Editar Paciente': 'Edit Patient',
        'Nuevo Odontólogo': 'New Dentist',
        'Registro desde Consultorios Libres': 'Register from Free Offices',
        'Gestión y Edición desde Consultorios': 'Register & Edit from Offices',
        'Nuevo Consultorio': 'New Office',
        'Crear Consultorio': 'Create Office',
        'Crear primer consultorio': 'Create first office',
        'Eliminar consultorio': 'Delete office',
        'Nombre o Número del Consultorio': 'Office Name or Number',
        'Adapta la cantidad de consultorios a la capacidad real de tu sede.':
            'Adapt the number of offices to your clinic’s real capacity.',
        'Se sugiere automáticamente el siguiente consecutivo disponible, pero puedes escribir el nombre propio de tu sede (2 a 30 caracteres).':
            'The next available number is suggested automatically, or enter your clinic’s custom name (2 to 30 characters).',
        'Ej. Consultorio 11 o Box Cirugía': 'e.g. Office 11 or Surgery Room',
        'Aún no hay consultorios configurados en esta sede.':
            'No offices configured in this clinic yet.',
        'No hay consultorios configurados en la sede': 'No offices configured in this clinic',
        'Cuerpo Médico': 'Medical Staff',
        'Cuerpo Médico DENTIX': 'DENTIX Medical Team',
        'Especialistas activos, consultorio y disponibilidad en tiempo real.':
            'Active specialists, office, and real-time availability.',
        'Especialidad': 'Specialty',
        'Especialidad Clínico': 'Clinical Specialty',
        'Especialidad Clínica': 'Clinical Specialty',
        'Especialidad / Consultorio': 'Specialty / Office',
        'Consultorio': 'Office',
        'Consultorio / Espacio': 'Office / Room',
        'Estado (Activo / En Cita)': 'Status (Active / In Appointment)',
        'Estado de Cuenta': 'Account Status',
        'No hay odontólogos registrados.': 'No dentists registered.',
        'Registrar Odontólogo': 'Register Dentist',
        'Editar Odontólogo': 'Edit Dentist',
        'Gestión de Especialistas': 'Specialist Management',
        'Correo Electrónico (Acceso)': 'Email Address (Access)',
        'Correo Electrónico (Login)': 'Email Address (Login)',
        'Enlace público (Slug):': 'Public Link (Slug):',
        'El enlace se actualiza de forma automática si modificas el nombre del especialista.':
            'The link updates automatically if you change the specialist’s name.',
        'Ver odontólogos registrados': 'View registered dentists',
        'Nuevo Servicio': 'New Service',
        'Catálogo de Citas': 'Service Catalog',
        'Tratamientos odontológicos, descripciones clínicas, duración estimada y tarifas vigentes.':
            'Dental treatments, clinical descriptions, estimated duration, and current pricing.',
        'Nombre': 'Name',
        'Nombre del Servicio': 'Service Name',
        'Nombre Completo': 'Full Name',
        'Descripción Clínica': 'Clinical Description',
        'Tratamiento / Tiempos': 'Treatment / Duration',
        'Tarifa / Precio': 'Fee / Price',
        'Costo / Precio': 'Cost / Price',
        'Costo / Precio (COP)': 'Cost / Price (COP)',
        'Duración Promedio': 'Average Duration',
        'Duración Promedio (minutos)': 'Average Duration (minutes)',
        'Mínimo 15 min.': 'Minimum 15 min.',
        'No hay servicios registrados.': 'No services registered.',
        'Sin descripción detallada.': 'No detailed description.',
        'Registrar Servicio': 'Register Service',
        'Ingrese la información completa del tratamiento u servicio odontológico':
            'Enter the complete details of the dental treatment or service',
        'Editar servicio': 'Edit service',
        'Editar Servicio': 'Edit Service',
        'Ver servicios registrados': 'View registered services',

        // --- Portal Odontólogo ---
        'Acceso Odontólogos': 'Dentist Login',
        'Ingresa con tu correo institucional para revisar tu agenda y finalizar citas':
            'Sign in with your institutional email to check your schedule and complete appointments',
        'Ingresar a mi Agenda': 'Go to My Schedule',
        'Ingresar a mi cuenta': 'Sign in to my account',
        'Odontólogo Activo': 'Active Dentist',
        'Mi Agenda': 'My Schedule',
        'Historial Finalizadas': 'Completed History',
        'Citas por Atender': 'Appointments to Attend',
        'Historial de Citas Finalizadas': 'Completed Appointments History',
        'Mi Consulta': 'My Consultation',
        'Mis Citas Asignadas': 'My Assigned Appointments',
        'No tienes citas confirmadas pendientes por atender en este momento.':
            'You have no confirmed appointments waiting for care right now.',
        'Finalizar cita': 'Complete appointment',
        'Finalizar Cita y Reportar Detalle': 'Complete Appointment & Submit Details',
        'Guardar y Finalizar Cita': 'Save & Complete Appointment',
        'Fecha de Salida de la Cita': 'Appointment Checkout Date',
        'Hora de Salida de la Cita': 'Appointment Checkout Time',
        'Resumen breve de cómo fue la cita': 'Brief summary of how the appointment went',
        'Extras, recomendaciones o problemáticas presentadas': 'Extras, recommendations, or issues encountered',
        'Paciente:': 'Patient:',
        'Cédula:': 'ID Number:',
        'Fecha y hora:': 'Date & time:',
        'Odontólogo:': 'Dentist:',
        'Ingreso:': 'Check-in:',
        'Salida:': 'Check-out:',
        'Resumen:': 'Summary:',
        'Procedimiento:': 'Procedure:',
        'Novedades:': 'Notes:',
        'Sin novedades': 'No remarks',
        'Sin detalle clínico registrado': 'No clinical detail recorded',
        'Archivo Histórico Personal': 'Personal Archive',
        'Archivo clínico permanente de todas tus atenciones finalizadas, detalles y salidas registradas.':
            'Permanent clinical archive of all your completed care visits, details, and check-outs.',
        'Paciente / Cédula': 'Patient / ID',
        'Fecha y Hora de Ingreso': 'Check-in Date & Time',
        'Detalle de la Cita y Procedimiento': 'Appointment Details & Procedure',
        'Novedades / Nota Clínica': 'Remarks / Clinical Note',
        'REGISTRO CLÍNICO (DETALLE_CITA)': 'CLINICAL RECORD (DETALLE_CITA)',
        'Nube de Historial — Citas Finalizadas': 'History Cloud — Completed Appointments',
        'Todavía no hay citas finalizadas guardadas en tu historial.':
            'There are no completed appointments saved in your history yet.',
        'No se encontraron citas finalizadas con ese criterio.':
            'No completed appointments matched that search.',

        // --- Reportes ---
        'Reportes Clínicos': 'Clinical Reports',
        'Reportes y Estadísticas de la Clínica': 'Clinic Reports & Statistics',
        'Análisis en tiempo real de pacientes frecuentes, demanda de tratamientos y productividad del cuerpo médico.':
            'Real-time analysis of frequent patients, treatment demand, and medical staff productivity.',
        'Desde': 'From',
        'Hasta': 'To',
        'Imprimir': 'Print',
        'Paciente con más citas': 'Top Patient by Appointments',
        'Servicio más solicitado': 'Most Requested Service',
        'Odontólogo con más citas': 'Top Dentist by Appointments',
        'Horarios Más Solicitados': 'Most Requested Time Slots',
        'Franjas horarias de mayor demanda.': 'Peak demand time slots.',
        'Pacientes que Más Citas Han Agendado': 'Patients with Most Booked Appointments',
        'Ranking de pacientes ordenado por historial de solicitudes y asistencia.':
            'Patient ranking sorted by request history and attendance.',
        'Servicios Más Utilizados y Pedidos': 'Most Used & Requested Services',
        'Demanda por tratamiento, participación porcentual e ingresos proyectados.':
            'Demand by treatment, percentage share, and projected revenue.',
        'Odontólogos con Mayor Atención y Citas': 'Dentists with Most Care & Appointments',
        'Productividad de especialistas ordenada por citas atendidas y asignadas (excluyendo canceladas).':
            'Specialist productivity sorted by attended and assigned appointments (excluding cancelled).',
        'Total Citas': 'Total Appointments',
        'Total Pedidos': 'Total Requests',
        'Última Cita': 'Last Appointment',
        'Completadas / Conf. / Pend.': 'Completed / Conf. / Pend.',
        'Finalizadas / Confirmadas': 'Completed / Confirmed',
        'Participación': 'Share',
        'Ingresos Estimados': 'Estimated Revenue',
        'Ingresos Est.': 'Est. Revenue',
        'Frecuencia': 'Frequency',
        'Carga Relativa': 'Relative Load',
        'Citas Confirmadas': 'Confirmed Appointments',
        'Citas Finalizadas': 'Completed Appointments',
        'Sin citas registradas en este período.': 'No appointments recorded in this period.',
        'Aún no hay pacientes con citas agendadas en este rango.': 'No patients with booked appointments in this range.',
        'Sin solicitudes en este período.': 'No requests in this period.',
        'Sin citas asignadas en este período.': 'No assigned appointments in this period.',
        'Sin citas registradas para calcular franjas horarias.': 'No recorded appointments to calculate time slots.',
        'citas agendadas': 'scheduled appointments',
        'en agenda': 'on schedule',
        'canceladas / no-show': 'cancelled / no-show',
        '% del total': '% of total',
        'En pesos colombianos.': 'In Colombian pesos.',

        // --- Placeholders & Atributos ---
        'Abrir menú': 'Open menu',
        'Buscar paciente o cédula...': 'Search patient or ID number...',
        'Buscar paciente, cédula o servicio...': 'Search patient, ID, or service...',
        'Cerrar': 'Close',
        'Cerrar aviso legal': 'Close legal notice',
        'Cerrar información': 'Close info',
        'Cerrar manual de usuario': 'Close user manual',
        'DENTIX, ir al inicio': 'DENTIX, go to top',
        'DENTIX, volver al inicio': 'DENTIX, back to home',
        'Describe brevemente el procedimiento, beneficios o alcance...':
            'Briefly describe the procedure, benefits, or scope...',
        'Descripción breve...': 'Brief description...',
        'Directorio completo de servicios y módulos DENTIX': 'Complete directory of DENTIX services and modules',
        'Editar cita': 'Edit appointment',
        'Limpiar búsqueda': 'Clear search',
        'Mínimo 8 caracteres': 'Minimum 8 characters',
        'Mínimo 6 caracteres para ingresar al portal médico.': 'Minimum 6 characters to sign in to the medical portal.',
        'Navegación del paciente': 'Patient navigation',
        'Protección activa de datos clínicos': 'Active clinical data protection',
        'Reconocimiento al cuerpo médico': 'Medical team recognition',
        'Repite tu contraseña': 'Repeat your password',
        'Resumen de citas': 'Appointment summary',
        'Secciones del manual de usuario': 'User manual sections',
        'Selección rápida de alergias comunes': 'Quick selection of common allergies',
        'Total en historial': 'Total in history',
        'Total finalizadas': 'Total completed',
        'Ver todo el historial': 'View full history',
        'Información de sede, idioma y tarifas': 'Clinic location, language, and fee info',
        '¿Tienes otra alergia o deseas especificar? Escríbela aquí (opcional)...':
            'Do you have another allergy or want to specify? Type it here (optional)...',
        '¿Deseas agregar algún detalle adicional sobre tu alergia? Escríbelo aquí (opcional)...':
            'Want to add any extra details about your allergy? Write them here (optional)...',
        'Escribe aquí a qué medicamento o sustancia eres alérgico (obligatorio)...':
            'Write here which medication or substance you are allergic to (required)...',
        'Ej. 1001234567': 'e.g. 1001234567',
        'Ej. 3001234567': 'e.g. 3001234567',
        'Ej. Blanqueamiento dental': 'e.g. Teeth Whitening',
        'Ej. Consultorio 1': 'e.g. Office 1',
        'Ej. Consultorio 302': 'e.g. Office 302',
        'Ej. Dr. Carlos Pérez': 'e.g. Dr. Carlos Pérez',
        'Ej. Dra. Ana Torres': 'e.g. Dr. Ana Torres',
        'Ej. Maik Cardenas o Dra. Ana Torres': 'e.g. Maik Cardenas or Dr. Ana Torres',
        'Se guardará como:': 'Will be saved as:',
        'Si no escribes Dr. o Dra., el sistema lo asignará automáticamente según el nombre.':
            'If you do not type Dr. or Dra., the system will assign it automatically based on the name.',
        'Ej. El paciente asistió puntualmente, presentó buena tolerancia durante la sesión...':
            'e.g. Patient arrived on time and tolerated the procedure well...',
        'Ej. El paciente solicitó reprogramar por motivos personales.':
            'e.g. Patient requested rescheduling for personal reasons.',
        'Ej. José Antonio': 'e.g. José Antonio',
        'Ej. Juan Pérez': 'e.g. Juan Pérez',
        'Ej. Ortodoncia / Endodoncia': 'e.g. Orthodontics / Endodontics',
        'Ej. Ortodoncia correctiva': 'e.g. Corrective Orthodontics',
        'Ej. Ortodoncia, Endodoncia, Odontopediatría': 'e.g. Orthodontics, Endodontics, Pediatric Dentistry',
        'Ej. Paciente reprograma por viaje': 'e.g. Patient reschedules due to travel',
        'Ej. Penicilina, látex, o dejar vacío si ninguna...': 'e.g. Penicillin, latex, or leave blank if none...',
        'Ej. Profilaxis ultrasónica, aplicación de flúor, ajuste de brackets, resina en pieza 16...':
            'e.g. Ultrasonic prophylaxis, fluoride application, bracket adjustment, resin on tooth 16...',
        'Ej. Sensibilidad leve en molar inferior, se recomienda control en 15 días. (Si no hubo inconvenientes puedes dejarlo vacío)':
            'e.g. Mild sensitivity in lower molar, follow-up recommended in 15 days. (Leave blank if none)',
        'Ej. Tratamiento estético avanzado para aclarar el tono natural de los dientes...':
            'e.g. Advanced cosmetic treatment to brighten natural tooth shade...',

        // --- Mensajes comunes PHP / Alertas / Confirmaciones ---
        '¿Deseas eliminar a este odontólogo? Si tiene citas registradas no se eliminará.':
            'Do you want to delete this dentist? If they have recorded appointments, they will not be deleted.',
        '¿Deseas eliminar este servicio? Si tiene citas asociadas no se eliminará.':
            'Do you want to delete this service? If it has associated appointments, it will not be deleted.',
        'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.':
            'The form session expired. Please reload the page and try again.',
        'Odontólogo registrado correctamente.': 'Dentist registered successfully.',
        'Odontólogo actualizado correctamente.': 'Dentist updated successfully.',
        'Odontólogo eliminado correctamente.': 'Dentist deleted successfully.',
        'Servicio registrado correctamente.': 'Service registered successfully.',
        'Servicio actualizado correctamente.': 'Service updated successfully.',
        'Paciente actualizado correctamente.': 'Patient updated successfully.',
        'Paciente registrado correctamente.': 'Patient registered successfully.',
        'Paciente registrado correctamente con consentimiento de datos informado.':
            'Patient registered successfully with informed data consent.',
        'La cita fue cancelada correctamente.': 'The appointment was cancelled successfully.',
        'La cita fue confirmada y el odontólogo quedó asignado.':
            'The appointment was confirmed and the dentist has been assigned.',
        '¡Cita actualizada correctamente!': 'Appointment updated successfully!',
        '¡Cita marcada como FINALIZADA y detalle clínico con hora de salida registrado!':
            'Appointment marked as COMPLETED and clinical detail with check-out time saved!',
        'Ingresa tu cédula y contraseña.': 'Enter your ID number and password.',
        'No pudimos iniciar sesión con esos datos. Verifica tu cédula y contraseña.':
            'We could not sign in with those credentials. Check your ID number and password.',
        'Credenciales incorrectas o cuenta inactiva. Verifique correo y contraseña.':
            'Incorrect credentials or inactive account. Check email and password.',
        'Credenciales incorrectas. Verifique correo y contraseña.':
            'Incorrect credentials. Check email and password.',
        'Ya existe una cuenta con esa cédula. Inicia sesión.':
            'An account with that ID number already exists. Please sign in.',
        'No puedes tener más de una cita el mismo día.':
            'You cannot have more than one appointment on the same day.',
        'Debes desplegar las 5 cláusulas en "Ver más..." y aceptar los Términos y Condiciones para crear tu cuenta.':
            'You must expand all 5 clauses in "Read more..." and accept the Terms and Conditions to create your account.',
        'Base Clínica': 'Clinical Database',
        'Expediente de Pacientes': 'Patient Records',
        'Administración completa de expedientes, historia clínica básica y datos de contacto.':
            'Full management of patient files, basic clinical history, and contact details.',

        // --- Cobertura completa adicional (fragmentos, etiquetas y tablas) ---
        ', con su detalle clínico y horarios de ingreso/salida.': ', including clinical detail and check-in/check-out times.',
        'Acceso especialistas': 'Specialists Access',
        'Agenda DENTIX': 'DENTIX Schedule',
        'Agenda de': 'Schedule for',
        'Agenda e historial de atención': 'Schedule & care history',
        'Agendar': 'Book',
        'Agendar una cita': 'Book an appointment',
        'Alergias registradas': 'Registered allergies',
        'Atención DENTIX': 'DENTIX Care',
        'Citas confirmadas': 'Confirmed appointments',
        'Citas confirmadas por recepción con su odontólogo asignado.':
            'Appointments confirmed by reception with an assigned dentist.',
        'Citas confirmadas y completadas': 'Confirmed and completed appointments',
        'Conoce las opciones de atención, sus características y tarifas antes de solicitar tu cita.':
            'Explore care options, details, and pricing before requesting your appointment.',
        'Cédula / Documento': 'ID / Document',
        'Debajo de Citas encontrarás': 'Below Appointments you will find',
        'Documento': 'Document',
        'ESPECIALISTAS': 'SPECIALISTS',
        'Editar Cita #': 'Edit Appointment #',
        'Empieza con una solicitud': 'Start with a request',
        'Estado de sus Citas': 'Appointment Status',
        'Haz clic en el botón superior': 'Click the top button',
        'Hola,': 'Hello,',
        '¡Hola,': 'Hello,',
        'Hora': 'Time',
        'Ingresa a tu cuenta para solicitar una cita y consultar su estado, tus próximas visitas y tu historial.':
            'Sign in to your account to request an appointment and check its status, upcoming visits, and history.',
        'Inicio / Landing': 'Home / Landing',
        'Mi Estado Actual': 'My Current Status',
        'Navegación': 'Navigation',
        'Paciente DENTIX': 'DENTIX Patient',
        'Pacientes confirmados por recepción bajo tu atención. Presiona':
            'Patients confirmed by reception under your care. Click',
        'Portal Médico': 'Medical Portal',
        'Programación': 'Scheduling',
        'Servicios odontológicos': 'Dental services',
        'Solicita tu cita': 'Request your appointment',
        'Solicitudes pendientes de confirmación y asignación de odontólogo.':
            'Requests pending confirmation and dentist assignment.',
        'Tarifa': 'Fee',
        'Teléfono / Correo': 'Phone / Email',
        'Tratamiento': 'Treatment',
        'Tu bienestar, nuestra prioridad': 'Your well-being, our priority',
        'Ya tengo cuenta': 'I already have an account',
        'al concluir la consulta.': 'when finishing the consultation.',
        'canc.': 'canc.',
        'comp.': 'comp.',
        'conf.': 'conf.',
        'pend.': 'pend.',
        'finalizadas': 'completed',
        'indicando el motivo obligatorio.': 'specifying the required reason.',
        'min': 'min',
        'minutos': 'minutes',
        'odontólogos activos': 'active dentists',
        'pacientes': 'patients',
        '© 2026 DENTIX · Tu salud oral, en buenas manos.': '© 2026 DENTIX · Your oral health, in good hands.',
        '© 2026 DENTIX. Sistema Odontológico.': '© 2026 DENTIX. Dental Management System.',
        'correo@ejemplo.com': 'email@example.com',
        'especialista@clinica.com': 'specialist@clinic.com',
        'Período:': 'Period:',
        'est.': 'est.',
        'solicitudes · $': 'requests · $',
        'Editar alergias': 'Edit allergies',
        'Actualizar alergias o alertas médicas': 'Update allergies or medical alerts',
        'Guardar alergias': 'Save allergies',
        'Tus alergias y alertas médicas fueron actualizadas correctamente.': 'Your allergies and medical alerts were updated successfully.',
        'Especifica otra alergia o detalle adicional (ej. Penicilina, látex)...': 'Specify another allergy or extra detail (e.g. Penicillin, latex)...',
        'Registro exclusivo por el titular': 'Holder-only registration',
        'Registro y edición exclusivos del titular': 'Holder-only registration & editing',
        'Consulta en vivo y depuración de expedientes. El registro y la edición pertenecen exclusivamente al titular (Ley 1581).':
            'Live view and cleanup of patient records. Registration and editing belong exclusively to the account holder (Law 1581).',
        'Consulta, edición y depuración de expedientes registrados personalmente por los titulares (Ley 1581).':
            'View, edit, and manage patient records personally registered by account holders (Law 1581).',
        '¿Deseas eliminar a este paciente? Si tiene citas o historial clínico registrado no podrá eliminarse.':
            'Do you want to delete this patient? If they have appointments or clinical history, they cannot be deleted.',
        'Paciente eliminado correctamente.': 'Patient deleted successfully.',
        'No se puede eliminar el paciente porque tiene citas o historial clínico registrado (Resolución 1995 de 1999).':
            'Cannot delete patient because they have appointments or clinical history recorded (Resolution 1995 of 1999).',
        'Cita asignada hoy': 'Appointment assigned today',
        'Imprimir Todo (PDF)': 'Print All (PDF)',
        'Exportar Todo (CSV)': 'Export All (CSV)',
        'Exportar Todo (Excel)': 'Export All (Excel)',
        'Resumen KPIs (Excel)': 'KPI Summary (Excel)',
        'Excel': 'Excel',
        'Imprimir PDF': 'Print PDF',
        'Imprimir todos los reportes en PDF': 'Print all reports as PDF',
        'Descargar reporte consolidado en CSV / Excel': 'Download consolidated report in CSV / Excel',
        'Descargar reporte consolidado en Excel (.xlsx) con 5 hojas diseñadas':
            'Download consolidated styled 5-sheet Excel (.xlsx) workbook',
        'Descargar solo el Resumen de KPIs y Líderes en Excel (.xlsx)':
            'Download KPI & Leaders Summary only in Excel (.xlsx)',
        'Descubre en poco más de un minuto cómo se conectan el Paciente, la Recepción y el Odontólogo dentro de la plataforma.':
            'Discover in just over a minute how Patient, Reception, and Dentist connect inside the platform.',
        'Descubre en 1 minuto cómo se conectan el Paciente, la Recepción y el Odontólogo dentro de la plataforma.':
            'Discover in 1 minute how Patient, Reception, and Dentist connect inside the platform.',
        'Conoce la explicación completa de los aprendices SENA creadores del software y el recorrido por los 3 portales.':
            'Watch the full explanation by the SENA apprentices who created the software and the tour across all 3 portals.',
        'Presentación Aprendices SENA · MP4': 'SENA Apprentices Presentation · MP4',
        'Ver comercial · 01:00': 'Watch commercial · 01:00',
        'Video Oficial DENTIX · HD': 'Official DENTIX Video · HD',
        'Reproducir comercial oficial DENTIX': 'Play official DENTIX commercial',
        'Ver en pantalla completa': 'View full screen',
        'Cerrar reproductor': 'Close player',
        'Demostración oficial · MP4': 'Official demo · MP4',
        'Experiencia clínica completa': 'Complete clinical experience',
        'Registro bajo Ley 1581, edición de perfil y solicitud autónoma de citas.':
            'Registration under Law 1581, profile editing, and autonomous appointment requests.',
        'Direccionamiento de citas, consultorios dinámicos y reportes en Excel y PDF.':
            'Appointment routing, dynamic offices, and Excel & PDF reports.',
        'Panel del Odontólogo': 'Dentist Panel',
        'Agenda del especialista, alertas médicas de alergias y cierre clínico.':
            'Specialist schedule, medical allergy alerts, and clinical closing.',
        'no-show': 'no-show',
    };

    const PATTERNS = [
        [/^¡?Hola,\s*(.+?)!?$/i, 'Hello, $1!'],
        [/^(\d+)\s+canceladas\s*·\s*(\d+)\s+no-show$/i, '$1 cancelled · $2 no-show'],
        [/^(\d+)\s+no-show$/i, '$1 no-show'],
        [/^(\d+)\s+programada\(s\)\s+en\s+días\s+futuros$/i, '$1 scheduled on future days'],
        [/^No puedes solicitar una cita para hoy en una hora que ya pasó \((.+?)\)\.\s*Elige un horario posterior a (.+?) o selecciona otra fecha\.$/i,
            'You cannot request an appointment for today at a past time ($1). Choose a time after $2 or select another date.'],
        [/^No puedes agendar una cita para hoy en una hora que ya pasó \((.+?)\)\.\s*Elige un horario posterior a (.+?) o selecciona otra fecha\.$/i,
            'You cannot schedule an appointment for today at a past time ($1). Choose a time after $2 or select another date.'],
        [/^Titular validado:\s*(.+)$/i, 'Validated holder: $1'],
        [/^Editar Cita #(\d+)$/i, 'Edit Appointment #$1'],
        [/^Agenda de\s+(.+)$/i, 'Schedule for $1'],
        [/^Cédula:\s*(.+)$/i, 'ID: $1'],
        [/^Documento:\s*(.+)$/i, 'Document: $1'],
        [/^Consultorio\s+(\d+)\s*\(Libre\)$/i, 'Office $1 (Free)'],
        [/^--\s*Especialista en\s+(.+?)\s*--$/i, '-- Specialist in $1 --'],
        [/^Sin odontólogo activo en\s+(.+)$/i, 'No active dentist in $1'],
        [/^Asigna esta especialidad a un odontólogo en$/i, 'Assign this specialty to a dentist in'],
        [/^Consultorio\s+(\d+)\s*\(Actual\)$/i, 'Office $1 (Current)'],
        [/^Consultorio\s+(\d+)\s*·\s*(.+)$/i, 'Office $1 · $2'],
        [/^Consultorio\s+(\d+)$/i, 'Office $1'],
        [/^(\d+)\s+Ocupados\s*\(Activos\)$/i, '$1 Occupied (Active)'],
        [/^(\d+)\s+Libres\s+de\s+(\d+)$/i, '$1 Free of $2'],
        [/^(\d+)\s+libres\s+de\s+(\d+)$/i, '$1 free of $2'],
        [/^Control en tiempo real de los (\d+) consultorios configurados en la sede: crea o elimina espacios según la capacidad de tu clínica\.$/i,
            'Real-time control of the $1 offices configured in this clinic: create or delete spaces according to your clinic capacity.'],
        [/^Consultorios activos \/ ocupados \((\d+)\):$/i, 'Active / occupied offices ($1):'],
        [/^Listado de consultorios libres disponibles \((\d+)\):$/i, 'Available free offices ($1):'],
        [/^Odontólogos inactivos \(sin consultorio activo — (\d+)\):$/i, 'Inactive dentists (no active office — $1):'],
        [/^Todos los consultorios \((\d+) de (\d+)\) están ocupados$/i, 'All offices ($1 of $2) are occupied'],
        [/^No quedan consultorios libres \((\d+) de (\d+) ocupados\)\. Crea uno nuevo si tu sede cuenta con más espacios\.$/i,
            'No free offices left ($1 of $2 occupied). Create a new one if your clinic has more rooms.'],
        [/^(\d+)\s+minutos?$/i, '$1 minutes'],
        [/^(\d+)\s+min$/i, '$1 min'],
        [/^(\d+)\s+de\s+(\d+)$/i, '$1 of $2'],
        [/^(\d+)\s+citas?$/i, '$1 appointments'],
        [/^\((\d+)\s+finalizadas\)$/i, '($1 completed)'],
        [/^(\d+)\s+solicitudes$/i, '$1 requests'],
        [/^(\d+)\s+pend\.$/i, '$1 pend.'],
        [/^(\d+)\s+conf\.$/i, '$1 conf.'],
        [/^(\d+)\s+comp\.$/i, '$1 comp.'],
        [/^(\d+)\s+canc\.$/i, '$1 canc.'],
        [/^Período:\s*(.+?)\s*→\s*(.+)$/i, 'Period: $1 → $2'],
        [/^Todas las atenciones completadas bajo el perfil de$/i, 'All completed care visits under the profile of'],
        [/^Todas las atenciones completadas bajo el perfil de\s+(.+)$/i, 'All completed care visits under the profile of $1'],
        [/^¡Atención!\s*Para poder confirmar que leíste todo, primero debes hacer clic en\s*"Ver más\.\.\."\s*y revisar la información completa de los 5 puntos \(te falta leer:\s*([^)]+)\)\.?$/i,
            'Attention! To confirm you read everything, you must first click "Read more..." and review all 5 points (remaining to read: $1).'],
        [/^El odontólogo seleccionado ya tiene una cita asignada para el día (.+?)\. Cada odontólogo solo puede atender máximo 1 cita por día\.$/i,
            'The selected dentist already has an appointment assigned on $1. Each dentist can handle at most 1 appointment per day.'],
        [/^Deben transcurrir al menos 4 días completos entre citas\. Ya tienes una cita programada o realizada el (.+)$/i,
            'At least 4 full days must pass between appointments. You already have an appointment on $1'],
        [/^Colombia · Clínica abierta \(08:00–19:30\)$/i, 'Colombia · Clinic Open (08:00–19:30)'],
        [/^Colombia · Agenda online 24\/7$/i, 'Colombia · 24/7 Online Booking'],
    ];

    // Patrones inversos EN -> ES (respaldo seguro ante nodos recreados dinámicamente en inglés)
    const REVERSE_PATTERNS = [
        [/^Hello,\s*(.+?)!$/i, '¡Hola, $1!'],
        [/^(\d+)\s+cancelled\s*·\s*(\d+)\s+no-show$/i, '$1 canceladas · $2 no-show'],
        [/^(\d+)\s+scheduled on future days$/i, '$1 programada(s) en días futuros'],
        [/^You cannot request an appointment for today at a past time \((.+?)\)\. Choose a time after (.+?) or select another date\.$/i,
            'No puedes solicitar una cita para hoy en una hora que ya pasó ($1). Elige un horario posterior a $2 o selecciona otra fecha.'],
        [/^You cannot schedule an appointment for today at a past time \((.+?)\)\. Choose a time after (.+?) or select another date\.$/i,
            'No puedes agendar una cita para hoy en una hora que ya pasó ($1). Elige un horario posterior a $2 o selecciona otra fecha.'],
        [/^Validated holder:\s*(.+)$/i, 'Titular validado: $1'],
        [/^Edit Appointment #(\d+)$/i, 'Editar Cita #$1'],
        [/^Schedule for\s+(.+)$/i, 'Agenda de $1'],
        [/^ID:\s*(.+)$/i, 'Cédula: $1'],
        [/^Document:\s*(.+)$/i, 'Documento: $1'],
        [/^Office\s+(\d+)\s*\(Free\)$/i, 'Consultorio $1 (Libre)'],
        [/^--\s*Specialist in\s+(.+?)\s*--$/i, '-- Especialista en $1 --'],
        [/^No active dentist in\s+(.+)$/i, 'Sin odontólogo activo en $1'],
        [/^Assign this specialty to a dentist in$/i, 'Asigna esta especialidad a un odontólogo en'],
        [/^Office\s+(\d+)\s*\(Current\)$/i, 'Consultorio $1 (Actual)'],
        [/^Office\s+(\d+)\s*·\s*(.+)$/i, 'Consultorio $1 · $2'],
        [/^Office\s+(\d+)$/i, 'Consultorio $1'],
        [/^(\d+)\s+Occupied\s*\(Active\)$/i, '$1 Ocupados (Activos)'],
        [/^(\d+)\s+Free\s+of\s+(\d+)$/i, '$1 Libres de $2'],
        [/^(\d+)\s+free\s+of\s+(\d+)$/i, '$1 libres de $2'],
        [/^Real-time control of the (\d+) offices configured in this clinic: create or delete spaces according to your clinic capacity\.$/i,
            'Control en tiempo real de los $1 consultorios configurados en la sede: crea o elimina espacios según la capacidad de tu clínica.'],
        [/^Active \/ occupied offices \((\d+)\):$/i, 'Consultorios activos / ocupados ($1):'],
        [/^Available free offices \((\d+)\):$/i, 'Listado de consultorios libres disponibles ($1):'],
        [/^Inactive dentists \(no active office — (\d+)\):$/i, 'Odontólogos inactivos (sin consultorio activo — $1):'],
        [/^All offices \((\d+) of (\d+)\) are occupied$/i, 'Todos los consultorios ($1 de $2) están ocupados'],
        [/^No free offices left \((\d+) of (\d+) occupied\)\. Create a new one if your clinic has more rooms\.$/i,
            'No quedan consultorios libres ($1 de $2 ocupados). Crea uno nuevo si tu sede cuenta con más espacios.'],
        [/^(\d+)\s+minutes$/i, '$1 minutos'],
        [/^(\d+)\s+of\s+(\d+)$/i, '$1 de $2'],
        [/^(\d+)\s+appointments$/i, '$1 citas'],
        [/^\((\d+)\s+completed\)$/i, '($1 finalizadas)'],
        [/^(\d+)\s+requests$/i, '$1 solicitudes'],
        [/^Period:\s*(.+?)\s*→\s*(.+)$/i, 'Período: $1 → $2'],
        [/^All completed care visits under the profile of\s+(.+)$/i, 'Todas las atenciones completadas bajo el perfil de $1'],
        [/^All completed care visits under the profile of$/i, 'Todas las atenciones completadas bajo el perfil de'],
        [/^Attention! To confirm you read everything, you must first click "Read more\.\.\." and review all 5 points \(remaining to read:\s*([^)]+)\)\.$/i,
            '¡Atención! Para poder confirmar que leíste todo, primero debes hacer clic en "Ver más..." y revisar la información completa de los 5 puntos (te falta leer: $1).'],
        [/^The selected dentist already has an appointment assigned on (.+?)\. Each dentist can handle at most 1 appointment per day\.$/i,
            'El odontólogo seleccionado ya tiene una cita asignada para el día $1. Cada odontólogo solo puede atender máximo 1 cita por día.'],
        [/^At least 4 full days must pass between appointments\. You already have an appointment on (.+)$/i,
            'Deben transcurrir al menos 4 días completos entre citas. Ya tienes una cita programada o realizada el $1'],
        [/^Colombia · Clinic Open \(08:00–19:30\)$/i, 'Colombia · Clínica abierta (08:00–19:30)'],
        [/^Colombia · 24\/7 Online Booking$/i, 'Colombia · Agenda online 24/7'],
    ];

    // Diccionario inverso EN -> ES construido automáticamente desde DICT
    const REVERSE_DICT = Object.create(null);
    (function buildReverseDict() {
        const tieneAcento = (s) => /[áéíóúüñ¡¿]/i.test(s);
        for (const esKey in DICT) {
            if (!Object.prototype.hasOwnProperty.call(DICT, esKey)) continue;
            const enVal = DICT[esKey];
            if (!enVal || enVal === esKey) continue;
            if (!(enVal in REVERSE_DICT) || (tieneAcento(esKey) && !tieneAcento(REVERSE_DICT[enVal]))) {
                REVERSE_DICT[enVal] = esKey;
            }
        }
    })();

    const OBSERVER_CONFIG = {
        childList: true,
        subtree: true,
        characterData: true,
    };

    const origText = new WeakMap();
    const origAttrs = new WeakMap();
    let origDocTitle = document.title;
    let currentLang = 'es';
    let currentCurrency = 'COP';
    let isApplying = false;
    let observer = null;

    /**
     * Bloquea el traductor automático del navegador (Chrome / Edge / Brave) para evitar
     * que inyecte nodos <font>, corrompa referencias del DOM o choque con ES <-> EN de DENTIX.
     */
    function ensureNoBrowserTranslate() {
        try {
            if (document.documentElement) {
                document.documentElement.setAttribute('translate', 'no');
                document.documentElement.classList.add('notranslate');
            }
            if (document.head && !document.head.querySelector('meta[name="google"][content="notranslate"]')) {
                const meta = document.createElement('meta');
                meta.name = 'google';
                meta.content = 'notranslate';
                document.head.appendChild(meta);
            }
        } catch (e) {
            // Ignorar
        }
    }

    /**
     * Pausa y desconecta el MutationObserver mientras aplicamos cambios propios al DOM,
     * evitando que la cola asíncrona de mutaciones sobrescriba origText con texto traducido.
     */
    function withObserverPaused(fn) {
        const shouldReconnect = Boolean(observer);
        if (observer) {
            observer.takeRecords();
            observer.disconnect();
        }
        const prevApplying = isApplying;
        isApplying = true;
        try {
            return fn();
        } finally {
            isApplying = prevApplying;
            if (shouldReconnect && !prevApplying && observer && document.body) {
                observer.observe(document.body, OBSERVER_CONFIG);
            }
        }
    }

    function readCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function detectInitialLang() {
        try {
            const urlLang = new URLSearchParams(window.location.search).get('lang');
            if (urlLang === 'en' || urlLang === 'es') return urlLang;
            const stored = window.localStorage.getItem(STORAGE_KEY);
            if (stored === 'en' || stored === 'es') return stored;
        } catch (e) {
            // Ignorar si storage está bloqueado
        }
        const cookieVal = readCookie(COOKIE_NAME);
        return cookieVal === 'en' ? 'en' : 'es';
    }

    function detectInitialCurrency() {
        try {
            const urlCur = new URLSearchParams(window.location.search).get('currency');
            if (urlCur === 'USD' || urlCur === 'COP') return urlCur;
            const stored = window.localStorage.getItem(CURRENCY_STORAGE_KEY);
            if (stored === 'USD' || stored === 'COP') return stored;
        } catch (e) {
            // Ignorar
        }
        const cookieVal = readCookie(CURRENCY_COOKIE_NAME);
        return cookieVal === 'USD' ? 'USD' : 'COP';
    }

    function persistLang(lang) {
        try {
            window.localStorage.setItem(STORAGE_KEY, lang);
        } catch (e) {
            // Ignorar
        }
        document.cookie = COOKIE_NAME + '=' + lang + ';path=/;max-age=31536000;SameSite=Lax';
    }

    function persistCurrency(currency) {
        try {
            window.localStorage.setItem(CURRENCY_STORAGE_KEY, currency);
        } catch (e) {
            // Ignorar
        }
        document.cookie = CURRENCY_COOKIE_NAME + '=' + currency + ';path=/;max-age=31536000;SameSite=Lax';
    }

    function formatCop(amount) {
        return Math.round(Number(amount) || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    }

    function formatUsd(amountCop) {
        const usd = (Number(amountCop) || 0) / COP_PER_USD;
        return usd.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function translateRaw(raw) {
        if (!raw || typeof raw !== 'string') return raw;
        const matchWs = raw.match(/^(\s*)([\s\S]*?)(\s*)$/);
        if (!matchWs) return raw;

        const lead = matchWs[1];
        const body = matchWs[2];
        const trail = matchWs[3];
        if (!body) return raw;

        const normalized = body.replace(/\s+/g, ' ').trim();
        if (Object.prototype.hasOwnProperty.call(DICT, normalized)) {
            return lead + DICT[normalized] + trail;
        }

        for (let i = 0; i < PATTERNS.length; i++) {
            const [regex, replacement] = PATTERNS[i];
            if (regex.test(normalized)) {
                return lead + normalized.replace(regex, replacement) + trail;
            }
        }

        if (normalized.includes('te falta leer:')) {
            return lead + normalized.replace(
                /^¡Atención!.*?te falta leer:\s*([^)]+)\)\.?$/i,
                'Attention! First click "Read more..." on all 5 points (remaining: $1).'
            ) + trail;
        }

        return raw;
    }

    function translateToSpanish(raw) {
        if (!raw || typeof raw !== 'string') return raw;
        const matchWs = raw.match(/^(\s*)([\s\S]*?)(\s*)$/);
        if (!matchWs) return raw;

        const lead = matchWs[1];
        const body = matchWs[2];
        const trail = matchWs[3];
        if (!body) return raw;

        const normalized = body.replace(/\s+/g, ' ').trim();
        if (normalized in REVERSE_DICT) {
            return lead + REVERSE_DICT[normalized] + trail;
        }

        for (let i = 0; i < REVERSE_PATTERNS.length; i++) {
            const [regex, replacement] = REVERSE_PATTERNS[i];
            if (regex.test(normalized)) {
                return lead + normalized.replace(regex, replacement) + trail;
            }
        }

        if (normalized.includes('remaining:')) {
            return lead + normalized.replace(
                /^Attention!.*?remaining:\s*([^)]+)\)\.?$/i,
                '¡Atención! Primero haz clic en "Ver más..." en los 5 puntos (te falta leer: $1).'
            ) + trail;
        }

        return raw;
    }

    function isKnownEnglish(raw) {
        if (!raw || typeof raw !== 'string') return false;
        const normalized = raw.replace(/\s+/g, ' ').trim();
        if (!normalized) return false;
        if (normalized in REVERSE_DICT && !Object.prototype.hasOwnProperty.call(DICT, normalized)) {
            return true;
        }
        for (let i = 0; i < REVERSE_PATTERNS.length; i++) {
            if (REVERSE_PATTERNS[i][0].test(normalized)) {
                return true;
            }
        }
        return false;
    }

    const TRANSLATABLE_ATTRS = ['placeholder', 'title', 'aria-label', 'data-label'];

    function processTextNode(node, lang) {
        if (!node || !node.parentElement) return;
        const tag = node.parentElement.tagName;
        if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'NOSCRIPT' || tag === 'TEXTAREA') return;
        if (node.parentElement.closest('[data-i18n-ignore], [data-cop-price], [data-cop-input-preview]')) return;

        if (lang === 'en') {
            if (!origText.has(node)) {
                const cur = node.nodeValue || '';
                origText.set(node, isKnownEnglish(cur) ? translateToSpanish(cur) : cur);
            }
            const base = origText.get(node);
            const translated = translateRaw(base);
            if (translated !== node.nodeValue) {
                node.nodeValue = translated;
            }
        } else {
            let targetEs = null;
            if (origText.has(node)) {
                const stored = origText.get(node);
                targetEs = isKnownEnglish(stored) ? translateToSpanish(stored) : stored;
                if (targetEs !== stored) {
                    origText.set(node, targetEs);
                }
            } else {
                const cur = node.nodeValue || '';
                const reversed = translateToSpanish(cur);
                if (reversed !== cur) {
                    targetEs = reversed;
                    origText.set(node, reversed);
                }
            }
            if (targetEs !== null && node.nodeValue !== targetEs) {
                node.nodeValue = targetEs;
            }
        }
    }

    function processElementAttrs(el, lang) {
        if (!el || el.nodeType !== 1) return;
        if (el.closest('[data-i18n-ignore], [data-cop-price]')) return;

        let map = origAttrs.get(el);
        if (!map) {
            map = {};
            origAttrs.set(el, map);
        }

        for (let i = 0; i < TRANSLATABLE_ATTRS.length; i++) {
            const attr = TRANSLATABLE_ATTRS[i];
            if (!el.hasAttribute(attr)) continue;
            if (lang === 'en') {
                if (!(attr in map)) {
                    const curAttr = el.getAttribute(attr);
                    map[attr] = isKnownEnglish(curAttr) ? translateToSpanish(curAttr) : curAttr;
                }
                const tr = translateRaw(map[attr]);
                if (tr !== el.getAttribute(attr)) {
                    el.setAttribute(attr, tr);
                }
            } else if (attr in map) {
                const restored = isKnownEnglish(map[attr]) ? translateToSpanish(map[attr]) : map[attr];
                map[attr] = restored;
                if (el.getAttribute(attr) !== restored) {
                    el.setAttribute(attr, restored);
                }
            } else {
                const curAttr = el.getAttribute(attr);
                const rev = translateToSpanish(curAttr);
                if (rev !== curAttr) {
                    map[attr] = rev;
                    el.setAttribute(attr, rev);
                }
            }
        }

        if (el.tagName === 'INPUT' && (el.type === 'submit' || el.type === 'button' || el.type === 'reset')) {
            if (lang === 'en') {
                if (!('value' in map)) {
                    map.value = isKnownEnglish(el.value) ? translateToSpanish(el.value) : el.value;
                }
                const tr = translateRaw(map.value);
                if (tr !== el.value) el.value = tr;
            } else if ('value' in map) {
                const restored = isKnownEnglish(map.value) ? translateToSpanish(map.value) : map.value;
                map.value = restored;
                if (el.value !== restored) el.value = restored;
            }
        }
    }

    function walkAndApply(root, lang) {
        if (!root) return;
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT, null);
        let current = walker.currentNode;
        while (current) {
            if (current.nodeType === 1) {
                processElementAttrs(current, lang);
            } else if (current.nodeType === 3) {
                processTextNode(current, lang);
            }
            current = walker.nextNode();
        }
    }

    function syncControlsUI(lang, currency) {
        const footerSelect = document.getElementById('dentixLangSelect');
        if (footerSelect && footerSelect.value !== lang) {
            footerSelect.value = lang;
        }

        const currencySelect = document.getElementById('dentixCurrencySelect');
        const currencyTag = document.getElementById('dentixCurrencyTag');
        if (currencySelect && currencySelect.value !== currency) {
            currencySelect.value = currency;
        }
        if (currencyTag) {
            currencyTag.textContent = currency;
        }

        const floatingLangBtns = document.querySelectorAll('[data-dentix-set-lang]');
        floatingLangBtns.forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.dentixSetLang === lang);
        });

        const floatingCurBtns = document.querySelectorAll('[data-dentix-set-currency]');
        floatingCurBtns.forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.dentixSetCurrency === currency);
        });
    }

    function updateInputPreviews(currency) {
        document.querySelectorAll('[data-cop-input-preview]').forEach((previewEl) => {
            const inputSel = previewEl.getAttribute('data-cop-input-preview');
            const inputEl = inputSel ? document.querySelector(inputSel) : null;
            if (!inputEl) return;

            const rawVal = parseFloat(inputEl.value || '0');
            const amount = Number.isFinite(rawVal) && rawVal >= 0 ? rawVal : 0;
            const usdText = formatUsd(amount);
            const copText = formatCop(amount);

            if (currentLang === 'en') {
                previewEl.textContent =
                    currency === 'USD'
                        ? 'Showing catalog in USD: ≈ $' + usdText + ' USD (Stored in COP: $' + copText + ', ref. 1 USD = $4,000 COP)'
                        : 'Approx. equivalent in USD: $' + usdText + ' USD (ref. 1 USD = $4,000 COP)';
            } else {
                previewEl.textContent =
                    currency === 'USD'
                        ? 'Vista en dólares: ≈ $' + usdText + ' USD (Se guarda en COP: $' + copText + ', tasa ref. 1 USD = $4.000 COP)'
                        : 'Equivalente aproximado en dólares: $' + usdText + ' USD (tasa ref. 1 USD = $4.000 COP)';
            }
        });
    }

    function syncFooterCurrencyLabels() {
        document.querySelectorAll('.mega-footer__item span').forEach((spanEl) => {
            const txt = (spanEl.textContent || '').trim();
            if (
                txt === 'Tratamientos, tiempos y tarifas COP' ||
                txt === 'Tratamientos, tiempos y tarifas USD' ||
                txt === 'Treatments, durations, and COP pricing' ||
                txt === 'Treatments, durations, and USD pricing'
            ) {
                const esLabel = currentCurrency === 'USD'
                    ? 'Tratamientos, tiempos y tarifas USD'
                    : 'Tratamientos, tiempos y tarifas COP';
                const enLabel = currentCurrency === 'USD'
                    ? 'Treatments, durations, and USD pricing'
                    : 'Treatments, durations, and COP pricing';
                spanEl.textContent = currentLang === 'en' ? enLabel : esLabel;
                if (spanEl.firstChild && spanEl.firstChild.nodeType === 3) {
                    origText.set(spanEl.firstChild, esLabel);
                }
            }
        });
    }

    function applyCurrency(currency) {
        currentCurrency = currency === 'USD' ? 'USD' : 'COP';
        persistCurrency(currentCurrency);

        withObserverPaused(() => {
            document.querySelectorAll('[data-cop-price]').forEach((el) => {
                const copVal = parseFloat(el.getAttribute('data-cop-price') || '0');
                const fmt = el.getAttribute('data-price-format') || 'table';

                if (currentCurrency === 'USD') {
                    const usd = formatUsd(copVal);
                    if (fmt === 'card') {
                        el.innerHTML = '$' + usd + ' <small>USD</small>';
                    } else if (fmt === 'est') {
                        el.textContent = '$' + usd + ' USD est.';
                    } else {
                        el.textContent = '$' + usd + ' USD';
                    }
                    el.setAttribute(
                        'title',
                        currentLang === 'en'
                            ? 'COP equivalent: $' + formatCop(copVal) + ' COP (1 USD = $4,000 COP)'
                            : 'Equivalente COP: $' + formatCop(copVal) + ' COP (1 USD = $4.000 COP)'
                    );
                } else {
                    const cop = formatCop(copVal);
                    if (fmt === 'card') {
                        el.innerHTML = '$' + cop + ' <small>COP</small>';
                    } else if (fmt === 'est') {
                        el.textContent = '$ ' + cop + ' est.';
                    } else {
                        el.textContent = '$ ' + cop;
                    }
                    el.setAttribute(
                        'title',
                        currentLang === 'en'
                            ? 'USD equivalent: $' + formatUsd(copVal) + ' USD'
                            : 'Equivalente USD: $' + formatUsd(copVal) + ' USD'
                    );
                }
            });

            syncFooterCurrencyLabels();
            updateInputPreviews(currentCurrency);
            syncControlsUI(currentLang, currentCurrency);
        });
    }

    function applyLanguage(lang) {
        currentLang = lang === 'en' ? 'en' : 'es';
        persistLang(currentLang);
        ensureNoBrowserTranslate();
        document.documentElement.lang = currentLang;

        withObserverPaused(() => {
            if (currentLang === 'en') {
                document.title = translateRaw(origDocTitle);
            } else {
                document.title = origDocTitle;
            }

            if (document.body) {
                walkAndApply(document.body, currentLang);
            }
            applyCurrency(currentCurrency);
            syncControlsUI(currentLang, currentCurrency);
        });

        try {
            window.dispatchEvent(new CustomEvent('dentix:langchange', { detail: { lang: currentLang } }));
        } catch (e) {
            // Ignorar
        }
    }

    function injectFloatingSwitcher() {
        if (document.getElementById('dentixLangFloating')) return;

        const style = document.createElement('style');
        style.textContent = `
            .dentix-lang-floating {
                position: fixed;
                right: 16px;
                bottom: 16px;
                z-index: 9998;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 4px 6px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.94);
                border: 1px solid rgba(10, 122, 112, 0.25);
                box-shadow: 0 10px 25px -10px rgba(7, 45, 48, 0.35);
                backdrop-filter: blur(8px);
                font-family: "Manrope", system-ui, sans-serif;
            }
            .dentix-lang-floating__icon {
                color: #0a7a70;
                font-size: 0.78rem;
                padding: 0 3px 0 6px;
            }
            .dentix-lang-floating__sep {
                width: 1px;
                height: 16px;
                background: rgba(10, 122, 112, 0.22);
                margin: 0 3px;
            }
            .dentix-lang-floating button {
                border: 0;
                background: transparent;
                color: #36595b;
                font-size: 0.71rem;
                font-weight: 800;
                padding: 4px 8px;
                border-radius: 999px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .dentix-lang-floating button.is-active {
                background: linear-gradient(135deg, #14b8a6, #0a7a70);
                color: #ffffff;
            }
            @media print {
                .dentix-lang-floating { display: none !important; }
            }
        `;
        document.head.appendChild(style);

        const wrap = document.createElement('div');
        wrap.id = 'dentixLangFloating';
        wrap.className = 'dentix-lang-floating notranslate';
        wrap.setAttribute('data-i18n-ignore', 'true');
        wrap.setAttribute('translate', 'no');
        wrap.setAttribute('aria-label', 'Language & Currency');
        wrap.innerHTML =
            '<i class="fa-solid fa-globe dentix-lang-floating__icon" aria-hidden="true"></i>' +
            '<button type="button" data-dentix-set-lang="es">ES</button>' +
            '<button type="button" data-dentix-set-lang="en">EN</button>' +
            '<span class="dentix-lang-floating__sep" aria-hidden="true"></span>' +
            '<button type="button" data-dentix-set-currency="COP">COP</button>' +
            '<button type="button" data-dentix-set-currency="USD">USD</button>';

        document.body.appendChild(wrap);

        wrap.querySelectorAll('[data-dentix-set-lang]').forEach((btn) => {
            btn.addEventListener('click', () => {
                applyLanguage(btn.dataset.dentixSetLang);
            });
        });

        wrap.querySelectorAll('[data-dentix-set-currency]').forEach((btn) => {
            btn.addEventListener('click', () => {
                applyCurrency(btn.dataset.dentixSetCurrency);
            });
        });
    }

    function bindFooterSelectors() {
        const footerSelect = document.getElementById('dentixLangSelect');
        if (footerSelect) {
            footerSelect.value = currentLang;
            footerSelect.setAttribute('translate', 'no');
            const onLangChange = () => applyLanguage(footerSelect.value);
            footerSelect.addEventListener('change', onLangChange);
            footerSelect.addEventListener('input', onLangChange);
        }

        const currencySelect = document.getElementById('dentixCurrencySelect');
        if (currencySelect) {
            currencySelect.value = currentCurrency;
            currencySelect.setAttribute('translate', 'no');
            const onCurChange = () => applyCurrency(currencySelect.value);
            currencySelect.addEventListener('change', onCurChange);
            currencySelect.addEventListener('input', onCurChange);
        }

        document.querySelectorAll('[data-cop-input-preview]').forEach((previewEl) => {
            const sel = previewEl.getAttribute('data-cop-input-preview');
            const inputEl = sel ? document.querySelector(sel) : null;
            if (inputEl) {
                inputEl.addEventListener('input', () => updateInputPreviews(currentCurrency));
            }
        });
    }

    function startObserver() {
        if (observer || !document.body) return;
        observer = new MutationObserver((mutations) => {
            if (isApplying || currentLang !== 'en') return;

            withObserverPaused(() => {
                for (let i = 0; i < mutations.length; i++) {
                    const m = mutations[i];
                    if (m.type === 'characterData') {
                        const node = m.target;
                        if (node.parentElement && !node.parentElement.closest('[data-i18n-ignore], [data-cop-price], [data-cop-input-preview]')) {
                            const val = node.nodeValue || '';
                            const prevOrig = origText.get(node);
                            // Nunca sobrescribir el original español con su propia traducción inglesa
                            if (prevOrig !== undefined && translateRaw(prevOrig) === val) {
                                continue;
                            }
                            if (!isKnownEnglish(val)) {
                                origText.set(node, val);
                            } else if (!origText.has(node)) {
                                origText.set(node, translateToSpanish(val));
                            }
                            processTextNode(node, 'en');
                        }
                    } else if (m.type === 'childList') {
                        m.addedNodes.forEach((added) => {
                            if (added.nodeType === 1) {
                                if (!added.closest('[data-i18n-ignore], [data-cop-price], [data-cop-input-preview]')) {
                                    walkAndApply(added, 'en');
                                }
                            } else if (added.nodeType === 3) {
                                processTextNode(added, 'en');
                            }
                        });
                    }
                }
            });
        });

        observer.observe(document.body, OBSERVER_CONFIG);
    }

    // Traducir también diálogos nativos window.confirm(...)
    const nativeConfirm = window.confirm.bind(window);
    window.confirm = function (message) {
        const finalMsg = currentLang === 'en' ? translateRaw(String(message)) : message;
        return nativeConfirm(finalMsg);
    };

    window.DENTIX_I18N = {
        getLang: () => currentLang,
        setLang: (lang) => applyLanguage(lang),
        getCurrency: () => currentCurrency,
        setCurrency: (currency) => applyCurrency(currency),
        t: (str) => (currentLang === 'en' ? translateRaw(str) : str),
        toEs: (str) => translateToSpanish(str),
    };

    function initDentixI18n() {
        ensureNoBrowserTranslate();
        origDocTitle = isKnownEnglish(document.title) ? translateToSpanish(document.title) : document.title;
        currentLang = detectInitialLang();
        currentCurrency = detectInitialCurrency();
        injectFloatingSwitcher();
        bindFooterSelectors();
        applyLanguage(currentLang);
        startObserver();
    }

    ensureNoBrowserTranslate();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDentixI18n);
    } else {
        initDentixI18n();
    }
})();
