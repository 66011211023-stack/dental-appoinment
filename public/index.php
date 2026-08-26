<?php
session_start();

// 1. Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relativeClass = substr($class, $len);
    $fileName = strtolower(basename(str_replace('\\', '/', $relativeClass))) . '.php';
    $rawPath = str_replace('\\', '/', $relativeClass) . '.php';
    $rootDir = dirname(__DIR__);

    $possibleFiles = [
        $rootDir . '/dental-appoinment/app/Core/' . $fileName,
        $rootDir . '/dental-appoinment/app/core/' . $fileName,
        $rootDir . '/dental-appoinment/app/Core/' . basename($rawPath),
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
use App\Core\Actions;
use App\Core\View;

$page = $_GET['page'] ?? 'login';

// 2. จัดการการส่งฟอร์ม (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'login') {
        $action = $_POST['action'] ?? 'login';
        if ($action === 'login') {
            Actions::login($_POST);
        } elseif ($action === 'forgot_lookup') {
            Actions::forgotLookup($_POST);
        } elseif ($action === 'reset_password') {
            Actions::resetPassword($_POST);
        }
    } elseif ($page === 'register') {
        Actions::register($_POST);
    }
    exit;
}

// 3. เช็คการออกจากระบบ
if ($page === 'logout') {
    Auth::logout();
    header('Location: /?page=login');
    exit;
}

// 4. เช็คสิทธิ์การเข้าถึงหน้าต่างๆ
if (!Auth::check() && !in_array($page, ['login', 'register', 'forgot'], true)) {
    header('Location: /?page=login');
    exit;
}

// 5. Map หน้า Views
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
