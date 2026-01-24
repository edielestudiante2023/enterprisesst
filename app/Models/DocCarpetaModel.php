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
}
