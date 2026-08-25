<?php
namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        // ค้นหาโฟลเดอร์ views แบบไม่สนตัวพิมพ์เล็ก-ใหญ่
        $baseViewsDir = is_dir(__DIR__ . '/Views') ? __DIR__ . '/Views' : __DIR__ . '/views';
        if (!is_dir($baseViewsDir)) {
            $baseViewsDir = __DIR__; // หากวาง Views ไว้ที่ root
        }

        $viewFile = $baseViewsDir . '/' . $view . '.php';

        // ถ้าหาไฟล์ View ไม่เจอ ให้พ่น Error ออกมาทันที แทนที่จะปล่อยหน้าขาว
        if (!file_exists($viewFile)) {
            echo "<div style='background:#fee2e2; color:#991b1b; padding:20px; font-family:sans-serif;'>";
            echo "<h3>⚠️ View File Not Found!</h3>";
            echo "<p>ไม่พบไฟล์หน้าเว็บที่พาธ: <code>" . htmlspecialchars($viewFile) . "</code></p>";
            echo "<p>กรุณาเช็กชื่อโฟลเดอร์ <b>Views</b> และชื่อไฟล์ <b>{$view}.php</b> ใน GitHub ว่าเป็นตัวพิมพ์เล็กหรือใหญ่</p>";
            echo "</div>";
            return;
        }

        // ดึงส่วนประกอบ Header / Components / Footer
        $componentsFile = $baseViewsDir . '/components.php';
        $headerFile = $baseViewsDir . '/layouts/header.php';
        $footerFile = $baseViewsDir . '/layouts/footer.php';

        if (file_exists($componentsFile)) require_once $componentsFile;
        if (file_exists($headerFile)) require $headerFile;
        
        require $viewFile;
        
        if (file_exists($footerFile)) require $footerFile;
    }
}
