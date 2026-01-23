<?php
namespace App\Models;

use CodeIgniter\Model;

class DocPlantillaModel extends Model
{
    protected $table = 'tbl_doc_plantillas';
    protected $primaryKey = 'id_plantilla';
    protected $allowedFields = [
        'id_tipo', 'nombre', 'descripcion', 'codigo_sugerido',
        'estructura_json', 'prompts_json', 'variables_contexto', 'activo', 'orden'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtiene plantillas activas
     */
    public function getActivas(): array
    {
        return $this->where('activo', 1)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene plantillas por tipo de documento
     */
    public function getByTipo(int $idTipo): array
    {
        return $this->where('id_tipo', $idTipo)
                    ->where('activo', 1)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene estructura de secciones de una plantilla
     */
    public function getEstructura(int $idPlantilla): array
    {
        $plantilla = $this->find($idPlantilla);

        if (!$plantilla || empty($plantilla['estructura_json'])) {
            return [];
        }

        return json_decode($plantilla['estructura_json'], true) ?? [];
    }

    /**
     * Obtiene prompts de una plantilla
     */
    public function getPrompts(int $idPlantilla): array
    {
        $plantilla = $this->find($idPlantilla);

        if (!$plantilla || empty($plantilla['prompts_json'])) {
            return [];
        }

        return json_decode($plantilla['prompts_json'], true) ?? [];
    }

    /**
     * Obtiene prompt para una sección específica
     */
    public function getPromptSeccion(int $idPlantilla, int $numeroSeccion): ?string
    {
        $prompts = $this->getPrompts($idPlantilla);

        return $prompts[$numeroSeccion] ?? null;
    }

    /**
     * Obtiene variables requeridas de una plantilla
     */
    public function getVariables(int $idPlantilla): array
    {
        $plantilla = $this->find($idPlantilla);

        if (!$plantilla || empty($plantilla['variables_contexto'])) {
            return [];
        }

        return json_decode($plantilla['variables_contexto'], true) ?? [];
    }

    /**
     * Obtiene plantilla con información de tipo
     */
    public function getConTipo(int $idPlantilla): ?array
    {
        return $this->select('tbl_doc_plantillas.*, tbl_doc_tipos.nombre as tipo_nombre, tbl_doc_tipos.codigo as tipo_codigo')
                    ->join('tbl_doc_tipos', 'tbl_doc_tipos.id_tipo = tbl_doc_plantillas.id_tipo')
                    ->where('tbl_doc_plantillas.id_plantilla', $idPlantilla)
                    ->first();
    }

    /**
     * Guarda estructura de secciones
     */
    public function guardarEstructura(int $idPlantilla, array $estructura): bool
    {
        return $this->update($idPlantilla, [
            'estructura_json' => json_encode($estructura, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Guarda prompts por sección
     */
    public function guardarPrompts(int $idPlantilla, array $prompts): bool
    {
        return $this->update($idPlantilla, [
            'prompts_json' => json_encode($prompts, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Duplica una plantilla
     */
    public function duplicar(int $idPlantilla, string $nuevoNombre): int
    {
        $original = $this->find($idPlantilla);

        if (!$original) {
            return 0;
        }

        unset($original['id_plantilla'], $original['created_at'], $original['updated_at']);
        $original['nombre'] = $nuevoNombre;

        $this->insert($original);
        return $this->getInsertID();
    }

    /**
     * Obtiene plantillas agrupadas por tipo
     */
    public function getAgrupadasPorTipo(): array
    {
        $plantillas = $this->select('tbl_doc_plantillas.*, tbl_doc_tipos.nombre as tipo_nombre')
                          ->join('tbl_doc_tipos', 'tbl_doc_tipos.id_tipo = tbl_doc_plantillas.id_tipo')
                          ->where('tbl_doc_plantillas.activo', 1)
                          ->orderBy('tbl_doc_tipos.nombre', 'ASC')
                          ->orderBy('tbl_doc_plantillas.nombre', 'ASC')
                          ->findAll();

        $agrupadas = [];
        foreach ($plantillas as $plantilla) {
            $tipo = $plantilla['tipo_nombre'];
            if (!isset($agrupadas[$tipo])) {
                $agrupadas[$tipo] = [];
            }
            $agrupadas[$tipo][] = $plantilla;
        }

        return $agrupadas;
    }
}
