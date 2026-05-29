<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public static function validate(?string $token): void
    {
        if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
            http_response_code(419);
            exit('Invalid or expired form token.');
        }
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }
}
