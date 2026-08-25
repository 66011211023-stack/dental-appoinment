<?php
/**
 * ส่วนประกอบหน้าจอที่ใช้ซ้ำทั้งระบบ
 * การรวมไว้ที่นี่ช่วยให้การ์ด ตาราง และหัวข้อทุกหน้ามี HTML เหมือนกัน
 */

function page_header(string $title, string $subtitle, ?string $action = null, string $href = '#'): void
{
    echo '<div class="page-header"><div><h2>'.htmlspecialchars($title).'</h2><p>'.htmlspecialchars($subtitle).'</p></div>';
    if ($action !== null) {
        echo '<a class="primary-button fit" href="'.htmlspecialchars($href).'">＋ '.htmlspecialchars($action).'</a>';
    }
    echo '</div>';
}

function stat_card(string $icon, string $value, string $label, string $trend, string $tone = 'blue'): void
{
    $trendClass = str_starts_with($trend, '+') ? 'up' : 'neutral';
    echo '<article class="stat-card"><div class="stat-icon '.htmlspecialchars($tone).'">'.htmlspecialchars($icon).'</div>';
    echo '<div><p>'.htmlspecialchars($label).'</p><h3>'.htmlspecialchars($value).'</h3></div>';
    echo '<span class="'.$trendClass.'">'.htmlspecialchars($trend).'</span></article>';
}

function status_class(string $status): string
{
    if (in_array($status, ['ยืนยันแล้ว', 'ใช้งาน', 'พร้อมใช้', 'เสร็จสิ้น'], true)) return 'green';
    if (in_array($status, ['รออนุมัติ', 'ต่ำกว่ากำหนด'], true)) return 'orange';
    return 'blue';
}

function data_table(array $heads, array $rows, ?int $statusIndex = null, ?int $actionIndex = null): void
{
    echo '<div class="table-wrap"><table><thead><tr>';
    foreach ($heads as $head) echo '<th>'.htmlspecialchars((string)$head).'</th>';
    echo '</tr></thead><tbody>';
    if (!$rows) {
        echo '<tr><td class="empty" colspan="'.count($heads).'">ไม่พบข้อมูลตามเงื่อนไข</td></tr>';
    }
    foreach ($rows as $row) {
        echo '<tr>';
        foreach (array_values($row) as $index => $cell) {
            $safe = htmlspecialchars((string)$cell);
            if ($index === $statusIndex) echo '<td><span class="status '.status_class((string)$cell).'">'.$safe.'</span></td>';
            elseif ($index === $actionIndex) echo '<td><button class="table-action" data-toast="เปิดรายละเอียดแล้ว">'.$safe.' →</button></td>';
            else echo '<td>'.$safe.'</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
