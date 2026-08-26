<?php use App\Core\Csrf; use App\Core\Database;
$materials=Database::query('SELECT * FROM materials ORDER BY material_name')->fetchAll();
$low=0;$value=0;foreach($materials as $m){$low+=(int)($m['quantity']<=$m['reorder_level']);
$value+=(float)$m['quantity']*(float)$m['price'];}
page_header('คลังวัสดุทันตกรรม','รับเข้า เบิกใช้ และตรวจจำนวนคงเหลือ');?><div class="stats-grid three">
    <?php stat_card('◇',(string)count($materials),'รายการวัสดุ','ทั้งหมด');
    stat_card('!',(string)$low,'ต่ำกว่าจุดสั่งซื้อ','ต้องตรวจสอบ','orange');
    stat_card('฿',number_format($value,2),'มูลค่าคงคลัง','บาท','green');?>
</div>
<details class="form-panel crud-form">
    <summary>＋ เพิ่มวัสดุใหม่</summary>
    <form method="post"><input type="hidden" name="action" value="material_save"><?=Csrf::field()?><div
            class="form-grid"><label>รหัส<input name="material_code" required></label><label>ชื่อวัสดุ<input
                    name="material_name" required></label><label>หน่วย<input name="unit"
                    required></label><label>จำนวนเริ่มต้น<input type="number" name="quantity" min="0"
                    value="0"></label><label>จุดสั่งซื้อ<input type="number" name="reorder_level" min="0"
                    value="5"></label><label>ราคา<input type="number" step="0.01" name="price"
                    min="0"></label><label>วันหมดอายุ<input type="date" name="expiry_date"></label></div><button
            class="primary-button fit">บันทึก</button></form>
</details>
<details class="form-panel crud-form">
    <summary>⇄ รับเข้า / เบิกวัสดุ</summary>
    <form method="post"><input type="hidden" name="action" value="material_transaction"><input type="hidden"
            name="return_page" value="materials"><?=Csrf::field()?><div class="form-grid"><label>วัสดุ<select
                    name="material_id"><?php foreach($materials as $m):?><option value="<?=$m['id']?>">
                        <?=htmlspecialchars($m['material_code'].' · '.$m['material_name'])?></option>
                    <?php endforeach?></select></label><label>ประเภท<select name="transaction_type">
                    <option value="receive">รับเข้า</option>
                    <option value="issue">เบิกใช้</option>
                </select></label><label>จำนวน<input type="number" min="1" name="quantity"
                    required></label><label>หมายเหตุ<input name="note"></label></div><button
            class="primary-button fit">บันทึกรายการ</button></form>
</details>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ</th>
                    <th>หน่วย</th>
                    <th>คงเหลือ</th>
                    <th>จุดสั่งซื้อ</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($materials as $m):$status=$m['quantity']<=$m['reorder_level']?'ต่ำกว่ากำหนด':'พร้อมใช้';?>
                <tr>
                    <td><?=$m['material_code']?></td>
                    <td><?=htmlspecialchars($m['material_name'])?></td>
                    <td><?=htmlspecialchars($m['unit'])?></td>
                    <td><?=$m['quantity']?></td>
                    <td><?=$m['reorder_level']?></td>
                    <td><?=number_format((float)$m['price'],2)?></td>
                    <td><span class="status <?=status_class($status)?>"><?=$status?></span></td>
                </tr><?php endforeach?></tbody>
        </table>
    </div>
</section>
