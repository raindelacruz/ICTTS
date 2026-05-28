<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

class ActivityLogger
{
    public static function log(string $action, ?string $entityType = null, ?string $entityId = null, ?string $details = null, ?string $actorName = null): void
    {
        $db = Database::getConnection();
        $user = Auth::user();
        $stmt = $db->prepare('INSERT INTO activity_logs (user_id, actor_name, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $user['id'] ?? null,
            $actorName ?? ($user['name'] ?? 'Public Requester'),
            $action,
            $entityType,
            $entityId,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
