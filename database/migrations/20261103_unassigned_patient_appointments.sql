ALTER TABLE cita
    ALTER COLUMN odontologo_id DROP NOT NULL;

CREATE OR REPLACE VIEW v_agenda_dia AS
SELECT
    c.id AS cita_id,
    c.fecha,
    c.hora,
    o.id AS odontologo_id,
    COALESCE(o.nombre, 'Por asignar')::varchar(100) AS odontologo,
    o.activo AS odontologo_activo,
    o.consultorio,
    p.nombre AS paciente,
    p.telefono,
    s.nombre AS servicio,
    s.duracion_min,
    rec.nombre AS asignado_por_recepcionista,
    c.estado,
    c.motivo
FROM cita c
INNER JOIN paciente p ON p.id = c.paciente_id
LEFT JOIN odontologo o ON o.id = c.odontologo_id
INNER JOIN servicio s ON s.id = c.servicio_id
LEFT JOIN recepcionista rec ON rec.id = c.recepcionista_id;

CREATE OR REPLACE VIEW v_historial_paciente AS
SELECT
    p.id AS paciente_id,
    p.nombre AS paciente,
    p.alergias,
    c.id AS cita_id,
    c.fecha,
    c.hora,
    c.estado,
    o.nombre AS odontologo,
    s.nombre AS servicio,
    d.detalle,
    d.procedimiento,
    d.nota_clinica
FROM paciente p
INNER JOIN cita c ON c.paciente_id = p.id
LEFT JOIN odontologo o ON o.id = c.odontologo_id
INNER JOIN servicio s ON s.id = c.servicio_id
LEFT JOIN detalle_cita d ON d.cita_id = c.id;
