<?php
use App\Core\Csrf;

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$eyeIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.7"/><path class="eye-slash" d="M4 4l16 16"/></svg>';
?>
<main class="auth-layout">
    <section class="auth-visual">
        <div class="auth-brand"><span class="brand-mark">DC</span><b>DentiCare</b></div>
        <div class="visual-copy">
            <span class="pill">สำหรับผู้ป่วยใหม่</span>
            <h1>เริ่มดูแล<br><em>รอยยิ้มของคุณ</em></h1>
            <p>สมัครสมาชิกเพื่อจองคิว ตรวจสอบนัดหมาย และดูประวัติการรักษา</p>
        </div>
        <small>© 2026 DentiCare Clinic Management System</small>
    </section>

    <section class="auth-panel">
        <div class="auth-card wide-card">
            <div class="auth-heading">
                <span class="eyebrow">บัญชีผู้ป่วย</span>
                <h2>สมัครสมาชิก</h2>
                <p>ระบบจะสร้างหมายเลข HN ให้อัตโนมัติ</p>
            </div>
            <?php if ($error): ?><p class="login-error"><?= htmlspecialchars((string)$error) ?></p><?php endif; ?>
            
            <form method="post" action="/?page=register" class="form-grid">
                <?= Csrf::field() ?>
                
                <div class="form-row-2">
                    <label>ชื่อ
                        <input name="first_name" required placeholder="กรอกชื่อ">
                    </label>
                    <label>นามสกุล
                        <input name="last_name" required placeholder="กรอกนามสกุล">
                    </label>
                </div>

                <label class="full-width">อีเมล (ใช้สำหรับเข้าสู่ระบบ)
                    <input type="email" name="email" required placeholder="example@gmail.com">
                </label>

                <div class="form-row-2">
                    <label>รหัสผ่าน
                        <span class="password-input-wrap">
                            <input name="password" required type="password" minlength="4" placeholder="กรอกรหัสผ่าน">
                            <button type="button" class="password-toggle" data-password-toggle aria-label="แสดงรหัสผ่าน"><?=$eyeIcon?></button>
                        </span>
                    </label>
                    <label>ยืนยันรหัสผ่าน
                        <span class="password-input-wrap">
                            <input name="password_confirmation" required type="password" minlength="4" placeholder="กรอกรหัสผ่านอีกครั้ง">
                            <button type="button" class="password-toggle" data-password-toggle aria-label="แสดงรหัสผ่าน"><?=$eyeIcon?></button>
                        </span>
                    </label>
                </div>

                <div class="form-row-2">
                    <label>เลขบัตรประชาชน
                        <input name="national_id" maxlength="13" placeholder="เลข 13 หลัก">
                    </label>
                    <label>วันเกิด
                        <input type="date" name="birth_date" placeholder="วว/ดด/ปปปป">
                    </label>
                </div>

                <div class="form-row-2">
                    <label>เบอร์โทรศัพท์
                        <input type="tel" name="phone" required placeholder="0812345678">
                    </label>
                    <label>สิทธิ์การรักษา
                        <select name="coverage_type">
                            <option value="">-- เลือกสิทธิ์ --</option>
                            <option value="จ่ายตรง/เงินสด">จ่ายตรง / เงินสด</option>
                            <option value="ประกันสังคม">ประกันสังคม</option>
                            <option value="บัตรทอง">บัตรทอง / 30 บาท</option>
                            <option value="ข้าราชการ">สวัสดิการข้าราชการ</option>
                            <option value="ประกันสุขภาพเอกชน">ประกันสุขภาพเอกชน</option>
                        </select>
                    </label>
                </div>

                <label class="full-width">ที่อยู่
                    <textarea name="address" rows="2" placeholder="กรอกที่อยู่ปัจจุบัน"></textarea>
                </label>
                
                <button class="primary-button full-width" type="submit">สมัครสมาชิก <span>→</span></button>
                <p class="auth-register-link full-width" style="text-align:center; margin-top:12px;">มีบัญชีผู้ใช้แล้ว? <a href="/?page=login">เข้าสู่ระบบ</a></p>
            </form>
        </div>
    </section>
</main>
