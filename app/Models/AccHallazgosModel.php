<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar Hallazgos/Orígenes de Acciones Correctivas
 * Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 - Resolución 0312 de 2019
 */
class AccHallazgosModel extends Model
{
    protected $table = 'tbl_acc_hallazgos';
    protected $primaryKey = 'id_hallazgo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_cliente',
        'tipo_origen',
        'numeral_asociado',
        'titulo',
        'descripcion',
        'area_proceso',
        'severidad',
        'fecha_deteccion',
        'fecha_limite_accion',
        'reportado_por',
        'evidencia_inicial',
        'estado',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Mapeo de tipo de origen a numeral por defecto
     */
    public const MAPEO_ORIGEN_NUMERAL = [
        'auditoria_interna' => '7.1.1',
        'revision_direccion' => '7.1.1',
        'inspeccion' => '7.1.1',
        'indicador' => '7.1.1',
        'evaluacion_estandares' => '7.1.1',
        'medida_no_efectiva' => '7.1.2',
        'investigacion_incidente' => '7.1.3',
        'investigacion_accidente' => '7.1.3',
        'investigacion_enfermedad' => '7.1.3',
        'requerimiento_arl' => '7.1.4',
        'requerimiento_autoridad' => '7.1.4',
        'copasst' => '7.1.1',
        'trabajador' => '7.1.1',
        'otro' => '7.1.1'
    ];

    /**
     * Obtener hallazgos por cliente
     */
    public function getByCliente(int $idCliente, ?string $numeral = null, ?string $estado = null): array
    {
        $builder = $this->select('tbl_acc_hallazgos.*, u.nombre_completo as reportado_por_nombre, u.email as reportado_por_email')
                        ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_hallazgos.reportado_por', 'left')
                        ->where('tbl_acc_hallazgos.id_cliente', $idCliente);

        if ($numeral) {
            $builder->where('numeral_asociado', $numeral);
        }

        if ($estado) {
            $builder->where('tbl_acc_hallazgos.estado', $estado);
        }

        return $builder->orderBy('fecha_deteccion', 'DESC')->findAll();
    }

    /**
     * Obtener hallazgo con todos los detalles
     */
    public function getConDetalles(int $idHallazgo): ?array
    {
        $hallazgo = $this->select('tbl_acc_hallazgos.*,
                                   u.nombre_completo as reportado_por_nombre,
                                   u.email as reportado_por_email,
                                   c.nombre_cliente,
                                   c.nit_cliente,
                                   cat.nombre_mostrar as tipo_origen_nombre,
                                   cat.icono as tipo_origen_icono,
                                   cat.color as tipo_origen_color')
                         ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_hallazgos.reportado_por', 'left')
                         ->join('tbl_clientes c', 'c.id_cliente = tbl_acc_hallazgos.id_cliente')
                         ->join('tbl_acc_catalogo_origenes cat', 'cat.tipo_origen = tbl_acc_hallazgos.tipo_origen', 'left')
                         ->find($idHallazgo);

        if ($hallazgo) {
            // Obtener acciones del hallazgo
            $accionesModel = new AccAccionesModel();
            $hallazgo['acciones'] = $accionesModel->getByHallazgo($idHallazgo);

            // Calcular estadísticas de acciones
            $hallazgo['total_acciones'] = count($hallazgo['acciones']);
            $hallazgo['acciones_cerradas'] = count(array_filter($hallazgo['acciones'], function($a) {
                return in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']);
            }));
            $hallazgo['acciones_pendientes'] = $hallazgo['total_acciones'] - $hallazgo['acciones_cerradas'];
        }

        return $hallazgo;
    }

    /**
     * Crear hallazgo nuevo
     */
    public function crearHallazgo(array $data): int|false
    {
        // Asignar numeral automáticamente si no viene
        if (empty($data['numeral_asociado']) && !empty($data['tipo_origen'])) {
            $data['numeral_asociado'] = self::MAPEO_ORIGEN_NUMERAL[$data['tipo_origen']] ?? '7.1.1';
        }

        // Estado inicial
        $data['estado'] = $data['estado'] ?? 'abierto';

        return $this->insert($data);
    }

    /**
     * Actualizar estado del hallazgo según sus acciones
     */
    public function actualizarEstadoSegunAcciones(int $idHallazgo): bool
    {
        $accionesModel = new AccAccionesModel();
        $acciones = $accionesModel->getByHallazgo($idHallazgo);

        if (empty($acciones)) {
            return $this->update($idHallazgo, ['estado' => 'abierto']);
        }

        $estados = array_column($acciones, 'estado');

        // Todas cerradas efectivamente
        if (count(array_unique($estados)) === 1 && $estados[0] === 'cerrada_efectiva') {
            return $this->update($idHallazgo, ['estado' => 'cerrado']);
        }

        // Todas cerradas pero alguna no efectiva
        $cerradas = ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'];
        if (count(array_diff($estados, $cerradas)) === 0 && in_array('cerrada_no_efectiva', $estados)) {
            return $this->update($idHallazgo, ['estado' => 'cerrado_no_efectivo']);
        }

        // Hay acciones en verificación
        if (in_array('en_verificacion', $estados)) {
            return $this->update($idHallazgo, ['estado' => 'en_verificacion']);
        }

        // Hay acciones en ejecución o asignadas
        return $this->update($idHallazgo, ['estado' => 'en_tratamiento']);
    }

    /**
     * Obtener estadísticas por cliente
     */
    public function getEstadisticas(int $idCliente, ?int $anio = null): array
    {
        $builder = $this->where('id_cliente', $idCliente);

        if ($anio) {
            $builder->where('YEAR(fecha_deteccion)', $anio);
        }

        $hallazgos = $builder->findAll();

        $stats = [
            'total' => count($hallazgos),
            'abiertos' => 0,
            'en_tratamiento' => 0,
            'en_verificacion' => 0,
            'cerrados' => 0,
            'por_numeral' => [
                '7.1.1' => 0,
                '7.1.2' => 0,
                '7.1.3' => 0,
                '7.1.4' => 0
            ],
            'por_severidad' => [
                'critica' => 0,
                'alta' => 0,
                'media' => 0,
                'baja' => 0
            ],
            'por_tipo_origen' => []
        ];

        foreach ($hallazgos as $h) {
            // Por estado
            switch ($h['estado']) {
                case 'abierto':
                    $stats['abiertos']++;
                    break;
                case 'en_tratamiento':
                    $stats['en_tratamiento']++;
                    break;
                case 'en_verificacion':
                    $stats['en_verificacion']++;
                    break;
                case 'cerrado':
                case 'cerrado_no_efectivo':
                    $stats['cerrados']++;
                    break;
            }

            // Por numeral
            if (isset($stats['por_numeral'][$h['numeral_asociado']])) {
                $stats['por_numeral'][$h['numeral_asociado']]++;
            }

            // Por severidad
            if (isset($stats['por_severidad'][$h['severidad']])) {
                $stats['por_severidad'][$h['severidad']]++;
            }

            // Por tipo de origen
            $tipo = $h['tipo_origen'];
            if (!isset($stats['por_tipo_origen'][$tipo])) {
                $stats['por_tipo_origen'][$tipo] = 0;
            }
            $stats['por_tipo_origen'][$tipo]++;
        }

        return $stats;
    }

    /**
     * Obtener hallazgos vencidos (pasaron de fecha límite sin cerrar)
     */
    public function getVencidos(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->whereNotIn('estado', ['cerrado', 'cerrado_no_efectivo'])
                    ->where('fecha_limite_accion <', date('Y-m-d'))
                    ->where('fecha_limite_accion IS NOT NULL')
                    ->orderBy('fecha_limite_accion', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener catálogo de tipos de origen
     */
    public function getCatalogoOrigenes(): array
    {
        $db = \Config\Database::connect();
        return $db->table('tbl_acc_catalogo_origenes')
                  ->where('activo', 1)
                  ->orderBy('orden', 'ASC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Obtener hallazgos para dashboard (resumen)
     */
    public function getParaDashboard(int $idCliente, int $limit = 10): array
    {
        return $this->select('tbl_acc_hallazgos.*, cat.nombre_mostrar as tipo_origen_nombre, cat.icono, cat.color')
                    ->join('tbl_acc_catalogo_origenes cat', 'cat.tipo_origen = tbl_acc_hallazgos.tipo_origen', 'left')
                    ->where('tbl_acc_hallazgos.id_cliente', $idCliente)
                    ->whereNotIn('tbl_acc_hallazgos.estado', ['cerrado'])
                    ->orderBy('FIELD(severidad, "critica", "alta", "media", "baja")', '', false)
                    ->orderBy('fecha_deteccion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
