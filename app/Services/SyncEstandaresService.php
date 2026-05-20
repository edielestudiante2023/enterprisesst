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
    public function syncDesdeClienteEstandares(int $idCliente, int $idEstandar, string $estado, bool $soloRellenar = false): void
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

            if ($registro) {
                // En modo "solo rellenar" (backfill) NO pisamos una evaluacion ya hecha
                if ($soloRellenar && trim((string) ($registro['evaluacion_inicial'] ?? '')) !== '') {
                    return;
                }

                $valor = (float) ($registro['valor'] ?? $estandar['peso_porcentual'] ?? 0);
                $puntaje = in_array($evaluacionInicial, ['CUMPLE TOTALMENTE', 'NO APLICA'], true) ? $valor : 0;

                $db->table('evaluacion_inicial_sst')
                    ->where('id_cliente', $idCliente)
                    ->where('numeral', $numeral)
                    ->update([
                        'evaluacion_inicial'   => $evaluacionInicial ?: null,
                        'puntaje_cuantitativo' => $puntaje,
                        'updated_at'           => date('Y-m-d H:i:s'),
                    ]);
                return;
            }

            // No existe el registro: lo CREAMOS para que aparezca en /listEvaluaciones (incl. NO APLICA)
            [$ciclo, $estandarTexto] = $this->resolverCicloYEstandar($db, $idCliente, $estandar);
            $valor = (float) ($estandar['peso_porcentual'] ?? 0);
            $puntaje = in_array($evaluacionInicial, ['CUMPLE TOTALMENTE', 'NO APLICA'], true) ? $valor : 0;

            $db->table('evaluacion_inicial_sst')->insert([
                'id_cliente'           => $idCliente,
                'ciclo'                => $ciclo,
                'estandar'             => $estandarTexto,
                'numeral'              => $numeral,
                'item_del_estandar'    => $estandar['nombre'] ?? '',
                'item'                 => $estandar['nombre'] ?? '',
                'criterio'             => $estandar['criterio'] ?? '',
                'evaluacion_inicial'   => $evaluacionInicial ?: null,
                'valor'                => $valor,
                'puntaje_cuantitativo' => $puntaje,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'SyncEstandares (estado→eval): ' . $e->getMessage());
        }
    }

    /**
     * Resuelve [ciclo, estandar] para una fila nueva de evaluacion_inicial_sst,
     * copiando el formato exacto de una fila hermana existente (misma categoria)
     * para que agrupe igual en /listEvaluaciones; si no hay, mapea desde el maestro.
     */
    private function resolverCicloYEstandar($db, int $idCliente, array $estandar): array
    {
        if (!empty($estandar['categoria'])) {
            $hermana = $db->table('evaluacion_inicial_sst e')
                ->select('e.ciclo, e.estandar')
                ->join('tbl_estandares_minimos m', 'm.item = e.numeral')
                ->where('e.id_cliente', $idCliente)
                ->where('m.categoria', $estandar['categoria'])
                ->where('e.ciclo IS NOT NULL')
                ->get()
                ->getRowArray();
            if ($hermana && !empty($hermana['ciclo'])) {
                return [$hermana['ciclo'], $hermana['estandar']];
            }
        }

        $mapCiclo = [
            'PLANEAR'   => 'I. PLANEAR',
            'HACER'     => 'II. HACER',
            'VERIFICAR' => 'III. VERIFICAR',
            'ACTUAR'    => 'IV. ACTUAR',
        ];
        $ciclo = $mapCiclo[strtoupper(trim($estandar['ciclo_phva'] ?? ''))] ?? ($estandar['ciclo_phva'] ?? '');

        return [$ciclo, $estandar['categoria_nombre'] ?? ''];
    }

    /**
     * Backfill: sincroniza TODOS los estandares de un cliente desde
     * tbl_cliente_estandares hacia evaluacion_inicial_sst.
     * Por defecto "solo rellenar" (crea faltantes y rellena vacios, sin pisar
     * evaluaciones ya hechas).
     *
     * @return array ['procesados'=>int, 'creados'=>int, 'rellenados'=>int]
     */
    public function syncTodoCliente(int $idCliente, bool $soloRellenar = true): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('tbl_cliente_estandares')
            ->select('id_estandar, estado')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getResultArray();

        $stats = ['procesados' => 0, 'creados' => 0, 'rellenados' => 0];

        foreach ($rows as $r) {
            $idEstandar = (int) $r['id_estandar'];
            $est = $db->table('tbl_estandares_minimos')->select('item')->where('id_estandar', $idEstandar)->get()->getRowArray();
            $numeral = $est['item'] ?? null;
            $existia = false;
            $existiaConValor = false;
            if ($numeral) {
                $reg = $db->table('evaluacion_inicial_sst')->select('evaluacion_inicial')
                    ->where('id_cliente', $idCliente)->where('numeral', $numeral)->get()->getRowArray();
                $existia = (bool) $reg;
                $existiaConValor = $reg && trim((string) ($reg['evaluacion_inicial'] ?? '')) !== '';
            }

            $this->syncDesdeClienteEstandares($idCliente, $idEstandar, $r['estado'], $soloRellenar);

            $stats['procesados']++;
            if (!$existia) {
                $stats['creados']++;
            } elseif (!$existiaConValor) {
                $stats['rellenados']++;
            }
        }

        return $stats;
    }
}
