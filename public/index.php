<?php
session_start();

// ระบบ Autoload ที่ปรับให้รองรับโครงสร้างไฟล์ตัวพิมพ์เล็ก (lowercase)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $path = str_replace('\\', '/', $relativeClass) . '.php';

    // รายการพาธที่ค้นหา (รองรับทั้งตัวพิมพ์เล็กทั้งหมด และแบบ CamelCase)
    $possibleFiles = [
        $baseDir . strtolower($path),                           // เช่น app/core/auth.php
        $baseDir . $path,                                       // เช่น app/Core/Auth.php
        dirname(__DIR__) . '/app/core/' . strtolower(basename($path)),
        dirname(__DIR__) . '/app/Core/' . basename($path)
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

// ตรวจสอบความปลอดภัย
if (!class_exists('App\Core\Auth')) {
    die("<div style='padding:20px; background:#fee2e2; color:#991b1b; font-family:sans-serif; border-radius:8px;'>"
        . "<h3>⚠️ ไม่พบไฟล์ auth.php!</h3>"
        . "<p>ระบบค้นหาในพาธ <code>app/core/auth.php</code> แล้วยังไม่พบครับ</p>"
        . "</div>");
}

// เช็คการออกจากระบบ
$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    Auth::logout();
    header('Location: /?page=login');
    exit;
}

// เช็คสิทธิ์การเข้าถึง
if (!Auth::check() && !in_array($page, ['login', 'register', 'forgot'], true)) {
    header('Location: /?page=login');
    exit;
}

// Map ชื่อหน้าไปยัง Views
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
