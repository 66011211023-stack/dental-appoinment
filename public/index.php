<?php
session_start();

// 1. ระบบ Autoload ที่ค้นหาครอบคลุมโฟลเดอร์ซ้อน dental-appoinment
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $fileName = strtolower(basename(str_replace('\\', '/', $relativeClass))) . '.php';
    $rawPath = str_replace('\\', '/', $relativeClass) . '.php';

    $rootDir = dirname(__DIR__); // /var/www/html

    // ค้นหารายการตำแหน่งพาธทั้งหมดที่เป็นไปได้บน Render/GitHub
    $possibleFiles = [
        // พาธซ้อนโฟลเดอร์ dental-appoinment (ตามโครงสร้างบน GitHub คุณ)
        $rootDir . '/dental-appoinment/app/Core/' . $fileName,
        $rootDir . '/dental-appoinment/app/core/' . $fileName,
        $rootDir . '/dental-appoinment/app/Core/' . basename($rawPath),
        
        // พาธระดับปกติ
        $rootDir . '/app/Core/' . $fileName,
        $rootDir . '/app/core/' . $fileName,
        $rootDir . '/app/Core/' . basename($rawPath),
        $rootDir . '/app/' . strtolower($rawPath),
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

// ตรวจสอบคลาส Auth
if (!class_exists('App\Core\Auth')) {
    die("<div style='padding:20px; background:#fee2e2; color:#991b1b; font-family:sans-serif; border-radius:8px;'>"
        . "<h3>⚠️ ไม่พบไฟล์ auth.php!</h3>"
        . "<p>พาธ root ปัจจุบัน: <code>" . htmlspecialchars(dirname(__DIR__)) . "</code></p>"
        . "</div>");
}

// 2. เช็คการออกจากระบบ
$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    Auth::logout();
    header('Location: /?page=login');
    exit;
}

// 3. เช็คสิทธิ์การเข้าถึง
if (!Auth::check() && !in_array($page, ['login', 'register', 'forgot'], true)) {
    header('Location: /?page=login');
    exit;
}

// 4. Map ชื่อหน้าไปยัง Views
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
