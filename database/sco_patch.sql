-- =====================================================================
-- SCO PATCH ÚNICO — Sistema de Citas Odontológicas (v2.2)
-- Archivo: sco_patch.sql  (script único de parche; reemplaza al antiguo
--           parche v2.2 del cargo, ya eliminado del repositorio)
--
-- QUÉ HACE:
--   * BD NUEVA:      crea todo (tipos, tablas, índices, vistas) y el seed.
--   * BD EXISTENTE:  conserva tus datos y agrega lo que falte (cargo, líder).
--   * IDEMPOTENTE:   se puede ejecutar tantas veces como quieras.
--                     No hay DROP: nada se borra.
--
-- CREDENCIALES DE PRUEBA (todos los usuarios del seed):
--   contraseña: test1234
--   líder:      checho.cardenas@clinica.com   (Dr. Checho Cardenas)
--   recepción:  maria.recepcion@clinica.com   (María López)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. TIPOS ENUM (solo se crean si no existen)
-- ---------------------------------------------------------------------
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_type t
        JOIN pg_namespace n ON n.oid = t.typnamespace
        WHERE t.typname = 'estado_cita' AND n.nspname = 'public'
    ) THEN
        CREATE TYPE estado_cita AS ENUM (
            'pendiente', 'confirmada', 'completada', 'cancelada', 'no_show'
        );
    END IF;
END
$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_type t
        JOIN pg_namespace n ON n.oid = t.typnamespace
        WHERE t.typname = 'cargo_odontologo' AND n.nspname = 'public'
    ) THEN
        CREATE TYPE cargo_odontologo AS ENUM ('lider', 'asociado');
    END IF;
END
$$;

-- ---------------------------------------------------------------------
-- 2. TABLAS (si la tabla ya existe se conserva tal cual con sus datos)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS paciente (
    id           INT GENERATED ALWAYS AS IDENTITY,
    nombre       VARCHAR(100) NOT NULL,
    telefono     VARCHAR(20)  NOT NULL,
    email        VARCHAR(100),
    fecha_nac    DATE,
    alergias     TEXT,
    CONSTRAINT pk_paciente PRIMARY KEY (id),
    CONSTRAINT ck_paciente_nombre CHECK (length(trim(nombre)) > 0),
    CONSTRAINT ck_paciente_telefono CHECK (length(trim(telefono)) > 0),
    CONSTRAINT ck_paciente_fecha_nac CHECK (fecha_nac IS NULL OR fecha_nac <= CURRENT_DATE)
);

CREATE TABLE IF NOT EXISTS recepcionista (
    id            INT GENERATED ALWAYS AS IDENTITY,
    nombre        VARCHAR(100) NOT NULL,
    telefono      VARCHAR(20)  NOT NULL,
    email         VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo        BOOLEAN      NOT NULL DEFAULT TRUE,
    ultimo_acceso TIMESTAMP,
    CONSTRAINT pk_recepcionista PRIMARY KEY (id),
    CONSTRAINT ck_recepcionista_nombre CHECK (length(trim(nombre)) > 0),
    CONSTRAINT ck_recepcionista_email CHECK (email LIKE '%@%.%')
);

CREATE TABLE IF NOT EXISTS odontologo (
    id              INT GENERATED ALWAYS AS IDENTITY,
    nombre          VARCHAR(100) NOT NULL,
    especialidad    VARCHAR(60)  NOT NULL,
    consultorio     VARCHAR(30),
    slug_publico    VARCHAR(60)  NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    activo          BOOLEAN      NOT NULL DEFAULT TRUE,
    cargo           cargo_odontologo NOT NULL DEFAULT 'asociado',
    ultimo_acceso   TIMESTAMP,
    CONSTRAINT pk_odontologo PRIMARY KEY (id),
    CONSTRAINT uq_odontologo_slug UNIQUE (slug_publico),
    CONSTRAINT ck_odontologo_nombre CHECK (length(trim(nombre)) > 0),
    CONSTRAINT ck_odontologo_email CHECK (email LIKE '%@%.%')
);

CREATE TABLE IF NOT EXISTS servicio (
    id              INT GENERATED ALWAYS AS IDENTITY,
    nombre          VARCHAR(80)   NOT NULL,
    duracion_min    INT           NOT NULL,
    precio          NUMERIC(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT pk_servicio PRIMARY KEY (id),
    CONSTRAINT uq_servicio_nombre UNIQUE (nombre),
    CONSTRAINT ck_servicio_duracion CHECK (duracion_min >= 15),
    CONSTRAINT ck_servicio_precio CHECK (precio >= 0)
);

CREATE TABLE IF NOT EXISTS cita (
    id                  INT GENERATED ALWAYS AS IDENTITY,
    paciente_id         INT          NOT NULL,
    odontologo_id       INT          NOT NULL,
    servicio_id         INT          NOT NULL,
    recepcionista_id    INT,
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
    CONSTRAINT uq_agenda UNIQUE (odontologo_id, fecha, hora),
    CONSTRAINT ck_cita_horario CHECK (
        hora >= TIME '08:00'
        AND hora <= TIME '19:30'
        AND EXTRACT(MINUTE FROM hora) IN (0, 30)
        AND EXTRACT(SECOND FROM hora) = 0
    ),
    CONSTRAINT ck_cita_cancelacion CHECK (
        estado <> 'cancelada'
        OR length(trim(COALESCE(motivo_cancelacion, ''))) > 0
    )
);

CREATE TABLE IF NOT EXISTS detalle_cita (
    id              INT GENERATED ALWAYS AS IDENTITY,
    cita_id         INT       NOT NULL,
    servicio_id     INT       NOT NULL,
    detalle         TEXT,
    procedimiento   TEXT,
    nota_clinica    TEXT,
    creado_en       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_detalle_cita PRIMARY KEY (id),
    CONSTRAINT fk_detalle_cita
        FOREIGN KEY (cita_id) REFERENCES cita (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicio (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_detalle_cita UNIQUE (cita_id)
);

-- ---------------------------------------------------------------------
-- 3. MIGRACIÓN v2.1 -> v2.2 (agrega el cargo si la tabla venía de antes)
--    En una tabla nueva la columna ya existe: esto no hace nada.
-- ---------------------------------------------------------------------
ALTER TABLE odontologo
    ADD COLUMN IF NOT EXISTS cargo cargo_odontologo NOT NULL DEFAULT 'asociado';

-- Si Checho ya existía pero nadie tiene el cargo, se lo asignamos a él.
-- (Si ya hay OTRO líder, no se toca nada: sigue mandando ese.)
UPDATE odontologo
SET cargo = 'lider'
WHERE lower(email) = 'checho.cardenas@clinica.com'
  AND NOT EXISTS (
    SELECT 1 FROM odontologo WHERE cargo = 'lider'
  );

-- ---------------------------------------------------------------------
-- 4. ÍNDICES (IF NOT EXISTS: re-ejecutable)
-- ---------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS ix_recepcionista_email ON recepcionista (lower(email));
CREATE INDEX IF NOT EXISTS ix_odontologo_email ON odontologo (lower(email));
CREATE INDEX IF NOT EXISTS ix_cita_fecha ON cita (fecha);
CREATE INDEX IF NOT EXISTS ix_cita_paciente ON cita (paciente_id, fecha DESC);
CREATE INDEX IF NOT EXISTS ix_cita_odontologo ON cita (odontologo_id, fecha);
CREATE INDEX IF NOT EXISTS ix_paciente_nombre ON paciente (lower(nombre));
CREATE INDEX IF NOT EXISTS ix_paciente_telefono ON paciente (telefono);

-- RN-07: máximo UN odontólogo líder activo en toda la clínica
CREATE UNIQUE INDEX IF NOT EXISTS uq_odontologo_lider
    ON odontologo (cargo)
    WHERE cargo = 'lider'
      AND activo = TRUE;

-- ---------------------------------------------------------------------
-- 5. VISTAS (CREATE OR REPLACE: se refrescan sin borrar)
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW v_agenda_dia AS
SELECT
    c.id            AS cita_id,
    c.fecha,
    c.hora,
    o.id            AS odontologo_id,
    o.nombre        AS odontologo,
    o.activo        AS odontologo_activo,
    o.consultorio,
    p.nombre        AS paciente,
    p.telefono,
    s.nombre        AS servicio,
    s.duracion_min,
    rec.nombre      AS asignado_por_recepcionista,
    c.estado,
    c.motivo
FROM cita c
INNER JOIN paciente       p   ON p.id = c.paciente_id
INNER JOIN odontologo     o   ON o.id = c.odontologo_id
INNER JOIN servicio       s   ON s.id = c.servicio_id
LEFT JOIN  recepcionista rec ON rec.id = c.recepcionista_id;

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
    d.nota_clinica
FROM paciente p
INNER JOIN cita c        ON c.paciente_id = p.id
INNER JOIN odontologo o  ON o.id = c.odontologo_id
INNER JOIN servicio s    ON s.id = c.servicio_id
LEFT JOIN detalle_cita d ON d.cita_id = c.id;

-- ---------------------------------------------------------------------
-- 6. DATOS DE PRUEBA (solo se inserta lo que falte: nunca duplica)
--    Contraseña de prueba de todos: test1234
--    Hash bcrypt real de "test1234" (cost 12).
-- ---------------------------------------------------------------------
INSERT INTO paciente (nombre, telefono, email, fecha_nac, alergias)
SELECT v.nombre, v.telefono, v.email, v.fecha_nac, v.alergias
FROM (VALUES
    ('Laura Gomez', '3001112233', 'laura@mail.com', DATE '1995-03-14', 'Penicilina'),
    ('Carlos Perez', '3104445566', 'carlos@mail.com', DATE '1988-11-02', NULL::TEXT)
) AS v(nombre, telefono, email, fecha_nac, alergias)
WHERE NOT EXISTS (SELECT 1 FROM paciente);

INSERT INTO recepcionista (nombre, telefono, email, password_hash)
SELECT 'María López', '3009998877', v.email, v.hash
FROM (VALUES
    ('maria.recepcion@clinica.com',
     '$2y$12$gocsfZA5oarhdeHjlvNhjeDweMLJfySRL2h8wKeWIZuNaH2.M0DZu')
) AS v(email, hash)
WHERE NOT EXISTS (
    SELECT 1 FROM recepcionista r WHERE lower(r.email) = lower(v.email)
);

-- Checho (líder de pruebas): cargo 'lider' SOLO si todavía no hay líder.
-- Es una fila suelta a propósito: en base vacía sale 'lider', y si ya
-- existe otro líder, él entra como 'asociado' sin violar el índice único.
INSERT INTO odontologo (nombre, especialidad, consultorio, slug_publico, email, password_hash, activo, cargo)
SELECT 'Dr. Checho Cardenas', 'Odontologia general', NULL::VARCHAR, 'dr-checho-cardenas',
       'checho.cardenas@clinica.com',
       '$2y$12$gocsfZA5oarhdeHjlvNhjeDweMLJfySRL2h8wKeWIZuNaH2.M0DZu',
       TRUE,
       CASE
           WHEN EXISTS (SELECT 1 FROM odontologo WHERE cargo = 'lider')
           THEN 'asociado'::cargo_odontologo
           ELSE 'lider'::cargo_odontologo
       END
WHERE NOT EXISTS (
    SELECT 1 FROM odontologo WHERE lower(email) = 'checho.cardenas@clinica.com'
);

-- Resto del cuerpo clínico: siempre asociados (Ana es odontóloga más).
INSERT INTO odontologo (nombre, especialidad, consultorio, slug_publico, email, password_hash, activo, cargo)
SELECT v.nombre, v.especialidad, v.consultorio, v.slug_publico, v.email, v.hash, v.activo,
       'asociado'::cargo_odontologo
FROM (VALUES
    ('Dra. Ana Torres', 'Odontologia general', 'Consultorio 1', 'dra-ana-torres',
     'ana.torres@clinica.com',
     '$2y$12$gocsfZA5oarhdeHjlvNhjeDweMLJfySRL2h8wKeWIZuNaH2.M0DZu', TRUE),
    ('Dr. Luis Mendoza', 'Ortodoncia', 'Consultorio 2', 'dr-luis-mendoza',
     'luis.mendoza@clinica.com',
     '$2y$12$gocsfZA5oarhdeHjlvNhjeDweMLJfySRL2h8wKeWIZuNaH2.M0DZu', TRUE),
    ('Dra. Sofia Rios', 'Endodoncia', 'Consultorio 3', 'dra-sofia-rios',
     'sofia.rios@clinica.com',
     '$2y$12$gocsfZA5oarhdeHjlvNhjeDweMLJfySRL2h8wKeWIZuNaH2.M0DZu', FALSE)
) AS v(nombre, especialidad, consultorio, slug_publico, email, hash, activo)
WHERE NOT EXISTS (
    SELECT 1 FROM odontologo o WHERE lower(o.email) = lower(v.email)
);

INSERT INTO servicio (nombre, duracion_min, precio)
SELECT v.nombre, v.duracion_min, v.precio
FROM (VALUES
    ('Consulta general', 30, 50000.00),
    ('Limpieza dental', 30, 80000.00),
    ('Calza (resina)', 60, 150000.00)
) AS v(nombre, duracion_min, precio)
WHERE NOT EXISTS (
    SELECT 1 FROM servicio s WHERE lower(s.nombre) = lower(v.nombre)
);

-- Citas de prueba: ambas en UN solo INSERT, y solo si la tabla está vacía
-- (en un solo statement la guarda se evalúa una sola vez, contra el estado
-- previo: en base nueva entra todo; en BD con datos, no se siembra nada).
INSERT INTO cita (paciente_id, odontologo_id, servicio_id, recepcionista_id, fecha, hora, estado, motivo)
SELECT p.id, o.id, s.id, r.id, CURRENT_DATE + 1, v.hora, v.estado, v.motivo
FROM (VALUES
    ('laura@mail.com',  'checho.cardenas@clinica.com', 'consulta general',
     '09:00'::TIME, 'pendiente'::estado_cita,  'Dolor en muela'),
    ('carlos@mail.com', 'ana.torres@clinica.com',      'limpieza dental',
     '09:30'::TIME, 'confirmada'::estado_cita, 'Limpieza semestral')
) AS v(paciente_email, odontologo_email, servicio_nombre, hora, estado, motivo)
JOIN paciente p        ON lower(p.email) = v.paciente_email
JOIN odontologo o      ON lower(o.email) = v.odontologo_email
JOIN servicio s        ON lower(s.nombre) = v.servicio_nombre
JOIN recepcionista r   ON lower(r.email) = 'maria.recepcion@clinica.com'
WHERE NOT EXISTS (SELECT 1 FROM cita);

-- =====================================================================
-- FIN DEL PATCH — si terminó sin error, la base quedó (o se quedó) lista.
-- =====================================================================
