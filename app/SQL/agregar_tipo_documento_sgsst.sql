-- Agregar tipo de documento para documentos generados por el módulo SG-SST
INSERT INTO detail_report (detail_report)
SELECT 'Documento SG-SST'
WHERE NOT EXISTS (SELECT 1 FROM detail_report WHERE detail_report = 'Documento SG-SST');
