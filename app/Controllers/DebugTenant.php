<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\TenantFilter;
use App\Models\ActaCapacitacionModel;
use Config\Database;

/**
 * Endpoint TEMPORAL de diagnostico para entender por que la lista de actas
 * de capacitacion solo muestra 1 cuando deberia mostrar 4.
 *
 * Acceder logueado a: /debug-tenant
 * BORRAR despues de usar.
 */
class DebugTenant extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return 'No estas logueado. Inicia sesion primero.';
        }

        $session = session();
        $db = Database::connect();

        $out = [];
        $out[] = '=== SESION ===';
        $out[] = 'isLoggedIn: ' . var_export($session->get('isLoggedIn'), true);
        $out[] = 'id_usuario: ' . var_export($session->get('id_usuario'), true);
        $out[] = 'email: ' . var_export($session->get('email'), true);
        $out[] = 'tipo_usuario: ' . var_export($session->get('tipo_usuario'), true);
        $out[] = 'role: ' . var_export($session->get('role'), true);
        $out[] = 'id_entidad: ' . var_export($session->get('id_entidad'), true);
        $out[] = 'id_consultor: ' . var_export($session->get('id_consultor'), true);
        $out[] = 'id_cliente: ' . var_export($session->get('id_cliente'), true);
        $out[] = 'id_empresa_consultora: ' . var_export($session->get('id_empresa_consultora'), true);
        $out[] = 'is_superadmin: ' . var_export($session->get('is_superadmin'), true);

        $out[] = '';
        $out[] = '=== TenantFilter ===';
        $out[] = 'getEmpresaId(): ' . var_export(TenantFilter::getEmpresaId(), true);
        $out[] = 'isSuperAdmin(): ' . var_export(TenantFilter::isSuperAdmin(), true);
        $clientIds = TenantFilter::getMyClientIds();
        $out[] = 'getMyClientIds(): ' . (is_array($clientIds) ? '[' . implode(',', $clientIds) . ']' : var_export($clientIds, true));

        $out[] = '';
        $out[] = '=== Query directa: TODAS las actas en BD ===';
        $rows = $db->table('tbl_acta_capacitacion')
            ->select('id, id_cliente, id_consultor, tema, estado')
            ->orderBy('id')
            ->get()->getResultArray();
        foreach ($rows as $r) {
            $out[] = sprintf("  id=%s | id_cliente=%s | id_consultor=%s | estado=%s | tema=%s",
                $r['id'], $r['id_cliente'], $r['id_consultor'], $r['estado'],
                substr($r['tema'] ?? '', 0, 50));
        }

        $out[] = '';
        $out[] = '=== Query del controller list() (con tenant filter) ===';
        $actaModel = new ActaCapacitacionModel();
        $actas = $actaModel
            ->select('tbl_acta_capacitacion.id, tbl_acta_capacitacion.id_cliente, tbl_acta_capacitacion.estado, tbl_acta_capacitacion.tema, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();
        $out[] = 'TOTAL FILAS: ' . count($actas);
        foreach ($actas as $a) {
            $out[] = sprintf("  id=%s | cliente=%s | estado=%s | tema=%s",
                $a['id'], $a['nombre_cliente'] ?? '?', $a['estado'], substr($a['tema'] ?? '', 0, 50));
        }

        $out[] = '';
        $out[] = '=== Ultima query SQL generada ===';
        $out[] = $db->getLastQuery()->getQuery();

        // Ejecutar el flujo COMPLETO del controller list() — incluyendo el loop
        // que enriquece con total_asistentes / total_firmados.
        $out[] = '';
        $out[] = '=== Flujo COMPLETO de list() (con foreach enriquecido) ===';
        $actaModel2 = new ActaCapacitacionModel();
        $asistenteModel = new \App\Models\ActaCapacitacionAsistenteModel();
        $actasFull = $actaModel2
            ->select('tbl_acta_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();
        $out[] = 'Despues de findAll: ' . count($actasFull) . ' filas';
        try {
            foreach ($actasFull as &$a) {
                $a['total_asistentes'] = $asistenteModel
                    ->where('id_acta_capacitacion', $a['id'])->countAllResults(false);
                $a['total_firmados'] = $asistenteModel
                    ->where('id_acta_capacitacion', $a['id'])
                    ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
            }
            unset($a);
            $out[] = 'Despues del foreach: ' . count($actasFull) . ' filas';
            foreach ($actasFull as $a) {
                $out[] = sprintf("  id=%s | cliente=%s | total_asist=%s | firmados=%s",
                    $a['id'], $a['nombre_cliente'] ?? '?',
                    $a['total_asistentes'] ?? '?', $a['total_firmados'] ?? '?');
            }
        } catch (\Throwable $e) {
            $out[] = 'EXCEPCION en foreach: ' . $e->getMessage();
        }

        // Renderizar la vista list.php con los datos y comparar
        $out[] = '';
        $out[] = '=== Render de view list.php (cuantas cards genera) ===';
        $rendered = view('inspecciones/acta_capacitacion/list', ['actas' => $actasFull]);
        $cardCount = substr_count($rendered, 'card-inspeccion');
        $out[] = "Tarjetas card-inspeccion en HTML: $cardCount";
        $out[] = "Tamano del HTML rendered: " . strlen($rendered) . " bytes";

        return '<pre style="font-family:monospace;font-size:13px;background:#f3f4f6;padding:20px;">'
            . htmlspecialchars(implode("\n", $out)) . '</pre>';
    }
}
