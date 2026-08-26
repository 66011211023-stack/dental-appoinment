<?php use App\Core\Csrf;use App\Core\Database;
$users=Database::query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
$counts=['admin'=>0,'staff'=>0,'dentist'=>0,'patient'=>0];foreach($users as $u)$counts[$u['role']]++;page_header('บุคลากรและผู้ใช้งาน','เพิ่มบัญชี กำหนดบทบาท และเปิดหรือปิดการใช้งาน');?>
<div class="role-summary">
    <?php foreach(['admin'=>'ผู้ดูแล','staff'=>'เจ้าหน้าที่','dentist'=>'ทันตแพทย์','patient'=>'ผู้ป่วย'] as $k=>$v):?>
    <div><span><?=$counts[$k]?></span>
        <p><b><?=$v?></b><small><?=$k?></small></p>
    </div><?php endforeach?></div>
<details class="form-panel crud-form">
    <summary>＋ เพิ่มบัญชีผู้ใช้</summary>
    <form method="post"><input type="hidden" name="action" value="user_save"><?=Csrf::field()?><div class="form-grid">
            <label>Username<input name="username" required></label><label>Password<input type="password" name="password"
                    required minlength="4"></label><label>ชื่อ-นามสกุล<input name="full_name"
                    required></label><label>อีเมล<input type="email" name="email"></label><label>บทบาท<select
                    name="role">
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="dentist">Dentist</option>
                    <option value="patient">Patient</option>
                </select></label><label>HN <small>(กรอกเมื่อเป็น Patient)</small><input
                    name="hn"></label><label>เลขใบอนุญาต <small>(กรอกเมื่อเป็น Dentist)</small><input
                    name="license_no"></label><label>สาขาเฉพาะทาง<input name="specialty"></label><label>โทรศัพท์<input
                    name="phone"></label></div><button class="primary-button fit">เพิ่มบัญชี</button></form>
</details>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>ชื่อ</th>
                    <th>บทบาท</th>
                    <th>สถานะ</th>
                    <th>ดำเนินการ</th>
                </tr>
            </thead>
            <tbody><?php foreach($users as $u):?><tr>
                    <td><?=$u['id']?></td>
                    <td><?=htmlspecialchars($u['username'])?></td>
                    <td><?=htmlspecialchars($u['full_name'])?></td>
                    <td><?=htmlspecialchars($u['role'])?></td>
                    <td><?=$u['is_active']?'ใช้งาน':'พักใช้งาน'?></td>
                    <td>
                        <form method="post"><input type="hidden" name="action" value="user_toggle"><input type="hidden"
                                name="id" value="<?=$u['id']?>"><?=Csrf::field()?><button
                                class="outline-button"><?=$u['is_active']?'ปิดบัญชี':'เปิดบัญชี'?></button></form>
                    </td>
                </tr><?php endforeach?></tbody>
        </table>
    </div>
</section>