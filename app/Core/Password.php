<?php
declare(strict_types=1);

namespace App\Core;

final class Password
{
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$2')) {
            return password_verify($password, $storedHash);
        }

        // รองรับบัญชีเดิมจาก init.sql ซึ่งใช้ SHA-256
        return strlen($storedHash) === 64
            && hash_equals(strtolower($storedHash), hash('sha256', $password));
    }

    public static function needsUpgrade(string $storedHash): bool
    {
        return !str_starts_with($storedHash, '$2')
            || password_needs_rehash($storedHash, PASSWORD_BCRYPT);
    }
}
