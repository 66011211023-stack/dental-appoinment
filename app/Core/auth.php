<?php

namespace App\Core;

class Auth
{
    // ตรวจสอบข้อมูลล็อกอิน
    public static function login(string $username, string $password): bool
    {
        $stmt = Database::query(
            "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1",
            [$username, $username]
        );
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            return true;
        }

        return false;
    }

    // ตรวจสอบสถานะการล็อกอิน
    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // ดึงข้อมูลผู้ใช้ปัจจุบัน
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (!isset($_SESSION['user'])) {
            $stmt = Database::query("SELECT * FROM users WHERE id = ? LIMIT 1", [$_SESSION['user_id']]);
            $_SESSION['user'] = $stmt->fetch() ?: null;
        }

        return $_SESSION['user'];
    }

    // ออกจากระบบ
    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user']);
        session_destroy();
    }
}
