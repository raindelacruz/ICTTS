<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Log extends Model
{
    public const PAGE_SIZES = [10, 25, 50, 100];

    public function activity(array $filters = []): array
    {
        [$where, $params] = $this->activityFilterSql($filters);
        $limit = $this->limit($filters['activity_limit'] ?? null);
        $stmt = $this->db->prepare('SELECT * FROM activity_logs' . $where . ' ORDER BY created_at DESC LIMIT ' . $limit);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function emails(): array
    {
        return $this->db->query('SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 300')->fetchAll();
    }

    private function activityFilterSql(array $filters): array
    {
        $where = [];
        $params = [];

        if (($filters['activity_q'] ?? '') !== '') {
            $where[] = '(actor_name LIKE ? OR action LIKE ? OR entity_type LIKE ? OR entity_id LIKE ? OR details LIKE ? OR ip_address LIKE ?)';
            for ($i = 0; $i < 6; $i++) {
                $params[] = '%' . $filters['activity_q'] . '%';
            }
        }
        if (($filters['action'] ?? '') !== '') {
            $where[] = 'action LIKE ?';
            $params[] = '%' . $filters['action'] . '%';
        }
        if (($filters['entity_type'] ?? '') !== '') {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function limit(?string $limit): int
    {
        $limit = (int) $limit;
        return in_array($limit, self::PAGE_SIZES, true) ? $limit : 10;
    }
}
