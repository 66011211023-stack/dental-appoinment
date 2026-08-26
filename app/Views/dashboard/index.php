<?php
use App\Core\Auth;use App\Core\Database;
$u=Auth::user();$role=$u['role'];
$today=(int)Database::query("SELECT COUNT(*) FROM appointments WHERE appointment_date=CURDATE() AND status<>'cancelled'")->fetchColumn();
$completed=(int)Database::query("SELECT COUNT(*) FROM appointments WHERE appointment_date=CURDATE() AND status='completed'")->fetchColumn();
$pending=(int)Database::query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$revenue=(float)Database::query('SELECT COALESCE(SUM(t.cost),0) FROM treatments t JOIN appointments a ON a.id=t.appointment_id WHERE a.appointment_date=CURDATE()')->fetchColumn();
$appointments=[];
$rows=array_map(fn($a)=>[substr($a['appointment_time'],0,5),$a['patient_name'],$a['dentist_name']??'-',$a['service'],['pending'=>'รออนุมัติ','approved'=>'ยืนยันแล้ว','completed'=>'เสร็จสิ้น','cancelled'=>'ยกเลิก'][$a['status']]??$a['status']],$appointments);
page_header('สวัสดี '.$u['full_name'],'ภาพรวมข้อมูลล่าสุดจากฐานข้อมูล'); ?>
<div class="stats-grid"><?php
if($role==='patient'){
 $pid=(int)Database::query('SELECT id FROM patients WHERE user_id=?',[$u['id']])->fetchColumn();
 $future=$pid?(int)Database::query("SELECT COUNT(*) FROM appointments WHERE patient_id=? AND appointment_date>=CURDATE() AND status<>'cancelled'",[$pid])->fetchColumn():0;
 $history=$pid?(int)Database::query('SELECT COUNT(*) FROM treatments t JOIN appointments a ON a.id=t.appointment_id WHERE a.patient_id=?',[$pid])->fetchColumn():0;
 $notice=(int)Database::query('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0',[$u['id']])->fetchColumn();
 stat_card('◷',(string)$future,'นัดหมายที่กำลังจะถึง','รายการ');stat_card('♡',(string)$history,'ประวัติการรักษา','รายการ','purple');
 stat_card('♢',(string)$notice,'การแจ้งเตือนใหม่','รายการ','orange');
}elseif($role==='dentist'){
 $did=(int)Database::query('SELECT id FROM dentists WHERE user_id=?',[$u['id']])->fetchColumn();
 $mine=(int)Database::query('SELECT COUNT(*) FROM appointments WHERE dentist_id=? AND appointment_date=CURDATE()',[$did])->fetchColumn();
 $month=(int)Database::query('SELECT COUNT(*) FROM appointments WHERE dentist_id=? AND YEAR(appointment_date)=YEAR(CURDATE()) AND MONTH(appointment_date)=MONTH(CURDATE())',[$did])->fetchColumn();
 stat_card('◷',(string)$mine,'ผู้ป่วยวันนี้','รายการ');stat_card('✓',(string)$completed,'รักษาแล้ววันนี้','รายการ','green');stat_card('↗',(string)$month,'บริการเดือนนี้','รายการ','purple');
}else{stat_card('◷',(string)$today,'นัดหมายวันนี้','รายการ');stat_card('✓',(string)$completed,'รักษาแล้ว','รายการ','green');stat_card('⌛',(string)$pending,'รออนุมัติ','รายการ','orange');
stat_card('฿',number_format($revenue,2),'รายรับวันนี้','บาท','purple');}
?></div>
<?php if($role==='patient'):?><section class="panel">
    <div class="quick-grid patient-quick"><a href="/?page=booking"><span>＋</span>จองคิว</a><a
            href="/?page=appointments"><span>◷</span>นัดหมาย</a><a
            href="/?page=history"><span>♡</span>ประวัติรักษา</a><a href="/?page=profile"><span>♙</span>ข้อมูลส่วนตัว</a>
    </div>
</section><?php else:?><section class="panel">
    <div class="panel-header">
        <div>
            <h3>นัดหมายล่าสุด</h3>
            <p>เรียงตามวันและเวลา</p>
        </div><a href="/?page=appointments">ดูทั้งหมด →</a>
    </div><?php data_table(['เวลา','ผู้ป่วย','ทันตแพทย์','บริการ','สถานะ'],$rows,4);?>
</section><?php endif?>
