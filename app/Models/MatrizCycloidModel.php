<?php

namespace App\Models;

use CodeIgniter\Model;

class MatrizCycloidModel extends Model
{
    protected $table = 'tbl_matricescycloid'; // Nombre de la tabla
    protected $primaryKey = 'id_matrizcycloid'; // Clave primaria

    // Campos permitidos para operaciones CRUD
    protected $allowedFields = [
        'titulo_matriz', 'Tipo_documento', 'enlace', 'observaciones', 'created_at', 'updated_at'
    ];

    // Utilizar los timestamps automáticos de CodeIgniter 4
    protected $useTimestamps = true;
    protected $createdField  = 'created_at'; // Campo para la fecha de creación
    protected $updatedField  = 'updated_at'; // Campo para la fecha de actualización

    // Si es necesario, puedes desactivar la protección de datos para permitir inserciones masivas sin limitaciones
    protected $useSoftDeletes = false; // Si deseas usar borrado suave, cámbialo a true

    // Validaciones (opcional, si deseas agregar validaciones)
    protected $validationRules = [
        'titulo_matriz'   => 'required|min_length[3]|max_length[255]',
        'Tipo_documento'  => 'required|max_length[255]',
        'enlace'          => 'required|valid_url',
    ];

    protected $validationMessages = [
        'titulo_matriz' => [
            'required' => 'El título de la matriz es obligatorio',
            'min_length' => 'El título debe tener al menos 3 caracteres',
        ],
        'Tipo_documento' => [
            'required' => 'El tipo de documento es obligatorio',
        ],
        'enlace' => [
            'required' => 'El enlace es obligatorio',
            'valid_url' => 'El enlace debe ser una URL válida',
        ],
    ];

    // Opción de auto-validación
    protected $skipValidation = false;
}
