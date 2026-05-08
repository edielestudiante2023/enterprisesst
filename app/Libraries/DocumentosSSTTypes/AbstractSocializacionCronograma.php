<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Abstracta para socializacion de Cronograma de Reuniones de un comite.
 * Aplica a COPASST, COCOLAB y BRIGADA. Difiere de la socializacion de miembros
 * en que aqui los datos vienen de un FORMULARIO del consultor (fechas/horas/lugar)
 * y no de una tabla preexistente.
 */
abstract class AbstractSocializacionCronograma extends AbstractDocumentoSST
{
    protected string $tipoComite = '';
    protected string $nombreLargoComite = '';
    protected string $codigoFt = '';

    public function getNombre(): string
    {
        return 'Cronograma de Reuniones ' . $this->nombreLargoComite;
    }

    public function getDescripcion(): string
    {
        return 'Documento PDF distribuido a colaboradores con el cronograma de reuniones del '
             . $this->nombreLargoComite . ' para el periodo vigente.';
    }

    public function getEstandar(): ?string
    {
        return match($this->tipoComite) {
            'COPASST' => '1.1.6',
            'COCOLAB' => '1.1.8',
            'BRIGADA' => '5.1.1',
            default   => null,
        };
    }

    public function getSecciones(): array
    {
        return [['numero' => 1, 'nombre' => 'Documento Completo', 'key' => 'documento_completo']];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return [];
    }

    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        return '';
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return 'Documento generado a partir de fechas registradas en el sistema.';
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
        return 'comites_elecciones/socializacion/cronograma_pdf';
    }

    /**
     * Mensaje fijo para el cronograma del comite (transversal por tipo).
     */
    abstract public function getMensajeCronograma(string $nombreEmpresa, int $anio): string;

    /**
     * Snapshot JSON.
     */
    public function buildContenidoSnapshot(array $datos): string
    {
        $snapshot = [
            'flujo'              => 'comite_electoral_socializacion',
            'tipo'               => 'cronograma',
            'tipo_comite'        => $this->tipoComite,
            'codigo'             => $this->codigoFt,
            'fecha_generacion'   => date('Y-m-d H:i:s'),
            'anio_cronograma'    => $datos['anio'] ?? null,
            'reuniones'          => $datos['reuniones'] ?? [],
            'cliente'            => [
                'id_cliente'     => $datos['id_cliente'] ?? null,
                'nombre_cliente' => $datos['nombre_cliente'] ?? null,
            ],
            'destinatarios'      => $datos['destinatarios'] ?? [],
            'totales'            => [
                'enviados_ok' => (int)($datos['enviados_ok'] ?? 0),
                'fallidos'    => (int)($datos['fallidos'] ?? 0),
            ],
        ];
        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
