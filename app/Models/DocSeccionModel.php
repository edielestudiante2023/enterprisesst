<?php
namespace App\Models;

use CodeIgniter\Model;

class DocSeccionModel extends Model
{
    protected $table = 'tbl_doc_secciones';
    protected $primaryKey = 'id_seccion';
    protected $allowedFields = [
        'id_documento', 'numero_seccion', 'nombre_seccion', 'contenido',
        'contenido_html', 'aprobado', 'fecha_aprobacion', 'regeneraciones',
        'ultima_regeneracion', 'contexto_adicional'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtiene secciones de un documento
     */
    public function getByDocumento(int $idDocumento): array
    {
        return $this->where('id_documento', $idDocumento)
                    ->orderBy('orden', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene una sección específica
     */
    public function getSeccion(int $idDocumento, int $numeroSeccion): ?array
    {
        return $this->where('id_documento', $idDocumento)
                    ->where('numero_seccion', $numeroSeccion)
                    ->first();
    }

    /**
     * Crea secciones iniciales para un documento basado en plantilla
     */
    public function crearDesdeEstructura(int $idDocumento, array $estructura): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($estructura as $index => $seccion) {
            // Manejar diferentes formatos de estructura
            $nombreSeccion = is_array($seccion) ? ($seccion['nombre'] ?? $seccion[0] ?? '') : $seccion;

            $this->insert([
                'id_documento' => $idDocumento,
                'numero_seccion' => $index + 1,
                'nombre_seccion' => $nombreSeccion,
                'contenido' => '',
                'aprobado' => 0
            ]);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Actualiza contenido de una sección
     */
    public function actualizarContenido(int $idSeccion, string $contenido, bool $generadoPorIA = false, ?string $prompt = null): bool
    {
        $data = [
            'contenido' => $contenido
        ];

        if ($generadoPorIA) {
            // Incrementar contador de regeneraciones
            $seccion = $this->find($idSeccion);
            $data['regeneraciones'] = ($seccion['regeneraciones'] ?? 0) + 1;
            $data['ultima_regeneracion'] = date('Y-m-d H:i:s');
        }

        return $this->update($idSeccion, $data);
    }

    /**
     * Obtiene progreso de secciones de un documento
     */
    public function getProgreso(int $idDocumento): array
    {
        $secciones = $this->where('id_documento', $idDocumento)->findAll();

        $total = count($secciones);
        $completadas = 0;
        $conContenido = 0;
        $pendientes = 0;

        foreach ($secciones as $seccion) {
            if ($seccion['aprobado']) {
                $completadas++;
            } elseif (!empty($seccion['contenido'])) {
                $conContenido++;
            } else {
                $pendientes++;
            }
        }

        return [
            'total' => $total,
            'completadas' => $completadas,
            'con_contenido' => $conContenido,
            'pendientes' => $pendientes,
            'porcentaje' => $total > 0 ? round((($completadas + $conContenido) / $total) * 100, 0) : 0
        ];
    }

    /**
     * Marca sección como aprobada
     */
    public function aprobar(int $idSeccion): bool
    {
        return $this->update($idSeccion, [
            'aprobado' => 1,
            'fecha_aprobacion' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Regenera contenido de una sección (guarda contexto adicional)
     */
    public function prepararRegeneracion(int $idSeccion, string $contextoAdicional): bool
    {
        return $this->update($idSeccion, [
            'contexto_adicional' => $contextoAdicional,
            'aprobado' => 0 // Resetear aprobación si se regenera
        ]);
    }

    /**
     * Obtiene secciones pendientes de un documento (sin contenido)
     */
    public function getPendientes(int $idDocumento): array
    {
        return $this->where('id_documento', $idDocumento)
                    ->where('contenido', '')
                    ->orWhere('contenido IS NULL')
                    ->orderBy('numero_seccion', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene siguiente sección pendiente
     */
    public function getSiguientePendiente(int $idDocumento): ?array
    {
        return $this->where('id_documento', $idDocumento)
                    ->groupStart()
                        ->where('contenido', '')
                        ->orWhere('contenido IS NULL')
                    ->groupEnd()
                    ->orderBy('numero_seccion', 'ASC')
                    ->first();
    }

    /**
     * Obtiene contenido completo del documento como texto
     */
    public function getContenidoCompleto(int $idDocumento): string
    {
        $secciones = $this->getByDocumento($idDocumento);
        $contenido = '';

        foreach ($secciones as $seccion) {
            $contenido .= "## {$seccion['numero_seccion']}. {$seccion['nombre_seccion']}\n\n";
            $contenido .= $seccion['contenido'] . "\n\n";
        }

        return $contenido;
    }

    /**
     * Obtiene contenido como JSON para snapshot
     */
    public function getContenidoJson(int $idDocumento): string
    {
        $secciones = $this->getByDocumento($idDocumento);

        $data = array_map(function ($seccion) {
            return [
                'numero_seccion' => $seccion['numero_seccion'],
                'nombre_seccion' => $seccion['nombre_seccion'],
                'contenido' => $seccion['contenido']
            ];
        }, $secciones);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
