<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_carpeta_reporte_vinculo: relacion N:M entre carpetas de
 * documentacion (tbl_doc_carpetas) y reportes del reportList (tbl_reporte).
 *
 * Cada fila representa un "vinculo" — el consultor decidio mostrar un reporte
 * existente como referencia dentro de una carpeta SIN duplicar el archivo.
 */
class CarpetaReporteVinculoModel extends Model
{
    protected $table = 'tbl_carpeta_reporte_vinculo';
    protected $primaryKey = 'id_vinculo';
    protected $returnType = 'array';
    protected $useTimestamps = false; // Solo created_at

    protected $allowedFields = [
        'id_carpeta', 'id_reporte', 'id_cliente',
        'observacion', 'created_by', 'created_at',
    ];

    /**
     * Devuelve los reportes vinculados a una carpeta con el detalle del reporte.
     * Hace LEFT JOIN para soportar reportes que se hayan borrado (los filtra fuera).
     */
    public function getByCarpeta(int $idCarpeta): array
    {
        return $this->select(
                'tbl_carpeta_reporte_vinculo.*,
                 r.titulo_reporte, r.enlace, r.estado AS estado_reporte,
                 r.id_report_type, r.id_detailreport, r.created_at AS reporte_created_at,
                 rt.report_type AS report_type_nombre,
                 dr.detail_report AS detail_report_nombre'
            )
            ->join('tbl_reporte r', 'r.id_reporte = tbl_carpeta_reporte_vinculo.id_reporte', 'inner')
            ->join('report_type_table rt', 'rt.id_report_type = r.id_report_type', 'left')
            ->join('detail_report dr', 'dr.id_detailreport = r.id_detailreport', 'left')
            ->where('tbl_carpeta_reporte_vinculo.id_carpeta', $idCarpeta)
            ->orderBy('tbl_carpeta_reporte_vinculo.created_at', 'DESC')
            ->findAll();
    }

    /**
     * True si ya existe vinculo entre esa carpeta y ese reporte.
     */
    public function existeVinculo(int $idCarpeta, int $idReporte): bool
    {
        return $this->where('id_carpeta', $idCarpeta)
                    ->where('id_reporte', $idReporte)
                    ->countAllResults() > 0;
    }
}
