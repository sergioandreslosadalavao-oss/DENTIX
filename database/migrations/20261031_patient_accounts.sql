ALTER TABLE paciente
    ADD COLUMN IF NOT EXISTS cedula VARCHAR(30),
    ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255);

CREATE UNIQUE INDEX IF NOT EXISTS uq_paciente_cedula
    ON paciente (cedula)
    WHERE cedula IS NOT NULL;
