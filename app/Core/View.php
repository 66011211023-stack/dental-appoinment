<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $rootDir = dirname(__DIR__, 2);

        // ค้นหาโฟลเดอร์ Views ในทุกรูปแบบตำแหน่ง
        $possibleDirs = [
            $rootDir . '/app/Views',
            $rootDir . '/app/views',
            $rootDir . '/Views',
            $rootDir . '/views',
            __DIR__ . '/../Views',
            __DIR__ . '/../views',
        ];

        $baseViewsDir = null;
        foreach ($possibleDirs as $dir) {
            if (is_dir($dir)) {
                $baseViewsDir = $dir;
                break;
            }
        }

        if (!$baseViewsDir) {
            $baseViewsDir = $rootDir . '/app/Views';
        }

        // ค้นหาไฟล์ย่อย เช่น auth/login
        $parts = explode('/', $view);
        $fileName = array_pop($parts);
        $subDir = implode('/', $parts);

        $targetDir = $baseViewsDir;
        if ($subDir !== '') {
            if (is_dir($baseViewsDir . '/' . $subDir)) {
                $targetDir = $baseViewsDir . '/' . $subDir;
            } elseif (is_dir($baseViewsDir . '/' . strtolower($subDir))) {
                $targetDir = $baseViewsDir . '/' . strtolower($subDir);
            } elseif (is_dir($baseViewsDir . '/' . ucfirst($subDir))) {
                $targetDir = $baseViewsDir . '/' . ucfirst($subDir);
            }
        }

        $possibleFiles = [
            $targetDir . '/' . $fileName . '.php',
            $targetDir . '/' . strtolower($fileName) . '.php',
            $targetDir . '/' . ucfirst($fileName) . '.php',
        ];

        $viewFile = null;
        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                $viewFile = $file;
                break;
            }
        }

        if (!$viewFile) {
            echo "<div style='background:#fee2e2; color:#991b1b; padding:20px; font-family:sans-serif; border-radius:8px;'>";
            echo "<h3>⚠️ View File Not Found!</h3>";
            echo "<p>ไม่พบไฟล์ที่: <code>" . htmlspecialchars($targetDir . '/' . $fileName . '.php') . "</code></p>";
            echo "<p><b>พาธ Views ปัจจุบัน:</b> " . htmlspecialchars($baseViewsDir) . "</p>";
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
