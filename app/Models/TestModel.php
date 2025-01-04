<?php 
namespace App\Models;

use CodeIgniter\Model;

class TestModel extends Model
{
    protected $table = 'tbl_tests';
    protected $primaryKey = 'id_test';
    protected $allowedFields = ['nombre_test', 'timestamp'];
}
?>
