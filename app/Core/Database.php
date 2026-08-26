<?php

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        // ค่าการเชื่อมต่อฐานข้อมูล InfinityFree จากหน้า Dashboard
        $dbHost = 'sql303.byetcluster.com';
        $dbPort = '3306';
        $dbName = 'if0_42591303_dental_db';
        $dbUser = 'if0_42591303';
        $dbPass = 'Achara1234';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $dbHost,
            $dbPort,
            $dbName
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return self::$pdo;
        } catch (PDOException $exception) {
            throw new PDOException(
                'เชื่อมต่อฐานข้อมูลไม่ได้: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public static function query(
        string $sql,
        array $params = []
    ): \PDOStatement {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }
}
