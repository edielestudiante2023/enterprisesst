<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentVersionModel extends Model
{
    protected $table = 'document_versions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 
        'policy_type_id', 
        'version_number', 
        'created_at', 
        'updated_at', 
        'document_type',    // Cambiado a inglés
        'acronym',
        'location',
        'status',
        'change_control'
    ];
    

    // Si deseas que CodeIgniter maneje automáticamente created_at y updated_at
    protected $useTimestamps = true;
}
