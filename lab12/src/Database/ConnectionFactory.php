<?php
declare(strict_types=1);

namespace App\Database;

use PDO;

final class ConnectionFactory
{
    public static function create(): PDO
    {
                $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST') ?: '127.0.0.1',
            getenv('DB_PORT') ?: '3307',
            getenv('DB_NAME') ?: 'php_library',
        );


        return new PDO(
            $dsn,
            getenv('DB_USER') ?: 'lib_app',
            getenv('DB_PASSWORD') ?: 'LibPass2026!',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }
}
