<?php
use App\Core\Auth;
use App\Core\Csrf;
$u=Auth::user();
page_header('ข้อมูลส่วนตัว','แก้ไขรูปโปรไฟล์ ชื่อ อีเมล และรหัสผ่าน');
preg_match('/^./u',(string)$u['full_name'],$avatarLetter);
$profileImage=trim((string)($u['profile_image']??''));
?>
<section class="form-panel profile-panel">
    <div class="profile-hero">
        <span class="avatar large" id="profilePreview"><?php if($profileImage!==''): ?><img
                src="<?=htmlspecialchars($profileImage)?>"
                alt="รูปโปรไฟล์"><?php else: ?><?=htmlspecialchars($avatarLetter[0]??'ผ')?><?php endif; ?></span>
        <div>
            <h3><?=htmlspecialchars((string)$u['full_name'])?></h3>
            <p><?=htmlspecialchars($u['username'].' · '.$u['role'])?></p>
        </div>
    </div>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="profile_save"><?=Csrf::field()?>
        <div class="profile-upload">
            <label>รูปโปรไฟล์ <small>(ไม่บังคับ)</small><input id="profileImageInput" type="file" name="profile_image"
                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"><span>รองรับ JPG, PNG และ WEBP
                    ขนาดไม่เกิน 2 MB</span></label>
            <?php if($profileImage!==''): ?><label class="check remove-profile"><input type="checkbox"
                    name="remove_profile_image" value="1"> ลบรูปปัจจุบัน</label><?php endif; ?>
        </div>
        <div class="form-grid"><label>ชื่อ-นามสกุล<input name="full_name"
                    value="<?=htmlspecialchars((string)$u['full_name'])?>" required></label><label>อีเมล<input
                    type="email" name="email" value="<?=htmlspecialchars((string)($u['email']??''))?>"></label><label
                class="full">รหัสผ่านใหม่ <small>(เว้นว่างหากไม่เปลี่ยน)</small><input type="password" name="password"
                    minlength="4" autocomplete="new-password"></label></div>
        <div class="form-actions"><button class="primary-button fit">บันทึกการเปลี่ยนแปลง</button></div>
    </form>
</section>