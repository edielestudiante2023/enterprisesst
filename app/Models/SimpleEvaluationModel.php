<?php

namespace App\Models;

use CodeIgniter\Model;

class SimpleEvaluationModel extends Model
{
    // Tabla asociada
    protected $table = 'evaluacion_inicial_sst';

    // Llave primaria
    protected $primaryKey = 'id_ev_ini';

    // Campos permitidos para inserción masiva
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

    // Desactivar validaciones automáticas
    protected $skipValidation = true;

    // Manejo de timestamps (opcional, depende de tu configuración de tabla)
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
