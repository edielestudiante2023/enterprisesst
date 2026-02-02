<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase abstracta AbstractDocumentoSST
 *
 * Proporciona implementación base común para todos los tipos de documentos.
 * Las clases concretas solo necesitan definir sus secciones y prompts específicos.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
abstract class AbstractDocumentoSST implements DocumentoSSTInterface
{
    /**
     * Genera el contexto base para la IA (implementación común)
     * Las clases hijas pueden sobrescribir si necesitan contexto adicional
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? 'No especificado';
        $numTrabajadores = $contexto['numero_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $nivelTexto = match(true) {
            $estandares <= 7 => 'básico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (más de 50 trabajadores o riesgo IV y V)'
        };

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})

INSTRUCCIONES DE GENERACIÓN:
- Personaliza el contenido para esta empresa específica
- Ajusta la extensión y complejidad según el nivel de estándares
- Usa terminología de la normativa colombiana (Resolución 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    /**
     * Validación básica de contenido de sección
     * Las clases hijas pueden sobrescribir para validaciones específicas
     */
    public function validarSeccion(string $seccionKey, string $contenido): bool
    {
        // Validación básica: no vacío y mínimo 50 caracteres
        $contenido = trim($contenido);
        if (empty($contenido)) {
            return false;
        }
        if (strlen($contenido) < 50) {
            return false;
        }
        // No debe contener placeholder de error
        if (strpos($contenido, '[Seccion no definida]') !== false) {
            return false;
        }
        return true;
    }

    /**
     * Obtiene el nombre de una sección por su key
     */
    public function getNombreSeccion(string $seccionKey): string
    {
        foreach ($this->getSecciones() as $seccion) {
            if ($seccion['key'] === $seccionKey) {
                return $seccion['nombre'];
            }
        }
        return $seccionKey;
    }

    /**
     * Obtiene el número de una sección por su key
     */
    public function getNumeroSeccion(string $seccionKey): int
    {
        foreach ($this->getSecciones() as $seccion) {
            if ($seccion['key'] === $seccionKey) {
                return $seccion['numero'];
            }
        }
        return 0;
    }

    /**
     * Verifica si el documento usa Vigía SST (7 estándares) o COPASST (21+)
     */
    protected function usaVigiaSst(int $estandares): bool
    {
        return $estandares <= 10; // Hasta 10 trabajadores = Vigía SST
    }

    /**
     * Obtiene el texto correcto para referirse al comité según estándares
     */
    protected function getTextoComite(int $estandares): string
    {
        return $this->usaVigiaSst($estandares) ? 'Vigía de SST' : 'COPASST';
    }

    /**
     * Obtiene el nivel de texto descriptivo según estándares
     */
    protected function getNivelTexto(int $estandares): string
    {
        return match(true) {
            $estandares <= 7 => 'básico',
            $estandares <= 21 => 'intermedio',
            default => 'avanzado'
        };
    }

    /**
     * Genera contenido estático genérico (fallback)
     * Las clases hijas deben sobrescribir para contenido específico
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        return "{$nombreEmpresa} establece el contenido de la sección '{$this->getNombreSeccion($seccionKey)}' " .
               "en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo.";
    }

    // =========================================================================
    // MÉTODOS DE CÓDIGOS DE DOCUMENTO
    // El código BASE viene de tbl_doc_plantillas.codigo_sugerido
    // El código COMPLETO se genera como: CODIGO_BASE-CONSECUTIVO
    // =========================================================================

    /**
     * Obtiene el código BASE del documento desde tbl_doc_plantillas
     * Este método consulta la BD para obtener el codigo_sugerido
     * Las clases hijas pueden sobrescribir si necesitan lógica especial
     *
     * IMPORTANTE: Este es el PREFIJO, NO incluye consecutivo
     * Ejemplo: 'PRG-CAP' (el sistema genera 'PRG-CAP-001')
     *
     * @return string Código base del documento
     */
    public function getCodigoBase(): string
    {
        // Consultar tbl_doc_plantillas para obtener el código
        $db = \Config\Database::connect();
        $plantilla = $db->table('tbl_doc_plantillas')
            ->select('codigo_sugerido')
            ->where('tipo_documento', $this->getTipoDocumento())
            ->where('activo', 1)
            ->get()
            ->getRow();

        if ($plantilla && !empty($plantilla->codigo_sugerido)) {
            return $plantilla->codigo_sugerido;
        }

        // Fallback: generar código basado en convención
        // tipo_documento = 'programa_capacitacion' -> 'PRG-CAP'
        log_message('warning', "Tipo de documento '{$this->getTipoDocumento()}' no tiene codigo_sugerido en tbl_doc_plantillas");
        return 'DOC-GEN';
    }

    // =========================================================================
    // MÉTODOS DE RUTAS Y URLs
    // Implementación por defecto basada en convenciones
    // Las clases hijas pueden sobrescribir si necesitan URLs personalizadas
    // =========================================================================

    /**
     * Obtiene el slug para URLs (convierte guiones bajos a guiones)
     * Ej: 'procedimiento_control_documental' -> 'procedimiento-control-documental'
     */
    public function getSlugUrl(): string
    {
        return str_replace('_', '-', $this->getTipoDocumento());
    }

    /**
     * Obtiene la URL de vista previa del documento
     * Usa base_url() de CodeIgniter si está disponible
     */
    public function getUrlVistaPrevia(int $idCliente, int $anio): string
    {
        $baseUrl = function_exists('base_url') ? base_url() : '/';
        return rtrim($baseUrl, '/') . '/documentos-sst/' . $idCliente . '/' . $this->getSlugUrl() . '/' . $anio;
    }

    /**
     * Obtiene la URL del editor/generador IA
     */
    public function getUrlEditor(int $idCliente): string
    {
        $baseUrl = function_exists('base_url') ? base_url() : '/';
        return rtrim($baseUrl, '/') . '/documentos/generar/' . $this->getTipoDocumento() . '/' . $idCliente;
    }

    /**
     * Obtiene la ruta relativa para la vista del documento
     * Por convención: 'documentos_sst/{tipo_documento}'
     */
    public function getVistaPath(): string
    {
        return 'documentos_sst/' . $this->getTipoDocumento();
    }
}
