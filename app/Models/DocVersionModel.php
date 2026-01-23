<?php
namespace App\Models;

use CodeIgniter\Model;

class DocVersionModel extends Model
{
    protected $table = 'tbl_doc_versiones';
    protected $primaryKey = 'id_version';
    protected $allowedFields = [
        'id_documento', 'version', 'tipo_cambio', 'descripcion_cambio',
        'contenido_snapshot', 'autorizado_por', 'fecha', 'archivo_pdf',
        'archivo_word', 'hash_documento', 'estado'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = false; // Solo tiene created_at automático

    /**
     * Obtiene historial de versiones de un documento
     */
    public function getByDocumento(int $idDocumento): array
    {
        return $this->where('id_documento', $idDocumento)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene la versión vigente de un documento
     */
    public function getVigente(int $idDocumento): ?array
    {
        return $this->where('id_documento', $idDocumento)
                    ->where('estado', 'vigente')
                    ->first();
    }

    /**
     * Crea nueva versión usando SP
     */
    public function crearVersion(int $idDocumento, string $tipoCambio, string $descripcion, string $autorizadoPor): array
    {
        $db = \Config\Database::connect();

        $query = $db->query("CALL sp_crear_version_documento(?, ?, ?, ?)", [
            $idDocumento,
            $tipoCambio,
            $descripcion,
            $autorizadoPor
        ]);

        $result = $query->getRowArray();
        $query->close();

        return $result ?? [];
    }

    /**
     * Obtiene snapshot de una versión
     */
    public function getSnapshot(int $idVersion): ?array
    {
        $version = $this->find($idVersion);

        if (!$version || empty($version['contenido_snapshot'])) {
            return null;
        }

        return json_decode($version['contenido_snapshot'], true);
    }

    /**
     * Compara dos versiones
     */
    public function comparar(int $idVersion1, int $idVersion2): array
    {
        $v1 = $this->getSnapshot($idVersion1);
        $v2 = $this->getSnapshot($idVersion2);

        if (!$v1 || !$v2) {
            return ['error' => 'No se pueden obtener las versiones'];
        }

        $diferencias = [];

        foreach ($v1 as $index => $seccion1) {
            $seccion2 = $v2[$index] ?? null;

            if (!$seccion2) {
                $diferencias[] = [
                    'seccion' => $seccion1['nombre_seccion'],
                    'tipo' => 'eliminada'
                ];
                continue;
            }

            if ($seccion1['contenido'] !== $seccion2['contenido']) {
                $diferencias[] = [
                    'seccion' => $seccion1['nombre_seccion'],
                    'tipo' => 'modificada',
                    'contenido_anterior' => $seccion1['contenido'],
                    'contenido_nuevo' => $seccion2['contenido']
                ];
            }
        }

        return $diferencias;
    }

    /**
     * Restaura una versión anterior
     */
    public function restaurar(int $idVersion, string $autorizadoPor): bool
    {
        $version = $this->find($idVersion);

        if (!$version) {
            return false;
        }

        $snapshot = json_decode($version['contenido_snapshot'], true);

        if (!$snapshot) {
            return false;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Eliminar secciones actuales
        $db->table('tbl_doc_secciones')
           ->where('id_documento', $version['id_documento'])
           ->delete();

        // Restaurar secciones del snapshot
        foreach ($snapshot as $index => $seccion) {
            $db->table('tbl_doc_secciones')->insert([
                'id_documento' => $version['id_documento'],
                'numero_seccion' => $seccion['numero_seccion'],
                'nombre_seccion' => $seccion['nombre_seccion'],
                'contenido' => $seccion['contenido'],
                'estado' => 'completada',
                'orden' => $index + 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Crear nueva versión indicando restauración
        $this->crearVersion(
            $version['id_documento'],
            'mayor',
            "Restauración a versión {$version['version']}",
            $autorizadoPor
        );

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Obtiene número de versiones de un documento
     */
    public function contarVersiones(int $idDocumento): int
    {
        return $this->where('id_documento', $idDocumento)->countAllResults();
    }

    /**
     * Determina si un cambio es menor o mayor
     */
    public function determinarTipoCambio(array $cambios): string
    {
        $camposMayores = ['objetivos', 'alcance', 'metodologia', 'indicadores', 'marco_normativo'];

        foreach ($cambios as $campo => $valor) {
            if (in_array($campo, $camposMayores)) {
                return 'mayor';
            }
        }

        return 'menor';
    }
}
