<?php

namespace App\Models;

use CodeIgniter\Model;

class PendientesModel extends Model
{
    protected $table = 'tbl_pendientes';  // Nombre de la tabla
    protected $primaryKey = 'id_pendientes';  // Llave primaria
    protected $allowedFields = [
        'id_cliente',
        'fecha_asignacion', // Nueva columna añadida
        'created_at',
        'responsable',
        'tarea_actividad',
        'fecha_cierre',
        'estado',
        'conteo_dias',
        'estado_avance',
        'evidencia_para_cerrarla'
    ];

    // Para que los campos de created_at y updated_at se actualicen automáticamente
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at'; // Si tienes un campo 'updated_at', sino lo puedes quitar

    // Validaciones
    protected $validationRules = [
        'id_cliente' => 'required|integer',
        'fecha_asignacion' => 'required|valid_date', // Validación para la nueva columna
        'responsable' => 'required|string',
        'tarea_actividad' => 'required|string',
        'fecha_cierre' => 'permit_empty|valid_date',
        'estado' => 'required|in_list[ABIERTA,CERRADA]',
        'conteo_dias' => 'required|integer',
        'estado_avance' => 'permit_empty|string',
        'evidencia_para_cerrarla' => 'permit_empty|string',
    ];

    // Mensajes de validación personalizados (opcional)
    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El campo id_cliente es obligatorio.',
            'integer' => 'El campo id_cliente debe ser un número entero.'
        ],
        'fecha_asignacion' => [
            'required' => 'El campo fecha de asignación es obligatorio.',
            'valid_date' => 'El campo fecha de asignación debe ser una fecha válida.'
        ],
        // Agrega mensajes para otros campos según sea necesario
    ];

    // Desactivar validación automática
    protected $skipValidation = false;
}
