<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Profesiograma — Exporta tabla cruzada a XLSX.
 *
 * Hoja 1: Tabla cruzada (cargos x examenes por momento)
 * Hoja 2: Catalogo de examenes con normativa
 */
class ProfesiogramaExportXlsx
{
    protected string $colorHeader = '1C2437';
    protected string $colorGold   = 'BD9751';
    protected string $colorIngreso  = 'DBEAFE';
    protected string $colorPeriodico = 'FEF3C7';
    protected string $colorRetiro   = 'FCE7F3';

    /**
     * @param array $cliente       fila de tbl_clientes
     * @param array $cargos        cargos del cliente (tbl_cargos_cliente)
     * @param array $asignaciones  todas las asignaciones con JOIN catalogo
     * @param array $catalogo      examenes del catalogo
     * @return string path al archivo temporal
     */
    public function generar(array $cliente, array $cargos, array $asignaciones, array $catalogo): string
    {
        $sp = new Spreadsheet();
        $sp->getProperties()
            ->setCreator('Enterprise SST')
            ->setTitle('Profesiograma - ' . ($cliente['nombre_cliente'] ?? ''))
            ->setSubject('Examenes Medicos Ocupacionales');

        $this->hojaProfesiograma($sp->getActiveSheet(), $cliente, $cargos, $asignaciones, $catalogo);

        $hojaCat = $sp->createSheet();
        $this->hojaCatalogo($hojaCat, $catalogo);

        $tmp = tempnam(sys_get_temp_dir(), 'prof_') . '.xlsx';
        (new Xlsx($sp))->save($tmp);
        return $tmp;
    }

    protected function hojaProfesiograma($sheet, array $cliente, array $cargos, array $asignaciones, array $catalogo): void
    {
        $sheet->setTitle('Profesiograma');

        // ── Indexar asignaciones: id_cargo => id_examen => momento => row ──
        $mapa = [];
        foreach ($asignaciones as $a) {
            $mapa[(int)$a['id_cargo']][(int)$a['id_examen']][$a['momento']] = $a;
        }

        // ── Encabezado empresa ──
        $totalCols = 3 + (count($catalogo) * 3); // cargo + ocupantes + proceso + (examen × 3 momentos)
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $sheet->setCellValue('A1', 'PROFESIOGRAMA - EXAMENES MEDICOS OCUPACIONALES');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorHeader);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        $sheet->setCellValue('A2', 'Cliente: ' . ($cliente['nombre_cliente'] ?? ''));
        $sheet->setCellValue('A3', 'Normativa: Resolucion 2346/2007, Decreto 1072/2015 Art 2.2.4.6.24');
        $sheet->setCellValue('F2', 'Fecha: ' . date('Y-m-d'));
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        // ── Fila 5: Headers de grupo (nombre examen, merge 3 cols) ──
        $col = 4; // Columna D en adelante
        foreach ($catalogo as $ex) {
            $colStart = Coordinate::stringFromColumnIndex($col);
            $colEnd   = Coordinate::stringFromColumnIndex($col + 2);
            $sheet->setCellValue($colStart . '5', $ex['nombre']);
            $sheet->mergeCells("{$colStart}5:{$colEnd}5");
            $sheet->getStyle("{$colStart}5:{$colEnd}5")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setTextRotation(90);
            $col += 3;
        }
        $lastDataCol = Coordinate::stringFromColumnIndex($col - 1);
        $sheet->getStyle("D5:{$lastDataCol}5")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("D5:{$lastDataCol}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorGold);
        $sheet->getStyle("D5:{$lastDataCol}5")->getFont()->getColor()->setRGB('FFFFFF');

        // ── Fila 6: Sub-headers (I, P, R por cada examen) ──
        $sheet->setCellValue('A5', 'Cargo');
        $sheet->setCellValue('B5', 'Ocupantes');
        $sheet->setCellValue('C5', 'Proceso');
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');

        $col = 4;
        foreach ($catalogo as $ex) {
            $c1 = Coordinate::stringFromColumnIndex($col);
            $c2 = Coordinate::stringFromColumnIndex($col + 1);
            $c3 = Coordinate::stringFromColumnIndex($col + 2);
            $sheet->setCellValue($c1 . '6', 'I');
            $sheet->setCellValue($c2 . '6', 'P');
            $sheet->setCellValue($c3 . '6', 'R');

            $sheet->getStyle($c1 . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorIngreso);
            $sheet->getStyle($c2 . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorPeriodico);
            $sheet->getStyle($c3 . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorRetiro);

            $col += 3;
        }
        $sheet->getStyle("A5:C6")->getFont()->setBold(true);
        $sheet->getStyle("A5:C6")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorHeader);
        $sheet->getStyle("A5:C6")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("D6:{$lastDataCol}6")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("D6:{$lastDataCol}6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ── Mapear procesos ──
        $db = \Config\Database::connect();
        $procesos = $db->table('tbl_procesos_cliente')->get()->getResultArray();
        $mapProc = array_column($procesos, 'nombre_proceso', 'id');

        // ── Filas de datos (fila 7+) ──
        $r = 7;
        foreach ($cargos as $cargo) {
            $idC = (int)$cargo['id'];
            $sheet->setCellValue('A' . $r, $cargo['nombre_cargo']);
            $sheet->setCellValue('B' . $r, (int)($cargo['num_ocupantes'] ?? 0));
            $sheet->setCellValue('C' . $r, $mapProc[$cargo['id_proceso'] ?? 0] ?? '');

            $col = 4;
            foreach ($catalogo as $ex) {
                $idEx = (int)$ex['id'];
                $c1 = Coordinate::stringFromColumnIndex($col);
                $c2 = Coordinate::stringFromColumnIndex($col + 1);
                $c3 = Coordinate::stringFromColumnIndex($col + 2);

                $ingreso   = isset($mapa[$idC][$idEx]['ingreso']);
                $periodico = isset($mapa[$idC][$idEx]['periodico']);
                $retiro    = isset($mapa[$idC][$idEx]['retiro']);

                $sheet->setCellValue($c1 . $r, $ingreso ? '✓' : '');
                $sheet->setCellValue($c2 . $r, $periodico ? '✓' : '');
                $sheet->setCellValue($c3 . $r, $retiro ? '✓' : '');

                // Colores suaves en celdas marcadas
                if ($ingreso) {
                    $sheet->getStyle($c1 . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
                }
                if ($periodico) {
                    $sheet->getStyle($c2 . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
                }
                if ($retiro) {
                    $sheet->getStyle($c3 . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
                }

                $col += 3;
            }

            // Zebra
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:C{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }

            $r++;
        }

        // ── Bordes y formato ──
        if ($r > 7) {
            $range = "A5:{$lastDataCol}" . ($r - 1);
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($range)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
        }

        // Anchos
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(20);
        for ($c = 4; $c < $col; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(4);
        }

        // Altura fila 5 (nombres rotados)
        $sheet->getRowDimension(5)->setRowHeight(120);
        // Alineacion cargo a la izquierda
        if ($r > 7) {
            $sheet->getStyle("A7:A" . ($r - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
    }

    protected function hojaCatalogo($sheet, array $catalogo): void
    {
        $sheet->setTitle('Catalogo Examenes');

        $sheet->setCellValue('A1', 'CATALOGO DE EXAMENES MEDICOS OCUPACIONALES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:F1');

        $headers = ['#', 'Examen', 'Tipo', 'Clasificaciones GTC45', 'Normativa', 'Aplica Retiro'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '3', $h);
        }
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getStyle('A3:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorGold);
        $sheet->getStyle('A3:F3')->getFont()->getColor()->setRGB('FFFFFF');

        $r = 4;
        foreach ($catalogo as $i => $ex) {
            $clasifs = json_decode($ex['clasificaciones_aplica'] ?? '[]', true) ?: [];
            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $ex['nombre']);
            $sheet->setCellValue('C' . $r, ucfirst($ex['tipo_examen']));
            $sheet->setCellValue('D' . $r, implode(', ', $clasifs));
            $sheet->setCellValue('E' . $r, $ex['normativa_referencia'] ?? '');
            $sheet->setCellValue('F' . $r, (int)$ex['aplica_retiro'] ? 'Si' : 'No');
            $r++;
        }

        if ($r > 4) {
            $range = "A3:F" . ($r - 1);
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($range)->getAlignment()->setWrapText(true);
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(12);
    }
}
