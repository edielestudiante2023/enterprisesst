<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Acuerdo de Confidencialidad del Comite de Convivencia Laboral (COCOLAB).
 *
 * Documento legal individual firmado por CADA miembro del COCOLAB declarando
 * confidencialidad sobre los casos atendidos por el comite.
 * Base legal: Ley 1010 de 2006, Resolucion 652 de 2012, Resolucion 3461 de 2025.
 *
 * Tipo C (data-driven, sin IA). Estructura del PDF:
 *   - Cabecera grupal con razon social del cliente
 *   - Texto institucional fijo con 4 clausulas numeradas
 *   - N secciones personales (una por miembro activo del COCOLAB)
 *     con datos personales + bloque de firma electronica (token-based)
 *
 * Aplica SOLO a COCOLAB (otros comites no tienen obligacion legal de confidencialidad).
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class AcuerdoConfidencialidadCocolab extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'acuerdo_confidencialidad_cocolab';
    }

    public function getNombre(): string
    {
        return 'Acuerdo de Confidencialidad Comite de Convivencia Laboral';
    }

    public function getDescripcion(): string
    {
        return 'Documento legal individual firmado por cada miembro del Comite de Convivencia Laboral '
             . 'declarando confidencialidad sobre los casos atendidos. Base legal: Ley 1010/2006, '
             . 'Resolucion 652/2012, Resolucion 3461/2025.';
    }

    public function getEstandar(): ?string
    {
        return '1.1.8'; // mismo numeral que conformacion COCOLAB
    }

    public function getSecciones(): array
    {
        return [['numero' => 1, 'nombre' => 'Acuerdo Completo', 'key' => 'acuerdo_completo']];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        // No hay firmantes "tipicos" como rep_legal o delegado_sst - los firmantes son
        // los miembros activos del comite. Se manejan dinamicamente via DocFirmaModel
        // creando un registro por miembro al solicitar firmas.
        return [];
    }

    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        return ''; // No usa IA
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return 'Documento legal con texto institucional fijo. No admite edicion manual del contenido.';
    }

    public function validarSeccion(string $seccionKey, string $contenido): bool
    {
        return true;
    }

    public function getCodigoBase(): string
    {
        // Hereda la consulta a tbl_doc_plantillas (FT-SST-018)
        return parent::getCodigoBase();
    }

    public function getVistaPath(): string
    {
        return 'comites_elecciones/acuerdo_confidencialidad_pdf';
    }

    /**
     * Snapshot JSON del documento — guarda la lista de miembros y el estado de
     * firma de cada uno al momento de generacion.
     */
    public function buildContenidoSnapshot(array $datos): string
    {
        $snapshot = [
            'flujo'             => 'comite_electoral_acuerdo',
            'tipo'              => 'acuerdo_confidencialidad',
            'tipo_comite'       => 'COCOLAB',
            'codigo'            => 'FT-SST-018',
            'fecha_generacion'  => date('Y-m-d H:i:s'),
            'cliente'           => [
                'id_cliente'     => $datos['id_cliente']     ?? null,
                'nombre_cliente' => $datos['nombre_cliente'] ?? null,
                'razon_social'   => $datos['razon_social']   ?? ($datos['nombre_cliente'] ?? null),
            ],
            'id_proceso'        => $datos['id_proceso']      ?? null,
            'miembros'          => array_map(function ($m) {
                return [
                    'id_miembro'    => $m['id_miembro']     ?? null,
                    'nombre'        => $m['nombre']         ?? '',
                    'cedula'        => $m['cedula']         ?? '',
                    'lugar_expedicion' => $m['lugar_expedicion'] ?? '',
                    'cargo'         => $m['cargo']          ?? '',
                    'representacion' => $m['representacion']?? '',
                    'tipo_plaza'    => $m['tipo_plaza']     ?? '',
                    'firmo'         => (bool)($m['firmo']   ?? false),
                    'fecha_firma'   => $m['fecha_firma']    ?? null,
                ];
            }, $datos['miembros'] ?? []),
            'totales'           => [
                'total_miembros' => count($datos['miembros'] ?? []),
                'firmados'       => (int)($datos['firmados'] ?? 0),
                'pendientes'     => (int)($datos['pendientes'] ?? 0),
            ],
        ];
        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
