<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Interface DocumentoSSTInterface
 *
 * Contrato que deben implementar todos los tipos de documentos del SG-SST.
 *
 * ARQUITECTURA (2026-02-18):
 * - Secciones, prompts y firmantes viven en BD (tbl_doc_secciones_config, tbl_doc_firmantes_config).
 * - Las clases PHP son responsables SOLO de lógica que requiere ejecución de código:
 *   → getContextoBase() para Tipo B (consulta PTA e indicadores de BD).
 *   → Tipo A usa la implementación base de AbstractDocumentoSST.
 * - NO hardcodear secciones, prompts ni firmantes en clases PHP.
 *
 * Ver: docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 */
interface DocumentoSSTInterface
{
    /**
     * Identificador único del tipo de documento.
     * Debe coincidir EXACTAMENTE con tbl_doc_tipo_configuracion.tipo_documento (snake_case).
     */
    public function getTipoDocumento(): string;

    /**
     * Nombre legible del documento.
     * Ej: 'Política de Seguridad y Salud en el Trabajo'
     */
    public function getNombre(): string;

    /**
     * Descripción breve del propósito del documento.
     */
    public function getDescripcion(): string;

    /**
     * Numeral del estándar relacionado (Resolución 0312/2019).
     * Ej: '2.1.1', '2.5.1', null si no aplica.
     */
    public function getEstandar(): ?string;

    /**
     * Contexto base para la IA.
     * Tipo A: usa la implementación de AbstractDocumentoSST (solo datos del cliente).
     * Tipo B: sobrescribir para incluir PTA e indicadores consultados desde BD.
     */
    public function getContextoBase(array $cliente, ?array $contexto): string;

    /**
     * Valida si una sección tiene contenido válido.
     */
    public function validarSeccion(string $seccionKey, string $contenido): bool;

    // =========================================================================
    // MÉTODOS DE RUTAS Y URLs
    // Cada documento es responsable de conocer sus propias rutas
    // =========================================================================

    /**
     * Obtiene el código BASE del documento para tbl_doc_plantillas
     * Este es el PREFIJO, NO el código completo.
     * El consecutivo se agrega automáticamente por el sistema.
     *
     * Ejemplo: 'PRG-CAP' (NO 'PRG-CAP-001')
     *
     * @return string Código base del documento
     */
    public function getCodigoBase(): string;

    /**
     * Obtiene el slug para URLs (con guiones, no guiones bajos)
     * Ej: 'procedimiento_control_documental' -> 'procedimiento-control-documental'
     *
     * @return string Slug para usar en URLs
     */
    public function getSlugUrl(): string;

    /**
     * Obtiene la URL de vista previa del documento
     *
     * @param int $idCliente ID del cliente
     * @param int $anio Año del documento
     * @return string URL completa de vista previa
     */
    public function getUrlVistaPrevia(int $idCliente, int $anio): string;

    /**
     * Obtiene la URL del editor/generador IA
     *
     * @param int $idCliente ID del cliente
     * @return string URL completa del editor
     */
    public function getUrlEditor(int $idCliente): string;

    /**
     * Obtiene la ruta relativa para la vista del documento
     * Ej: 'documentos_sst/procedimiento_control_documental'
     *
     * @return string Ruta de la vista
     */
    public function getVistaPath(): string;
}
