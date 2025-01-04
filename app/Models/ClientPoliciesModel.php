<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientPoliciesModel extends Model
{
    protected $table = 'client_policies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 'policy_type_id', 'policy_content', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
}
