<?php use App\Core\Auth;use App\Core\Csrf;use App\Core\Database;
$user=Auth::user();
$items=Database::query('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC',[$user['id']])->fetchAll();page_header('การแจ้งเตือน','ข่าวสารเกี่ยวกับนัดหมายและการรักษา');?>
<form method="post" class="form-actions"><input type="hidden" name="action"
        value="notifications_read"><?=Csrf::field()?><button class="outline-button">ทำเครื่องหมายว่าอ่านทั้งหมด</button>
</form>
<section class="panel notification-list"><?php foreach($items as $n):?><article class="<?=$n['is_read']?'':'unread'?>">
        <span>♢</span>
        <div><b><?=htmlspecialchars($n['title'])?></b>
            <p><?=htmlspecialchars($n['message'])?></p><small><?=$n['created_at']?></small>
        </div><?php if(!$n['is_read']):?><i></i><?php endif?>
    </article><?php endforeach?><?php if(!$items):?><p>ยังไม่มีการแจ้งเตือน</p><?php endif?></section>