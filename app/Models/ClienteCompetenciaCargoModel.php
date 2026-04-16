<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ClienteCompetenciaCargoModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_cliente_competencia_cargo';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_cargo_cliente',
        'id_competencia',
        'nivel_requerido',
        'observacion',
    ];

    public function porCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('id_cargo_cliente', 'ASC')
            ->orderBy('id_competencia', 'ASC')
            ->findAll();
    }

    public function porCargo(int $idCargoCliente): array
    {
        return $this->where('id_cargo_cliente', $idCargoCliente)
            ->orderBy('id_competencia', 'ASC')
            ->findAll();
    }

    /**
     * Matriz completa del cliente con nombres resueltos, lista para render.
     * Retorna filas con: id_cargo_cliente, nombre_cargo, id_competencia,
     * nombre_competencia, familia, nivel_requerido, observacion.
     */
    public function matrizCliente(int $idCliente): array
    {
        return $this->db->table($this->table . ' m')
            ->select('m.id, m.id_cargo_cliente, m.id_competencia, m.nivel_requerido, m.observacion,
                      cg.nombre_cargo,
                      c.nombre AS nombre_competencia, c.numero AS numero_competencia, c.familia')
            ->join('tbl_cargos_cliente cg', 'cg.id = m.id_cargo_cliente', 'inner')
            ->join('tbl_competencia_cliente c', 'c.id_competencia = m.id_competencia', 'inner')
            ->where('m.id_cliente', $idCliente)
            ->orderBy('cg.nombre_cargo', 'ASC')
            ->orderBy('c.numero', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Upsert de asignacion cargo-competencia: si existe actualiza nivel y
     * observacion, si no existe la crea.
     */
    public function upsertAsignacion(int $idCliente, int $idCargo, int $idCompetencia, int $nivel, ?string $observacion = null): void
    {
        $existente = $this->where('id_cargo_cliente', $idCargo)
            ->where('id_competencia', $idCompetencia)
            ->first();

        if ($existente) {
            $this->update($existente['id'], [
                'nivel_requerido' => $nivel,
                'observacion'     => $observacion,
            ]);
        } else {
            $this->insert([
                'id_cliente'       => $idCliente,
                'id_cargo_cliente' => $idCargo,
                'id_competencia'   => $idCompetencia,
                'nivel_requerido'  => $nivel,
                'observacion'      => $observacion,
            ]);
        }
    }
}
