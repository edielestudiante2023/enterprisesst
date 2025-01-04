<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailReportModel extends Model
{
    protected $table         = 'detail_report';
    protected $primaryKey    = 'id_detailreport';
    protected $allowedFields = ['detail_report'];
    protected $useTimestamps = false; // Cambia a true si añades campos de timestamp
}
