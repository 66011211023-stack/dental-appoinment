<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        // หา Root Directory ของ htdocs หรือโฟลเดอร์ปัจจุบัน
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        if (!$docRoot || !is_dir($docRoot)) {
            $docRoot = __DIR__;
        }

        // ค้นหาโฟลเดอร์ Views ทั้งหมดที่เป็นไปได้ (ทั้งตัวพิมพ์เล็กและใหญ่)
        $possibleViewsDirs = [
            $docRoot . '/Views',
            $docRoot . '/views',
            __DIR__ . '/Views',
            __DIR__ . '/views',
        ];

        $baseViewsDir = null;
        foreach ($possibleViewsDirs as $dir) {
            if (is_dir($dir)) {
                $baseViewsDir = $dir;
                break;
            }
        }

        if (!$baseViewsDir) {
            $baseViewsDir = $docRoot . '/Views';
        }

        // ค้นหาพาธไฟล์ย่อย (เช่น auth/login) แบบไม่สนตัวพิมพ์เล็ก-ใหญ่
        $parts = explode('/', $view);
        $fileName = array_pop($parts);
        $subDir = implode('/', $parts);

        // ลองค้นหาโฟลเดอร์ย่อย (เช่น auth หรือ Auth)
        $targetDir = $baseViewsDir;
        if ($subDir !== '') {
            if (is_dir($baseViewsDir . '/' . $subDir)) {
                $targetDir = $baseViewsDir . '/' . $subDir;
            } elseif (is_dir($baseViewsDir . '/' . ucfirst($subDir))) {
                $targetDir = $baseViewsDir . '/' . ucfirst($subDir);
            } elseif (is_dir($baseViewsDir . '/' . strtolower($subDir))) {
                $targetDir = $baseViewsDir . '/' . strtolower($subDir);
            } else {
                $targetDir = $baseViewsDir . '/' . $subDir;
            }
        }

        // ลองค้นหาตัวไฟล์ (เช่น login.php, Login.php)
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
            echo "<p>ไม่พบไฟล์หน้าเว็บที่: <code>" . htmlspecialchars($targetDir . '/' . $fileName . '.php') . "</code></p>";
            echo "<p><b>สิ่งที่ต้องเช็กใน File Manager:</b></p>";
            echo "<ul>";
            echo "<li>เช็กโฟลเดอร์ <b>Views</b> (หรือ <b>views</b>) ว่าด้านในมีโฟลเดอร์ <b>auth</b> อยู่หรือไม่</li>";
            echo "<li>เช็กด้านในโฟลเดอร์ <b>auth</b> ว่ามีไฟล์ <b>login.php</b> หรือไม่</li>";
            echo "</ul>";
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
