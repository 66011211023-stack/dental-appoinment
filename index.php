<?php
// Front Controller: ทุก URL ของระบบจะเข้ามาที่ไฟล์นี้ก่อนเสมอ
declare(strict_types=1);

// ใช้ Session ผ่าน Cookie เท่านั้น และป้องกันการนำ Session ID เดิมกลับมาใช้ซ้ำ
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
session_name('denticare_session');
session_start();

// Autoloader: ดึงไฟล์ตาม Namespace โดยลองทั้งชื่อตรงและตัวพิมพ์เล็ก (รองรับ Linux Server)
// Autoloader ค้นหาทั้งใน app/ และ Root directory
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;

    $relativeClass = substr($class, strlen($prefix));
    $file = str_replace('\\', '/', $relativeClass) . '.php';
    $filenameOnly = basename($file); // ดึงเฉพาะชื่อไฟล์ เช่น Auth.php

    $paths = [
        __DIR__ . '/' . $filenameOnly,                       // เช็กที่ Root: /Auth.php
        __DIR__ . '/' . strtolower($filenameOnly),           // เช็กที่ Root ตัวพิมพ์เล็ก: /auth.php
        __DIR__ . '/app/' . $file,                           // เช็กใน app/Core/Auth.php
        __DIR__ . '/app/' . strtolower($file),               // เช็กใน app/core/auth.php
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
use App\Core\Auth;
use App\Core\Actions;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Password;
use App\Core\View;

if (Auth::check() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    header('Location: /?page=dashboard'); exit;
}

// สมัครสมาชิกได้เฉพาะบทบาทผู้ป่วย ระบบสร้างบัญชีและ HN ใน Transaction เดียวกัน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    Csrf::verify();
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirmation'] ?? '');
    $firstName = trim((string)($_POST['first_name'] ?? ''));
    $lastName = trim((string)($_POST['last_name'] ?? ''));
    $fullName = trim($firstName . ' ' . $lastName);
    $citizenId = preg_replace('/\D+/', '', (string)($_POST['citizen_id'] ?? ''));
    $email = trim(str_replace(['＠','。','．'], ['@','.','.'], (string)($_POST['email'] ?? '')));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $birthDate = trim((string)($_POST['birth_date'] ?? '')) ?: null;
    $address = trim((string)($_POST['address'] ?? ''));
    $treatmentRight = trim((string)($_POST['treatment_right'] ?? ''));
    $errors = [];
    if (strlen($password) < 4) $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร';
    if ($password !== $confirmation) $errors[] = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
    if ($firstName === '') $errors[] = 'กรุณากรอกชื่อ';
    if ($lastName === '') $errors[] = 'กรุณากรอกนามสกุล';
    if ($citizenId !== '' && strlen($citizenId) !== 13) $errors[] = 'เลขบัตรประชาชนต้องมี 13 หลัก';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'กรุณากรอกอีเมลที่ถูกต้อง';
    if ($email !== '' && Database::query('SELECT COUNT(*) FROM users WHERE email=?', [$email])->fetchColumn()) $errors[] = 'อีเมลนี้ถูกใช้สมัครสมาชิกแล้ว';
    if ($citizenId !== '' && Database::query('SELECT COUNT(*) FROM patients WHERE citizen_id=?', [$citizenId])->fetchColumn()) $errors[] = 'เลขบัตรประชาชนนี้มีบัญชีแล้ว';
    if ($errors) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old'] = $_POST;
        header('Location: /?page=register'); exit;
    }
    // users.username ยังคงเป็นคอลัมน์บังคับ จึงสร้างค่าใช้ภายในจากอีเมลโดยไม่แสดงให้ผู้ป่วยกรอก
    $emailName = strtolower((string)strtok($email, '@'));
    $usernameBase = substr((string)preg_replace('/[^a-z0-9_.-]/', '', $emailName), 0, 40);
    if (strlen($usernameBase) < 4) $usernameBase = 'patient';
    $username = $usernameBase;
    $suffix = 1;
    while (Database::query('SELECT COUNT(*) FROM users WHERE username=?', [$username])->fetchColumn()) {
        $username = substr($usernameBase, 0, 44) . $suffix++;
    }
    $pdo = Database::connection();
    try {
        $pdo->beginTransaction();
        Database::query('INSERT INTO users(username,password_hash,role,full_name,email) VALUES(?,?,\'patient\',?,?)', [$username,Password::hash($password),$fullName,$email]);
        $userId = (int)$pdo->lastInsertId();
        $lastHnText = (string)(Database::query('SELECT hn FROM patients ORDER BY id DESC LIMIT 1 FOR UPDATE')->fetchColumn() ?: 'HN-00000');
        $lastHn = (int)preg_replace('/\D+/', '', $lastHnText);
        $hn = 'HN-' . str_pad((string)($lastHn + 1), 5, '0', STR_PAD_LEFT);
        Database::query('INSERT INTO patients(user_id,hn,citizen_id,full_name,birth_date,phone,address,treatment_right) VALUES(?,?,?,?,?,?,?,?)', [$userId,$hn,$citizenId ?: null,$fullName,$birthDate,$phone,$address,$treatmentRight]);
        Database::query('INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)', [$userId,'ยินดีต้อนรับสู่ DentiCare','สมัครสมาชิกสำเร็จ หมายเลขผู้ป่วยของคุณคือ '.$hn]);
        $pdo->commit();
        $user = Database::query('SELECT id,username,role,full_name,email,profile_image,is_active,created_at FROM users WHERE id=?', [$userId])->fetch();
        $_SESSION['user'] = $user;
        $_SESSION['flash_success'] = 'สมัครสมาชิกสำเร็จ หมายเลขผู้ป่วยของคุณคือ '.$hn;
        header('Location: /?page=dashboard'); exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['register_errors'] = ['สมัครสมาชิกไม่สำเร็จ: '.$e->getMessage()];
        $_SESSION['register_old'] = $_POST;
        header('Location: /?page=register'); exit;
    }
}

// ค้นหาบัญชีผู้ป่วยสำหรับตั้งรหัสผ่านใหม่ด้วยอีเมล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'forgot_lookup') {
    Csrf::verify();
    $email = strtolower(trim(str_replace(['＠','。','．'], ['@','.','.'], (string)($_POST['email'] ?? ''))));
    $account = Database::query(
        "SELECT u.id
         FROM users u
         WHERE LOWER(u.email)=?
           AND u.role='patient'
           AND u.is_active=1
         LIMIT 1",
        [$email]
    )->fetch();

    if (!$account) {
        $_SESSION['forgot_error'] = 'ไม่พบบัญชีผู้ป่วยที่ใช้อีเมลนี้';
        $_SESSION['auth_screen'] = 'forgot';
        header('Location: /?page=login'); exit;
    }

    $_SESSION['reset_user_id'] = (int)$account['id'];
    $_SESSION['reset_started_at'] = time();
    $_SESSION['auth_screen'] = 'new-password';
    header('Location: /?page=login'); exit;
}

// บันทึกรหัสผ่านใหม่จริงลงฐานข้อมูล สิทธิ์รีเซ็ตมีอายุ 15 นาทีและใช้ได้ครั้งเดียว
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    Csrf::verify();
    $userId = (int)($_SESSION['reset_user_id'] ?? 0);
    $startedAt = (int)($_SESSION['reset_started_at'] ?? 0);
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirmation'] ?? '');
    $errors = [];

    if (!$userId || !$startedAt || time() - $startedAt > 900) $errors[] = 'ขั้นตอนตั้งรหัสผ่านหมดอายุ กรุณาตรวจสอบบัญชีใหม่';
    if (strlen($password) < 4) $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร';
    if ($password !== $confirmation) $errors[] = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';

    if ($errors) {
        $_SESSION['reset_error'] = implode(' ', $errors);
        $_SESSION['auth_screen'] = $userId ? 'new-password' : 'forgot';
        header('Location: /?page=login'); exit;
    }

    Database::query('UPDATE users SET password_hash=? WHERE id=? AND role=\'patient\' AND is_active=1', [Password::hash($password),$userId]);
    unset($_SESSION['reset_user_id'],$_SESSION['reset_started_at']);
    $_SESSION['auth_screen'] = 'success';
    header('Location: /?page=login'); exit;
}

// คำสั่งที่เปลี่ยนข้อมูลทั้งหมดต้องเป็น POST และผ่าน CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] !== 'login') {
    Actions::handle((string)$_POST['action']);
}

// อ่านชื่อหน้าจาก Query String หากล็อกอินแล้วให้เริ่มที่ Dashboard
$page = $_GET['page'] ?? (Auth::check() ? 'dashboard' : 'login');

// เมื่อเปิดลิงก์หน้า Login โดยตรง ให้ล้างบัญชีเดิมก่อนเสมอ
// ป้องกันเครื่องที่ใช้ร่วมกันเปิดเว็บแล้วเข้าเป็นผู้ใช้คนก่อน
if (Auth::check() && $page === 'login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::logout();
    header('Location: /?page=login'); exit;
}

// ผู้ใช้ที่ล็อกอินอยู่แล้วไม่ควรเปิดหน้าสมัครซ้อนกับ Layout หลัก
if (Auth::check() && $page === 'register') {
    header('Location: /?page=dashboard'); exit;
}

// ประมวลผลฟอร์มเข้าสู่ระบบด้วยบัญชีจากฐานข้อมูล
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(str_replace(['＠','。','．'], ['@','.','.'], (string)($_POST['username'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $user = Database::query('SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1', [$username,$username])->fetch();
    if ($user && Password::verify($password, (string)$user['password_hash'])) {
        // เปลี่ยน Session ID ทุกครั้งที่เข้าสู่ระบบ ป้องกัน Session Fixation
        session_regenerate_id(true);
        // อัปเกรดบัญชีเก่าจาก SHA-256 เป็น bcrypt หลังเข้าสู่ระบบสำเร็จ
        if (Password::needsUpgrade((string)$user['password_hash'])) {
            Database::query('UPDATE users SET password_hash=? WHERE id=?', [Password::hash($password),(int)$user['id']]);
        }
        Database::query('INSERT INTO login_logs(user_id, ip_address) VALUES(?, ?)', [(int)$user['id'], $_SERVER['REMOTE_ADDR'] ?? null]);
        unset($user['password_hash']);
        $_SESSION = ['user' => $user];
        header('Location: /?page=dashboard'); exit;
    }
    $_SESSION['error'] = 'อีเมล/ชื่อผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง';
    header('Location: /?page=login'); exit;
}

// ล้าง Session เมื่อผู้ใช้กดออกจากระบบในเมนูบัญชี
if ($page === 'logout') {
    Auth::logout();
    header('Location: /?page=login'); exit;
}

if ($page === 'login') { View::render('auth/login', ['title' => 'เข้าสู่ระบบ']); exit; }
if ($page === 'register') { View::render('auth/register', ['title' => 'สมัครสมาชิกผู้ป่วย']); exit; }
Auth::requireLogin();
$role = Auth::user()['role'];

// Whitelist ป้องกันการเรียก View ที่ระบบไม่ได้อนุญาต
$allowed = ['dashboard','appointments','patients','history','schedule','treatments','materials','reports','users','rights','notifications','profile','booking','material-usage','service-stats'];
if (!in_array($page, $allowed, true)) $page = 'dashboard';

// จำกัดหน้าให้ตรงกับขอบเขตงานของผู้ใช้งาน 4 กลุ่ม
$rolePages = [
    'admin' => ['dashboard','appointments','schedule','patients','treatments','materials','users','rights','reports','profile'],
    'staff' => ['dashboard','appointments','schedule','patients','materials','reports','profile'],
    'dentist' => ['dashboard','schedule','patients','treatments','material-usage','service-stats','profile'],
    'patient' => ['dashboard','booking','appointments','history','notifications','profile'],
];
if (!in_array($page, $rolePages[$role], true)) $page = 'dashboard';

if ($page === 'dashboard') {
    $stats = [
        'patients' => (int)Database::query('SELECT COUNT(*) FROM patients')->fetchColumn(),
        'appointments' => (int)Database::query("SELECT COUNT(*) FROM appointments WHERE appointment_date >= CURDATE() AND status <> 'cancelled'")->fetchColumn(),
        'pending' => (int)Database::query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn(),
        'low_materials' => (int)Database::query('SELECT COUNT(*) FROM materials WHERE quantity <= reorder_level')->fetchColumn(),
    ];
    $appointments = Database::query('SELECT a.*,p.full_name patient_name,u.full_name dentist_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN dentists d ON d.id=a.dentist_id LEFT JOIN users u ON u.id=d.user_id ORDER BY a.appointment_date,a.appointment_time LIMIT 8')->fetchAll();
    $dashboardTitles = ['admin'=>'ภาพรวมระบบ','staff'=>'ภาพรวมเจ้าหน้าที่','dentist'=>'ภาพรวมทันตแพทย์','patient'=>'หน้าหลักผู้ป่วย'];
    View::render('dashboard/index', compact('stats','appointments') + ['title'=>$dashboardTitles[$role]]); exit;
}

$titles = [
    'appointments'=>'คำขอและนัดหมาย','patients'=>'ผู้ป่วยและซักประวัติ','history'=>'ประวัติผู้ป่วย',
    'schedule'=>'ตารางเวลาทันตแพทย์','treatments'=>'วินิจฉัยและการรักษา','materials'=>'คลังวัสดุทันตกรรม',
    'reports'=>'รายงานและสถิติ','users'=>'บุคลากรและผู้ใช้งาน','rights'=>'สิทธิ์การรักษา',
    'notifications'=>'การแจ้งเตือน','profile'=>'ข้อมูลส่วนตัว','booking'=>'จองคิวทันตกรรม',
    'material-usage'=>'เบิกใช้วัสดุ','service-stats'=>'สถิติการให้บริการ'
];
// แต่ละโมดูลมี View แยกไฟล์ ทำให้อ่าน แก้ไข และอธิบายโค้ดได้ง่าย
$moduleView = 'modules/' . $page;
View::render($moduleView, ['title'=>$titles[$page], 'module'=>$page]);
