<?php

declare(strict_types=1);

function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    if ($value === false && isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }

    if ($value === false || $value === null) {
        return $default;
    }

    $value = trim((string) $value);
    if (strlen($value) >= 2) {
        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        }
    }

    return $value;
}

function smtp_password_value(string $host, string $password): string
{
    if (stripos($host, 'gmail.com') !== false) {
        return preg_replace('/\s+/', '', $password) ?? $password;
    }

    return $password;
}

load_env_file(__DIR__ . '/../.env');

define('APP_NAME', 'ICTSD Ticketing System');
define('APP_TIMEZONE', 'Asia/Manila');
define('BASE_URL', env_value('BASE_URL', '/ICTTS/public'));
define('APP_PUBLIC_URL', env_value('APP_PUBLIC_URL', 'https://ebps.nfa.gov.ph/ICTTS/public'));

define('DB_HOST', env_value('DB_HOST', '127.0.0.1'));
define('DB_NAME', env_value('DB_NAME', 'ictts'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS'));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8mb4'));

define('MAIL_FROM', env_value('MAIL_FROM', 'tech.support@nfa.gov.ph'));
define('MAIL_FROM_NAME', env_value('MAIL_FROM_NAME', 'ICTSD'));
define('ICT_NOTIFICATION_EMAIL', env_value('ICT_NOTIFICATION_EMAIL', 'tech.support@nfa.gov.ph'));

define('SMTP_ENABLED', filter_var(env_value('SMTP_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN));
define('SMTP_HOST', env_value('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int) env_value('SMTP_PORT', '587'));
define('SMTP_USERNAME', env_value('SMTP_USERNAME', 'tech.support@nfa.gov.ph'));
define('SMTP_PASSWORD', smtp_password_value(SMTP_HOST, env_value('SMTP_PASSWORD')));
define('SMTP_ENCRYPTION', env_value('SMTP_ENCRYPTION', 'tls'));

date_default_timezone_set(APP_TIMEZONE);
