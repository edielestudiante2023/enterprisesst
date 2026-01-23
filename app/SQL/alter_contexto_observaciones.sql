-- ============================================================================
-- ALTER: Agregar campo de observaciones y contexto libre
-- Tabla: tbl_cliente_contexto_sst
-- Fecha: Enero 2026
-- ============================================================================

-- Agregar campo para observaciones y contexto cualitativo de la empresa
-- Este campo permite al consultor documentar información relevante que no
-- aparece en documentos formales pero es crucial para generar documentos
-- SST personalizados y relevantes.

ALTER TABLE `tbl_cliente_contexto_sst`
ADD COLUMN `observaciones_contexto` TEXT NULL
    COMMENT 'Contexto cualitativo: cultura organizacional, operaciones reales, exposición a riesgos, estructura informal, observaciones de campo'
    AFTER `peligros_identificados`;

-- Ejemplo de contenido esperado:
-- "La empresa aunque formalmente es de comercio, tiene un pequeño taller de
-- reparaciones donde 3 trabajadores realizan soldadura y trabajo en alturas
-- ocasional. La cultura de seguridad es baja, los trabajadores rara vez usan
-- EPP. El gerente está comprometido pero los mandos medios no. Las instalaciones
-- son antiguas con problemas de iluminación en bodega. Hay rotación alta de
-- personal temporal lo que dificulta la capacitación."
