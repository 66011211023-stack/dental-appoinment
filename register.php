<?php
use App\Core\Csrf;
$errors=$_SESSION['register_errors']??[];$old=$_SESSION['register_old']??[];
unset($_SESSION['register_errors'],$_SESSION['register_old']);
$value=static fn(string $key):string=>htmlspecialchars((string)($old[$key]??''),ENT_QUOTES,'UTF-8');
?>
<main class="auth-layout register-layout">
  <section class="auth-visual">
    <div class="auth-brand"><span class="brand-mark">DC</span><b>DentiCare</b></div>
    <div class="visual-copy"><span class="pill">สำหรับผู้ป่วยใหม่</span><h1>เริ่มดูแล<br><em>รอยยิ้มของคุณ</em></h1><p>สมัครสมาชิกเพื่อจองคิว ตรวจสอบนัดหมาย และดูประวัติการรักษา</p></div>
    <small>© 2026 DentiCare Clinic Management System</small>
  </section>
  <section class="auth-panel register-panel"><div class="auth-card register-card"><a class="back-button" href="/?page=login">← กลับหน้าเข้าสู่ระบบ</a><div class="auth-heading"><span class="eyebrow">บัญชีผู้ป่วย</span><h2>สมัครสมาชิก</h2><p>ระบบจะสร้างหมายเลข HN ให้อัตโนมัติ</p></div>
  <?php if($errors):?><div class="register-errors"><b>กรุณาตรวจสอบข้อมูล</b><ul><?php foreach($errors as $error):?><li><?=htmlspecialchars($error)?></li><?php endforeach?></ul></div><?php endif?>
  <form method="post" action="/?page=register" class="form-stack"><input type="hidden" name="action" value="register"><?=Csrf::field()?>
    <div class="register-grid"><label>ชื่อ<input name="first_name" value="<?=$value('first_name')?>" required></label><label>นามสกุล<input name="last_name" value="<?=$value('last_name')?>" required></label><label class="full">อีเมล <small>(ใช้สำหรับเข้าสู่ระบบ)</small><input type="text" inputmode="email" autocomplete="email" name="email" value="<?=$value('email')?>" placeholder="example@gmail.com" required></label><label>รหัสผ่าน<span class="password-input-wrap"><input type="password" name="password" minlength="4" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="แสดงรหัสผ่าน" title="แสดงรหัสผ่าน"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.7"/><path class="eye-slash" d="M4 4l16 16"/></svg></button></span></label><label>ยืนยันรหัสผ่าน<span class="password-input-wrap"><input type="password" name="password_confirmation" minlength="4" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="แสดงรหัสผ่าน" title="แสดงรหัสผ่าน"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.7"/><path class="eye-slash" d="M4 4l16 16"/></svg></button></span></label><label>เลขบัตรประชาชน<input name="citizen_id" value="<?=$value('citizen_id')?>" inputmode="numeric" maxlength="13"></label><label>วันเกิด<input type="date" name="birth_date" value="<?=$value('birth_date')?>"></label><label>เบอร์โทรศัพท์<input name="phone" value="<?=$value('phone')?>"></label><label>สิทธิ์การรักษา<select name="treatment_right"><option value="">-- เลือกสิทธิ์ --</option><?php foreach(['บัตรทอง','ประกันสังคม','สิทธิข้าราชการ','ชำระเงินเอง'] as $right):?><option <?=$value('treatment_right')===htmlspecialchars($right)?'selected':''?>><?=$right?></option><?php endforeach?></select></label><label class="full">ที่อยู่<textarea name="address"><?=$value('address')?></textarea></label></div>
    <button class="primary-button" type="submit">สมัครสมาชิก <span>→</span></button>
  </form></div></section>
</main>
