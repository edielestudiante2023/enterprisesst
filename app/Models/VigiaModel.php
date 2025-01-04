<?php

namespace App\Models;

use CodeIgniter\Model;

class VigiaModel extends Model
{
    protected $table      = 'tbl_vigias';  // Nombre de la tabla
    protected $primaryKey = 'id_vigia';    // Clave primaria

    // Campos permitidos para manipulación
    protected $allowedFields = [
        'nombre_vigia', 
        'cedula_vigia', 
        'periodo_texto', 
        'firma_vigia', 
        'observaciones', 
        'id_cliente'
    ];

    // Habilitar los campos de timestamps
    protected $useTimestamps = true;

    // Nombres de los campos de timestamp (opcional, solo si se llaman diferente)
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
