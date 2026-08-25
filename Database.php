<?php

/**
 * ไฟล์: app/Core/Database.php
 * หน้าที่: สร้างการเชื่อมต่อและส่งคำสั่งไปยังฐานข้อมูล MySQL
 */

namespace App\Core;

// นำคลาส PDO มาใช้สำหรับเชื่อมต่อฐานข้อมูล
use PDO;

// นำคลาส PDOException มาใช้สำหรับตรวจข้อผิดพลาด
use PDOException;

/**
 * คลาส Database
 *
 * ใช้เชื่อมต่อ MySQL ผ่าน PDO และเรียกคำสั่ง SQL
 */
final class Database
{
    /**
     * เก็บ Connection ที่สร้างไว้แล้ว
     *
     * static ทำให้ทุกส่วนของระบบใช้ Connection เดียวกัน
     * โดยไม่ต้องเชื่อมฐานข้อมูลใหม่ทุกครั้ง
     */
    private static ?PDO $pdo = null;

    /**
     * สร้างหรือคืนค่า Connection ของฐานข้อมูล
     */
    public static function connection(): PDO
    {
        /*
         * ถ้ามี Connection อยู่แล้ว
         * ให้คืนค่าเดิมทันที
         */
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        /*
         * อ่านการตั้งค่าจาก config/config.php
         *
         * dirname(__DIR__, 2) หมายถึงย้อนจาก:
         * app/Core → app → โฟลเดอร์หลักของโครงการ
         */
        $config = require dirname(__DIR__, 2)
            . '/config/config.php';

        // เลือกเฉพาะการตั้งค่าฐานข้อมูล
        $db = $config['db'];

        /*
         * สร้าง Data Source Name สำหรับ MySQL
         *
         * เมื่อใช้ Docker:
         * host = db
         * port = 3306
         *
         * เมื่อใช้ PHP บน Windows:
         * host = 127.0.0.1
         * port = 3307
         */
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'],
            $db['name']
        );

        try {
            /*
             * สร้าง PDO Connection
             */
            self::$pdo = new PDO(
                $dsn,
                $db['user'],
                $db['pass'],
                [
                    /*
                     * ให้ PDO โยน Exception เมื่อ SQL ผิดพลาด
                     */
                    PDO::ATTR_ERRMODE =>
                        PDO::ERRMODE_EXCEPTION,

                    /*
                     * ให้ผลลัพธ์จากฐานข้อมูลเป็น Associative Array
                     *
                     * ตัวอย่าง:
                     * $user['username']
                     */
                    PDO::ATTR_DEFAULT_FETCH_MODE =>
                        PDO::FETCH_ASSOC,

                    /*
                     * ใช้ Prepared Statement ของ MySQL จริง
                     * ช่วยเพิ่มความปลอดภัยในการส่ง Parameter
                     */
                    PDO::ATTR_EMULATE_PREPARES =>
                        false,
                ]
            );

            // คืนค่า Connection
            return self::$pdo;
        } catch (PDOException $exception) {
            /*
             * แสดงข้อความเมื่อเชื่อมต่อฐานข้อมูลไม่ได้
             */
            throw new PDOException(
                'เชื่อมต่อฐานข้อมูลไม่ได้ '
                . 'กรุณาตรวจสอบ Docker, DB_HOST และ DB_PORT: '
                . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * เตรียมและประมวลผลคำสั่ง SQL
     *
     * ตัวอย่าง:
     *
     * Database::query(
     *     'SELECT * FROM users WHERE username = ?',
     *     [$username]
     * );
     */
    public static function query(
        string $sql,
        array $params = []
    ): \PDOStatement {
        /*
         * เรียก Connection และเตรียมคำสั่ง SQL
         */
        $statement = self::connection()->prepare($sql);

        /*
         * ส่งค่าตัวแปรเข้าสู่ Prepared Statement
         */
        $statement->execute($params);

        /*
         * คืน PDOStatement เพื่อนำไป fetch() หรือ fetchAll()
         */
        return $statement;
    }
}