<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase abstracta para los documentos PDF de Socializacion de Miembros del Comite.
 *
 * Aplica para COPASST, COCOLAB y BRIGADA. Cada hija solo cambia el tipo_comite,
 * el codigo (FT-SST-XXX) y el mensaje pertinente al comite (transversal a cualquier
 * tipo de empresa, segun lo confirmado con el usuario en 2026-05).
 *
 * Estos documentos NO usan IA. Datos vienen de:
 *  - tbl_candidatos (electos, con foto, cargo, representacion)
 *  - tbl_clientes (nombre, logo)
 *  - Form del consultor (periodo, no hay mensaje editable porque es estatico por comite)
 */
abstract class AbstractSocializacionMiembros extends AbstractDocumentoSST
{
    protected string $tipoComite = '';
    protected string $nombreLargoComite = '';
    protected string $codigoFt = '';

    public function getNombre(): string
    {
        return 'Socializacion de Miembros ' . $this->nombreLargoComite;
    }

    public function getDescripcion(): string
    {
        return 'Documento PDF distribuido a colaboradores presentando los miembros del '
             . $this->nombreLargoComite . ' electos para el periodo vigente.';
    }

    public function getEstandar(): ?string
    {
        // Mismo numeral que la conformacion del comite respectivo
        return match($this->tipoComite) {
            'COPASST' => '1.1.6',
            'COCOLAB' => '1.1.8',
            'BRIGADA' => '5.1.1',
            default   => null,
        };
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Documento Completo', 'key' => 'documento_completo']
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return []; // No se firma, solo se distribuye
    }

    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        return ''; // No usa IA
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return 'Documento generado a partir del proceso electoral del ' . $this->nombreLargoComite
             . '. No admite edicion manual.';
    }

    public function validarSeccion(string $seccionKey, string $contenido): bool
    {
        return true;
    }

    public function getCodigoBase(): string
    {
        return $this->codigoFt;
    }

    public function getVistaPath(): string
    {
        return 'comites_elecciones/socializacion/miembros_pdf';
    }

    /**
     * Mensaje fijo del comite, transversal a cualquier tipo de empresa.
     * Sobrescrito por cada hija con texto pertinente al tipo de comite.
     */
    abstract public function getMensajeComite(string $nombreEmpresa): string;

    /**
     * Snapshot JSON del documento para guardar en tbl_documentos_sst.contenido.
     */
    public function buildContenidoSnapshot(array $datos): string
    {
        $snapshot = [
            'flujo'               => 'comite_electoral_socializacion',
            'tipo'                => 'miembros',
            'tipo_comite'         => $this->tipoComite,
            'codigo'              => $this->codigoFt,
            'fecha_generacion'    => date('Y-m-d H:i:s'),
            'periodo'             => [
                'inicio' => $datos['periodo_inicio'] ?? null,
                'fin'    => $datos['periodo_fin'] ?? null,
            ],
            'mensaje_comite'      => $datos['mensaje_comite'] ?? '',
            'miembros'            => $datos['miembros'] ?? [],
            'cliente'             => [
                'id_cliente'      => $datos['id_cliente'] ?? null,
                'nombre_cliente'  => $datos['nombre_cliente'] ?? null,
            ],
            'destinatarios'       => $datos['destinatarios'] ?? [],
            'totales'             => [
                'enviados_ok' => (int)($datos['enviados_ok'] ?? 0),
                'fallidos'    => (int)($datos['fallidos'] ?? 0),
            ],
        ];
        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
