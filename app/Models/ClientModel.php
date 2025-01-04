<?php 
namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'tbl_clientes';
    protected $primaryKey = 'id_cliente';
    protected $allowedFields = [
        'datetime', 'fecha_ingreso', 'nit_cliente', 'nombre_cliente', 'usuario',
        'password', 'correo_cliente', 'telefono_1_cliente', 'telefono_2_cliente',
        'direccion_cliente', 'persona_contacto_compras', 'codigo_actividad_economica',
        'nombre_rep_legal', 'cedula_rep_legal', 'fecha_fin_contrato', 'ciudad_cliente',
        'estado', 'id_consultor', 'logo', 'firma_representante_legal', 'estandares'
    ];
    
}
?>
