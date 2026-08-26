<?php
session_start();

// 1. ระบบ Autoload (ถอยกลับ 1 ชั้นออกไปหาโฟลเดอร์ app/)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    // ถอยจาก /public ไประดับ Root เพื่อเข้า /app
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. เรียกใช้งาน Core Class
use App\Core\Auth;
use App\Core\View;

// 3. ตรวจสอบการออกจากระบบ
$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    Auth::logout();
    header('Location: /?page=login');
    exit;
}

// 4. เช็คสิทธิ์การเข้าถึง (ถ้ายังไม่ล็อกอิน ให้ดีดไปหน้า login)
if (!Auth::check() && !in_array($page, ['login', 'register', 'forgot'], true)) {
    header('Location: /?page=login');
    exit;
}

// 5. จับคู่ชื่อหน้ากับไฟล์ View
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
