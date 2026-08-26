<?php
use App\Core\Csrf;
use App\Core\Database;
$slots=Database::query("SELECT ds.id,ds.available_date,ds.start_time,ds.end_time,u.full_name,d.specialty FROM dentist_schedules ds JOIN dentists d ON d.id=ds.dentist_id JOIN users u ON u.id=d.user_id WHERE ds.status='available' AND ds.available_date>=CURDATE() AND u.is_active=1 ORDER BY ds.available_date,ds.start_time,u.full_name")->fetchAll();
page_header('จองคิวทันตกรรม','เลือกบริการและช่วงเวลาที่ยังว่าง ระบบจะส่งคำขอให้เจ้าหน้าที่อนุมัติ'); ?>
<section class="form-panel">
    <form method="post"><input type="hidden" name="action" value="appointment_save"><?=Csrf::field()?><div
            class="form-grid">
            <label>บริการ<select name="service" required>
                    <option>ตรวจสุขภาพช่องปาก</option>
                    <option>ขูดหินปูน</option>
                    <option>อุดฟัน</option>
                    <option>ถอนฟัน</option>
                    <option>รักษารากฟัน</option>
                    <option>ปรึกษาทันตแพทย์</option>
                </select></label>
            <label>ช่วงเวลาที่ว่าง<select name="schedule_id" required <?php if(!$slots) echo 'disabled';?>>
                    <option value="">เลือกวัน เวลา และทันตแพทย์</option><?php foreach($slots as $s):?><option
                        value="<?=$s['id']?>">
                        <?=htmlspecialchars($s['available_date'].' · '.substr($s['start_time'],0,5).'-'.substr($s['end_time'],0,5).' น. · '.$s['full_name'].' · '.($s['specialty']?:'ทันตกรรมทั่วไป'))?>
                    </option><?php endforeach?>
                </select></label>
            <label class="full">หมายเหตุ<textarea name="note"
                    placeholder="ระบุอาการหรือข้อมูลเพิ่มเติม (ถ้ามี)"></textarea></label>
        </div>
        <?php if(!$slots):?><p class="empty-state">ยังไม่มีช่วงเวลาว่าง กรุณาติดต่อเจ้าหน้าที่หรือรอการเพิ่มตารางใหม่
        </p><?php endif?><div class="form-actions"><button class="primary-button fit"
                <?php if(!$slots) echo 'disabled';?>>ส่งคำขอจองคิว</button></div>
    </form>
</section>