-- =====================================================================
-- SISTEMA DE CITAS ODONTOLÓGICAS (DENTIX) - VERSIÓN 4.0 (alineada con SRS DENTIX v4.0)
-- Script maestro SEGURO e IDEMPOTENTE para PostgreSQL / pgAdmin
-- (NO ejecuta DROP TABLE / CASCADE: preserva datos existentes de la clínica.
--  Para reinicio destructivo desde cero en laboratorio use: database/reset_laboratorio.sql)
-- =====================================================================

-- 1. EXTENSIÓN CRIPTOGRÁFICA (bcrypt: crypt / gen_salt)
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- 2. TIPO ENUM (Estados de la cita - creación segura idempotente)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_cita') THEN
        CREATE TYPE estado_cita AS ENUM (
            'pendiente',
            'confirmada',
            'completada',
            'cancelada',
            'no_show'
        );
    END IF;
END $$;



-- =====================================================================
-- 3. TABLAS MAESTRAS Y ENTIDADES CON AUTENTICACIÓN (NO DESTRUCTIVO)
-- =====================================================================

-- ---------- Paciente ----------
CREATE TABLE IF NOT EXISTS paciente (
    id            INT GENERATED ALWAYS AS IDENTITY,
    nombre        VARCHAR(100) NOT NULL,
    telefono      VARCHAR(20)  NOT NULL,
    email         VARCHAR(100),
    cedula        VARCHAR(30)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fecha_nac     DATE,
    alergias      TEXT,
    
    CONSTRAINT pk_paciente PRIMARY KEY (id),
    CONSTRAINT ck_paciente_nombre CHECK (length(trim(nombre)) > 0),
    CONSTRAINT ck_paciente_nombre_formato CHECK (
        nombre ~ '^((Mc|Mac|[A-ZÁÉÍÓÚÜÑ][''-])?[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(-[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*)( ((de|del|la|las|los|y|da|do|dos) )*((Mc|Mac|[A-ZÁÉÍÓÚÜÑ][''-])?[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(-[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*))*$'
    ),
    CONSTRAINT ck_paciente_telefono CHECK (length(trim(telefono)) > 0),
    CONSTRAINT ck_paciente_fecha_nac CHECK (fecha_nac IS NULL OR fecha_nac <= CURRENT_DATE)
);



-- ---------- Recepcionista ----------
CREATE TABLE IF NOT EXISTS recepcionista (
    id            INT GENERATED ALWAYS AS IDENTITY,
    nombre        VARCHAR(100) NOT NULL,
    telefono      VARCHAR(20)  NOT NULL,
    email         VARCHAR(100) UNIQUE NOT NULL, -- Usuario para login
    password_hash VARCHAR(255) NOT NULL,        -- Hash seguro de contraseña (bcrypt)
    activo        BOOLEAN      NOT NULL DEFAULT TRUE,
    ultimo_acceso TIMESTAMP,
    
    CONSTRAINT pk_recepcionista PRIMARY KEY (id),
    CONSTRAINT ck_recepcionista_nombre CHECK (length(trim(nombre)) > 0),
    CONSTRAINT ck_recepcionista_email CHECK (email LIKE '%@%.%')
);



-- ---------- Consultorio (Catálogo dinámico de espacios físicos por sede - RF-041 / RN-20) ----------
CREATE TABLE IF NOT EXISTS consultorio (
    id              INT GENERATED ALWAYS AS IDENTITY,
    nombre          VARCHAR(30)  NOT NULL,
    creado_en       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_consultorio PRIMARY KEY (id),
    CONSTRAINT ck_consultorio_nombre CHECK (length(trim(nombre)) > 0)
);



-- ---------- Odontólogo ----------
CREATE TABLE IF NOT EXISTS odontologo (
    id              INT GENERATED ALWAYS AS IDENTITY,
    nombre          VARCHAR(100) NOT NULL,
    especialidad    VARCHAR(60)  NOT NULL,
    consultorio     VARCHAR(30),
    slug_publico    VARCHAR(60)  NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL, -- Usuario para login
    password_hash   VARCHAR(255) NOT NULL,        -- Hash seguro de contraseña
    activo          BOOLEAN      NOT NULL DEFAULT TRUE,
    ultimo_acceso   TIMESTAMP,
    
    CONSTRAINT pk_odontologo PRIMARY KEY (id),
    CONSTRAINT uq_odontologo_slug UNIQUE (slug_publico),
    CONSTRAINT ck_odontologo_nombre CHECK (length(trim(nombre)) > 0),
    CONSTRAINT ck_odontologo_email CHECK (email LIKE '%@%.%')
);



-- ---------- Servicio ----------
CREATE TABLE IF NOT EXISTS servicio (
    id              INT GENERATED ALWAYS AS IDENTITY,
    nombre          VARCHAR(80)   NOT NULL,
    duracion_min    INT           NOT NULL,
    precio          NUMERIC(10,2) NOT NULL DEFAULT 0,
    descripcion     TEXT,
    
    CONSTRAINT pk_servicio PRIMARY KEY (id),
    CONSTRAINT uq_servicio_nombre UNIQUE (nombre),
    CONSTRAINT ck_servicio_duracion CHECK (duracion_min >= 15),
    CONSTRAINT ck_servicio_precio CHECK (precio >= 0)
);





-- =====================================================================
-- 4. TABLA TRANSACCIONAL: CITA
-- =====================================================================
CREATE TABLE IF NOT EXISTS cita (
    id                  INT GENERATED ALWAYS AS IDENTITY,
    paciente_id         INT          NOT NULL,
    odontologo_id       INT,                   -- NULL cuando el paciente la solicita; asignado por Recepción al confirmar
    servicio_id         INT          NOT NULL,
    recepcionista_id    INT,                   -- Recepcionista autenticada que gestionó/asignó
    fecha               DATE         NOT NULL,
    hora                TIME         NOT NULL,
    estado              estado_cita  NOT NULL DEFAULT 'pendiente',
    motivo              VARCHAR(200),
    motivo_cancelacion  VARCHAR(200),
    creado_en           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_cita PRIMARY KEY (id),
    
    CONSTRAINT fk_cita_paciente
        FOREIGN KEY (paciente_id) REFERENCES paciente (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
        
    CONSTRAINT fk_cita_odontologo
        FOREIGN KEY (odontologo_id) REFERENCES odontologo (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
        
    CONSTRAINT fk_cita_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicio (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_cita_recepcionista
        FOREIGN KEY (recepcionista_id) REFERENCES recepcionista (id)
        ON UPDATE CASCADE ON DELETE SET NULL,

    -- RN-02: Sin choques de horario por odontólogo en la misma fecha y hora
    CONSTRAINT uq_agenda UNIQUE (odontologo_id, fecha, hora),

    -- RN-03: Atención de 08:00 a 19:30 en intervalos estrictos de 30 minutos
    CONSTRAINT ck_cita_horario CHECK (
        hora >= TIME '08:00'
        AND hora <= TIME '19:30'
        AND EXTRACT(MINUTE FROM hora) IN (0, 30)
        AND EXTRACT(SECOND FROM hora) = 0
    ),

    -- RN-06: La cancelación exige motivo obligatoriamente
    CONSTRAINT ck_cita_cancelacion CHECK (
        estado <> 'cancelada'
        OR length(trim(COALESCE(motivo_cancelacion, ''))) > 0
    )
);






-- =====================================================================
-- 5. DETALLE_CITA (Detalle clínico, 1 por cita completada)
-- =====================================================================
CREATE TABLE IF NOT EXISTS detalle_cita (
    id              INT GENERATED ALWAYS AS IDENTITY,
    cita_id         INT       NOT NULL,
    servicio_id     INT       NOT NULL,
    detalle         TEXT,
    procedimiento   TEXT,
    nota_clinica    TEXT,
    fecha_salida    DATE,
    hora_salida     TIME,
    creado_en       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_detalle_cita PRIMARY KEY (id),
    
    CONSTRAINT fk_detalle_cita
        FOREIGN KEY (cita_id) REFERENCES cita (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
        
    CONSTRAINT fk_detalle_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicio (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    -- RN-04: Máximo un detalle clínico por cita
    CONSTRAINT uq_detalle_cita UNIQUE (cita_id)
);







-- =====================================================================
-- 6. DISPARADORES Y REGLAS DE NEGOCIO (TRIGGERS)
-- =====================================================================

-- RN-07: Control de frecuencia de citas por paciente
-- No permite repetir cita el mismo día y exige un margen mínimo de 4 días completos entre citas.
CREATE OR REPLACE FUNCTION fn_validar_espaciado_citas_paciente()
RETURNS TRIGGER AS $$
DECLARE
    v_conflicto RECORD;
BEGIN
    IF NEW.estado = 'cancelada' THEN
        RETURN NEW;
    END IF;

    -- Bloqueo transaccional por paciente para evitar condiciones de carrera en solicitudes simultáneas (RN-07)
    PERFORM pg_advisory_xact_lock(NEW.paciente_id);

    -- RN-01: Solo odontólogos activos pueden recibir asignación de citas pendientes o confirmadas
    IF NEW.odontologo_id IS NOT NULL AND NEW.estado IN ('pendiente', 'confirmada') THEN
        IF EXISTS (
            SELECT 1
            FROM odontologo
            WHERE id = NEW.odontologo_id
              AND activo IS FALSE
        ) THEN
            RAISE EXCEPTION 'No se puede asignar la cita a un odontólogo inactivo (RN-01).';
        END IF;

        -- RN-21 / RF-044: La especialidad del odontólogo asignado debe coincidir con el servicio solicitado por el paciente
        IF EXISTS (
            SELECT 1
            FROM odontologo o
            INNER JOIN servicio s ON s.id = NEW.servicio_id
            WHERE o.id = NEW.odontologo_id
              AND lower(trim(o.especialidad)) <> lower(trim(s.nombre))
        ) THEN
            RAISE EXCEPTION 'La especialidad del odontólogo no coincide con el servicio solicitado por el paciente (RN-21).';
        END IF;
    END IF;

    SELECT id, fecha, estado 
    INTO v_conflicto
    FROM cita
    WHERE paciente_id = NEW.paciente_id
      AND id <> COALESCE(NEW.id, 0)
      AND estado IN ('pendiente', 'confirmada', 'completada')
      AND ABS(fecha - NEW.fecha) < 5
    LIMIT 1;

    IF FOUND THEN
        IF v_conflicto.fecha = NEW.fecha THEN
            RAISE EXCEPTION 'El paciente ya cuenta con una cita registrada para el mismo día (%).', NEW.fecha;
        ELSE
            RAISE EXCEPTION 'Deben pasar al menos 4 días entre citas. El paciente ya tiene una cita el % (estado: %).', v_conflicto.fecha, v_conflicto.estado;
        END IF;
    END IF;

    -- RN-08: Máximo 1 cita por odontólogo por día
    IF NEW.odontologo_id IS NOT NULL AND NEW.estado IN ('confirmada', 'completada') THEN
        IF EXISTS (
            SELECT 1 
            FROM cita 
            WHERE odontologo_id = NEW.odontologo_id 
              AND fecha = NEW.fecha 
              AND id <> COALESCE(NEW.id, 0) 
              AND estado IN ('confirmada', 'completada')
        ) THEN
            RAISE EXCEPTION 'El odontólogo ya tiene una cita asignada para el día %. Máximo 1 cita por odontólogo al día.', NEW.fecha;
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_validar_espaciado_citas ON cita;
CREATE TRIGGER trg_validar_espaciado_citas
BEFORE INSERT OR UPDATE OF paciente_id, odontologo_id, servicio_id, fecha, estado ON cita
FOR EACH ROW
EXECUTE FUNCTION fn_validar_espaciado_citas_paciente();

-- RN-04 / RN-13: Validación de integridad del detalle clínico en el motor
CREATE OR REPLACE FUNCTION fn_validar_integridad_detalle_cita()
RETURNS TRIGGER AS $$
DECLARE
    v_cita RECORD;
BEGIN
    SELECT id, fecha, estado
    INTO v_cita
    FROM cita
    WHERE id = NEW.cita_id;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'La cita asociada al detalle clínico no existe.';
    END IF;

    IF v_cita.estado IN ('cancelada', 'no_show') THEN
        RAISE EXCEPTION 'No se puede registrar detalle clínico sobre una cita en estado % (RN-04 / RN-13).', v_cita.estado;
    END IF;

    IF NEW.fecha_salida IS NOT NULL AND NEW.fecha_salida < v_cita.fecha THEN
        RAISE EXCEPTION 'La fecha de salida (%) no puede ser anterior a la fecha de la cita (%).', NEW.fecha_salida, v_cita.fecha;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_validar_detalle_cita ON detalle_cita;
CREATE TRIGGER trg_validar_detalle_cita
BEFORE INSERT OR UPDATE ON detalle_cita
FOR EACH ROW
EXECUTE FUNCTION fn_validar_integridad_detalle_cita();





-- =====================================================================
-- 7. ÍNDICES ESTRATÉGICOS DE RENDIMIENTO Y BÚSQUEDA
-- =====================================================================
CREATE INDEX IF NOT EXISTS ix_recepcionista_email ON recepcionista (lower(email));
CREATE INDEX IF NOT EXISTS ix_odontologo_email ON odontologo (lower(email));
CREATE INDEX IF NOT EXISTS ix_cita_fecha ON cita (fecha);
CREATE INDEX IF NOT EXISTS ix_cita_paciente ON cita (paciente_id, fecha DESC);
CREATE INDEX IF NOT EXISTS ix_cita_odontologo ON cita (odontologo_id, fecha);
CREATE INDEX IF NOT EXISTS ix_paciente_nombre ON paciente (lower(nombre));
CREATE INDEX IF NOT EXISTS ix_paciente_telefono ON paciente (telefono);
CREATE INDEX IF NOT EXISTS ix_paciente_cedula ON paciente (cedula);

-- RN-20 / RF-041: Evitar nombres de consultorio duplicados (insensible a mayúsculas y espacios)
CREATE UNIQUE INDEX IF NOT EXISTS uq_consultorio_nombre_ci
    ON consultorio (lower(trim(nombre)));

-- RN-19: Un consultorio físico no puede estar ocupado por dos odontólogos activos simultáneamente.
-- Antes de crear el índice único, si existen odontólogos activos compartiendo el mismo consultorio
-- en una BD preexistente, reasigna automáticamente los duplicados al siguiente consultorio libre.
DO $$
DECLARE
    v_dup RECORD;
    v_num INTEGER;
    v_candidato TEXT;
BEGIN
    FOR v_dup IN
        SELECT id
        FROM (
            SELECT id,
                   ROW_NUMBER() OVER (
                       PARTITION BY lower(trim(consultorio))
                       ORDER BY id ASC
                   ) AS rn
            FROM odontologo
            WHERE activo IS TRUE
              AND consultorio IS NOT NULL
              AND trim(consultorio) <> ''
        ) sub
        WHERE sub.rn > 1
        ORDER BY sub.id ASC
    LOOP
        v_num := 1;
        LOOP
            v_candidato := 'Consultorio ' || v_num;
            EXIT WHEN NOT EXISTS (
                SELECT 1
                FROM odontologo
                WHERE activo IS TRUE
                  AND lower(trim(consultorio)) = lower(v_candidato)
            );
            v_num := v_num + 1;
        END LOOP;

        UPDATE odontologo
        SET consultorio = v_candidato
        WHERE id = v_dup.id;
    END LOOP;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS uq_odontologo_consultorio_activo
    ON odontologo (lower(trim(consultorio)))
    WHERE activo IS TRUE AND consultorio IS NOT NULL AND trim(consultorio) <> '';





-- =====================================================================
-- 8. VISTAS OPTIMIZADAS
-- =====================================================================

-- Vista Agenda del Día (Consultada en view/cita/lista_citas.php por Recepción para monitorear y asignar)
CREATE OR REPLACE VIEW v_agenda_dia AS
SELECT
    c.id            AS cita_id,
    c.fecha,
    c.hora,
    o.id            AS odontologo_id,
    COALESCE(o.nombre, 'Por asignar')::VARCHAR(100) AS odontologo,
    o.activo        AS odontologo_activo,
    o.consultorio,
    p.nombre        AS paciente,
    p.telefono,
    s.nombre        AS servicio,
    s.duracion_min,
    rec.nombre      AS asignado_por_recepcionista,
    c.estado,
    c.motivo,
    p.cedula,
    o.especialidad  AS odontologo_especialidad,
    c.motivo_cancelacion
FROM cita c
INNER JOIN paciente       p   ON p.id = c.paciente_id
LEFT JOIN  odontologo     o   ON o.id = c.odontologo_id
INNER JOIN servicio       s   ON s.id = c.servicio_id
LEFT JOIN  recepcionista rec ON rec.id = c.recepcionista_id;

-- Vista Ficha del Paciente (Consultada en view/odontologo/historial_finalizadas.php para el historial clínico)
CREATE OR REPLACE VIEW v_historial_paciente AS
SELECT
    p.id            AS paciente_id,
    p.nombre        AS paciente,
    p.alergias,
    c.id            AS cita_id,
    c.fecha,
    c.hora,
    c.estado,
    o.nombre        AS odontologo,
    s.nombre        AS servicio,
    d.detalle,
    d.procedimiento,
    d.nota_clinica,
    d.fecha_salida,
    d.hora_salida,
    p.cedula        AS paciente_cedula,
    p.telefono      AS paciente_telefono,
    c.odontologo_id,
    s.duracion_min,
    COALESCE(d.creado_en, c.creado_en) AS detalle_fecha
FROM paciente p
INNER JOIN cita c        ON c.paciente_id = p.id
LEFT JOIN odontologo o  ON o.id = c.odontologo_id
INNER JOIN servicio s    ON s.id = c.servicio_id
LEFT JOIN detalle_cita d ON d.cita_id = c.id;




-- =====================================================================
-- 9. DATOS INICIALES Y DE PRUEBA
-- Contraseña general para todos los usuarios de prueba: 12141214
-- Los hashes se generan con pgcrypto: crypt('12141214', gen_salt('bf', 10)).
-- PHP password_verify() acepta estos hashes bcrypt ($2a$).
-- =====================================================================

-- 5 Pacientes (idempotente: ON CONFLICT DO NOTHING)
INSERT INTO paciente (nombre, telefono, email, cedula, password_hash, fecha_nac, alergias) VALUES
 ('Laura Gomez', '3001112233', 'laura@mail.com', '1001112233', crypt('12141214', gen_salt('bf', 10)), '1995-03-14', 'Penicilina'),
 ('Carlos Perez', '3104445566', 'carlos@mail.com', '1004445566', crypt('12141214', gen_salt('bf', 10)), '1988-11-02', 'Ninguna'),
 ('Maik Cardenas', '3167798282', 'cardenasmaik30@gmail.com', '1076907716', crypt('12141214', gen_salt('bf', 10)), '2003-05-18', 'Ninguna'),
 ('Andrea Morales', '3125556677', 'andrea.morales@mail.com', '1005556677', crypt('12141214', gen_salt('bf', 10)), '1998-07-20', 'Ninguna'),
 ('Felipe Restrepo', '3157778899', 'felipe.restrepo@mail.com', '1007778899', crypt('12141214', gen_salt('bf', 10)), '1992-11-15', 'Ibuprofeno')
ON CONFLICT (cedula) DO NOTHING;

-- 3 Recepcionistas (idempotente: ON CONFLICT DO NOTHING)
INSERT INTO recepcionista (nombre, telefono, email, password_hash) VALUES
 ('Jose Antonio', '3238898007', 'jose.recepcion@clinica.com', crypt('12141214', gen_salt('bf', 10))),
 ('María López', '3009998877', 'maria.recepcion@clinica.com', crypt('12141214', gen_salt('bf', 10))),
 ('Sofia Ruiz', '3229998822', 'sofia.recepcion@clinica.com', crypt('12141214', gen_salt('bf', 10)))
ON CONFLICT (email) DO NOTHING;

-- 10 Consultorios base iniciales (solo si la tabla consultorio está vacía, respetando los cambios de cada sede)
INSERT INTO consultorio (nombre)
SELECT v.nombre
FROM (VALUES
    ('Consultorio 1'),
    ('Consultorio 2'),
    ('Consultorio 3'),
    ('Consultorio 4'),
    ('Consultorio 5'),
    ('Consultorio 6'),
    ('Consultorio 7'),
    ('Consultorio 8'),
    ('Consultorio 9'),
    ('Consultorio 10')
) AS v(nombre)
WHERE NOT EXISTS (SELECT 1 FROM consultorio LIMIT 1);

-- 5 Odontólogos iniciales con especialidad alineada 1:1 a los 5 servicios odontológicos (RN-21 / RF-044, idempotente: ON CONFLICT DO NOTHING)
INSERT INTO odontologo (nombre, especialidad, consultorio, slug_publico, email, password_hash, activo) VALUES
 ('Dra. Ana Torres', 'Consulta general', 'Consultorio 1', 'dra-ana-torres', 'ana.torres@clinica.com', crypt('12141214', gen_salt('bf', 10)), TRUE),
 ('Dr. Luis Mendoza', 'Ortodoncia correctiva', 'Consultorio 2', 'dr-luis-mendoza', 'luis.mendoza@clinica.com', crypt('12141214', gen_salt('bf', 10)), TRUE),
 ('Dra. Sofia Rios', 'Limpieza dental', 'Consultorio 3', 'dra-sofia-rios', 'sofia.rios@clinica.com', crypt('12141214', gen_salt('bf', 10)), TRUE),
 ('Dr. Carlos Vega', 'Calza (resina)', 'Consultorio 4', 'dr-carlos-vega', 'carlos.vega@clinica.com', crypt('12141214', gen_salt('bf', 10)), TRUE),
 ('Dra. Valentina Ortiz', 'Blanqueamiento dental', 'Consultorio 5', 'dra-valentina-ortiz', 'valentina.ortiz@clinica.com', crypt('12141214', gen_salt('bf', 10)), TRUE)
ON CONFLICT DO NOTHING;

-- Sincronizar en la tabla consultorio cualquier consultorio previo ya asignado a un odontólogo
INSERT INTO consultorio (nombre)
SELECT DISTINCT trim(o.consultorio)
FROM odontologo o
WHERE o.consultorio IS NOT NULL
  AND trim(o.consultorio) <> ''
  AND NOT EXISTS (
      SELECT 1 FROM consultorio c
      WHERE lower(trim(c.nombre)) = lower(trim(o.consultorio))
  );

-- 5 Servicios (idempotente: ON CONFLICT DO NOTHING)
INSERT INTO servicio (nombre, duracion_min, precio, descripcion) VALUES
 ('Consulta general', 30, 50000.00, 'Evaluación diagnóstica completa del estado de salud oral y plan de tratamiento personalizado.'),
 ('Limpieza dental', 30, 80000.00, 'Profilaxis dental profunda para eliminar placa bacteriana y sarro, previniendo enfermedades periodontales.'),
 ('Calza (resina)', 60, 150000.00, 'Restauración estética con resina de alta resistencia para devolver la función y forma natural al diente.'),
 ('Blanqueamiento dental', 45, 250000.00, 'Tratamiento estético avanzado con luz LED y geles especializados para aclarar el tono dental de forma segura y eficaz.'),
 ('Ortodoncia correctiva', 60, 350000.00, 'Alineación y corrección de la posición dental mediante brackets o alineadores para mejorar la estética y mordida.')
ON CONFLICT (nombre) DO NOTHING;

-- Citas de prueba iniciales (solo si la tabla cita está vacía)
INSERT INTO cita (paciente_id, odontologo_id, servicio_id, recepcionista_id, fecha, hora, estado, motivo)
SELECT v.paciente_id, v.odontologo_id, v.servicio_id, v.recepcionista_id, v.fecha, v.hora, v.estado, v.motivo
FROM (VALUES
 (1, NULL::INT, 1, NULL::INT, CURRENT_DATE + 5, TIME '09:00', 'pendiente'::estado_cita, 'Dolor en muela'),
 (2, 3,         2, 1,         CURRENT_DATE + 12, TIME '10:00', 'confirmada'::estado_cita, 'Limpieza semestral')
) AS v(paciente_id, odontologo_id, servicio_id, recepcionista_id, fecha, hora, estado, motivo)
WHERE NOT EXISTS (SELECT 1 FROM cita LIMIT 1);
