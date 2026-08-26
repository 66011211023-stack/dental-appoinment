<?php
/** หน้าเข้าสู่ระบบและตั้งรหัสผ่านใหม่ */
use App\Core\Csrf;

$error = $_SESSION['error'] ?? null;
$forgotError = $_SESSION['forgot_error'] ?? null;
$resetError = $_SESSION['reset_error'] ?? null;
$screen = $_SESSION['auth_screen'] ?? 'login';
unset($_SESSION['error'],$_SESSION['forgot_error'],$_SESSION['reset_error'],$_SESSION['auth_screen']);

$eyeIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.7"/><path class="eye-slash" d="M4 4l16 16"/></svg>';
?>
<main class="auth-layout">
    <section class="auth-visual">
        <div class="auth-brand"><span class="brand-mark">DC</span><b>DentiCare</b></div>
        <div class="visual-copy"><span class="pill">ระบบบริหารจัดการทันตกรรมแบบครบวงจร</span>
            <h1>ทุกการดูแล<br><em>เริ่มต้นจากข้อมูลที่ดี</em></h1>
            <p>จัดการนัดหมาย ผู้ป่วย การรักษา บุคลากร และคลังวัสดุในพื้นที่ทำงานเดียว</p>
        </div>
        <small>© 2026 DentiCare Clinic Management System</small>
    </section>

    <section class="auth-panel">
        <div class="auth-card auth-screen" data-auth="login" <?=$screen==='login'?'':'hidden'?>>
            <span class="mobile-brand"><span class="brand-mark">DC</span>DentiCare</span>
            <div class="auth-heading"><span class="eyebrow">ยินดีต้อนรับ</span>
                <h2>เข้าสู่ระบบ</h2>
                <p>ผู้ป่วยใช้อีเมลที่สมัครไว้ ส่วนเจ้าหน้าที่ใช้ชื่อผู้ใช้</p>
            </div>
            <?php if($error): ?><p class="login-error"><?=htmlspecialchars((string)$error)?></p><?php endif; ?>
            <form method="post" action="/?page=login" class="form-stack" autocomplete="off">
                <label>อีเมล หรือชื่อผู้ใช้เจ้าหน้าที่<input name="username" value="" required autocomplete="off"
                        placeholder="example@gmail.com หรือ admin"></label>
                <label>รหัสผ่าน<span class="password-input-wrap"><input name="password" value="" required
                            type="password" autocomplete="new-password" placeholder="กรอกรหัสผ่าน">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="แสดงรหัสผ่าน"
                            title="แสดงรหัสผ่าน"><?=$eyeIcon?></button>
                    </span>
                </label>
                <div class="form-row"><span></span><button type="button" class="text-button auth-go"
                        data-target="forgot">ลืมรหัสผ่าน?</button></div>
                <button class="primary-button" type="submit">เข้าสู่ระบบ <span>→</span></button>
                <p class="auth-register-link">ยังไม่มีบัญชี? <a href="/?page=register">สมัครสมาชิกผู้ป่วย</a></p>
            </form>
        </div>

        <div class="auth-card compact auth-screen" data-auth="forgot" <?=$screen==='forgot'?'':'hidden'?>>
            <button type="button" class="back-button auth-go" data-target="login">← กลับหน้าเข้าสู่ระบบ</button>
            <div class="auth-icon">✉</div>
            <div class="auth-heading">
                <h2>ลืมรหัสผ่าน?</h2>
                <p>กรอกอีเมลที่ใช้สมัครสมาชิกผู้ป่วย</p>
            </div>
            <?php if($forgotError): ?><p class="login-error"><?=htmlspecialchars((string)$forgotError)?></p>
            <?php endif; ?>
            <form method="post" action="/?page=login" class="form-stack">
                <input type="hidden" name="action" value="forgot_lookup"><?=Csrf::field()?>
                <label>อีเมล<input type="text" inputmode="email" name="email" required
                        placeholder="example@gmail.com"></label>
                <button class="primary-button" type="submit">ตรวจสอบบัญชี <span>→</span></button>
            </form>
        </div>

        <div class="auth-card compact auth-screen" data-auth="new-password" <?=$screen==='new-password'?'':'hidden'?>>
            <button type="button" class="back-button auth-go" data-target="forgot">← ย้อนกลับ</button>
            <div class="auth-heading"><span class="eyebrow">ขั้นตอนที่ 2 จาก 2</span>
                <h2>ตั้งรหัสผ่านใหม่</h2>
                <p>รหัสผ่านควรมีอย่างน้อย 4 ตัวอักษรหรือตัวเลข</p>
            </div>
            <?php if($resetError): ?><p class="login-error"><?=htmlspecialchars((string)$resetError)?></p>
            <?php endif; ?>
            <form method="post" action="/?page=login" class="form-stack">
                <input type="hidden" name="action" value="reset_password"><?=Csrf::field()?>
                <label>รหัสผ่านใหม่<span class="password-input-wrap"><input required minlength="4" name="password"
                            type="password" autocomplete="new-password" placeholder="รหัสผ่านใหม่">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="แสดงรหัสผ่าน"
                            title="แสดงรหัสผ่าน"><?=$eyeIcon?></button></span></label>
                <label>ยืนยันรหัสผ่าน<span class="password-input-wrap"><input required minlength="4"
                            name="password_confirmation" type="password" autocomplete="new-password"
                            placeholder="ยืนยันรหัสผ่านใหม่"><button type="button" class="password-toggle"
                            data-password-toggle aria-label="แสดงรหัสผ่าน"
                            title="แสดงรหัสผ่าน"><?=$eyeIcon?></button></span></label>
                <button class="primary-button" type="submit">บันทึกรหัสผ่าน <span>✓</span></button>
            </form>
        </div>

        <div class="auth-card compact success-card auth-screen" data-auth="success"
            <?=$screen==='success'?'':'hidden'?>>
            <div class="success-check">✓</div>
            <div class="auth-heading">
                <h2>เปลี่ยนรหัสผ่านสำเร็จ</h2>
                <p>รหัสผ่านใหม่ถูกบันทึกลงฐานข้อมูลแล้ว</p>
            </div>
            <button type="button" class="primary-button auth-go" data-target="login">กลับสู่หน้าเข้าสู่ระบบ
                <span>→</span></button>
        </div>
    </section>
</main>
