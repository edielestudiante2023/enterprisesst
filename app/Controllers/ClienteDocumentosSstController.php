<?php

namespace App\Controllers;

use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para que el CLIENTE vea sus documentos SST aprobados
 * Solo lectura - sin edicion, sin gestion
 */
class ClienteDocumentosSstController extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Lista los documentos SST del cliente organizados por carpeta/estandar
     */
    public function index()
    {
        $session = session();
        $idCliente = $session->get('id_cliente') ?? $session->get('user_id');

        if (!$idCliente) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesion');
        }

        // Obtener datos del cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$cliente) {
            return redirect()->to('/dashboardclient')->with('error', 'Cliente no encontrado');
        }

        // Obtener carpeta raiz del cliente
        $carpetaRaiz = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre IS NULL')
            ->where('visible', 1)
            ->get()
            ->getRowArray();

        // Obtener carpetas PHVA (hijos de la raiz)
        $carpetasPHVA = [];
        if ($carpetaRaiz) {
            $carpetasPHVA = $this->db->table('tbl_doc_carpetas')
                ->where('id_cliente', $idCliente)
                ->where('id_carpeta_padre', $carpetaRaiz['id_carpeta'])
                ->where('visible', 1)
                ->orderBy('orden', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Construir arbol de carpetas con conteo de documentos
        $arbolCarpetas = [];
        foreach ($carpetasPHVA as $phva) {
            $arbolCarpetas[] = $this->construirArbolConDocumentos($phva, $idCliente);
        }

        // Total de documentos del cliente
        $totalDocumentos = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->whereIn('estado', ['aprobado', 'firmado'])
            ->countAllResults();

        $data = [
            'titulo' => 'Mis Documentos SST',
            'cliente' => $cliente,
            'arbolCarpetas' => $arbolCarpetas,
            'totalDocumentos' => $totalDocumentos,
            'carpetaActual' => null
        ];

        return view('client/documentos_sst/index', $data);
    }

    /**
     * Muestra los documentos de una carpeta especifica
     */
    public function carpeta(int $idCarpeta)
    {
        $session = session();
        $idCliente = $session->get('id_cliente') ?? $session->get('user_id');

        if (!$idCliente) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesion');
        }

        // Obtener datos del cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        // Obtener carpeta actual (verificar que pertenece al cliente)
        $carpeta = $this->db->table('tbl_doc_carpetas')
            ->where('id_carpeta', $idCarpeta)
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$carpeta) {
            return redirect()->to('/client/mis-documentos-sst')->with('error', 'Carpeta no encontrada');
        }

        // Obtener ruta de navegacion (breadcrumb)
        $ruta = $this->obtenerRutaCarpeta($idCarpeta);

        // Obtener subcarpetas
        $subcarpetas = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre', $idCarpeta)
            ->where('visible', 1)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        // Agregar conteo de documentos a cada subcarpeta
        foreach ($subcarpetas as &$sub) {
            $sub['total_docs'] = $this->contarDocumentosEnCarpeta($sub['id_carpeta'], $idCliente);
        }

        // Obtener documentos aprobados/firmados de esta carpeta
        $documentos = $this->obtenerDocumentosCarpeta($idCarpeta, $idCliente);

        $data = [
            'titulo' => $carpeta['nombre'] . ' - Mis Documentos SST',
            'cliente' => $cliente,
            'carpeta' => $carpeta,
            'ruta' => $ruta,
            'subcarpetas' => $subcarpetas,
            'documentos' => $documentos
        ];

        return view('client/documentos_sst/carpeta', $data);
    }

    /**
     * Construye el arbol de carpetas con conteo de documentos
     */
    protected function construirArbolConDocumentos(array $carpeta, int $idCliente): array
    {
        $hijos = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre', $carpeta['id_carpeta'])
            ->where('visible', 1)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        $carpeta['hijos'] = [];
        $carpeta['total_docs'] = $this->contarDocumentosEnCarpeta($carpeta['id_carpeta'], $idCliente);

        foreach ($hijos as $hijo) {
            $carpeta['hijos'][] = $this->construirArbolConDocumentos($hijo, $idCliente);
        }

        return $carpeta;
    }

    /**
     * Cuenta documentos aprobados/firmados en una carpeta (recursivo)
     */
    protected function contarDocumentosEnCarpeta(int $idCarpeta, int $idCliente): int
    {
        // Obtener codigos de carpeta (estandares)
        $carpeta = $this->db->table('tbl_doc_carpetas')
            ->where('id_carpeta', $idCarpeta)
            ->get()
            ->getRowArray();

        $codigoCarpeta = $carpeta['codigo'] ?? '';

        // Buscar plantillas mapeadas a esta carpeta
        $plantillas = $this->db->table('tbl_doc_plantilla_carpeta')
            ->where('codigo_carpeta', $codigoCarpeta)
            ->get()
            ->getResultArray();

        $total = 0;

        // Contar documentos por cada plantilla mapeada
        foreach ($plantillas as $p) {
            // Mapear codigo de plantilla a tipo_documento
            $tipoDoc = $this->mapearPlantillaATipoDocumento($p['codigo_plantilla']);
            if ($tipoDoc) {
                $total += $this->db->table('tbl_documentos_sst')
                    ->where('id_cliente', $idCliente)
                    ->where('tipo_documento', $tipoDoc)
                    ->whereIn('estado', ['aprobado', 'firmado'])
                    ->countAllResults();
            }
        }

        // Contar recursivamente en subcarpetas
        $subcarpetas = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre', $idCarpeta)
            ->where('visible', 1)
            ->get()
            ->getResultArray();

        foreach ($subcarpetas as $sub) {
            $total += $this->contarDocumentosEnCarpeta($sub['id_carpeta'], $idCliente);
        }

        return $total;
    }

    /**
     * Obtiene documentos de una carpeta especifica
     */
    protected function obtenerDocumentosCarpeta(int $idCarpeta, int $idCliente): array
    {
        $carpeta = $this->db->table('tbl_doc_carpetas')
            ->where('id_carpeta', $idCarpeta)
            ->get()
            ->getRowArray();

        $codigoCarpeta = $carpeta['codigo'] ?? '';

        // Buscar plantillas mapeadas
        $plantillas = $this->db->table('tbl_doc_plantilla_carpeta')
            ->where('codigo_carpeta', $codigoCarpeta)
            ->get()
            ->getResultArray();

        $documentos = [];

        foreach ($plantillas as $p) {
            $tipoDoc = $this->mapearPlantillaATipoDocumento($p['codigo_plantilla']);
            if ($tipoDoc) {
                $docs = $this->db->table('tbl_documentos_sst')
                    ->where('id_cliente', $idCliente)
                    ->where('tipo_documento', $tipoDoc)
                    ->whereIn('estado', ['aprobado', 'firmado'])
                    ->orderBy('anio', 'DESC')
                    ->get()
                    ->getResultArray();

                foreach ($docs as $d) {
                    $documentos[] = $d;
                }
            }
        }

        return $documentos;
    }

    /**
     * Mapea codigo de plantilla a tipo_documento
     */
    protected function mapearPlantillaATipoDocumento(string $codigoPlantilla): ?string
    {
        $mapa = [
            'PRG-CAP' => 'programa_capacitacion',
            'ASG-RES' => 'asignacion_responsable_sgsst',
            // 1.1.2 Responsabilidades en el SG-SST (3 documentos)
            // Nota: Vigia/Delegado SST ahora esta combinado en responsabilidades_rep_legal_sgsst
            'RES-REP' => 'responsabilidades_rep_legal_sgsst',
            'RES-SST' => 'responsabilidades_responsable_sgsst',
            'RES-TRA' => 'responsabilidades_trabajadores_sgsst',
        ];

        return $mapa[$codigoPlantilla] ?? null;
    }

    /**
     * Obtiene la ruta de navegacion (breadcrumb) de una carpeta
     */
    protected function obtenerRutaCarpeta(int $idCarpeta): array
    {
        $ruta = [];
        $actual = $idCarpeta;

        while ($actual) {
            $carpeta = $this->db->table('tbl_doc_carpetas')
                ->where('id_carpeta', $actual)
                ->get()
                ->getRowArray();

            if ($carpeta) {
                array_unshift($ruta, $carpeta);
                $actual = $carpeta['id_carpeta_padre'];
            } else {
                break;
            }
        }

        return $ruta;
    }
}
