<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
$current=Auth::user();
$dentistWhere=$current['role']==='dentist'?' AND u.id='.(int)$current['id']:'';
$dentists=Database::query('SELECT d.id,u.full_name FROM dentists d JOIN users u ON u.id=d.user_id WHERE u.is_active=1'.$dentistWhere.' ORDER BY u.full_name')->fetchAll();
$rows=Database::query('SELECT ds.*,u.full_name FROM dentist_schedules ds JOIN dentists d ON d.id=ds.dentist_id JOIN users u ON u.id=d.user_id'.($current['role']==='dentist'?' WHERE u.id='.(int)$current['id']:'').' ORDER BY ds.available_date DESC,ds.start_time')->fetchAll();
page_header('ตารางเวลาทันตแพทย์','เพิ่มและตรวจสอบช่วงเวลาปฏิบัติงาน'); ?>
<details class="form-panel crud-form">
    <summary>＋ เพิ่มเวลาว่าง</summary>
    <form method="post"><input type="hidden" name="action" value="schedule_save"><?=Csrf::field()?><div
            class="form-grid"><label>ทันตแพทย์<select name="dentist_id" required><?php foreach($dentists as $d):?>
                    <option value="<?=$d['id']?>"><?=htmlspecialchars($d['full_name'])?></option>
                    <?php endforeach?></select></label><label>วันที่<input type="date" name="available_date"
                    min="<?=date('Y-m-d')?>" required></label><label>เริ่ม<input type="time" name="start_time"
                    required></label><label>สิ้นสุด<input type="time" name="end_time"
                    required></label><label>สถานะ<select name="status">
                    <option value="available">ว่าง</option>
                    <option value="reserved">มีนัด</option>
                    <option value="unavailable">ไม่ว่าง</option>
                </select></label></div><button class="primary-button fit">บันทึก</button></form>
</details>
<section class="panel">
    <?php $sl=['available'=>'ว่าง','reserved'=>'มีนัด','unavailable'=>'ไม่ว่าง'];data_table(['วันที่','ทันตแพทย์','เริ่ม','สิ้นสุด','สถานะ'],array_map(fn($r)=>[$r['available_date'],$r['full_name'],substr($r['start_time'],0,5),substr($r['end_time'],0,5),$sl[$r['status']]??$r['status']],$rows),4);?>
</section>