<?php
use App\Core\Auth;

$user = Auth::user();
$role = $user['role'] ?? '';
$roleLabels = ['admin'=>'ผู้ดูแลระบบ','staff'=>'เจ้าหน้าที่ทันตกรรม','dentist'=>'ทันตแพทย์','patient'=>'ผู้ป่วย · HN-00127'];
$displayName = $user['full_name'] ?? 'ผู้ใช้งาน';
$nameParts = preg_split('/\s+/u', trim($displayName));
preg_match('/^./u', (string)($nameParts[0] ?? 'ผ'), $firstInitial);
preg_match('/^./u', (string)($nameParts[1] ?? ''), $secondInitial);
$initial = ($firstInitial[0] ?? 'ผ') . ($secondInitial[0] ?? '');
$profileImage = trim((string)($user['profile_image'] ?? ''));
$avatarContent = $profileImage !== ''
    ? '<img src="' . htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8') . '" alt="รูปโปรไฟล์">'
    : htmlspecialchars($initial, ENT_QUOTES, 'UTF-8');
$menus = [
 'admin'=>[['งานประจำวัน','dashboard','▦','ภาพรวมระบบ'],['งานประจำวัน','appointments','◷','คำขอและนัดหมาย'],['งานประจำวัน','schedule','▤','ตารางทันตแพทย์'],['การรักษา','patients','♙','ผู้ป่วยและซักประวัติ'],['การรักษา','treatments','✚','วินิจฉัยและการรักษา'],['การจัดการ','materials','◇','คลังวัสดุ'],['การจัดการ','users','♧','บุคลากรและผู้ใช้'],['การจัดการ','rights','☷','สิทธิ์การรักษา'],['การจัดการ','reports','↗','รายงานและสถิติ']],
 'staff'=>[['งานประจำวัน','dashboard','▦','ภาพรวมเจ้าหน้าที่'],['งานประจำวัน','appointments','◷','อนุมัติคำขอจองคิว'],['งานประจำวัน','schedule','▤','ตารางรักษารายวัน'],['งานผู้ป่วย','patients','♙','ผู้ป่วยและซักประวัติ'],['คลังวัสดุ','materials','◇','รับและเบิกวัสดุ'],['รายงาน','reports','↗','รายงานวัสดุและผู้ป่วย']],
 'dentist'=>[['งานประจำวัน','dashboard','▦','ภาพรวมทันตแพทย์'],['งานประจำวัน','schedule','▤','ตารางรักษาของฉัน'],['การรักษา','patients','♙','ประวัติผู้ป่วย'],['การรักษา','treatments','✚','วินิจฉัยและการรักษา'],['การรักษา','material-usage','◇','เบิกใช้วัสดุ'],['รายงาน','service-stats','↗','สถิติการให้บริการ']],
 'patient'=>[['เมนูของฉัน','dashboard','▦','หน้าหลักผู้ป่วย'],['เมนูของฉัน','booking','＋','จองคิวทันตกรรม'],['เมนูของฉัน','appointments','◷','นัดหมายของฉัน'],['ข้อมูลสุขภาพ','history','♡','ประวัติการรักษา'],['ข้อมูลสุขภาพ','notifications','♢','การแจ้งเตือน'],['บัญชี','profile','♙','ข้อมูลส่วนตัว']],
];
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'DentiCare') ?> | DentiCare</title>
    <link rel="stylesheet" href="/assets/css/browser-latest.css?v=14">
    <link rel="stylesheet" href="/assets/css/php-compat.css?v=14">
</head>

<body>
    <?php if ($user): ?>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="/?page=dashboard"><span
                    class="brand-mark">DC</span><span><b>DentiCare</b><small>Clinic Management</small></span></a>
            <nav>
                <?php $lastGroup=''; foreach ($menus[$role] as [$group,$key,$icon,$label]): ?>
                <?php if ($group !== $lastGroup): if ($lastGroup !== '') echo '</div>'; ?><div class="nav-group">
                    <p><?= htmlspecialchars($group) ?></p><?php $lastGroup=$group; endif; ?>
                    <a class="<?= $currentPage===$key?'active':'' ?>"
                        href="/?page=<?= $key ?>"><span><?= $icon ?></span><?= htmlspecialchars($label) ?></a>
                    <?php endforeach; if ($lastGroup !== '') echo '</div>'; ?>
            </nav>
            <div class="sidebar-user"><span class="avatar"><?= $avatarContent ?></span>
                <div><b><?= htmlspecialchars($displayName) ?></b><small><?= $roleLabels[$role] ?></small></div>
            </div>
            <div class="sidebar-help"><span>?</span>
                <div><b>ต้องการความช่วยเหลือ?</b><small>คู่มือการใช้งานระบบ</small></div>
            </div>
        </aside>
        <button class="nav-backdrop" id="navBackdrop" aria-label="ปิดเมนู"></button>
        <main class="workspace">
            <header class="topbar">
                <div class="topbar-title"><button class="icon-button mobile-only" id="menuButton"
                        aria-label="เปิดเมนู">☰</button>
                    <div><span class="eyebrow">ระบบจัดการคลินิกทันตกรรม</span>
                        <h1><?= htmlspecialchars($title ?? 'DentiCare') ?></h1>
                    </div>
                </div>
                <div class="topbar-actions"><label class="search-box"><span>⌕</span><input
                            placeholder="ค้นหาข้อมูล..."></label><button class="icon-button"
                        data-toast="ไม่มีการแจ้งเตือนใหม่">♢<i></i></button>
                    <details class="account-menu">
                        <summary class="profile-button"><span
                                class="avatar"><?= $avatarContent ?></span><span><b><?= htmlspecialchars($displayName) ?></b><small><?= $roleLabels[$role] ?></small></span><span
                                class="menu-caret">⌄</span></summary>
                        <div class="account-dropdown"><small>ประเภทผู้ใช้งาน</small>
                            <p class="role-current"><?= $roleLabels[$role] ?></p><a class="logout-button"
                                href="/?page=logout"><span>↪</span> ออกจากระบบ</a>
                        </div>
                    </details>
                </div>
            </header>
            <div class="page-content">
                <?php if (!empty($_SESSION['flash_success'])): ?><div class="flash-message success">
                    <?= htmlspecialchars((string)$_SESSION['flash_success']) ?></div>
                <?php unset($_SESSION['flash_success']); endif; ?>
                <?php if (!empty($_SESSION['flash_error'])): ?><div class="flash-message error">
                    <?= htmlspecialchars((string)$_SESSION['flash_error']) ?></div>
                <?php unset($_SESSION['flash_error']); endif; ?>
                <?php endif; ?>