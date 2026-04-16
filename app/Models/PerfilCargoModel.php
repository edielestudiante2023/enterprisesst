<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class PerfilCargoModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_perfil_cargo';
    protected $primaryKey = 'id_perfil_cargo';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_cargo_cliente',
        'objetivo_cargo',
        'reporta_a',
        'colaboradores_a_cargo',
        'condiciones_laborales',
        'edad_min',
        'estado_civil',
        'genero',
        'factores_riesgo',
        'formacion_educacion',
        'conocimiento_complementario',
        'experiencia_laboral',
        'validacion_educacion_experiencia',
        'funciones_especificas',
        'aprobador_nombre',
        'aprobador_cargo',
        'aprobador_cedula',
        'firma_aprobador_base64',
        'fecha_aprobacion',
        'version_actual',
        'estado',
        'id_documento_sst',
    ];

    protected array $jsonFields = [
        'condiciones_laborales',
        'factores_riesgo',
        'formacion_educacion',
        'conocimiento_complementario',
        'experiencia_laboral',
        'funciones_especificas',
    ];

    public function porCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->orderBy('id_perfil_cargo', 'DESC')
                    ->findAll();
    }

    public function porCargo(int $idCargoCliente): ?array
    {
        $row = $this->where('id_cargo_cliente', $idCargoCliente)->first();
        return $row ? $this->decodeJsonFields($row) : null;
    }

    public function buscarPorId(int $idPerfilCargo): ?array
    {
        $row = $this->find($idPerfilCargo);
        return $row ? $this->decodeJsonFields($row) : null;
    }

    public function crearVacio(int $idCliente, int $idCargoCliente): int
    {
        $this->insert([
            'id_cliente'       => $idCliente,
            'id_cargo_cliente' => $idCargoCliente,
            'estado'           => 'borrador',
            'version_actual'   => 1,
        ]);
        return (int)$this->getInsertID();
    }

    protected function decodeJsonFields(array $row): array
    {
        foreach ($this->jsonFields as $field) {
            if (isset($row[$field]) && is_string($row[$field]) && $row[$field] !== '') {
                $decoded = json_decode($row[$field], true);
                $row[$field] = is_array($decoded) ? $decoded : null;
            }
        }
        return $row;
    }
}
