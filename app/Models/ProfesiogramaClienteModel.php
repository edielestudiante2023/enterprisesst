<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ProfesiogramaClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_profesiograma_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente', 'id_cargo', 'cargo_texto',
        'id_examen', 'momento', 'frecuencia',
        'obligatorio', 'observaciones', 'origen', 'activo',
    ];

    /**
     * Examenes asignados a un cargo de un cliente.
     */
    public function porCargo(int $idCliente, int $idCargo): array
    {
        return $this->where('id_cliente', $idCliente)
            ->where('id_cargo', $idCargo)
            ->where('activo', 1)
            ->orderBy('momento', 'ASC')
            ->orderBy('id_examen', 'ASC')
            ->findAll();
    }

    /**
     * Conteo de examenes por cargo y momento para la vista index.
     */
    public function resumenPorCliente(int $idCliente): array
    {
        $db = \Config\Database::connect();
        return $db->table($this->table)
            ->select('id_cargo, cargo_texto, momento, COUNT(*) as total')
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupBy('id_cargo, cargo_texto, momento')
            ->get()
            ->getResultArray();
    }

    /**
     * Todos los cargos con examenes asignados para un cliente.
     */
    public function cargosConExamenes(int $idCliente): array
    {
        $db = \Config\Database::connect();
        return $db->table($this->table)
            ->select('DISTINCT id_cargo, cargo_texto')
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->get()
            ->getResultArray();
    }

    /**
     * Examenes de un cargo con datos del catalogo (JOIN).
     */
    public function examenesCargoConCatalogo(int $idCliente, int $idCargo): array
    {
        $db = \Config\Database::connect();
        return $db->table("{$this->table} p")
            ->select('p.*, c.nombre as examen_nombre, c.tipo_examen, c.descripcion as examen_descripcion, c.normativa_referencia')
            ->join('tbl_profesiograma_examenes_catalogo c', 'c.id = p.id_examen')
            ->where('p.id_cliente', $idCliente)
            ->where('p.id_cargo', $idCargo)
            ->where('p.activo', 1)
            ->orderBy('c.orden', 'ASC')
            ->orderBy('p.momento', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Borrado logico (soft-delete).
     */
    public function desactivar(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }
}
