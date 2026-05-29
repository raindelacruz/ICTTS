<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    public function create(int $userId, string $title, string $message, ?string $link = null): void
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $title, $message, $link]);
        } catch (\PDOException $exception) {
            return;
        }
    }

    public function createForUsers(array $userIds, string $title, string $message, ?string $link = null): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        foreach ($userIds as $userId) {
            $this->create($userId, $title, $message, $link);
        }
    }

    public function recentForUser(int $userId, int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . max(1, min($limit, 20)));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL');
        $stmt->execute([$userId]);
    }

    public function markReadAndFind(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('UPDATE notifications SET read_at = COALESCE(read_at, NOW()) WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }
}
