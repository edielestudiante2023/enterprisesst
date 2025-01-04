<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientKpiModel extends Model
{
    protected $table = 'tbl_client_kpi';
    protected $primaryKey = 'id_client_kpi';

    protected $allowedFields = [
        'created_at',
        'updated_at',
        'year',
        'month',
        'kpi_interpretation',
        'id_cliente',
        'id_kpi_policy',
        'id_objectives',
        'id_kpis',
        'id_kpi_type',
        'id_kpi_definition',
        'kpi_target',
        'kpi_formula',
        'data_source',
        'id_data_owner',
        'positions_should_know_result',
        'periodicidad', // Asegúrate de incluir este nuevo campo


        // Campos para los periodos 1 a 12
        'variable_numerador_1',
        'dato_variable_numerador_1',
        'variable_denominador_1',
        'dato_variable_denominador_1',
        'valor_indicador_1',
        'variable_numerador_2',
        'dato_variable_numerador_2',
        'variable_denominador_2',
        'dato_variable_denominador_2',
        'valor_indicador_2',
        'variable_numerador_3',
        'dato_variable_numerador_3',
        'variable_denominador_3',
        'dato_variable_denominador_3',
        'valor_indicador_3',
        'variable_numerador_4',
        'dato_variable_numerador_4',
        'variable_denominador_4',
        'dato_variable_denominador_4',
        'valor_indicador_4',
        'variable_numerador_5',
        'dato_variable_numerador_5',
        'variable_denominador_5',
        'dato_variable_denominador_5',
        'valor_indicador_5',
        'variable_numerador_6',
        'dato_variable_numerador_6',
        'variable_denominador_6',
        'dato_variable_denominador_6',
        'valor_indicador_6',
        'variable_numerador_7',
        'dato_variable_numerador_7',
        'variable_denominador_7',
        'dato_variable_denominador_7',
        'valor_indicador_7',
        'variable_numerador_8',
        'dato_variable_numerador_8',
        'variable_denominador_8',
        'dato_variable_denominador_8',
        'valor_indicador_8',
        'variable_numerador_9',
        'dato_variable_numerador_9',
        'variable_denominador_9',
        'dato_variable_denominador_9',
        'valor_indicador_9',
        'variable_numerador_10',
        'dato_variable_numerador_10',
        'variable_denominador_10',
        'dato_variable_denominador_10',
        'valor_indicador_10',
        'variable_numerador_11',
        'dato_variable_numerador_11',
        'variable_denominador_11',
        'dato_variable_denominador_11',
        'valor_indicador_11',
        'variable_numerador_12',
        'dato_variable_numerador_12',
        'variable_denominador_12',
        'dato_variable_denominador_12',
        'valor_indicador_12',

        'gran_total_indicador', // Total de todos los periodos
        'analisis_datos',     // Nueva columna
        'seguimiento1',       // Nueva columna
        'seguimiento2',       // Nueva columna
        'seguimiento3'    // Nueva columna
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
