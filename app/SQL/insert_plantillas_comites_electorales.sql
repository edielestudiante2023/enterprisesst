-- ============================================================================
-- Plantillas para documentos de Comites Electorales
-- Registra los 8 tipos de acta en tbl_doc_plantillas para integracion
-- con el sistema de versionamiento SST
-- ============================================================================

-- Actas de Constitucion
INSERT INTO tbl_doc_plantillas (tipo_documento, nombre, codigo_sugerido, descripcion, activo)
VALUES
('acta_constitucion_copasst', 'Acta de Constitucion COPASST', 'FT-SST-013', 'Acta de constitucion del Comite Paritario de Seguridad y Salud en el Trabajo', 1),
('acta_constitucion_cocolab', 'Acta de Constitucion Comite Convivencia Laboral', 'FT-SST-013', 'Acta de constitucion del Comite de Convivencia Laboral', 1),
('acta_constitucion_brigada', 'Acta de Constitucion Brigada Emergencias', 'FT-SST-013', 'Acta de constitucion de la Brigada de Emergencias', 1),
('acta_constitucion_vigia', 'Acta de Constitucion Vigia SST', 'FT-SST-013', 'Acta de constitucion del Vigia de Seguridad y Salud en el Trabajo', 1);

-- Actas de Recomposicion
INSERT INTO tbl_doc_plantillas (tipo_documento, nombre, codigo_sugerido, descripcion, activo)
VALUES
('acta_recomposicion_copasst', 'Acta de Recomposicion COPASST', 'FT-SST-156', 'Acta de recomposicion del COPASST por cambio de miembros', 1),
('acta_recomposicion_cocolab', 'Acta de Recomposicion Comite Convivencia Laboral', 'FT-SST-155', 'Acta de recomposicion del Comite de Convivencia Laboral por cambio de miembros', 1),
('acta_recomposicion_brigada', 'Acta de Recomposicion Brigada Emergencias', 'FT-SST-156', 'Acta de recomposicion de la Brigada de Emergencias por cambio de miembros', 1),
('acta_recomposicion_vigia', 'Acta de Recomposicion Vigia SST', 'FT-SST-156', 'Acta de recomposicion del Vigia SST por cambio de designacion', 1);
