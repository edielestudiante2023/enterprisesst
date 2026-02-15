<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase abstracta para Actas de Recomposicion de Comites
 *
 * Base parametrizada para los 4 tipos: COPASST, COCOLAB, BRIGADA, VIGIA.
 * Estos documentos NO usan generacion IA - su contenido proviene
 * de los datos de recomposicion del comite.
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
abstract class AbstractActaRecomposicion extends AbstractDocumentoSST
{
    protected string $tipoComite = '';
    protected string $nombreComite = '';
    protected ?string $estandarNumeral = null;
    protected string $codigoBase = 'FT-SST-156';

    public function getNombre(): string
    {
        return 'Acta de Recomposicion ' . $this->nombreComite;
    }

    public function getDescripcion(): string
    {
        $descripciones = [
            'COPASST' => 'Acta de recomposicion del Comite Paritario de Seguridad y Salud en el Trabajo por cambio de miembros',
            'COCOLAB' => 'Acta de recomposicion del Comite de Convivencia Laboral por cambio de miembros',
            'BRIGADA' => 'Acta de recomposicion de la Brigada de Emergencias por cambio de miembros',
            'VIGIA' => 'Acta de recomposicion del Vigia de Seguridad y Salud en el Trabajo por cambio de designacion',
        ];
        return $descripciones[$this->tipoComite] ?? 'Acta de recomposicion de comite';
    }

    public function getEstandar(): ?string
    {
        return $this->estandarNumeral;
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Acta Completa', 'key' => 'acta_completa']
        ];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        return '';
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['representante_legal', 'delegado_sst'];
    }

    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        return '';
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return 'Este documento se genera automaticamente a partir de los datos de recomposicion del comite. No admite edicion manual de contenido.';
    }

    public function validarSeccion(string $seccionKey, string $contenido): bool
    {
        return true;
    }

    public function getCodigoBase(): string
    {
        return $this->codigoBase;
    }

    public function getVistaPath(): string
    {
        return 'comites_elecciones/recomposicion/acta_pdf';
    }

    /**
     * Genera un snapshot JSON de los datos de recomposicion para almacenar en contenido
     */
    public function buildContenidoSnapshot(array $datosRecomposicion): string
    {
        $saliente = $datosRecomposicion['saliente'] ?? [];
        $entrante = $datosRecomposicion['entrante'] ?? null;

        $snapshot = [
            'flujo' => 'comite_electoral',
            'tipo_acta' => 'recomposicion',
            'tipo_comite' => $this->tipoComite,
            'id_proceso' => $datosRecomposicion['proceso']['id_proceso'] ?? null,
            'id_recomposicion' => $datosRecomposicion['recomposicion']['id_recomposicion'] ?? null,
            'numero_recomposicion' => $datosRecomposicion['recomposicion']['numero_recomposicion'] ?? null,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'saliente' => [
                'nombre' => trim(($saliente['nombres'] ?? '') . ' ' . ($saliente['apellidos'] ?? '')),
                'cedula' => $saliente['cedula'] ?? '',
                'cargo' => $saliente['cargo'] ?? '',
                'representacion' => $saliente['representacion'] ?? '',
                'motivo' => $datosRecomposicion['motivoTexto'] ?? '',
            ],
            'entrante' => $entrante ? [
                'nombre' => trim(($entrante['nombres'] ?? '') . ' ' . ($entrante['apellidos'] ?? '')),
                'cedula' => $entrante['cedula'] ?? '',
                'cargo' => $entrante['cargo'] ?? '',
                'representacion' => $entrante['representacion'] ?? '',
            ] : null,
            'miembros_actuales' => array_values(array_map(fn($m) => [
                'nombre' => trim(($m['nombres'] ?? '') . ' ' . ($m['apellidos'] ?? '')),
                'cedula' => $m['cedula'] ?? '',
                'representacion' => $m['representacion'] ?? '',
                'tipo_plaza' => $m['tipo_plaza'] ?? '',
                'es_recomposicion' => (bool)($m['es_recomposicion'] ?? false),
                'marca' => $m['marca'] ?? 'A',
            ], $datosRecomposicion['miembrosActuales'] ?? [])),
        ];

        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
