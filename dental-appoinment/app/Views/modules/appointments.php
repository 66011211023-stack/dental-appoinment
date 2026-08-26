<?php
use App\Core\Auth; use App\Core\Csrf; use App\Core\Database;
$user=Auth::user();$role=$user['role'];$where='1=1';$params=[];
if($role==='patient'){$pid=(int)Database::query('SELECT id FROM patients WHERE user_id=?',[$user['id']])->fetchColumn();$where='a.patient_id=?';$params=[$pid];}
if($role==='dentist'){$did=(int)Database::query('SELECT id FROM dentists WHERE user_id=?',[$user['id']])->fetchColumn();$where='a.dentist_id=?';$params=[$did];}
$items=Database::query("SELECT a.*,p.full_name patient_name,u.full_name dentist_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN dentists d ON d.id=a.dentist_id LEFT JOIN users u ON u.id=d.user_id WHERE $where ORDER BY a.appointment_date DESC,a.appointment_time DESC",$params)->fetchAll();
$patients=Database::query('SELECT id,hn,full_name FROM patients ORDER BY full_name')->fetchAll();$dentists=Database::query('SELECT d.id,u.full_name FROM dentists d JOIN users u ON u.id=d.user_id ORDER BY u.full_name')->fetchAll();
$labels=['pending'=>'รออนุมัติ','approved'=>'ยืนยันแล้ว','completed'=>'เสร็จสิ้น','cancelled'=>'ยกเลิก'];
page_header($role==='patient'?'นัดหมายของฉัน':'คำขอและนัดหมาย','ข้อมูลนัดหมายจากฐานข้อมูล'); ?>
<?php if(in_array($role,['admin','staff'],true)):?><details class="form-panel crud-form">
    <summary>＋ สร้างนัดหมาย</summary>
    <form method="post"><input type="hidden" name="action" value="appointment_save"><?=Csrf::field()?><div
            class="form-grid"><label>ผู้ป่วย<select name="patient_id" required><?php foreach($patients as $p):?><option
                        value="<?=$p['id']?>"><?=htmlspecialchars($p['hn'].' · '.$p['full_name'])?></option>
                    <?php endforeach?></select></label><label>ทันตแพทย์<select name="dentist_id"
                    required><?php foreach($dentists as $d):?><option value="<?=$d['id']?>">
                        <?=htmlspecialchars($d['full_name'])?></option>
                    <?php endforeach?></select></label><label>วันที่<input type="date" name="appointment_date"
                    min="<?=date('Y-m-d')?>" required></label><label>เวลา<input type="time" name="appointment_time"
                    required></label><label>บริการ<input name="service" required></label><label>หมายเหตุ<input
                    name="note"></label></div><button class="primary-button fit">บันทึก</button></form>
</details><?php endif?>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>วันที่/เวลา</th>
                    <th>ผู้ป่วย</th>
                    <th>ทันตแพทย์</th>
                    <th>บริการ</th>
                    <th>สถานะ</th>
                    <th>ดำเนินการ</th>
                </tr>
            </thead>
            <tbody><?php foreach($items as $a):?><tr>
                    <td><?=htmlspecialchars($a['appointment_date'].' '.substr($a['appointment_time'],0,5))?></td>
                    <td><?=htmlspecialchars($a['patient_name'])?></td>
                    <td><?=htmlspecialchars($a['dentist_name']??'-')?></td>
                    <td><?=htmlspecialchars($a['service'])?></td>
                    <td><span
                            class="status <?=status_class($labels[$a['status']]??$a['status'])?>"><?=htmlspecialchars($labels[$a['status']]??$a['status'])?></span>
                    </td>
                    <td>
                        <?php if($role==='patient'&&in_array($a['status'],['pending','approved'],true)&&$a['appointment_date']>=date('Y-m-d')):?>
                        <form method="post" class="inline-form" onsubmit="return confirm('ยืนยันการยกเลิกนัดหมายนี้?')">
                            <input type="hidden" name="action" value="appointment_cancel_patient"><input type="hidden"
                                name="id" value="<?=$a['id']?>"><?=Csrf::field()?><button
                                class="outline-button">ยกเลิกนัด</button></form>
                        <?php elseif($role==='dentist'&&$a['status']==='approved'):?><form method="post"
                            class="inline-form"><input type="hidden" name="action" value="appointment_status"><input
                                type="hidden" name="id" value="<?=$a['id']?>"><input type="hidden" name="status"
                                value="completed"><?=Csrf::field()?><button class="outline-button">เสร็จสิ้น</button>
                        </form>
                        <?php elseif(in_array($role,['admin','staff'],true)):?><form method="post" class="inline-form">
                            <input type="hidden" name="action" value="appointment_status"><input type="hidden" name="id"
                                value="<?=$a['id']?>"><?=Csrf::field()?><select
                                name="status"><?php foreach($labels as $v=>$l):?><option value="<?=$v?>"
                                    <?=$a['status']===$v?'selected':''?>><?=$l?></option>
                                <?php endforeach?></select><button class="outline-button">บันทึก</button></form>
                        <?php else:?>—<?php endif?></td>
                </tr><?php endforeach?><tr <?php if($items) echo 'hidden';?>>
                    <td colspan="6">ยังไม่มีนัดหมาย</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>