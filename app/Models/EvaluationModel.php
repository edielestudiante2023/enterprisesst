<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluationModel extends Model
{
    // Definimos la tabla
    protected $table = 'evaluacion_inicial_sst'; 

    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_ev_ini';

    // Definimos qué columnas se pueden llenar con datos (permitimos inserción masiva)
    protected $allowedFields = [
        'created_at',
        'updated_at',
        'id_cliente',
        'ciclo',
        'estandar',
        'detalle_estandar',
        'estandares_minimos',
        'numeral',
        'numerales_del_cliente',
        'siete',
        'veintiun',
        'sesenta',
        'item_del_estandar',
        'evaluacion_inicial',
        'valor',
        'puntaje_cuantitativo',
        'item',
        'criterio',
        'modo_de_verificacion',
        'calificacion',
        'nivel_de_evaluacion',
        'observaciones'
    ];

    // Definimos si queremos que CodeIgniter maneje automáticamente las fechas de creación y actualización
    protected $useTimestamps = true;

    // Definimos los nombres de las columnas para las fechas automáticas
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Opcional: Puedes agregar validaciones aquí si quieres asegurarte de que los datos sean válidos
}
