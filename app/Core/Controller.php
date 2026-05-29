<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/' . $layout . '.php';
    }

    protected function publicView(string $view, array $data = []): void
    {
        $this->view($view, $data, 'layouts/public');
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
