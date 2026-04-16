<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * IPEVR GTC 45 — Exporta matriz a XLSX replicando FT-SST-035.
 */
class IpevrExportXlsx
{
    /**
     * @param array $matriz     fila de tbl_ipevr_matriz
     * @param array $cliente    fila de tbl_clientes
     * @param array $filas      filas enriquecidas (con codigos ya resueltos)
     * @param array $catalogo   bundleFrontend() de Gtc45CatalogoModel
     * @param array $maestros   maestros cliente
     * @return string  path al archivo temporal generado
     */
    public function generar(array $matriz, array $cliente, array $filas, array $catalogo, array $maestros): string
    {
        $sp = new Spreadsheet();
        $sp->getProperties()
            ->setCreator('Enterprise SST')
            ->setTitle('Matriz IPEVR GTC 45 - ' . ($cliente['nombre_cliente'] ?? ''))
            ->setSubject('IPEVR v' . $matriz['version']);

        $this->hojaMatriz($sp->getActiveSheet(), $matriz, $cliente, $filas, $catalogo, $maestros);

        $hojaTablas = $sp->createSheet();
        $this->hojaTablasEvaluacion($hojaTablas, $catalogo);

        $hojaInstr = $sp->createSheet();
        $this->hojaInstructivo($hojaInstr);

        $tmp = tempnam(sys_get_temp_dir(), 'ipevr_') . '.xlsx';
        (new Xlsx($sp))->save($tmp);
        return $tmp;
    }

    protected function hojaMatriz($sheet, array $matriz, array $cliente, array $filas, array $catalogo, array $maestros): void
    {
        $sheet->setTitle('Matriz IPEVR');

        // Encabezado
        $sheet->setCellValue('A1', 'MATRIZ DE IDENTIFICACION DE PELIGROS, EVALUACION Y VALORACION DE RIESGOS (IPEVR) - GTC 45');
        $sheet->mergeCells('A1:AC1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1C2437');
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        $sheet->setCellValue('A2', 'Cliente: ' . ($cliente['nombre_cliente'] ?? ''));
        $sheet->setCellValue('F2', 'Version: ' . $matriz['version']);
        $sheet->setCellValue('J2', 'Estado: ' . $matriz['estado']);
        $sheet->setCellValue('N2', 'Fecha: ' . ($matriz['fecha_creacion'] ?? date('Y-m-d')));

        // Cabecera de columnas (fila 5)
        $cols = ['Proceso','Actividad','Tarea','Zona','Rut.','Cargos','N°','Descripcion','Clasif.','Efectos','Fuente','Medio','Individuo','ND','NE','NP','Interp.NP','NC','NR','Nivel','Aceptab.','Peor cons.','Req. legal','Elim.','Sust.','Ing.','Admin.','EPP'];
        foreach ($cols as $i => $c) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '5', $c);
        }
        $sheet->getStyle('A5:AB5')->getFont()->setBold(true);
        $sheet->getStyle('A5:AB5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BD9751');
        $sheet->getStyle('A5:AB5')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:AB5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Maps resolvers
        $mapProc = array_column($maestros['procesos'] ?? [], 'nombre_proceso', 'id');
        $mapZona = array_column($maestros['zonas'] ?? [], 'nombre_zona', 'id');
        $mapTarea = array_column($maestros['tareas'] ?? [], 'nombre_tarea', 'id');
        $mapClasif = array_column($catalogo['clasificaciones'] ?? [], 'nombre', 'id');
        $mapND = array_column($catalogo['nd'] ?? [], null, 'id');
        $mapNE = array_column($catalogo['ne'] ?? [], null, 'id');
        $mapNC = array_column($catalogo['nc'] ?? [], null, 'id');
        $mapNP = array_column($catalogo['np'] ?? [], null, 'id');
        $mapNR = array_column($catalogo['nr'] ?? [], null, 'id');

        $r = 6;
        foreach ($filas as $f) {
            $proc = $f['proceso_texto'] ?: ($mapProc[$f['id_proceso']] ?? '');
            $zona = $f['zona_texto'] ?: ($mapZona[$f['id_zona']] ?? '');
            $tarea = $f['tarea_texto'] ?: ($mapTarea[$f['id_tarea']] ?? '');
            $clasif = $mapClasif[$f['id_clasificacion']] ?? '';
            $nd = $mapND[$f['id_nd']] ?? null;
            $ne = $mapNE[$f['id_ne']] ?? null;
            $nc = $mapNC[$f['id_nc']] ?? null;
            $np = $mapNP[$f['id_np']] ?? null;
            $nr = $mapNR[$f['id_nivel_riesgo']] ?? null;
            $cargos = [];
            if (!empty($f['cargos_expuestos'])) {
                $t = json_decode($f['cargos_expuestos'], true);
                if (is_array($t)) $cargos = $t;
            }

            $row = [
                $proc, $f['actividad'] ?? '', $tarea, $zona,
                ($f['rutinaria'] ? 'Si' : 'No'),
                implode(', ', $cargos),
                (int)$f['num_expuestos'],
                $f['descripcion_peligro'] ?? '',
                $clasif,
                $f['efectos_posibles'] ?? '',
                $f['control_fuente'] ?? '',
                $f['control_medio'] ?? '',
                $f['control_individuo'] ?? '',
                $nd ? $nd['codigo'] : '',
                $ne ? $ne['codigo'] : '',
                $f['np'] ?? '',
                $np ? $np['nombre'] : '',
                $nc ? $nc['codigo'] : '',
                $f['nr'] ?? '',
                $nr ? $nr['nombre'] : '',
                $f['aceptabilidad'] ?? '',
                $f['peor_consecuencia'] ?? '',
                $f['requisito_legal'] ?? '',
                $f['medida_eliminacion'] ?? '',
                $f['medida_sustitucion'] ?? '',
                $f['medida_ingenieria'] ?? '',
                $f['medida_administrativa'] ?? '',
                $f['medida_epp'] ?? '',
            ];
            foreach ($row as $i => $v) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $r, $v);
            }
            // Color del nivel de riesgo (columna 20 = T)
            if ($nr && !empty($nr['color_hex'])) {
                $hex = ltrim($nr['color_hex'], '#');
                $cellNivel = Coordinate::stringFromColumnIndex(20) . $r;
                $sheet->getStyle($cellNivel)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($hex);
                $sheet->getStyle($cellNivel)->getFont()->getColor()->setRGB('FFFFFF');
            }
            $r++;
        }

        // Bordes en toda la tabla
        if ($r > 6) {
            $range = 'A5:AB' . ($r - 1);
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($range)->getAlignment()->setWrapText(true);
        }
        for ($c = 1; $c <= 28; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(18);
        }
    }

    protected function hojaTablasEvaluacion($sheet, array $catalogo): void
    {
        $sheet->setTitle('Tablas Evaluacion');
        $r = 1;
        $sheet->setCellValue('A' . $r, 'TABLAS DE EVALUACION DEL RIESGO GTC 45');
        $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(14);
        $r += 2;

        $secciones = [
            ['Nivel de Deficiencia (ND)', $catalogo['nd'], ['codigo','nombre','valor','descripcion']],
            ['Nivel de Exposicion (NE)',  $catalogo['ne'], ['codigo','nombre','valor','descripcion']],
            ['Nivel de Consecuencia (NC)', $catalogo['nc'], ['codigo','nombre','valor','danos_personales']],
            ['Nivel de Probabilidad (NP)', $catalogo['np'], ['codigo','nombre','rango_min','rango_max','descripcion']],
            ['Nivel de Riesgo (NR)', $catalogo['nr'], ['codigo','nombre','rango_min','rango_max','significado','aceptabilidad']],
        ];
        foreach ($secciones as [$titulo, $datos, $cols]) {
            $sheet->setCellValue('A' . $r, $titulo);
            $sheet->getStyle('A' . $r)->getFont()->setBold(true);
            $r++;
            foreach ($cols as $i => $c) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $r, strtoupper($c));
            }
            $sheet->getStyle('A' . $r . ':' . chr(64 + count($cols)) . $r)->getFont()->setBold(true);
            $r++;
            foreach ($datos as $fila) {
                foreach ($cols as $i => $c) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $r, $fila[$c] ?? '');
                }
                $r++;
            }
            $r++;
        }
        for ($c = 1; $c <= 6; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    protected function hojaInstructivo($sheet): void
    {
        $sheet->setTitle('Instructivo');
        $lineas = [
            'INSTRUCTIVO - MATRIZ IPEVR GTC 45',
            '',
            '1. PROCESO / ACTIVIDAD / TAREA',
            '  - Proceso: clasificar el tipo de proceso (estrategico, misional, apoyo).',
            '  - Zona o lugar: sitio donde se realiza.',
            '  - Actividad: tipo de actividad a realizar.',
            '  - Tarea: tarea especifica.',
            '  - Rutinaria: indica si la actividad es rutinaria (Si/No).',
            '',
            '2. IDENTIFICACION DE PELIGROS',
            '  - Descripcion: peligros a los que esta expuesto el trabajador.',
            '  - Clasificacion: biologico, fisico, quimico, psicosocial, biomecanico, condiciones de seguridad o fenomenos naturales.',
            '  - Efectos posibles: efectos en la salud del individuo o seguridad de las instalaciones.',
            '',
            '3. CONTROLES EXISTENTES',
            '  - Fuente, Medio, Individuo: controles actuales implementados.',
            '',
            '4. EVALUACION DEL RIESGO',
            '  - ND: MA=10, A=6, M=2, B=0',
            '  - NE: EC=4, EF=3, EO=2, EE=1',
            '  - NP = ND x NE (calculado automaticamente)',
            '  - NC: M=100, MG=60, G=25, L=10',
            '  - NR = NP x NC (calculado automaticamente)',
            '  - Nivel de riesgo I-IV segun rangos de NR.',
            '',
            '5. CRITERIOS',
            '  - Peor consecuencia, N° de expuestos, Requisito legal aplicable.',
            '',
            '6. MEDIDAS DE INTERVENCION',
            '  - Eliminacion, Sustitucion, Control de ingenieria, Controles administrativos, EPP.',
        ];
        foreach ($lineas as $i => $l) {
            $sheet->setCellValue('A' . ($i + 1), $l);
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getColumnDimension('A')->setWidth(100);
    }
}
