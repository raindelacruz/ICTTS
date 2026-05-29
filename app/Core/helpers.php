<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function public_url(string $path = ''): string
{
    return rtrim(APP_PUBLIC_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function csrf_field(): string
{
    return Csrf::field();
}

function current_user(): ?array
{
    return Auth::user();
}

function status_badge(string $status): string
{
    $map = [
        'Submitted' => 'secondary',
        'Assigned' => 'info',
        'In Progress' => 'primary',
        'Pending' => 'secondary',
        'Completed' => 'warning',
        'Confirmed Completed' => 'success',
        'Returned for Further Action' => 'warning',
        'Cancelled' => 'danger',
    ];

    return '<span class="badge text-bg-' . ($map[$status] ?? 'secondary') . '">' . e($status) . '</span>';
}
