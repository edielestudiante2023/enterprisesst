<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * IPEVR GTC 45 — Exporta matriz a PDF landscape.
 */
class IpevrExportPdf
{
    public function generar(array $matriz, array $cliente, array $filas, array $catalogo, array $maestros): string
    {
        $mapProc = array_column($maestros['procesos'] ?? [], 'nombre_proceso', 'id');
        $mapZona = array_column($maestros['zonas'] ?? [], 'nombre_zona', 'id');
        $mapTarea = array_column($maestros['tareas'] ?? [], 'nombre_tarea', 'id');
        $mapClasif = array_column($catalogo['clasificaciones'] ?? [], 'nombre', 'id');
        $mapND = array_column($catalogo['nd'] ?? [], null, 'id');
        $mapNE = array_column($catalogo['ne'] ?? [], null, 'id');
        $mapNC = array_column($catalogo['nc'] ?? [], null, 'id');
        $mapNR = array_column($catalogo['nr'] ?? [], null, 'id');

        $html = '<html><head><meta charset="utf-8"><style>
            @page { margin: 15mm; }
            body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7pt; }
            h1 { font-size: 12pt; margin: 0 0 4mm; color: #1c2437; }
            .meta { font-size: 8pt; margin-bottom: 4mm; color: #374151; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #1c2437; color: #fff; padding: 3px; text-align: center; font-size: 7pt; }
            td { border: 1px solid #d1d5db; padding: 3px; vertical-align: top; }
            .nr-cell { color: #fff; text-align: center; font-weight: bold; }
        </style></head><body>';

        $html .= '<h1>Matriz IPEVR GTC 45</h1>';
        $html .= '<div class="meta"><strong>' . htmlspecialchars($cliente['nombre_cliente'] ?? '') . '</strong>';
        $html .= ' · Version ' . htmlspecialchars($matriz['version']);
        $html .= ' · Estado: ' . htmlspecialchars($matriz['estado']);
        $html .= ' · Fecha: ' . htmlspecialchars($matriz['fecha_creacion'] ?? date('Y-m-d'));
        $html .= '</div>';

        $html .= '<table><thead><tr>
            <th>Proceso</th><th>Actividad</th><th>Tarea</th><th>Peligro</th><th>Clasif.</th>
            <th>Efectos</th><th>ND</th><th>NE</th><th>NP</th><th>NC</th><th>NR</th><th>Nivel</th><th>Aceptabilidad</th>
        </tr></thead><tbody>';

        foreach ($filas as $f) {
            $proc = $f['proceso_texto'] ?: ($mapProc[$f['id_proceso']] ?? '');
            $tarea = $f['tarea_texto'] ?: ($mapTarea[$f['id_tarea']] ?? '');
            $nd = $mapND[$f['id_nd']] ?? null;
            $ne = $mapNE[$f['id_ne']] ?? null;
            $nc = $mapNC[$f['id_nc']] ?? null;
            $nr = $mapNR[$f['id_nivel_riesgo']] ?? null;
            $clasif = $mapClasif[$f['id_clasificacion']] ?? '';
            $color = $nr ? htmlspecialchars($nr['color_hex']) : '#9ca3af';

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($proc) . '</td>';
            $html .= '<td>' . htmlspecialchars($f['actividad'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($tarea) . '</td>';
            $html .= '<td>' . htmlspecialchars($f['descripcion_peligro'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($clasif) . '</td>';
            $html .= '<td>' . htmlspecialchars($f['efectos_posibles'] ?? '') . '</td>';
            $html .= '<td>' . ($nd['codigo'] ?? '—') . '</td>';
            $html .= '<td>' . ($ne['codigo'] ?? '—') . '</td>';
            $html .= '<td>' . htmlspecialchars((string)($f['np'] ?? '')) . '</td>';
            $html .= '<td>' . ($nc['codigo'] ?? '—') . '</td>';
            $html .= '<td>' . htmlspecialchars((string)($f['nr'] ?? '')) . '</td>';
            $html .= '<td class="nr-cell" style="background:' . $color . '">' . ($nr['nombre'] ?? '—') . '</td>';
            $html .= '<td>' . htmlspecialchars($f['aceptabilidad'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        $tmp = tempnam(sys_get_temp_dir(), 'ipevr_') . '.pdf';
        file_put_contents($tmp, $dompdf->output());
        return $tmp;
    }
}
