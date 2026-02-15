<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar el marco normativo por tipo de documento
 * Módulo: Insumos IA - Pregeneración
 */
class MarcoNormativoModel extends Model
{
    protected $table = 'tbl_marco_normativo';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tipo_documento', 'marco_normativo_texto', 'fecha_actualizacion',
        'actualizado_por', 'metodo_actualizacion', 'vigencia_dias', 'activo'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener marco normativo por tipo de documento
     */
    public function getByTipoDocumento(string $tipo): ?array
    {
        return $this->where('tipo_documento', $tipo)
                     ->where('activo', 1)
                     ->first();
    }

    /**
     * Guardar marco normativo (SIEMPRE crea nueva versión, nunca sobrescribe)
     * Desactiva versiones anteriores y guarda la nueva como activa
     */
    public function guardar(string $tipo, string $texto, string $metodo = 'manual', string $actualizadoPor = 'consultor'): bool
    {
        // Desactivar todas las versiones anteriores de este tipo
        $this->where('tipo_documento', $tipo)
             ->where('activo', 1)
             ->set(['activo' => 0])
             ->update();

        // Insertar nueva versión como activa (SIEMPRE INSERT para mantener historial)
        $data = [
            'tipo_documento'        => $tipo,
            'marco_normativo_texto' => $texto,
            'fecha_actualizacion'   => date('Y-m-d H:i:s'),
            'actualizado_por'       => $actualizadoPor,
            'metodo_actualizacion'  => $metodo,
            'activo'                => 1
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Verificar si el marco normativo está vigente (dentro de vigencia_dias)
     */
    public function estaVigente(string $tipo): bool
    {
        $registro = $this->getByTipoDocumento($tipo);
        if (!$registro) {
            return false;
        }

        $dias = $this->getDiasDesdeActualizacion($tipo);
        return $dias !== null && $dias <= ($registro['vigencia_dias'] ?? 90);
    }

    /**
     * Obtener días transcurridos desde la última actualización
     */
    public function getDiasDesdeActualizacion(string $tipo): ?int
    {
        $registro = $this->getByTipoDocumento($tipo);
        if (!$registro || empty($registro['fecha_actualizacion'])) {
            return null;
        }

        $fechaActualizacion = new \DateTime($registro['fecha_actualizacion']);
        $hoy = new \DateTime();
        return (int) $hoy->diff($fechaActualizacion)->days;
    }

    /**
     * Obtener historial completo de versiones de un tipo de documento
     * @param string $tipo Tipo de documento
     * @param int $limit Número máximo de versiones a retornar (0 = todas)
     * @return array Lista de versiones ordenadas por fecha (más reciente primero)
     */
    public function getHistorial(string $tipo, int $limit = 0): array
    {
        $builder = $this->where('tipo_documento', $tipo)
                        ->orderBy('fecha_actualizacion', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Obtener todos los tipos de documentos con marco normativo
     * @return array Lista de tipos únicos con su versión activa
     */
    public function getTiposConMarco(): array
    {
        return $this->select('tipo_documento, MAX(fecha_actualizacion) as ultima_actualizacion')
                    ->where('activo', 1)
                    ->groupBy('tipo_documento')
                    ->orderBy('tipo_documento', 'ASC')
                    ->findAll();
    }
}
