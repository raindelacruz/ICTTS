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
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
    }

    public function technicalActive(): array
    {
        return $this->db->query('SELECT id, name, email, position FROM users WHERE role = "technical" AND status = "active" ORDER BY name')->fetchAll();
    }

    public function supervisors(): array
    {
        return $this->db->query('SELECT id, name, email, role FROM users WHERE role IN ("unit_head","division_chief","admin") AND status = "active"')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (id_number, name, position, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_number'],
            $data['name'],
            $data['position'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
            $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = 'id_number = ?, name = ?, position = ?, email = ?, role = ?, status = ?';
        $params = [$data['id_number'], $data['name'], $data['position'], $data['email'], $data['role'], $data['status']];
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
