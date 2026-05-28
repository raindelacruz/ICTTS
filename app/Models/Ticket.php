<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDOException;

class Ticket extends Model
{
    public const STATUSES = ['Submitted', 'Assigned', 'In Progress', 'Pending', 'Completed', 'Confirmed Completed', 'Returned for Further Action', 'Cancelled'];
    public const PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];
    public const SLA_STATUSES = ['Within SLA', 'Response Overdue', 'Resolution Overdue', 'Met', 'Breached'];
    public const TECH_UPDATE_STATUSES = ['In Progress', 'Pending', 'Completed'];
    public const TECH_CANCEL_STATUS = 'Cancelled';
    public const RETURNED_STATUS = 'Returned for Further Action';

    public function create(array $data): int
    {
        $ticketNo = $this->nextTicketNumber();
        $priority = $this->serviceItemPriority((int) $data['service_item_id']);
        $targets = $this->slaTargets($priority);

        $stmt = $this->db->prepare('INSERT INTO tickets (ticket_no, requested_at, requester_name, requester_position, requester_email, requester_contact, region_id, office_id, requested_for, service_category_id, service_item_id, responsible_group, description, priority, response_due_at, resolution_due_at) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR), DATE_ADD(NOW(), INTERVAL ? HOUR))');
        $stmt->execute([
            $ticketNo,
            $data['requester_name'],
            $data['requester_position'],
            $data['requester_email'],
            $data['requester_contact'],
            $data['region_id'],
            $data['office_id'],
            $data['requested_for'],
            $data['service_category_id'],
            $data['service_item_id'],
            $data['responsible_group'] ?? null,
            $data['description'],
            $priority,
            $targets['response_hours'],
            $targets['resolution_hours'],
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->logStatus($id, null, 'Submitted', null, 'Public Requester', 'Ticket submitted.');
        return $id;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE t.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' JOIN requester_confirmation_tokens rct ON rct.ticket_id = t.id WHERE rct.token_hash = ? AND rct.used_at IS NULL AND rct.expires_at > NOW()');
        $stmt->execute([hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }

    public function list(array $filters = []): array
    {
        $this->refreshSlaStatuses();
        [$where, $params] = $this->filterSql($filters);
        $stmt = $this->db->prepare($this->baseSelect() . $where . ' ORDER BY FIELD(t.priority, "Critical", "High", "Medium", "Low"), CASE WHEN t.sla_status IN ("Response Overdue","Resolution Overdue","Breached") THEN 0 ELSE 1 END, t.created_at DESC LIMIT 300');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function dashboardStats(?array $user = null): array
    {
        $this->refreshSlaStatuses();
        $stats = array_fill_keys(array_merge(['total'], self::STATUSES), 0);
        [$scope, $params] = $this->roleScopeSql($user);
        $stmt = $this->db->prepare('SELECT status, COUNT(*) total FROM tickets t' . $scope . ' GROUP BY status');
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $row) {
            $stats[$row['status']] = (int) $row['total'];
            $stats['total'] += (int) $row['total'];
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tickets t' . $scope . ($scope ? ' AND ' : ' WHERE ') . 't.assigned_to IS NULL AND t.status = "Submitted"');
        $stmt->execute($params);
        $stats['unassigned'] = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tickets t' . $scope . ($scope ? ' AND ' : ' WHERE ') . 't.sla_status IN ("Response Overdue","Resolution Overdue","Breached")');
        $stmt->execute($params);
        $stats['overdue'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    public function breakdowns(?array $user = null): array
    {
        [$scope, $params] = $this->roleScopeSql($user);
        return [
            'priority' => $this->rows('SELECT t.priority label, COUNT(*) total FROM tickets t' . $scope . ' GROUP BY t.priority ORDER BY FIELD(t.priority, "Critical", "High", "Medium", "Low")', $params),
            'sla' => $this->rows('SELECT t.sla_status label, COUNT(*) total FROM tickets t' . $scope . ' GROUP BY t.sla_status ORDER BY total DESC', $params),
            'category' => $this->rows('SELECT sc.name label, COUNT(*) total FROM tickets t JOIN service_categories sc ON sc.id = t.service_category_id' . $scope . ' GROUP BY sc.name ORDER BY total DESC', $params),
            'assignee' => $this->rows('SELECT COALESCE(u.name, "Unassigned") label, COUNT(DISTINCT t.id) total FROM tickets t LEFT JOIN ticket_assignees tas ON tas.ticket_id = t.id AND tas.removed_at IS NULL LEFT JOIN users u ON u.id = tas.user_id' . $scope . ' GROUP BY label ORDER BY total DESC', $params),
        ];
    }

    public function assign(int $ticketId, int $assignedTo, int $assignedBy, ?string $notes = null, string $role = 'primary'): void
    {
        $ticket = $this->find($ticketId);
        $role = $role === 'primary' ? 'primary' : 'supporting';
        $this->db->beginTransaction();

        if ($role === 'primary') {
            $stmt = $this->db->prepare('UPDATE ticket_assignees SET removed_at = NOW() WHERE ticket_id = ? AND assignment_role = "primary" AND removed_at IS NULL');
            $stmt->execute([$ticketId]);
            $stmt = $this->db->prepare('UPDATE tickets SET assigned_to = ?, assigned_by = ?, assigned_at = NOW(), status = "Assigned" WHERE id = ?');
            $stmt->execute([$assignedTo, $assignedBy, $ticketId]);
            $this->logStatus($ticketId, $ticket['status'] ?? null, 'Assigned', $assignedBy, null, 'Ticket assigned.');
        }

        $this->addCurrentAssignee($ticketId, $assignedTo, $assignedBy, $role, $notes);
        $stmt = $this->db->prepare('INSERT INTO ticket_assignments (ticket_id, assigned_to, assigned_by, assignment_role, action, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$ticketId, $assignedTo, $assignedBy, $role, $role === 'primary' ? 'assign' : 'add_support', $notes]);
        $this->db->commit();
    }

    public function reassign(int $ticketId, int $newAssignee, int $reassignedBy, string $reason, ?string $notes = null): void
    {
        $ticket = $this->find($ticketId);
        $previous = (int) ($ticket['assigned_to'] ?? 0) ?: null;
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('UPDATE ticket_assignees SET removed_at = NOW() WHERE ticket_id = ? AND assignment_role = "primary" AND removed_at IS NULL');
        $stmt->execute([$ticketId]);
        $this->addCurrentAssignee($ticketId, $newAssignee, $reassignedBy, 'primary', $notes);
        $stmt = $this->db->prepare('UPDATE tickets SET assigned_to = ?, assigned_by = ?, assigned_at = NOW(), status = CASE WHEN status = "Submitted" THEN "Assigned" ELSE status END WHERE id = ?');
        $stmt->execute([$newAssignee, $reassignedBy, $ticketId]);
        $stmt = $this->db->prepare('INSERT INTO ticket_assignments (ticket_id, previous_assignee, assigned_to, assigned_by, assignment_role, action, notes, reason) VALUES (?, ?, ?, ?, "primary", "reassign", ?, ?)');
        $stmt->execute([$ticketId, $previous, $newAssignee, $reassignedBy, $notes, $reason]);
        $this->logStatus($ticketId, $ticket['status'] ?? null, $ticket['status'] ?? 'Assigned', $reassignedBy, null, 'Reassigned: ' . $reason);
        $this->db->commit();
    }

    public function updateStatus(int $ticketId, string $status, int $userId, ?string $remarks = null): void
    {
        $ticket = $this->find($ticketId);
        $fields = 'status = ?';
        $params = [$status];
        if ($status === 'Completed') {
            $fields .= ', completed_by_tech_at = NOW(), sla_status = CASE WHEN sla_status IN ("Response Overdue","Resolution Overdue","Breached") THEN "Breached" ELSE "Met" END';
        }
        if (in_array($status, ['In Progress', 'Pending', 'Completed'], true) && empty($ticket['first_responded_at'])) {
            $fields .= ', first_responded_at = NOW()';
        }
        $params[] = $ticketId;
        $stmt = $this->db->prepare("UPDATE tickets SET {$fields} WHERE id = ?");
        $stmt->execute($params);
        $this->logStatus($ticketId, $ticket['status'] ?? null, $status, $userId, null, $remarks);
        $this->refreshSlaStatuses($ticketId);
    }

    public function createConfirmationToken(int $ticketId): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare('INSERT INTO requester_confirmation_tokens (ticket_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 14 DAY))');
        $stmt->execute([$ticketId, hash('sha256', $token)]);
        return $token;
    }

    public function confirmByToken(string $token, array $feedback): ?array
    {
        $ticket = $this->findByToken($token);
        if (!$ticket) {
            return null;
        }

        $resolved = ($feedback['resolved_yes_no'] ?? 'yes') === 'no' ? 'no' : 'yes';
        $rating = $resolved === 'yes' ? max(1, min(5, (int) ($feedback['rating'] ?? 0))) : null;
        $comments = trim($feedback['feedback_comments'] ?? '') ?: null;
        $newStatus = $resolved === 'yes' ? 'Confirmed Completed' : self::RETURNED_STATUS;

        $this->db->beginTransaction();
        $stmt = $this->db->prepare('UPDATE requester_confirmation_tokens SET used_at = NOW() WHERE token_hash = ? AND used_at IS NULL');
        $stmt->execute([hash('sha256', $token)]);
        $stmt = $this->db->prepare('UPDATE tickets SET status = ?, requester_confirmed_at = CASE WHEN ? = "Confirmed Completed" THEN NOW() ELSE requester_confirmed_at END, closed_at = CASE WHEN ? = "Confirmed Completed" THEN NOW() ELSE closed_at END WHERE id = ?');
        $stmt->execute([$newStatus, $newStatus, $newStatus, (int) $ticket['id']]);
        $stmt = $this->db->prepare('INSERT INTO ticket_feedback (ticket_id, rating, resolved_yes_no, feedback_comments, submitted_by_name) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([(int) $ticket['id'], $rating ?: null, $resolved, $comments, $ticket['requester_name']]);
        if ($resolved === 'no') {
            $this->logReopen((int) $ticket['id'], $ticket['status'], self::RETURNED_STATUS, null, 'Public Requester', $comments ?: 'Requester returned the ticket for further action.');
        }
        $this->logStatus((int) $ticket['id'], $ticket['status'], $newStatus, null, 'Public Requester', $resolved === 'yes' ? 'Requester confirmed completion.' : 'Requester returned the ticket for further action.');
        $this->db->commit();

        return $this->find((int) $ticket['id']);
    }

    public function reopen(int $ticketId, int $userId, string $reason, string $newStatus = self::RETURNED_STATUS): void
    {
        $ticket = $this->find($ticketId);
        $newStatus = in_array($newStatus, [self::RETURNED_STATUS, 'In Progress'], true) ? $newStatus : self::RETURNED_STATUS;
        $stmt = $this->db->prepare('UPDATE tickets SET status = ?, closed_at = NULL WHERE id = ?');
        $stmt->execute([$newStatus, $ticketId]);
        $this->logReopen($ticketId, $ticket['status'], $newStatus, $userId, null, $reason);
        $this->logStatus($ticketId, $ticket['status'], $newStatus, $userId, null, 'Reopened: ' . $reason);
    }

    public function endorse(int $ticketId, int $categoryId, int $serviceItemId, string $toGroup, int $endorsedBy, string $reason): void
    {
        $ticket = $this->find($ticketId);
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('UPDATE tickets SET service_category_id = ?, service_item_id = ?, responsible_group = ?, assigned_to = NULL, assigned_by = NULL, assigned_at = NULL, status = CASE WHEN status IN ("Submitted","Assigned") THEN "Submitted" ELSE status END WHERE id = ?');
        $stmt->execute([$categoryId, $serviceItemId, $toGroup, $ticketId]);
        $stmt = $this->db->prepare('UPDATE ticket_assignees SET removed_at = NOW() WHERE ticket_id = ? AND removed_at IS NULL');
        $stmt->execute([$ticketId]);
        $stmt = $this->db->prepare('INSERT INTO ticket_endorsements (ticket_id, from_group, to_group, old_service_category_id, new_service_category_id, old_service_item_id, new_service_item_id, endorsed_by, reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$ticketId, $ticket['responsible_group'] ?: $ticket['category_name'], $toGroup, $ticket['service_category_id'], $categoryId, $ticket['service_item_id'], $serviceItemId, $endorsedBy, $reason]);
        $this->logStatus($ticketId, $ticket['status'], $ticket['status'], $endorsedBy, null, 'Endorsed to ' . $toGroup . ': ' . $reason);
        $this->db->commit();
    }

    public function attachFile(int $ticketId, array $file, string $source, ?int $uploadedBy, ?string $uploadedByName, ?string $remarks = null): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return 'Upload failed for ' . ($file['name'] ?? 'attachment') . '.';
        }
        if ((int) $file['size'] > 5 * 1024 * 1024) {
            return 'Each attachment must be 5 MB or smaller.';
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!isset($allowed[$mime])) {
            return 'Unsupported attachment type: ' . ($file['name'] ?? 'file') . '.';
        }

        $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tickets' . DIRECTORY_SEPARATOR . $ticketId;
        if (!is_dir($root) && !mkdir($root, 0755, true) && !is_dir($root)) {
            return 'Unable to create attachment directory.';
        }

        $stored = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $target = $root . DIRECTORY_SEPARATOR . $stored;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return 'Unable to store attachment.';
        }

        $relativePath = 'uploads/tickets/' . $ticketId . '/' . $stored;
        $stmt = $this->db->prepare('INSERT INTO ticket_attachments (ticket_id, uploaded_by, uploaded_by_name, source, original_name, stored_name, file_path, mime_type, file_size, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$ticketId, $uploadedBy, $uploadedByName, $source, $file['name'], $stored, $relativePath, $mime, (int) $file['size'], $remarks]);
        $this->logStatus($ticketId, null, 'Attachment Added', $uploadedBy, $uploadedByName, 'Attachment uploaded: ' . $file['name']);
        return null;
    }

    public function attachments(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ticket_attachments WHERE ticket_id = ? ORDER BY created_at DESC');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function feedback(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ticket_feedback WHERE ticket_id = ? ORDER BY created_at DESC');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function statusLogs(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT tsl.*, u.name changed_by_user_name FROM ticket_status_logs tsl LEFT JOIN users u ON u.id = tsl.changed_by WHERE tsl.ticket_id = ? ORDER BY tsl.created_at DESC');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function usedStatuses(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT DISTINCT new_status FROM ticket_status_logs WHERE ticket_id = ?');
        $stmt->execute([$ticketId]);
        return array_column($stmt->fetchAll(), 'new_status');
    }

    public function availableTechnicalStatuses(int $ticketId): array
    {
        $ticket = $this->find($ticketId);
        return match ($ticket['status'] ?? '') {
            'Assigned' => ['In Progress', 'Pending', 'Completed'],
            'In Progress' => ['Pending', 'Completed', self::TECH_CANCEL_STATUS],
            'Pending' => ['In Progress', 'Completed'],
            self::RETURNED_STATUS => ['In Progress', 'Pending', 'Completed'],
            default => [],
        };
    }

    public function assignments(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT ta.*, ato.name assigned_to_name, prev.name previous_assignee_name, aby.name assigned_by_name FROM ticket_assignments ta JOIN users ato ON ato.id = ta.assigned_to LEFT JOIN users prev ON prev.id = ta.previous_assignee JOIN users aby ON aby.id = ta.assigned_by WHERE ta.ticket_id = ? ORDER BY ta.assigned_at DESC, ta.id DESC');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function currentAssignees(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT ta.*, u.name, u.email FROM ticket_assignees ta JOIN users u ON u.id = ta.user_id WHERE ta.ticket_id = ? AND ta.removed_at IS NULL ORDER BY FIELD(ta.assignment_role, "primary", "supporting"), u.name');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function canUserUpdate(int $ticketId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ticket_assignees WHERE ticket_id = ? AND user_id = ? AND removed_at IS NULL');
        $stmt->execute([$ticketId, $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function histories(int $ticketId): array
    {
        return [
            'endorsements' => $this->rows('SELECT te.*, u.name endorsed_by_name FROM ticket_endorsements te JOIN users u ON u.id = te.endorsed_by WHERE te.ticket_id = ? ORDER BY te.created_at DESC', [$ticketId]),
            'reopens' => $this->rows('SELECT trl.*, u.name reopened_by_user_name FROM ticket_reopen_logs trl LEFT JOIN users u ON u.id = trl.reopened_by WHERE trl.ticket_id = ? ORDER BY trl.created_at DESC', [$ticketId]),
            'escalations' => $this->rows('SELECT te.*, u.name escalated_to_name FROM ticket_escalations te LEFT JOIN users u ON u.id = te.escalated_to_user WHERE te.ticket_id = ? ORDER BY te.created_at DESC', [$ticketId]),
        ];
    }

    public function overdueTickets(?array $user = null): array
    {
        $filters = ['overdue' => '1'];
        if (($user['role'] ?? '') === 'technical') {
            $filters['assigned_to'] = (string) $user['id'];
        }
        return $this->list($filters);
    }

    public function escalateOverdue(?int $escalatedBy = null): array
    {
        $this->refreshSlaStatuses();
        $tickets = $this->overdueTickets();
        $userModel = new User();
        $supervisors = $userModel->supervisors();
        $created = [];

        foreach ($tickets as $ticket) {
            $type = $ticket['sla_status'] === 'Response Overdue' ? 'response_overdue' : 'resolution_overdue';
            $noticeKey = $type . ':' . $ticket['id'];
            foreach ($supervisors as $supervisor) {
                try {
                    $stmt = $this->db->prepare('INSERT INTO ticket_escalations (ticket_id, escalation_type, escalated_to_role, escalated_to_user, escalated_by, reason, notice_key) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([(int) $ticket['id'], $type, $supervisor['role'] ?? 'supervisor', (int) $supervisor['id'], $escalatedBy, $ticket['sla_status'] . ' for ' . $ticket['ticket_no'], $noticeKey . ':' . $supervisor['id']]);
                    $created[] = ['ticket' => $ticket, 'recipient' => $supervisor, 'type' => $type];
                } catch (PDOException $exception) {
                    continue;
                }
            }
        }

        return $created;
    }

    public function refreshSlaStatuses(?int $ticketId = null): void
    {
        $where = $ticketId ? ' AND id = ?' : '';
        $params = $ticketId ? [$ticketId] : [];

        $stmt = $this->db->prepare('UPDATE tickets SET sla_status = "Response Overdue", sla_breached_at = COALESCE(sla_breached_at, NOW()) WHERE status NOT IN ("Completed","Confirmed Completed","Cancelled") AND first_responded_at IS NULL AND response_due_at IS NOT NULL AND response_due_at < NOW()' . $where);
        $stmt->execute($params);

        $stmt = $this->db->prepare('UPDATE tickets SET sla_status = "Resolution Overdue", sla_breached_at = COALESCE(sla_breached_at, NOW()) WHERE status NOT IN ("Completed","Confirmed Completed","Cancelled") AND resolution_due_at IS NOT NULL AND resolution_due_at < NOW()' . $where);
        $stmt->execute($params);
    }

    public function slaTargets(string $priority): array
    {
        return match ($this->normalizePriority($priority)) {
            'Critical' => ['response_hours' => 1, 'resolution_hours' => 8],
            'High' => ['response_hours' => 4, 'resolution_hours' => 48],
            'Low' => ['response_hours' => 24, 'resolution_hours' => 120],
            default => ['response_hours' => 8, 'resolution_hours' => 72],
        };
    }

    private function addCurrentAssignee(int $ticketId, int $assignedTo, int $assignedBy, string $role, ?string $notes): void
    {
        $stmt = $this->db->prepare('UPDATE ticket_assignees SET removed_at = NULL, assignment_role = ?, assigned_by = ?, assigned_at = NOW(), notes = ? WHERE ticket_id = ? AND user_id = ? AND removed_at IS NULL');
        $stmt->execute([$role, $assignedBy, $notes, $ticketId, $assignedTo]);
        if ($stmt->rowCount() === 0) {
            $stmt = $this->db->prepare('INSERT INTO ticket_assignees (ticket_id, user_id, assignment_role, assigned_by, notes) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$ticketId, $assignedTo, $role, $assignedBy, $notes]);
        }
    }

    private function nextTicketNumber(): string
    {
        return 'ICTSD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    public function logStatus(int $ticketId, ?string $oldStatus, string $newStatus, ?int $userId, ?string $changedByName, ?string $remarks): void
    {
        $stmt = $this->db->prepare('INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by, changed_by_name, remarks) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$ticketId, $oldStatus, $newStatus, $userId, $changedByName, $remarks]);
    }

    private function logReopen(int $ticketId, string $oldStatus, string $newStatus, ?int $userId, ?string $userName, string $reason): void
    {
        $stmt = $this->db->prepare('INSERT INTO ticket_reopen_logs (ticket_id, old_status, new_status, reopened_by, reopened_by_name, reason) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$ticketId, $oldStatus, $newStatus, $userId, $userName, $reason]);
    }

    private function baseSelect(): string
    {
        return 'SELECT t.*, r.name region_name, o.name office_name, sc.name category_name, si.name service_name, assignee.name assigned_to_name, assigner.name assigned_by_name, COALESCE(completed_by.name, completed_log.changed_by_name) completed_by_name,
                (SELECT GROUP_CONCAT(CONCAT(u2.name, " (", tas.assignment_role, ")") ORDER BY FIELD(tas.assignment_role, "primary", "supporting"), u2.name SEPARATOR ", ") FROM ticket_assignees tas JOIN users u2 ON u2.id = tas.user_id WHERE tas.ticket_id = t.id AND tas.removed_at IS NULL) active_assignee_names,
                (SELECT CONCAT(UPPER(tf.resolved_yes_no), COALESCE(CONCAT(" / Rating ", tf.rating), "")) FROM ticket_feedback tf WHERE tf.ticket_id = t.id ORDER BY tf.created_at DESC, tf.id DESC LIMIT 1) latest_feedback
            FROM tickets t
            JOIN regions r ON r.id = t.region_id
            JOIN offices o ON o.id = t.office_id
            JOIN service_categories sc ON sc.id = t.service_category_id
            JOIN service_items si ON si.id = t.service_item_id
            LEFT JOIN users assignee ON assignee.id = t.assigned_to
            LEFT JOIN users assigner ON assigner.id = t.assigned_by
            LEFT JOIN ticket_status_logs completed_log ON completed_log.id = (
                SELECT tsl.id
                FROM ticket_status_logs tsl
                WHERE tsl.ticket_id = t.id AND tsl.new_status = "Completed"
                ORDER BY tsl.created_at DESC, tsl.id DESC
                LIMIT 1
            )
            LEFT JOIN users completed_by ON completed_by.id = completed_log.changed_by';
    }

    private function filterSql(array $filters): array
    {
        $where = [];
        $params = [];
        foreach (['region_id', 'office_id', 'service_category_id', 'service_item_id', 'status', 'priority', 'sla_status'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                $where[] = "t.{$field} = ?";
                $params[] = $filters[$field];
            }
        }
        if (($filters['assigned_to'] ?? '') !== '') {
            $where[] = '(t.assigned_to = ? OR EXISTS (SELECT 1 FROM ticket_assignees tas WHERE tas.ticket_id = t.id AND tas.user_id = ? AND tas.removed_at IS NULL))';
            $params[] = $filters['assigned_to'];
            $params[] = $filters['assigned_to'];
        }
        if (($filters['overdue'] ?? '') !== '') {
            $where[] = 't.sla_status IN ("Response Overdue","Resolution Overdue","Breached")';
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(t.requested_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(t.requested_at) <= ?';
            $params[] = $filters['date_to'];
        }

        return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function roleScopeSql(?array $user): array
    {
        if (($user['role'] ?? '') !== 'technical') {
            return ['', []];
        }

        return [' WHERE EXISTS (SELECT 1 FROM ticket_assignees tas WHERE tas.ticket_id = t.id AND tas.user_id = ? AND tas.removed_at IS NULL)', [(int) $user['id']]];
    }

    private function rows(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function normalizePriority(string $priority): string
    {
        return in_array($priority, self::PRIORITIES, true) ? $priority : 'Medium';
    }

    private function serviceItemPriority(int $serviceItemId): string
    {
        $stmt = $this->db->prepare('SELECT default_priority FROM service_items WHERE id = ?');
        $stmt->execute([$serviceItemId]);
        return $this->normalizePriority((string) $stmt->fetchColumn());
    }
}
