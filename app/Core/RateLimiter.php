<?php

declare(strict_types=1);

namespace App\Core;

class RateLimiter
{
    public static function hit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $now = time();
        $bucket = $_SESSION['_rate_limits'][$key] ?? ['attempts' => 0, 'reset_at' => $now + $windowSeconds];

        if (($bucket['reset_at'] ?? 0) <= $now) {
            $bucket = ['attempts' => 0, 'reset_at' => $now + $windowSeconds];
        }

        $bucket['attempts'] = (int) ($bucket['attempts'] ?? 0) + 1;
        $_SESSION['_rate_limits'][$key] = $bucket;

        return $bucket['attempts'] <= $maxAttempts;
    }

    public static function clear(string $key): void
    {
        unset($_SESSION['_rate_limits'][$key]);
    }
}
