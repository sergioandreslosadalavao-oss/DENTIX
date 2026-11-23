-- =====================================================================
-- ⚠️ ADVERTENCIA CRÍTICA — SCRIPT SOLO PARA LABORATORIO / PRUEBAS QA ⚠️
-- NO EJECUTAR ESTE ARCHIVO SOBRE UNA BASE DE DATOS EN PRODUCCIÓN.
-- Elimina todos los objetos y datos del esquema DENTIX para iniciar desde cero.
-- Después de ejecutar este archivo, ejecute: database/citas_odontologicas.sql
-- =====================================================================

DROP VIEW IF EXISTS v_historial_paciente CASCADE;
DROP VIEW IF EXISTS v_agenda_dia CASCADE;
DROP TABLE IF EXISTS detalle_cita CASCADE;
DROP TABLE IF EXISTS cita CASCADE;
DROP TABLE IF EXISTS servicio CASCADE;
DROP TABLE IF EXISTS odontologo CASCADE;
DROP TABLE IF EXISTS consultorio CASCADE;
DROP TABLE IF EXISTS recepcionista CASCADE;
DROP TABLE IF EXISTS paciente CASCADE;
DROP TYPE IF EXISTS estado_cita CASCADE;
DROP FUNCTION IF EXISTS fn_validar_integridad_detalle_cita CASCADE;
DROP FUNCTION IF EXISTS fn_validar_espaciado_citas_paciente CASCADE;

