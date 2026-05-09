<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory;

/**
 * Smoke test temporal para verificar las clases InformeTrimestralCopasst e InformeAnualCopasst.
 * Ejecutar: php spark smoke:informes-copasst [id_cliente] [trimestre]
 *
 * Si no se pasa id_cliente, busca uno automaticamente con COPASST activo.
 * Si no se pasa trimestre, usa 2.
 *
 * Este archivo es transitorio — se puede borrar cuando el flujo este validado.
 */
class SmokeInformesCopasst extends BaseCommand
{
    protected $group       = 'Smoke';
    protected $name        = 'smoke:informes-copasst';
    protected $description = 'Smoke test de las clases InformeTrimestralCopasst e InformeAnualCopasst.';
    protected $usage       = 'smoke:informes-copasst [id_cliente] [trimestre]';

    public function run(array $params)
    {
        $idClienteParam = $params[0] ?? null;
        $trimestre      = (int) ($params[1] ?? 2);

        $db = \Config\Database::connect();

        if ($idClienteParam) {
            $idCliente = (int) $idClienteParam;
        } else {
            $row = $db->table('tbl_comites c')
                ->select('c.id_cliente')
                ->join('tbl_tipos_comite t', 't.id_tipo = c.id_tipo')
                ->where('t.codigo', 'COPASST')
                ->where('c.estado', 'activo')
                ->orderBy('c.id_comite', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
            if (!$row) {
                CLI::error('No hay COPASST activo en BD local.');
                return;
            }
            $idCliente = (int) $row['id_cliente'];
        }

        CLI::write("Cliente de prueba: id={$idCliente}, trimestre={$trimestre}", 'yellow');

        $cliente = $db->table('tbl_clientes')->where('id_cliente', $idCliente)->get()->getRowArray() ?? [];
        $cliente['id_cliente'] = $idCliente;

        $contexto = (new \App\Models\ClienteContextoSstModel())->getByCliente($idCliente) ?? [];
        $contexto['anio']      = (int) date('Y');
        $contexto['trimestre'] = $trimestre;

        // Test 1: trimestral
        CLI::write("\n=== TRIMESTRAL ===", 'cyan');
        try {
            $h = DocumentoSSTFactory::crear('informe_trimestral_copasst');
            CLI::write('clase: ' . get_class($h));
            CLI::write('tipo: ' . $h->getTipoDocumento());
            CLI::write('nombre: ' . $h->getNombre());
            CLI::write('estandar: ' . $h->getEstandar());
            CLI::write('secciones: ' . count($h->getSecciones()));
            $ctx = $h->getContextoBase($cliente, $contexto);
            CLI::write('--- contexto trimestral (primeros 3000 chars) ---');
            CLI::write(mb_substr($ctx, 0, 3000));
            CLI::write('--- (longitud total: ' . mb_strlen($ctx) . ' chars) ---', 'light_gray');
        } catch (\Throwable $e) {
            CLI::error('FAIL trimestral: ' . $e->getMessage());
            CLI::write($e->getTraceAsString());
        }

        // Test 2: anual
        CLI::write("\n=== ANUAL ===", 'cyan');
        try {
            $h2 = DocumentoSSTFactory::crear('informe_anual_copasst');
            CLI::write('clase: ' . get_class($h2));
            CLI::write('tipo: ' . $h2->getTipoDocumento());
            CLI::write('secciones: ' . count($h2->getSecciones()));
            $ctx2 = $h2->getContextoBase($cliente, $contexto);
            CLI::write('--- contexto anual (primeros 3000 chars) ---');
            CLI::write(mb_substr($ctx2, 0, 3000));
            CLI::write('--- (longitud total: ' . mb_strlen($ctx2) . ' chars) ---', 'light_gray');
        } catch (\Throwable $e) {
            CLI::error('FAIL anual: ' . $e->getMessage());
            CLI::write($e->getTraceAsString());
        }

        // Test COCOLAB (cliente con COCOLAB activo)
        CLI::write("\n=== COCOLAB ===", 'cyan');
        $rowCoc = $db->table('tbl_comites c')
            ->select('c.id_cliente')
            ->join('tbl_tipos_comite t', 't.id_tipo = c.id_tipo')
            ->where('t.codigo', 'COCOLAB')
            ->where('c.estado', 'activo')
            ->orderBy('c.id_comite', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
        if ($rowCoc) {
            $idClienteCoc = (int) $rowCoc['id_cliente'];
            $clienteCoc = $db->table('tbl_clientes')->where('id_cliente', $idClienteCoc)->get()->getRowArray() ?? [];
            $clienteCoc['id_cliente'] = $idClienteCoc;
            $contextoCoc = (new \App\Models\ClienteContextoSstModel())->getByCliente($idClienteCoc) ?? [];
            $contextoCoc['anio'] = (int) date('Y');
            $contextoCoc['trimestre'] = $trimestre;
            try {
                $hCT = DocumentoSSTFactory::crear('informe_trimestral_cocolab');
                CLI::write('clase: ' . get_class($hCT) . ' | secciones: ' . count($hCT->getSecciones()));
                $ctxCT = $hCT->getContextoBase($clienteCoc, $contextoCoc);
                CLI::write('--- contexto COCOLAB trimestral (primeros 1500 chars) ---');
                CLI::write(mb_substr($ctxCT, 0, 1500));
                CLI::write('--- (longitud total: ' . mb_strlen($ctxCT) . ' chars) ---', 'light_gray');

                $hCA = DocumentoSSTFactory::crear('informe_anual_cocolab');
                CLI::write('clase: ' . get_class($hCA) . ' | secciones: ' . count($hCA->getSecciones()));
                $ctxCA = $hCA->getContextoBase($clienteCoc, $contextoCoc);
                CLI::write('--- contexto COCOLAB anual (primeros 1500 chars) ---');
                CLI::write(mb_substr($ctxCA, 0, 1500));
                CLI::write('--- (longitud total: ' . mb_strlen($ctxCA) . ' chars) ---', 'light_gray');
            } catch (\Throwable $e) {
                CLI::error('FAIL COCOLAB: ' . $e->getMessage());
                CLI::write($e->getTraceAsString());
            }
        } else {
            CLI::write('(no hay COCOLAB activo en local — saltando)', 'yellow');
        }

        // Test 3: BD
        CLI::write("\n=== BD ===", 'cyan');
        foreach (['informe_trimestral_copasst', 'informe_anual_copasst', 'informe_trimestral_cocolab', 'informe_anual_cocolab'] as $t) {
            $r = $db->table('tbl_doc_tipo_configuracion')->where('tipo_documento', $t)->get()->getRowArray();
            if ($r) {
                $cnt = $db->table('tbl_doc_secciones_config')->where('id_tipo_config', $r['id_tipo_config'])->countAllResults();
                CLI::write("[OK] {$t}: id_tipo_config={$r['id_tipo_config']}, secciones={$cnt}, flujo={$r['flujo']}");
            } else {
                CLI::error("[FAIL] {$t} no esta en tbl_doc_tipo_configuracion");
            }
        }

        $r = $db->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'trimestre'")->getRowArray();
        CLI::write($r ? "[OK] columna trimestre presente: tipo={$r['Type']}, NULL={$r['Null']}" : '[FAIL] columna trimestre no existe');
    }
}
