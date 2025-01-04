<?php
namespace App\Models;

use CodeIgniter\Model;

class AccesoModel extends Model
{
    protected $table = 'accesos';
    protected $primaryKey = 'id_acceso';
    protected $allowedFields = ['nombre', 'url', 'dimension'];
}
?>
