<?php

namespace App\Models;

use CodeIgniter\Model;

class PruebaModel extends Model
{
    protected $table = 'prueba';
    protected $primaryKey = 'idprueba';
    protected $allowedFields = ['nombre_prueba'];
}

?>
