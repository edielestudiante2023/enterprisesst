<?php

namespace App\Services;

/**
 * Sincroniza estados entre las dos tablas de estándares mínimos:
 * - evaluacion_inicial_sst (listEvaluaciones)
 * - tbl_cliente_estandares (estandares/{id})
 *
 * Llave de cruce: evaluacion_inicial_sst.numeral = tbl_estandares_minimos.item
 */
class SyncEstandaresService
{
    // Mapeo: evaluacion_inicial_sst.evaluacion_inicial → tbl_cliente_estandares.estado
    private const EVAL_TO_ESTADO = [
        'CUMPLE TOTALMENTE' => 'cumple',
        'NO CUMPLE'         => 'no_cumple',
        'NO APLICA'         => 'no_aplica',
    ];

    // Mapeo inverso: tbl_cliente_estandares.estado → evaluacion_inicial_sst.evaluacion_inicial
    private const ESTADO_TO_EVAL = [
        'cumple'     => 'CUMPLE TOTALMENTE',
        'no_cumple'  => 'NO CUMPLE',
        'no_aplica'  => 'NO APLICA',
        'pendiente'  => '',
        'en_proceso' => '',
    ];

    /**
     * Sincroniza desde evaluacion_inicial_sst → tbl_cliente_estandares
     * Se llama cuando se edita en /listEvaluaciones
     */
    public function syncDesdeEvaluacion(int $idCliente, string $numeral, string $evaluacionInicial): void
    {
        try {
            $db = \Config\Database::connect();

            $estandar = $db->table('tbl_estandares_minimos')
                ->where('item', $numeral)
                ->get()
                ->getRowArray();

            if (!$estandar) {
                return;
            }

            $idEstandar = (int) $estandar['id_estandar'];
            $estado = self::EVAL_TO_ESTADO[$evaluacionInicial] ?? null;
            $pesoEstandar = (float) ($estandar['peso_porcentual'] ?? 0);

            // Si evaluacion_inicial está vacía, no sobreescribir un posible "en_proceso"
            if ($estado === null) {
                return;
            }

            $calificacion = ($estado === 'cumple' || $estado === 'no_aplica') ? $pesoEstandar : 0;

            $existe = $db->table('tbl_cliente_estandares')
                ->where('id_cliente', $idCliente)
                ->where('id_estandar', $idEstandar)
                ->countAllResults();

            if ($existe > 0) {
                $db->table('tbl_cliente_estandares')
                    ->where('id_cliente', $idCliente)
                    ->where('id_estandar', $idEstandar)
                    ->update([
                        'estado'       => $estado,
                        'calificacion' => $calificacion,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'SyncEstandares (eval→estado): ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza desde tbl_cliente_estandares → evaluacion_inicial_sst
     * Se llama cuando se edita en /estandares/{id}
     */
    public function syncDesdeClienteEstandares(int $idCliente, int $idEstandar, string $estado): void
    {
        try {
            $db = \Config\Database::connect();

            $estandar = $db->table('tbl_estandares_minimos')
                ->where('id_estandar', $idEstandar)
                ->get()
                ->getRowArray();

            if (!$estandar || empty($estandar['item'])) {
                return;
            }

            $numeral = $estandar['item'];
            $evaluacionInicial = self::ESTADO_TO_EVAL[$estado] ?? '';

            $registro = $db->table('evaluacion_inicial_sst')
                ->where('id_cliente', $idCliente)
                ->where('numeral', $numeral)
                ->get()
                ->getRowArray();

            if (!$registro) {
                return;
            }

            $valor = (float) ($registro['valor'] ?? 0);
            $puntaje = in_array($evaluacionInicial, ['CUMPLE TOTALMENTE', 'NO APLICA']) ? $valor : 0;

            $db->table('evaluacion_inicial_sst')
                ->where('id_cliente', $idCliente)
                ->where('numeral', $numeral)
                ->update([
                    'evaluacion_inicial'   => $evaluacionInicial ?: null,
                    'puntaje_cuantitativo' => $puntaje,
                    'updated_at'           => date('Y-m-d H:i:s'),
                ]);
        } catch (\Exception $e) {
            log_message('error', 'SyncEstandares (estado→eval): ' . $e->getMessage());
        }
    }
}
