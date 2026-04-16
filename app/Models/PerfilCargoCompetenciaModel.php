<?php

namespace App\Models;

use CodeIgniter\Model;

class PerfilCargoCompetenciaModel extends Model
{
    protected $table = 'tbl_perfil_cargo_competencia';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_perfil_cargo',
        'id_competencia',
        'nivel_requerido',
        'observacion',
        'orden',
    ];

    /**
     * Devuelve las competencias del perfil con JOIN al catalogo del cliente
     * para obtener nombre, familia y codigo.
     */
    public function porPerfil(int $idPerfilCargo): array
    {
        return $this->select('tbl_perfil_cargo_competencia.*, tbl_competencia_cliente.nombre, tbl_competencia_cliente.codigo, tbl_competencia_cliente.familia, tbl_competencia_cliente.definicion')
                    ->join('tbl_competencia_cliente', 'tbl_competencia_cliente.id_competencia = tbl_perfil_cargo_competencia.id_competencia')
                    ->where('tbl_perfil_cargo_competencia.id_perfil_cargo', $idPerfilCargo)
                    ->orderBy('tbl_perfil_cargo_competencia.orden', 'ASC')
                    ->findAll();
    }

    public function reemplazarTodas(int $idPerfilCargo, array $items): void
    {
        $this->where('id_perfil_cargo', $idPerfilCargo)->delete();
        if (empty($items)) return;

        $rows = [];
        foreach ($items as $i => $item) {
            $rows[] = [
                'id_perfil_cargo' => $idPerfilCargo,
                'id_competencia'  => (int)$item['id_competencia'],
                'nivel_requerido' => (int)$item['nivel_requerido'],
                'observacion'     => $item['observacion'] ?? null,
                'orden'           => $item['orden'] ?? ($i + 1),
            ];
        }
        $this->insertBatch($rows);
    }

    /**
     * Precarga las competencias asignadas al cargo en tbl_cliente_competencia_cargo
     * (modulo Diccionario de Competencias) como sugerencia inicial.
     */
    public function precargarDesdeMatriz(int $idPerfilCargo, int $idCargoCliente): int
    {
        $db = \Config\Database::connect();
        $filas = $db->table('tbl_cliente_competencia_cargo')
                    ->where('id_cargo_cliente', $idCargoCliente)
                    ->get()->getResultArray();

        if (empty($filas)) return 0;

        $items = [];
        foreach ($filas as $i => $f) {
            $items[] = [
                'id_competencia'  => (int)$f['id_competencia'],
                'nivel_requerido' => (int)$f['nivel_requerido'],
                'observacion'     => $f['observacion'] ?? null,
                'orden'           => $i + 1,
            ];
        }
        $this->reemplazarTodas($idPerfilCargo, $items);
        return count($items);
    }
}
