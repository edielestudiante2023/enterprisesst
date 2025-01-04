<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportTypeModel extends Model
{
    protected $table = 'report_type_table'; // Nombre de la tabla
    protected $primaryKey = 'id_report_type'; // Llave primaria

    protected $allowedFields = [
        'report_type' // Los campos que pueden ser llenados
    ];

    protected $useTimestamps = false; // Si tienes campos de fecha como created_at y updated_at, cámbialo a true
}
