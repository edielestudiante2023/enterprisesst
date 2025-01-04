<?php

namespace App\Models;

use CodeIgniter\Model;

class CapacitacionModel extends Model
{
    // Definimos la tabla
    protected $table = 'capacitaciones_sst'; 

    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_capacitacion';

    // Definimos qué columnas se pueden llenar con datos (permitimos inserción masiva)
    protected $allowedFields = [
        'capacitacion',
        'objetivo_capacitacion',
        'observaciones'
    ];

    // Opcional: Puedes agregar validaciones aquí si quieres asegurarte de que los datos sean válidos
    protected $validationRules = [
        'capacitacion' => 'required|max_length[255]',
        'objetivo_capacitacion' => 'required',
    ];

    // Definimos si queremos que CodeIgniter maneje automáticamente las fechas de creación y actualización
    protected $useTimestamps = false; // Si no necesitas timestamps automáticos, puedes dejarlo como falso

    // Opcional: Si quisieras timestamps automáticos para saber cuándo se crearon o actualizaron las capacitaciones
    // protected $createdField = 'created_at';
    // protected $updatedField = 'updated_at';
}
