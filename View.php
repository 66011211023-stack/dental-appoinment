<?php
namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        // ค้นหาโฟลเดอร์ Views หรือ views (รองรับ Linux Case-Sensitivity)
        $baseViewsDir = is_dir(__DIR__ . '/Views') ? __DIR__ . '/Views' : __DIR__ . '/views';

        $viewFile = $baseViewsDir . '/' . $view . '.php';
        $componentsFile = $baseViewsDir . '/components.php';
        $headerFile = $baseViewsDir . '/layouts/header.php';
        $footerFile = $baseViewsDir . '/layouts/footer.php';

        if (file_exists($componentsFile)) {
            require_once $componentsFile;
        }
        if (file_exists($headerFile)) {
            require $headerFile;
        }
        if (file_exists($viewFile)) {
            require $viewFile;
        }
        if (file_exists($footerFile)) {
            require $footerFile;
        }
    }
}
