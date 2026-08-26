<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        // อ้างอิงโฟลเดอร์ที่ view.php อยู่เป็นหลัก
        $baseDir = __DIR__;

        // ค้นหาโฟลเดอร์ Views/views ภายในพาธปัจจุบัน
        $possibleViewsDirs = [
            $baseDir . '/Views',
            $baseDir . '/views',
            $baseDir . '/app/Views',
            $baseDir . '/app/views',
        ];

        $baseViewsDir = null;
        foreach ($possibleViewsDirs as $dir) {
            if (is_dir($dir)) {
                $baseViewsDir = $dir;
                break;
            }
        }

        if (!$baseViewsDir) {
            $baseViewsDir = $baseDir . '/Views';
        }

        $viewFile = $baseViewsDir . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            echo "<div style='background:#fee2e2; color:#991b1b; padding:20px; font-family:sans-serif;'>";
            echo "<h3>⚠️ View File Not Found!</h3>";
            echo "<p>ไม่พบไฟล์หน้าเว็บที่: <code>" . htmlspecialchars($viewFile) . "</code></p>";
            echo "</div>";
            return;
        }

        $componentsFile = $baseViewsDir . '/components.php';
        $headerFile = $baseViewsDir . '/layouts/header.php';
        $footerFile = $baseViewsDir . '/layouts/footer.php';

        // ตรวจสอบไฟล์ประกอบก่อน require เพื่อป้องกัน Fatal Error
        if (file_exists($componentsFile)) {
            require_once $componentsFile;
        }
        
        if (file_exists($headerFile)) {
            require $headerFile;
        }
        
        require $viewFile;
        
        if (file_exists($footerFile)) {
            require $footerFile;
        }
    }
}
