<?php

namespace app\database;

use PDO;

class DatabaseInitializer
{
    public function init(PDO $connection)
    {
        $connection->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $connection->exec("USE " . DB_NAME);

        $scriptPath = __DIR__ . '/scripts/script.sql';

        if (file_exists($scriptPath)) {
            $sql = file_get_contents($scriptPath);
            try {
                $connection->exec($sql);
            } catch (\Exception $e) {
                error_log("Erro ao inicializar banco de dados: " . $e->getMessage());
            }
        }
    }
}
