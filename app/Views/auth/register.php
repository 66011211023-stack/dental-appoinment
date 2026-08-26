<?php
use App\Core\Csrf;

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<main class="auth-layout">
    <section class="auth-visual">
        <div class="auth-brand"><span class="brand-mark">DC</span><b>DentiCare</b></div>
        <div class="visual-copy">
            <span class="pill">ระบบบริหารจัดการทันตกรรมแบบครบวงจร</span>
            <h1>เริ่มต้นดูแลสุขภาพฟัน<br><em>สมัครสมาชิกผู้ป่วย</em></h1>
            <p>กรอกข้อมูลเพื่อเริ่มต้นการจองคิวนัดหมายและดูประวัติการรักษา</p>
        </div>
        <small>© 2026 DentiCare Clinic Management System</small>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <div class="auth-heading">
                <span class="eyebrow">ลงทะเบียนใหม่</span>
                <h2>สมัครสมาชิก</h2>
                <p>สำหรับผู้ป่วยใหม่ที่ต้องการใช้บริการคลินิก</p>
            </div>
            <?php if ($error): ?><p class="login-error"><?= htmlspecialchars((string)$error) ?></p><?php endif; ?>
            
            <form method="post" action="/?page=register" class="form-stack">
                <label>ชื่อ - นามสกุล
                    <input name="full_name" required placeholder="นายสมชาย ใจดี">
                </label>
                <label>อีเมล (ใช้เป็นชื่อเข้าสู่ระบบ)
                    <input type="email" name="email" required placeholder="example@gmail.com">
                </label>
                <label>เบอร์โทรศัพท์
                    <input type="tel" name="phone" placeholder="0812345678">
                </label>
                <label>รหัสผ่าน
                    <input name="password" required type="password" minlength="4" placeholder="กำหนดรหัสผ่าน">
                </label>
                <label>ยืนยันรหัสผ่าน
                    <input name="password_confirmation" required type="password" minlength="4" placeholder="กรอกรหัสผ่านอีกครั้ง">
                </label>
                
                <button class="primary-button" type="submit">ยืนยันการสมัครสมาชิก <span>→</span></button>
                <p class="auth-register-link">มีบัญชีผู้ใช้แล้ว? <a href="/?page=login">เข้าสู่ระบบ</a></p>
            </form>
        </div>
    </section>
</main>
