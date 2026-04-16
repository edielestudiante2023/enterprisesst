<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class MatrizModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_matrices'; // Nombre de la tabla
    protected $primaryKey = 'id_matriz'; // Clave primaria
    protected $allowedFields = [
        'tipo', 
        'descripcion', 
        'observaciones', 
        'enlace', 
        'id_cliente', 
        'created_at', 
        'updated_at'
    ]; // Campos permitidos para operaciones de inserción/actualización
    protected $useTimestamps = true; // Habilitar gestión automática de created_at y updated_at
}
