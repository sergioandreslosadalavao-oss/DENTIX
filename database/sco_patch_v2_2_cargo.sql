-- =====================================================================
-- PATCH v2.1 -> v2.2: Cargo de odontólogo (RN-07 / RN-08)
-- Aplica en la BD VIVA sin DROPs ni pérdida de datos.
-- Contraseña de prueba de todos los usuarios del seed: test1234
-- =====================================================================

-- 1. Tipo ENUM (idempotente: solo se crea si no existe)
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

-- 2. Columna cargo (idempotente)
ALTER TABLE odontologo
    ADD COLUMN IF NOT EXISTS cargo cargo_odontologo NOT NULL DEFAULT 'asociado';

-- 3. Bootstrap del líder de pruebas (Dr. Checho Cardenas).
--    Si no existe aún en la BD, se da de alta con la misma contraseña de prueba
--    del seed (test1234); si todavía no hay líder, se le asigna el cargo.
INSERT INTO odontologo (nombre, especialidad, slug_publico, email, password_hash, activo, cargo)
SELECT 'Dr. Checho Cardenas', 'Odontologia general', 'dr-checho-cardenas',
       'checho.cardenas@clinica.com',
       '$2y$12$gocsfZA5oarhdeHjlvNhjeDweMLJfySRL2h8wKeWIZuNaH2.M0DZu',
       TRUE, 'asociado'
WHERE NOT EXISTS (
    SELECT 1 FROM odontologo WHERE email = 'checho.cardenas@clinica.com'
);

UPDATE odontologo
SET cargo = 'lider'
WHERE email = 'checho.cardenas@clinica.com'
  AND NOT EXISTS (
    SELECT 1 FROM odontologo WHERE cargo = 'lider'
  );

-- 4. Regla al motor: máximo UN líder activo (RN-07)
--    NOTA: si se ejecuta dos veces falla por duplicado; CREATE INDEX no tiene IF NOT EXISTS
--    combinable con cláusula parcial. Verificar antes con \d odontologo.
CREATE UNIQUE INDEX uq_odontologo_lider
    ON odontologo (cargo)
    WHERE cargo = 'lider'
      AND activo = TRUE;

-- =====================================================================
-- IMPORTANTE: recargar el script completo (citas_odontologicas) reinicia
-- las tablas con DROP. Para la BD viva usar SOLO este patch.
-- =====================================================================
