<?php

namespace App\Models;

use CodeIgniter\Model;

class DocSeccionesConfigModel extends Model
{
    protected $table      = 'tbl_doc_secciones_config';
    protected $primaryKey = 'id_seccion_config';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_tipo_config',
        'numero',
        'nombre',
        'seccion_key',
        'prompt_ia',
        'tipo_contenido',
        'tabla_dinamica_tipo',
        'es_obligatoria',
        'orden',
        'activo',
    ];

    protected $useTimestamps = false;

    public function getConTipoConfig()
    {
        return $this->db->table('tbl_doc_secciones_config sc')
            ->select('sc.*, tc.nombre AS nombre_tipo_config, tc.tipo_documento')
            ->join('tbl_doc_tipo_configuracion tc', 'tc.id_tipo_config = sc.id_tipo_config', 'left')
            ->orderBy('sc.id_tipo_config, sc.orden')
            ->get()
            ->getResultArray();
    }

    public function getTiposConfig()
    {
        return $this->db->table('tbl_doc_tipo_configuracion')
            ->select('id_tipo_config, nombre, tipo_documento')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get()
            ->getResultArray();
    }
}
