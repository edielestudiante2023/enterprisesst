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

        return '<pre style="font-family:monospace;font-size:13px;background:#f3f4f6;padding:20px;">'
            . htmlspecialchars(implode("\n", $out)) . '</pre>';
    }
}
