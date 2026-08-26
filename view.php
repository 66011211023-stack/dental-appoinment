<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $baseDir = __DIR__;

        // ค้นหาโฟลเดอร์ Views แบบไม่สนตัวพิมพ์เล็ก-ใหญ่
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

        // ลองค้นหาไฟล์ view แบบยืดหยุ่น (รองรับทั้งตัวพิมพ์เล็กและตัวพิมพ์ใหญ่)
        $viewParts = explode('/', $view);
        $fileName = array_pop($viewParts);
        $subDir = implode('/', $viewParts);
        
        $targetDir = $baseViewsDir . ($subDir ? '/' . $subDir : '');

        $possibleFiles = [
            $targetDir . '/' . $fileName . '.php',
            $targetDir . '/' . ucfirst($fileName) . '.php',
            $targetDir . '/' . strtolower($fileName) . '.php',
        ];

        $viewFile = null;
        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                $viewFile = $file;
                break;
            }
        }

        if (!$viewFile) {
            echo "<div style='background:#fee2e2; color:#991b1b; padding:20px; font-family:sans-serif;'>";
            echo "<h3>⚠️ View File Not Found!</h3>";
            echo "<p>ไม่พบไฟล์หน้าเว็บที่: <code>" . htmlspecialchars($targetDir . '/' . $fileName . '.php') . "</code></p>";
            echo "<p>โปรดตรวจสอบโฟลเดอร์ <b>Views/auth/</b> ว่ามีไฟล์ <b>login.php</b> อยู่หรือไม่</p>";
            echo "</div>";
            return;
        }

        $componentsFile = $baseViewsDir . '/components.php';
        $headerFile = $baseViewsDir . '/layouts/header.php';
        $footerFile = $baseViewsDir . '/layouts/footer.php';

        if (file_exists($componentsFile)) require_once $componentsFile;
        if (file_exists($headerFile)) require $headerFile;
        
        require $viewFile;
        
        if (file_exists($footerFile)) require $footerFile;
    }
}
