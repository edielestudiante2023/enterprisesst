<?php
namespace App\Models;

use CodeIgniter\Model;

class PtaclienteModel extends Model
{
    protected $table = 'tbl_pta_cliente';  // Nombre de la tabla
    protected $primaryKey = 'id_ptacliente';  // Llave primaria
    protected $allowedFields = [
        'id_cliente',
        'id_plandetrabajo',
        'phva_plandetrabajo',
        'numeral_plandetrabajo',
        'actividad_plandetrabajo',
        'responsable_sugerido_plandetrabajo',
        'fecha_propuesta',
        'fecha_cierre',
        'responsable_definido_paralaactividad',
        'estado_actividad',
        'porcentaje_avance',
        'semana',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id_cliente' => 'required|integer',
        'id_plandetrabajo' => 'required|integer',
        'phva_plandetrabajo' => 'required|string',
        'numeral_plandetrabajo' => 'required|string',
        'actividad_plandetrabajo' => 'required|integer',  // Cambiado a integer si es un ID
        'responsable_sugerido_plandetrabajo' => 'string',
        'fecha_propuesta' => 'valid_date',
        'fecha_cierre' => 'valid_date',
        'estado_actividad' => 'required|string',
        'porcentaje_avance' => 'required|decimal',
    ];

    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El campo id_cliente es obligatorio.',
            'integer' => 'El campo id_cliente debe ser un número entero.'
        ],
        // Agrega mensajes para otros campos según sea necesario
    ];

    protected $skipValidation = true; // Cambiado para depurar
}
