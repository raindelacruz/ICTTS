<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = ?';
        $params = [$email];
        if ($excludeId) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function idNumberExists(string $idNumber, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE id_number = ?';
        $params = [$idNumber];
        if ($excludeId) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, sc.name service_category_name FROM users u LEFT JOIN service_categories sc ON sc.id = u.service_category_id WHERE u.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query('SELECT u.*, sc.name service_category_name FROM users u LEFT JOIN service_categories sc ON sc.id = u.service_category_id ORDER BY u.role, u.name')->fetchAll();
    }

    public function technicalActive(?int $serviceCategoryId = null): array
    {
        $sql = 'SELECT u.id, u.name, u.email, u.position, u.service_category_id, sc.name service_category_name FROM users u LEFT JOIN service_categories sc ON sc.id = u.service_category_id WHERE u.role = "technical" AND u.status = "active"';
        $params = [];
        if ($serviceCategoryId !== null) {
            $sql .= ' AND u.service_category_id = ?';
            $params[] = $serviceCategoryId;
        }
        $sql .= ' ORDER BY u.name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function supervisors(): array
    {
        return $this->db->query('SELECT u.id, u.name, u.email, u.role, u.service_category_id, sc.name service_category_name FROM users u LEFT JOIN service_categories sc ON sc.id = u.service_category_id WHERE u.role IN ("unit_head","division_chief","admin") AND u.status = "active"')->fetchAll();
    }

    public function unitHeadsByServiceCategory(int $serviceCategoryId): array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.name, u.email, u.role, u.service_category_id, sc.name service_category_name FROM users u JOIN service_categories sc ON sc.id = u.service_category_id WHERE u.role = "unit_head" AND u.status = "active" AND u.service_category_id = ? ORDER BY u.name');
        $stmt->execute([$serviceCategoryId]);
        return $stmt->fetchAll();
    }

    public function activeTechnicalCanHandle(int $userId, int $serviceCategoryId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE id = ? AND role = "technical" AND status = "active" AND service_category_id = ?');
        $stmt->execute([$userId, $serviceCategoryId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (id_number, name, position, email, password_hash, role, service_category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_number'],
            $data['name'],
            $data['position'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
            $data['service_category_id'] ?: null,
            $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = 'id_number = ?, name = ?, position = ?, email = ?, role = ?, service_category_id = ?, status = ?';
        $params = [$data['id_number'], $data['name'], $data['position'], $data['email'], $data['role'], $data['service_category_id'] ?: null, $data['status']];
        if (!empty($data['password'])) {
            $fields .= ', password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET {$fields} WHERE id = ?");
        $stmt->execute($params);
    }

    public function updateProfile(int $id, array $data): void
    {
        $fields = 'name = ?, position = ?, email = ?';
        $params = [$data['name'], $data['position'], $data['email']];
        if (!empty($data['password'])) {
            $fields .= ', password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET {$fields} WHERE id = ?");
        $stmt->execute($params);
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }
}
