<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase abstracta para Actas de Constitucion de Comites
 *
 * Base parametrizada para los 4 tipos: COPASST, COCOLAB, BRIGADA, VIGIA.
 * Estos documentos NO usan generacion IA - su contenido proviene
 * de los datos del proceso electoral almacenados en multiples tablas.
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
abstract class AbstractActaConstitucion extends AbstractDocumentoSST
{
    protected string $tipoComite = '';
    protected string $nombreComite = '';
    protected ?string $estandarNumeral = null;

    public function getNombre(): string
    {
        return 'Acta de Constitucion ' . $this->nombreComite;
    }

    public function getDescripcion(): string
    {
        $descripciones = [
            'COPASST' => 'Acta de constitucion del Comite Paritario de Seguridad y Salud en el Trabajo segun Decreto 1072/2015 y Resolucion 2013/1986',
            'COCOLAB' => 'Acta de constitucion del Comite de Convivencia Laboral segun Resolucion 652/2012 y Resolucion 3461/2025',
            'BRIGADA' => 'Acta de constitucion de la Brigada de Emergencias segun Decreto 1072/2015',
            'VIGIA' => 'Acta de designacion del Vigia de Seguridad y Salud en el Trabajo segun Decreto 1072/2015 y Resolucion 0312/2019',
        ];
        return $descripciones[$this->tipoComite] ?? 'Acta de constitucion de comite';
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
        return 'Este documento se genera automaticamente a partir de los datos del proceso electoral. No admite edicion manual de contenido.';
    }

    public function validarSeccion(string $seccionKey, string $contenido): bool
    {
        return true;
    }

    public function getCodigoBase(): string
    {
        return 'FT-SST-013';
    }

    public function getVistaPath(): string
    {
        return 'comites_elecciones/acta_constitucion_preview';
    }

    /**
     * Genera un snapshot JSON de los datos electorales para almacenar en contenido
     */
    public function buildContenidoSnapshot(array $datosActa): string
    {
        $snapshot = [
            'flujo' => 'comite_electoral',
            'tipo_acta' => 'constitucion',
            'tipo_comite' => $this->tipoComite,
            'id_proceso' => $datosActa['proceso']['id_proceso'] ?? null,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'proceso' => [
                'fecha_votacion' => $datosActa['proceso']['fecha_votacion'] ?? null,
                'plazas_principales' => $datosActa['proceso']['plazas_principales'] ?? null,
                'plazas_suplentes' => $datosActa['proceso']['plazas_suplentes'] ?? null,
                'estado' => $datosActa['proceso']['estado'] ?? null,
                'anio' => $datosActa['proceso']['anio'] ?? null,
            ],
            'jurados' => array_map(fn($j) => [
                'nombre' => trim(($j['nombres'] ?? '') . ' ' . ($j['apellidos'] ?? '')),
                'rol' => $j['rol'] ?? '',
                'cedula' => $j['cedula'] ?? '',
            ], $datosActa['jurados'] ?? []),
            'resultados_votacion' => array_map(fn($r) => [
                'nombre' => trim(($r['nombres'] ?? '') . ' ' . ($r['apellidos'] ?? '')),
                'votos' => (int)($r['votos_obtenidos'] ?? 0),
            ], $datosActa['resultadosVotacion'] ?? []),
            'miembros_elegidos' => [
                'trabajadores_principales' => array_values(array_map(fn($c) => [
                    'nombre' => trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')),
                    'cedula' => $c['cedula'] ?? '',
                    'cargo' => $c['cargo'] ?? '',
                ], $datosActa['principales'] ?? [])),
                'trabajadores_suplentes' => array_values(array_map(fn($c) => [
                    'nombre' => trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')),
                    'cedula' => $c['cedula'] ?? '',
                    'cargo' => $c['cargo'] ?? '',
                ], $datosActa['suplentes'] ?? [])),
                'empleador_principales' => array_values(array_map(fn($c) => [
                    'nombre' => trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')),
                    'cedula' => $c['cedula'] ?? '',
                    'cargo' => $c['cargo'] ?? '',
                ], $datosActa['empleadorPrincipales'] ?? [])),
                'empleador_suplentes' => array_values(array_map(fn($c) => [
                    'nombre' => trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')),
                    'cedula' => $c['cedula'] ?? '',
                    'cargo' => $c['cargo'] ?? '',
                ], $datosActa['empleadorSuplentes'] ?? [])),
            ],
            'estadisticas' => [
                'total_votantes' => (int)($datosActa['totalVotantes'] ?? 0),
                'votos_emitidos' => (int)($datosActa['votaronCount'] ?? 0),
                'participacion' => (float)($datosActa['participacion'] ?? 0),
                'total_votos' => (int)($datosActa['totalVotos'] ?? 0),
            ],
        ];

        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
