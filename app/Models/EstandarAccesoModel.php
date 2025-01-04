<?php
namespace App\Models;

use CodeIgniter\Model;

class EstandarAccesoModel extends Model
{
    protected $table = 'estandares_accesos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_estandar', 'id_acceso'];
}
?>
