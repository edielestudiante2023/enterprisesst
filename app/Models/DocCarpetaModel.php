<?php
namespace App\Models;

use CodeIgniter\Model;

class DocCarpetaModel extends Model
{
    protected $table = 'tbl_doc_carpetas';
    protected $primaryKey = 'id_carpeta';
    protected $allowedFields = [
        'id_cliente', 'id_carpeta_padre', 'nombre', 'codigo', 'orden',
        'tipo', 'icono', 'color', 'id_estandar'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Genera estructura de carpetas para un cliente usando SP
     * Usa estructura estilo Drive: SG-SST [Año] / PHVA / Categoría / [Documentos]
     * Las carpetas se crean según el nivel de estándares del cliente (7, 21 o 60)
     */
    public function generarEstructura(int $idCliente, int $anio, int $nivelEstandares = 60): int
    {
        $db = \Config\Database::connect();

        // Usar el SP que respeta el nivel de estándares
        $query = $db->query("CALL sp_generar_carpetas_por_nivel(?, ?, ?)", [$idCliente, $anio, $nivelEstandares]);
        $result = $query->getRowArray();

        // Liberar resultados del SP para evitar errores de "commands out of sync"
        if (method_exists($query, 'freeResult')) {
            $query->freeResult();
        }
        while ($db->connID->next_result()) {
            $db->connID->store_result();
        }

        return $result['id_carpeta_raiz'] ?? 0;
    }

    /**
     * Obtiene la carpeta destino para un documento según su plantilla
     */
    public function getCarpetaParaDocumento(int $idCliente, string $codigoPlantilla): ?int
    {
        $db = \Config\Database::connect();

        $query = $db->query("SELECT fn_get_carpeta_documento(?, ?) as id_carpeta", [$idCliente, $codigoPlantilla]);
        $result = $query->getRowArray();

        return $result['id_carpeta'] ?? null;
    }

    /**
     * Obtiene carpetas raíz de un cliente
     */
    public function getRaiz(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('id_carpeta_padre IS NULL')
                    ->orderBy('orden', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene hijos de una carpeta
     */
    public function getHijos(int $idCarpeta): array
    {
        return $this->where('id_carpeta_padre', $idCarpeta)
                    ->orderBy('orden', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene árbol completo de carpetas de un cliente
     */
    public function getArbolCompleto(int $idCliente): array
    {
        $carpetas = $this->where('id_cliente', $idCliente)
                         ->orderBy('orden', 'ASC')
                         ->findAll();

        return $this->construirArbol($carpetas);
    }

    /**
     * Construye árbol jerárquico desde lista plana
     */
    private function construirArbol(array $carpetas, ?int $padreId = null): array
    {
        $arbol = [];

        foreach ($carpetas as $carpeta) {
            if ($carpeta['id_carpeta_padre'] == $padreId) {
                $carpeta['hijos'] = $this->construirArbol($carpetas, $carpeta['id_carpeta']);
                $arbol[] = $carpeta;
            }
        }

        return $arbol;
    }

    /**
     * Obtiene lista plana de carpetas con nivel de indentacion
     * Util para selectores dropdown
     */
    public function getListaPlanaConNivel(int $idCliente): array
    {
        $carpetas = $this->where('id_cliente', $idCliente)
                         ->orderBy('orden', 'ASC')
                         ->findAll();

        $resultado = [];
        $this->aplanarArbol($carpetas, null, 0, $resultado);
        return $resultado;
    }

    /**
     * Aplana el arbol de carpetas agregando nivel de indentacion
     */
    private function aplanarArbol(array $carpetas, ?int $padreId, int $nivel, array &$resultado): void
    {
        foreach ($carpetas as $carpeta) {
            if ($carpeta['id_carpeta_padre'] == $padreId) {
                $carpeta['nivel'] = $nivel;
                $resultado[] = $carpeta;
                $this->aplanarArbol($carpetas, $carpeta['id_carpeta'], $nivel + 1, $resultado);
            }
        }
    }

    /**
     * Busca carpeta por codigo dentro de las carpetas de un cliente
     */
    public function getByCodigo(int $idCliente, string $codigo): ?array
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('codigo', $codigo)
                    ->first();
    }

    /**
     * Obtiene ruta completa de una carpeta (breadcrumb)
     */
    public function getRuta(int $idCarpeta): array
    {
        $ruta = [];
        $carpeta = $this->find($idCarpeta);

        while ($carpeta) {
            array_unshift($ruta, $carpeta);
            $carpeta = $carpeta['id_carpeta_padre']
                ? $this->find($carpeta['id_carpeta_padre'])
                : null;
        }

        return $ruta;
    }

    /**
     * Obtiene carpeta por estándar
     */
    public function getByEstandar(int $idCliente, int $idEstandar): ?array
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('id_estandar', $idEstandar)
                    ->first();
    }

    /**
     * Cuenta documentos en una carpeta
     */
    public function contarDocumentos(int $idCarpeta): int
    {
        return $this->db->table('tbl_doc_documentos')
                       ->where('id_carpeta', $idCarpeta)
                       ->countAllResults();
    }

    /**
     * Obtiene carpetas con conteo de documentos
     */
    public function getConConteo(int $idCliente): array
    {
        return $this->select('tbl_doc_carpetas.*,
                             COUNT(tbl_doc_documentos.id_documento) as total_documentos')
                    ->join('tbl_doc_documentos', 'tbl_doc_documentos.id_carpeta = tbl_doc_carpetas.id_carpeta', 'left')
                    ->where('tbl_doc_carpetas.id_cliente', $idCliente)
                    ->groupBy('tbl_doc_carpetas.id_carpeta')
                    ->orderBy('tbl_doc_carpetas.orden', 'ASC')
                    ->findAll();
    }

    /**
     * Mueve una carpeta a otro padre
     */
    public function mover(int $idCarpeta, ?int $nuevoPadreId): bool
    {
        return $this->update($idCarpeta, ['id_carpeta_padre' => $nuevoPadreId]);
    }

    /**
     * Obtiene JSON de carpetas usando la función
     */
    public function getJsonCarpetas(int $idCliente): ?string
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT fn_get_carpetas_json(?) as json_carpetas", [$idCliente]);
        $result = $query->getRowArray();

        return $result['json_carpetas'] ?? null;
    }

    /**
     * Obtiene árbol completo de carpetas con documentos y estados IA
     * Incluye conteo de documentos por estado en cada carpeta
     */
    public function getArbolConDocumentosYEstados(int $idCliente): array
    {
        $carpetas = $this->where('id_cliente', $idCliente)
                         ->orderBy('orden', 'ASC')
                         ->findAll();

        $documentoModel = new DocDocumentoModel();

        // Obtener todos los documentos del cliente con sus estados IA
        $todosDocumentos = $documentoModel->getByClienteConEstadoIA($idCliente);

        // Indexar documentos por carpeta
        $docsPorCarpeta = [];
        foreach ($todosDocumentos as $doc) {
            $idCarpeta = $doc['id_carpeta'] ?? 0;
            if (!isset($docsPorCarpeta[$idCarpeta])) {
                $docsPorCarpeta[$idCarpeta] = [];
            }
            $docsPorCarpeta[$idCarpeta][] = $doc;
        }

        return $this->construirArbolConDocs($carpetas, $docsPorCarpeta);
    }

    /**
     * Construye árbol jerárquico con documentos y estadísticas
     */
    private function construirArbolConDocs(array $carpetas, array $docsPorCarpeta, ?int $padreId = null): array
    {
        $arbol = [];

        foreach ($carpetas as $carpeta) {
            if ($carpeta['id_carpeta_padre'] == $padreId) {
                $idCarpeta = $carpeta['id_carpeta'];

                // Agregar documentos de esta carpeta
                $carpeta['documentos'] = $docsPorCarpeta[$idCarpeta] ?? [];

                // Calcular estadísticas de esta carpeta
                $carpeta['stats'] = [
                    'total' => count($carpeta['documentos']),
                    'pendiente' => 0,
                    'creado' => 0,
                    'aprobado' => 0
                ];

                foreach ($carpeta['documentos'] as $doc) {
                    $estado = $doc['estado_ia'] ?? 'pendiente';
                    if (isset($carpeta['stats'][$estado])) {
                        $carpeta['stats'][$estado]++;
                    }
                }

                // Procesar hijos recursivamente
                $carpeta['hijos'] = $this->construirArbolConDocs($carpetas, $docsPorCarpeta, $idCarpeta);

                // Agregar estadísticas de subcarpetas al total
                foreach ($carpeta['hijos'] as $hijo) {
                    $carpeta['stats']['total'] += $hijo['stats']['total'];
                    $carpeta['stats']['pendiente'] += $hijo['stats']['pendiente'];
                    $carpeta['stats']['creado'] += $hijo['stats']['creado'];
                    $carpeta['stats']['aprobado'] += $hijo['stats']['aprobado'];
                }

                $arbol[] = $carpeta;
            }
        }

        return $arbol;
    }

    /**
     * Obtiene ruta completa de una carpeta con información extendida (breadcrumb)
     */
    public function getRutaCompleta(int $idCarpeta): array
    {
        $ruta = [];
        $carpeta = $this->find($idCarpeta);

        while ($carpeta) {
            array_unshift($ruta, [
                'id_carpeta' => $carpeta['id_carpeta'],
                'nombre' => $carpeta['nombre'],
                'codigo' => $carpeta['codigo'] ?? '',
                'tipo' => $carpeta['tipo'] ?? 'custom'
            ]);
            $carpeta = $carpeta['id_carpeta_padre']
                ? $this->find($carpeta['id_carpeta_padre'])
                : null;
        }

        return $ruta;
    }
}
