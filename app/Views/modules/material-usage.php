<?php use App\Core\Csrf;use App\Core\Database;
$materials=Database::query('SELECT * FROM materials WHERE quantity>0 ORDER BY material_name')->fetchAll();
$tx=Database::query("SELECT mt.created_at,m.material_name,m.unit,mt.quantity,u.full_name FROM material_transactions mt JOIN materials m ON m.id=mt.material_id JOIN users u ON u.id=mt.user_id WHERE mt.transaction_type='issue' ORDER BY mt.id DESC LIMIT 30")->fetchAll();
page_header('เบิกใช้วัสดุทันตกรรม','บันทึกการใช้วัสดุและตัดจำนวนคงเหลือ');?>
<section class="form-panel">
    <form method="post"><input type="hidden" name="action" value="material_transaction"><input type="hidden"
            name="transaction_type" value="issue"><input type="hidden" name="return_page"
            value="material-usage"><?=Csrf::field()?><div class="form-grid"><label>วัสดุ<select
                    name="material_id"><?php foreach($materials as $m):?><option value="<?=$m['id']?>">
                        <?=htmlspecialchars($m['material_name'].' (เหลือ '.$m['quantity'].' '.$m['unit'].')')?></option>
                    <?php endforeach?></select></label><label>จำนวน<input type="number" min="1" name="quantity"
                    required></label><label class="full">หมายเหตุ<textarea name="note"></textarea></label></div><button
            class="primary-button fit">บันทึกการเบิก</button></form>
</section>
<section class="panel">
    <?php data_table(['วันที่','วัสดุ','จำนวน','ผู้เบิก'],array_map(fn($r)=>[$r['created_at'],$r['material_name'],$r['quantity'].' '.$r['unit'],$r['full_name']],$tx));?>
</section>
