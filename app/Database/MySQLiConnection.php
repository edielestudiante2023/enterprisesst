<?php

namespace App\Database;

use CodeIgniter\Database\MySQLi\Connection;

/**
 * Custom MySQLi Connection that forces utf8mb4_general_ci collation
 * to avoid "Illegal mix of collations" errors with MySQL 8
 */
class MySQLiConnection extends Connection
{
    /**
     * Connect to the database.
     *
     * @return mixed
     */
    public function connect(bool $persistent = false)
    {
        $result = parent::connect($persistent);

        // Force collation after connection
        if ($this->connID) {
            $this->connID->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
            $this->connID->query("SET collation_connection = 'utf8mb4_general_ci'");
            $this->connID->query("SET collation_database = 'utf8mb4_general_ci'");
        }

        return $result;
    }
}
