<?php

namespace App\Models;

use CodeIgniter\Model;

class ComiteModel extends Model
{
    protected $table = 'tbl_comites';
    protected $primaryKey = 'id_comite';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_cliente',
        'id_tipo',
        'fecha_conformacion',
        'fecha_vencimiento',
        'acta_conformacion_id',
        'reglamento_documento_id',
        'periodicidad_personalizada',
        'dia_reunion_preferido',
        'hora_reunion_preferida',
        'lugar_habitual',
        'estado'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener comités activos de un cliente
     */
    public function getByCliente(int $idCliente): array
    {
        return $this->select('tbl_comites.*, tbl_tipos_comite.codigo, tbl_tipos_comite.nombre as tipo_nombre')
                    ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                    ->where('tbl_comites.id_cliente', $idCliente)
                    ->where('tbl_comites.estado', 'activo')
                    ->findAll();
    }

    /**
     * Obtener comité por cliente y tipo
     */
    public function getByClienteYTipo(int $idCliente, string $codigoTipo): ?array
    {
        return $this->select('tbl_comites.*, tbl_tipos_comite.codigo, tbl_tipos_comite.nombre as tipo_nombre')
                    ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                    ->where('tbl_comites.id_cliente', $idCliente)
                    ->where('tbl_tipos_comite.codigo', $codigoTipo)
                    ->where('tbl_comites.estado', 'activo')
                    ->first();
    }

    /**
     * Obtener comité con detalles completos
     */
    public function getConDetalles(int $idComite): ?array
    {
        $comite = $this->select('tbl_comites.*, tbl_tipos_comite.codigo, tbl_tipos_comite.codigo as tipo_codigo, tbl_tipos_comite.nombre as tipo_nombre,
                                 tbl_tipos_comite.periodicidad_dias as periodicidad_default,
                                 tbl_tipos_comite.requiere_quorum, tbl_tipos_comite.quorum_minimo_porcentaje')
                       ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                       ->find($idComite);

        if ($comite) {
            // Calcular periodicidad efectiva
            $comite['periodicidad_efectiva'] = $comite['periodicidad_personalizada'] ?? $comite['periodicidad_default'];

            // Contar miembros activos
            $miembroModel = new MiembroComiteModel();
            $comite['total_miembros'] = $miembroModel->contarActivos($idComite);
            $comite['miembros_principales'] = $miembroModel->contarPorTipo($idComite, 'principal');
            $comite['miembros_suplentes'] = $miembroModel->contarPorTipo($idComite, 'suplente');
        }

        return $comite;
    }

    /**
     * Verificar si el comité está vencido
     */
    public function estaVencido(int $idComite): bool
    {
        $comite = $this->find($idComite);
        if (!$comite || empty($comite['fecha_vencimiento'])) {
            return false;
        }
        return $comite['fecha_vencimiento'] < date('Y-m-d');
    }

    /**
     * Obtener comités próximos a vencer (30 días)
     */
    public function getProximosAVencer(int $dias = 30): array
    {
        $fechaLimite = date('Y-m-d', strtotime("+{$dias} days"));

        return $this->select('tbl_comites.*, tbl_tipos_comite.nombre as tipo_nombre, tbl_clientes.nombre_cliente')
                    ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_comites.id_cliente')
                    ->where('tbl_comites.estado', 'activo')
                    ->where('tbl_comites.fecha_vencimiento IS NOT NULL')
                    ->where('tbl_comites.fecha_vencimiento <=', $fechaLimite)
                    ->where('tbl_comites.fecha_vencimiento >=', date('Y-m-d'))
                    ->findAll();
    }

    /**
     * Crear comité con fecha de vencimiento automática
     */
    public function crearComite(array $data): int|false
    {
        // Obtener vigencia del tipo
        $tipoModel = new TipoComiteModel();
        $tipo = $tipoModel->find($data['id_tipo']);

        if ($tipo && $tipo['vigencia_periodo_meses']) {
            $data['fecha_vencimiento'] = date('Y-m-d', strtotime(
                $data['fecha_conformacion'] . " +{$tipo['vigencia_periodo_meses']} months"
            ));
        }

        return $this->insert($data);
    }
}
