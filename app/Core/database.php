<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // ดึงค่าจาก Environment Variables หากไม่มีให้ใช้ค่า Local Docker (db)
            $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'db');
            $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
            $dbname = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'dental_db');
            $user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
            $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? 'root');

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("<div style='padding:20px; background:#fee2e2; color:#991b1b; font-family:sans-serif; border-radius:8px;'>"
                    . "<h3>⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้!</h3>"
                    . "<p><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</p>"
                    . "</div>");
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}
