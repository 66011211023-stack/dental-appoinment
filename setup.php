<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $path = dirname(__DIR__) . '/app/'
        . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) require $path;
});

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$setupKey = (string)(getenv('SETUP_KEY') ?: '');
$providedKey = (string)($_GET['key'] ?? '');

if ($setupKey === '' || !hash_equals($setupKey, $providedKey)) {
    http_response_code(404);
    exit('Not Found');
}

try {
    $pdo = Database::connection();
    $tableExists = (bool)$pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();

    if ($tableExists) {
        echo '<h2>DentiCare พร้อมใช้งานแล้ว</h2>';
        echo '<p>ฐานข้อมูลถูกสร้างไว้ก่อนหน้านี้ ไม่ได้มีการแก้ไขข้อมูลซ้ำ</p>';
        echo '<p><a href="/?page=login">ไปหน้าเข้าสู่ระบบ</a></p>';
        exit;
    }

    $sqlFile = dirname(__DIR__) . '/database/init.sql';
    $sql = file_get_contents($sqlFile);
    if ($sql === false) throw new RuntimeException('ไม่พบไฟล์เริ่มต้นฐานข้อมูล');

    $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement !== '') $pdo->exec($statement);
    }

    echo '<h2>สร้างฐานข้อมูล DentiCare สำเร็จ</h2>';
    echo '<p>บัญชีทดสอบ: admin / 1234</p>';
    echo '<p><a href="/?page=login">ไปหน้าเข้าสู่ระบบ</a></p>';
} catch (Throwable $error) {
    error_log('DentiCare setup failed: ' . $error->getMessage());
    http_response_code(500);
    echo '<h2>สร้างฐานข้อมูลไม่สำเร็จ</h2>';
    echo '<p>กรุณาตรวจสอบตัวแปร DB_HOST, DB_PORT, DB_NAME, DB_USER และ DB_PASS ใน Railway</p>';
}
