<?php
namespace App\Core;

final class Auth
{
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function check(): bool { return isset($_SESSION['user']); }
    public static function requireLogin(): void
    {
        if (!self::check()) { header('Location: /?page=login'); exit; }
    }
    public static function logout(): void
    {
        $_SESSION = [];

        // ลบ Session Cookie ออกจากเบราว์เซอร์ด้วย ไม่ใช่ล้างเฉพาะข้อมูลฝั่ง Server
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => (bool)$params['secure'],
                    'httponly' => (bool)$params['httponly'],
                    'samesite' => 'Lax',
                ]
            );
        }

        session_destroy();
    }
}
