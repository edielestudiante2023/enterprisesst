<?php
namespace App\Models;

use CodeIgniter\Model;

class EstandarModel extends Model
{
    protected $table = 'estandares';
    protected $primaryKey = 'id_estandar';
    protected $allowedFields = ['nombre'];
}
?>
