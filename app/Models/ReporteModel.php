<?php

namespace App\Models;

use CodeIgniter\Model;

class ReporteModel extends Model
{
    protected $table = 'tbl_reporte';
    protected $primaryKey = 'id_reporte';
    protected $allowedFields = [
        'titulo_reporte', 'id_detailreport', 'enlace', 'estado', 
        'observaciones', 'id_cliente', 'created_at', 'updated_at', 'id_report_type'
    ];
    protected $useTimestamps = true;
}
