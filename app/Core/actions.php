<?php

namespace App\Core;

class Actions
{
    public static function login(array $post): void
    {
        $username = trim($post['username'] ?? '');
        $password = $post['password'] ?? '';

        if (Auth::login($username, $password)) {
            header('Location: /?page=dashboard');
            exit;
        }

        $_SESSION['error'] = 'ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง';
        header('Location: /?page=login');
        exit;
    }

    public static function register(array $post): void
    {
        $firstName = trim($post['first_name'] ?? '');
        $lastName = trim($post['last_name'] ?? '');
        $fullName = trim($firstName . ' ' . $lastName);
        $email = trim($post['email'] ?? '');
        $phone = trim($post['phone'] ?? '');
        $password = $post['password'] ?? '';
        $confirmPassword = $post['password_confirmation'] ?? '';
        $nationalId = trim($post['national_id'] ?? '');
        $birthDate = !empty($post['birth_date']) ? $post['birth_date'] : null;
        $coverageType = trim($post['coverage_type'] ?? 'จ่ายตรง/เงินสด');
        $address = trim($post['address'] ?? '');

        if (empty($firstName) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน';
            header('Location: /?page=register');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
            header('Location: /?page=register');
            exit;
        }

        $exist = Database::query("SELECT id FROM users WHERE username = ? OR email = ?", [$email, $email])->fetch();
        if ($exist) {
            $_SESSION['error'] = 'อีเมลนี้ถูกใช้งานในระบบแล้ว';
            header('Location: /?page=register');
            exit;
        }

        // 1. เพิ่ม User
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        Database::query(
            "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'patient')",
            [$email, $email, $hashedPassword, $fullName]
        );
        $userId = Database::lastInsertId();

        // 2. สุ่มรหัส HN (เช่น HN69001)
        $hn = 'HN' . rand(10000, 99999);

        // 3. บันทึกลงตาราง patients
        Database::query(
            "INSERT INTO patients (user_id, hn, full_name, phone, national_id, birth_date, coverage_type, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$userId, $hn, $fullName, $phone, $nationalId, $birthDate, $coverageType, $address]
        );

        // Auto Login
        Auth::login($email, $password);
        header('Location: /?page=dashboard');
        exit;
    }

    public static function forgotLookup(array $post): void
    {
        $email = trim($post['email'] ?? '');
        $user = Database::query("SELECT id FROM users WHERE email = ?", [$email])->fetch();

        if ($user) {
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['auth_screen'] = 'new-password';
        } else {
            $_SESSION['forgot_error'] = 'ไม่พบอีเมลนี้ในระบบ';
            $_SESSION['auth_screen'] = 'forgot';
        }

        header('Location: /?page=login');
        exit;
    }

    public static function resetPassword(array $post): void
    {
        $userId = $_SESSION['reset_user_id'] ?? null;
        $password = $post['password'] ?? '';
        $confirmPassword = $post['password_confirmation'] ?? '';

        if (!$userId) {
            $_SESSION['error'] = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
            header('Location: /?page=login');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['reset_error'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
            $_SESSION['auth_screen'] = 'new-password';
            header('Location: /?page=login');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        Database::query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);

        unset($_SESSION['reset_user_id']);
        $_SESSION['auth_screen'] = 'success';
        header('Location: /?page=login');
        exit;
    }
}
