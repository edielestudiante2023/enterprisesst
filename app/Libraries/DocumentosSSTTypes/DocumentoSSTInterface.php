<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Interface DocumentoSSTInterface
 *
 * Contrato que deben implementar todos los tipos de documentos del SG-SST.
 * Esta interfaz permite agregar nuevos tipos de documentos de forma escalable
 * sin modificar el controlador principal.
 *
 * PATRÓN: Strategy Pattern
 *
 * Para agregar un nuevo tipo de documento:
 * 1. Crear una clase que implemente esta interfaz
 * 2. Colocarla en app/Libraries/DocumentosSSTTypes/
 * 3. El Factory la detectará automáticamente
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
interface DocumentoSSTInterface
{
    /**
     * Obtiene el identificador único del tipo de documento
     * Debe coincidir con el valor usado en tbl_documentos_sst.tipo_documento
     *
     * @return string Ej: 'programa_capacitacion', 'procedimiento_control_documental'
     */
    public function getTipoDocumento(): string;

    /**
     * Obtiene el nombre legible del documento
     *
     * @return string Ej: 'Programa de Capacitación en SST'
     */
    public function getNombre(): string;

    /**
     * Obtiene la descripción del documento
     *
     * @return string Descripción breve del propósito del documento
     */
    public function getDescripcion(): string;

    /**
     * Obtiene el código de estándar relacionado (si aplica)
     *
     * @return string|null Ej: '2.5.1', '1.1.3', null si no aplica
     */
    public function getEstandar(): ?string;

    /**
     * Obtiene las secciones que componen el documento
     *
     * @return array Array de secciones con estructura:
     *               [['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'], ...]
     */
    public function getSecciones(): array;

    /**
     * Obtiene el prompt de IA para generar una sección específica
     *
     * @param string $seccionKey Clave de la sección (ej: 'objetivo', 'alcance')
     * @param int $estandares Nivel de estándares del cliente (7, 21, 60)
     * @return string El prompt para enviar a la IA
     */
    public function getPromptParaSeccion(string $seccionKey, int $estandares): string;

    /**
     * Obtiene los firmantes requeridos para el documento
     *
     * @param int $estandares Nivel de estándares (afecta si es COPASST o Vigía)
     * @return array Lista de tipos de firmante: ['representante_legal', 'responsable_sst', ...]
     */
    public function getFirmantesRequeridos(int $estandares): array;

    /**
     * Genera el contexto base para la IA basado en datos del cliente
     *
     * @param array $cliente Datos del cliente
     * @param array|null $contexto Contexto SST del cliente (actividad económica, etc.)
     * @return string Contexto formateado para incluir en el prompt
     */
    public function getContextoBase(array $cliente, ?array $contexto): string;

    /**
     * Obtiene contenido estático/fallback para una sección
     * Usado cuando la IA no está disponible o falla
     *
     * @param string $seccionKey Clave de la sección
     * @param array $cliente Datos del cliente
     * @param array|null $contexto Contexto SST
     * @param int $estandares Nivel de estándares
     * @param int $anio Año del documento
     * @return string Contenido estático de la sección
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string;

    /**
     * Valida si una sección tiene contenido válido
     *
     * @param string $seccionKey Clave de la sección
     * @param string $contenido Contenido a validar
     * @return bool True si el contenido es válido
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
