<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Library extends Model
{
    public function categories(): array
    {
        return $this->db->query('SELECT * FROM service_categories WHERE status = "active" ORDER BY name')->fetchAll();
    }

    public function allCategories(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (($filters['category_q'] ?? '') !== '') {
            $where[] = 'name LIKE ?';
            $params[] = '%' . $filters['category_q'] . '%';
        }
        if (($filters['category_status'] ?? '') !== '') {
            $where[] = 'status = ?';
            $params[] = $filters['category_status'];
        }

        $stmt = $this->db->prepare('SELECT * FROM service_categories' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY status, name');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function serviceItems(?int $categoryId = null): array
    {
        if ($categoryId) {
            $stmt = $this->db->prepare('SELECT * FROM service_items WHERE service_category_id = ? AND status = "active" ORDER BY name');
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll();
        }

        return $this->db->query('SELECT si.*, sc.name AS category_name FROM service_items si JOIN service_categories sc ON sc.id = si.service_category_id ORDER BY sc.name, si.name')->fetchAll();
    }

    public function regions(): array
    {
        return $this->db->query('SELECT * FROM regions WHERE status = "active" ORDER BY id')->fetchAll();
    }

    public function allRegions(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (($filters['region_q'] ?? '') !== '') {
            $where[] = '(code LIKE ? OR name LIKE ?)';
            $params[] = '%' . $filters['region_q'] . '%';
            $params[] = '%' . $filters['region_q'] . '%';
        }
        if (($filters['region_status'] ?? '') !== '') {
            $where[] = 'status = ?';
            $params[] = $filters['region_status'];
        }

        $stmt = $this->db->prepare('SELECT * FROM regions' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function offices(?int $regionId = null): array
    {
        if ($regionId) {
            $stmt = $this->db->prepare('SELECT * FROM offices WHERE region_id = ? AND status = "active" ORDER BY name');
            $stmt->execute([$regionId]);
            return $stmt->fetchAll();
        }

        return $this->db->query('SELECT o.*, r.name AS region_name FROM offices o JOIN regions r ON r.id = o.region_id ORDER BY r.id, o.name')->fetchAll();
    }

    public function serviceItemsForManagement(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (($filters['item_q'] ?? '') !== '') {
            $where[] = 'si.name LIKE ?';
            $params[] = '%' . $filters['item_q'] . '%';
        }
        if (($filters['item_category_id'] ?? '') !== '') {
            $where[] = 'si.service_category_id = ?';
            $params[] = $filters['item_category_id'];
        }
        if (($filters['item_status'] ?? '') !== '') {
            $where[] = 'si.status = ?';
            $params[] = $filters['item_status'];
        }

        $stmt = $this->db->prepare(
            'SELECT si.*, sc.name AS category_name FROM service_items si JOIN service_categories sc ON sc.id = si.service_category_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY sc.name, si.name'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function officesForManagement(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (($filters['office_q'] ?? '') !== '') {
            $where[] = 'o.name LIKE ?';
            $params[] = '%' . $filters['office_q'] . '%';
        }
        if (($filters['office_region_id'] ?? '') !== '') {
            $where[] = 'o.region_id = ?';
            $params[] = $filters['office_region_id'];
        }
        if (($filters['office_type'] ?? '') !== '') {
            $where[] = 'o.office_type = ?';
            $params[] = $filters['office_type'];
        }
        if (($filters['office_status'] ?? '') !== '') {
            $where[] = 'o.status = ?';
            $params[] = $filters['office_status'];
        }

        $stmt = $this->db->prepare(
            'SELECT o.*, r.name AS region_name FROM offices o JOIN regions r ON r.id = o.region_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY r.id, o.name'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addServiceItem(int $categoryId, string $name): void
    {
        $stmt = $this->db->prepare('INSERT INTO service_items (service_category_id, name, default_priority) VALUES (?, ?, ?)');
        $stmt->execute([$categoryId, $name, 'Medium']);
    }

    public function addServiceItemWithPriority(int $categoryId, string $name, string $priority): void
    {
        $priority = in_array($priority, Ticket::PRIORITIES, true) ? $priority : 'Medium';
        $stmt = $this->db->prepare('INSERT INTO service_items (service_category_id, name, default_priority) VALUES (?, ?, ?)');
        $stmt->execute([$categoryId, $name, $priority]);
    }

    public function addServiceCategory(string $name): void
    {
        $stmt = $this->db->prepare('INSERT INTO service_categories (name) VALUES (?)');
        $stmt->execute([$name]);
    }

    public function updateServiceCategory(int $id, string $name, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE service_categories SET name = ?, status = ? WHERE id = ?');
        $stmt->execute([$name, $status, $id]);
    }

    public function deleteServiceCategory(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE service_categories SET status = "inactive" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function updateServiceItem(int $id, int $categoryId, string $name, string $status): void
    {
        $this->updateServiceItemWithPriority($id, $categoryId, $name, $status, 'Medium');
    }

    public function updateServiceItemWithPriority(int $id, int $categoryId, string $name, string $status, string $priority): void
    {
        $priority = in_array($priority, Ticket::PRIORITIES, true) ? $priority : 'Medium';
        $stmt = $this->db->prepare('UPDATE service_items SET service_category_id = ?, name = ?, default_priority = ?, status = ? WHERE id = ?');
        $stmt->execute([$categoryId, $name, $priority, $status, $id]);
    }

    public function deleteServiceItem(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE service_items SET status = "inactive" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function addOffice(int $regionId, string $name, string $type): void
    {
        $stmt = $this->db->prepare('INSERT INTO offices (region_id, name, office_type) VALUES (?, ?, ?)');
        $stmt->execute([$regionId, $name, $type]);
    }

    public function addRegion(string $code, string $name): void
    {
        $stmt = $this->db->prepare('INSERT INTO regions (code, name) VALUES (?, ?)');
        $stmt->execute([$code, $name]);
    }

    public function updateRegion(int $id, string $code, string $name, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE regions SET code = ?, name = ?, status = ? WHERE id = ?');
        $stmt->execute([$code, $name, $status, $id]);
    }

    public function deleteRegion(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE regions SET status = "inactive" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function updateOffice(int $id, int $regionId, string $name, string $type, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE offices SET region_id = ?, name = ?, office_type = ?, status = ? WHERE id = ?');
        $stmt->execute([$regionId, $name, $type, $status, $id]);
    }

    public function deleteOffice(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE offices SET status = "inactive" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function validRequestSelections(int $regionId, int $officeId, int $categoryId, int $serviceItemId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM offices WHERE id = ? AND region_id = ? AND status = "active"');
        $stmt->execute([$officeId, $regionId]);
        if ((int) $stmt->fetchColumn() !== 1) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM service_items WHERE id = ? AND service_category_id = ? AND status = "active"');
        $stmt->execute([$serviceItemId, $categoryId]);
        return (int) $stmt->fetchColumn() === 1;
    }
}
