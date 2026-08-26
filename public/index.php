<?php
session_start();

// 1. ระบบ Autoload แบบยืดหยุ่น (รองรับตัวพิมพ์เล็ก-ใหญ่บน Linux)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $path = str_replace('\\', '/', $relativeClass) . '.php';

    // รายการพาธที่อาจเป็นไปได้
    $possibleFiles = [
        $baseDir . $path,
        $baseDir . strtolower($path),
        dirname(__DIR__) . '/app/Core/' . basename($path),
        dirname(__DIR__) . '/app/core/' . basename($path)
    ];

    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

use App\Core\Auth;
use App\Core\View;

// ตรวจสอบความปลอดภัย: หาก Autoload ทำงานแล้วยังพบคลาสไม่สมบูรณ์
if (!class_exists('App\Core\Auth')) {
    die("<div style='padding:20px; background:#fee2e2; color:#991b1b; font-family:sans-serif; border-radius:8px;'>"
        . "<h3>⚠️ ไม่พบไฟล์ Auth.php!</h3>"
        . "<p>กรุณาตรวจสอบว่ามีไฟล์ <b>app/Core/Auth.php</b> ใน GitHub หรือไม่</p>"
        . "<p>พาธที่ค้นหา: <code>" . htmlspecialchars(dirname(__DIR__) . '/app/Core/Auth.php') . "</code></p>"
        . "</div>");
}

// 2. ตรวจสอบการออกจากระบบ
$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    Auth::logout();
    header('Location: /?page=login');
    exit;
}

// 3. เช็คสิทธิ์การเข้าถึง (ถ้ายังไม่ล็อกอิน ให้ดีดไปหน้า login)
if (!Auth::check() && !in_array($page, ['login', 'register', 'forgot'], true)) {
    header('Location: /?page=login');
    exit;
}

// 4. จับคู่ชื่อหน้ากับไฟล์ View
$viewMap = [
    'login'          => 'auth/login',
    'register'       => 'auth/register',
    'dashboard'      => 'dashboard/index',
    'appointments'   => 'modules/appointments',
    'booking'        => 'modules/booking',
    'history'        => 'modules/history',
    'materials'      => 'modules/materials',
    'material-usage' => 'modules/material-usage',
    'notifications'  => 'modules/notifications',
    'patients'       => 'modules/patients',
    'profile'        => 'modules/profile',
    'reports'        => 'modules/reports',
    'rights'         => 'modules/rights',
    'schedule'       => 'modules/schedule',
    'service-stats'  => 'modules/service-stats',
    'users'          => 'modules/users',
];

$view = $viewMap[$page] ?? 'auth/login';
View::render($view);
