<?php use App\Core\Auth;use App\Core\Database;$u=Auth::user();$pid=(int)Database::query('SELECT id FROM patients WHERE user_id=?',[$u['id']])->fetchColumn();
$items=$pid?Database::query
('SELECT t.*,a.appointment_date,a.service,du.full_name dentist_name FROM treatments t JOIN appointments a ON a.id=t.appointment_id JOIN dentists d ON d.id=a.dentist_id LEFT JOIN users du ON du.id=d.user_id WHERE a.patient_id=? ORDER BY a.appointment_date DESC',[$pid])->fetchAll():[];$total=array_sum(array_column($items,'cost'));
page_header('ประวัติการรักษาของฉัน','ผลวินิจฉัย การรักษา และค่าใช้จ่าย');?>
<div class="stats-grid three">
    <?php stat_card('♡',(string)count($items),'ครั้งที่รับบริการ','ทั้งหมด');stat_card('฿',number_format((float)$total,2),'ค่าใช้จ่ายรวม','บาท','green');?>
</div>
<section class="panel">
    <?php data_table(['วันที่','ทันตแพทย์','วินิจฉัย','การรักษา','ตำแหน่งฟัน','ค่าใช้จ่าย'],array_map(fn($r)=>[$r['appointment_date'],$r['dentist_name'],$r['diagnosis'],$r['treatment_detail'],$r['tooth_position'],number_format((float)$r['cost'],2)],$items));?>
</section>