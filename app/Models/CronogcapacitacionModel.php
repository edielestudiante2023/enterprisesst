<?php

namespace App\Models;

use CodeIgniter\Model;

class CronogcapacitacionModel extends Model
{
    // Definimos la tabla asociada al modelo
    protected $table = 'tbl_cronog_capacitacion';

    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_cronograma_capacitacion';

    // Definimos los campos permitidos para inserciones/actualizaciones masivas
    protected $allowedFields = [
        'id_capacitacion',
        'id_cliente',
        'fecha_programada',
        'fecha_de_realizacion',
        'estado',
        'perfil_de_asistentes',
        'nombre_del_capacitador',
        'horas_de_duracion_de_la_capacitacion',
        'indicador_de_realizacion_de_la_capacitacion',
        'numero_de_asistentes_a_capacitacion',
        'numero_total_de_personas_programadas',
        'porcentaje_cobertura',
        'numero_de_personas_evaluadas',
        'promedio_de_calificaciones',
        'observaciones'
    ];

    // Activamos el uso automático de las columnas 'created_at' y 'updated_at'
    protected $useTimestamps = false;

    // Si decides agregar timestamps manualmente:
    // protected $createdField = 'created_at';
    // protected $updatedField = 'updated_at';

    // Si necesitas un valor por defecto para 'estado' u otra columna:
    // protected $defaultValues = ['estado' => 'PENDIENTE'];
}
