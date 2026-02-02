<?php

namespace App\Models;

use CodeIgniter\Model;

class PlantillaOrdenDiaModel extends Model
{
    protected $table = 'tbl_actas_plantillas_orden';
    protected $primaryKey = 'id_plantilla';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_tipo_comite',
        'nombre',
        'es_default',
        'puntos',
        'activo'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected array $casts = [
        'puntos' => 'json-array'
    ];

    /**
     * Obtener plantilla por defecto de un tipo de comité
     */
    public function getDefaultPorTipo(int $idTipo): ?array
    {
        $plantilla = $this->where('id_tipo_comite', $idTipo)
                          ->where('es_default', 1)
                          ->where('activo', 1)
                          ->first();

        if ($plantilla && is_string($plantilla['puntos'])) {
            $plantilla['puntos'] = json_decode($plantilla['puntos'], true);
        }

        return $plantilla;
    }

    /**
     * Obtener todas las plantillas de un tipo de comité
     */
    public function getByTipoComite(int $idTipo): array
    {
        $plantillas = $this->where('id_tipo_comite', $idTipo)
                           ->where('activo', 1)
                           ->findAll();

        foreach ($plantillas as &$p) {
            if (is_string($p['puntos'])) {
                $p['puntos'] = json_decode($p['puntos'], true);
            }
        }

        return $plantillas;
    }

    /**
     * Obtener puntos fijos de una plantilla
     */
    public function getPuntosFijos(int $idPlantilla): array
    {
        $plantilla = $this->find($idPlantilla);
        if (!$plantilla) {
            return [];
        }

        $puntos = is_string($plantilla['puntos'])
            ? json_decode($plantilla['puntos'], true)
            : $plantilla['puntos'];

        return array_filter($puntos, fn($p) => !empty($p['fijo']));
    }

    /**
     * Crear plantilla personalizada
     */
    public function crearPlantilla(int $idTipoComite, string $nombre, array $puntos): int|false
    {
        return $this->insert([
            'id_tipo_comite' => $idTipoComite,
            'nombre' => $nombre,
            'es_default' => 0,
            'puntos' => json_encode($puntos),
            'activo' => 1
        ]);
    }

    /**
     * Establecer como plantilla por defecto
     */
    public function establecerDefault(int $idPlantilla): bool
    {
        $plantilla = $this->find($idPlantilla);
        if (!$plantilla) {
            return false;
        }

        // Quitar default a las demás del mismo tipo
        $this->where('id_tipo_comite', $plantilla['id_tipo_comite'])
             ->set('es_default', 0)
             ->update();

        // Establecer esta como default
        return $this->update($idPlantilla, ['es_default' => 1]);
    }
}
