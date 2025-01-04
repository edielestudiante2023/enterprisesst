<?php

namespace App\Models;

use CodeIgniter\Model;

class PolicyTypeModel extends Model
{
    protected $table = 'policy_types';
    protected $primaryKey = 'id';
    protected $allowedFields = ['type_name', 'description', 'created_at', 'updated_at'];
    protected $useTimestamps = true; // Habilitar timestamps automáticos
}
