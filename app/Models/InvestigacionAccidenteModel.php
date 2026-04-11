<?php

namespace App\Models;

use CodeIgniter\Model;

class InvestigacionAccidenteModel extends Model
{
    protected $table = 'tbl_investigacion_accidente';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'id_miembro', 'creado_por_tipo',
        'tipo_evento', 'gravedad',
        'fecha_evento', 'hora_evento', 'lugar_exacto', 'descripcion_detallada', 'fecha_investigacion',
        'nombre_trabajador', 'documento_trabajador', 'cargo_trabajador', 'area_trabajador',
        'antiguedad_trabajador', 'tipo_vinculacion', 'jornada_habitual',
        'parte_cuerpo_lesionada', 'tipo_lesion', 'agente_accidente', 'mecanismo_accidente', 'dias_incapacidad',
        'potencial_danio',
        'actos_substandar', 'condiciones_substandar', 'factores_personales', 'factores_trabajo',
        'metodologia_analisis', 'descripcion_analisis',
        'investigador_jefe_nombre', 'investigador_jefe_cargo',
        'investigador_copasst_nombre', 'investigador_copasst_cargo',
        'investigador_sst_nombre', 'investigador_sst_cargo',
        'firma_jefe', 'firma_copasst', 'firma_sst',
        'token_firma_remota', 'token_firma_tipo', 'token_firma_expiracion',
        'numero_furat',
        'observaciones', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_investigacion_accidente.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_investigacion_accidente.id_consultor', 'left')
            ->where('tbl_investigacion_accidente.id_cliente', $idCliente)
            ->orderBy('tbl_investigacion_accidente.fecha_evento', 'DESC')
            ->findAll();
    }
}
