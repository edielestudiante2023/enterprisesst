<?php
namespace App\Models;

use CodeIgniter\Model;

class PtaclienteModel extends Model
{
    protected $table = 'tbl_pta_cliente';  // Nombre de la tabla
    protected $primaryKey = 'id_ptacliente';  // Llave primaria
    protected $allowedFields = [
        'id_cliente',
        'tipo_servicio',  // Reemplazo de id_plandetrabajo
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
        'tipo_servicio' => 'permit_empty|string|max_length[255]',

        'phva_plandetrabajo' => 'required|string|max_length[255]',
        'numeral_plandetrabajo' => 'required|string|max_length[255]',
        'actividad_plandetrabajo' => 'required|string',
        'responsable_sugerido_plandetrabajo' => 'permit_empty|string|max_length[255]',
        'fecha_propuesta' => 'permit_empty|valid_date',
        'fecha_cierre' => 'permit_empty|valid_date',  // Ahora permite NULL
        'estado_actividad' => 'required|string|in_list[ABIERTA,CERRADA,GESTIONANDO]',
        'porcentaje_avance' => 'required|decimal',
    ];
    

    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El campo id_cliente es obligatorio.',
            'integer' => 'El campo id_cliente debe ser un número entero.'
        ],
        
    ];

    protected $skipValidation = false;
}
