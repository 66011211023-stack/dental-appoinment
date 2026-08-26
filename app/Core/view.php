<?php
namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        require_once dirname(__DIR__) . '/Views/components.php';
        require dirname(__DIR__) . '/Views/layouts/header.php';
        require $viewFile;
        require dirname(__DIR__) . '/Views/layouts/footer.php';
    }
}
