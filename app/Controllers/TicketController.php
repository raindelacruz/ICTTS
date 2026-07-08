<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Library;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\EmailService;

class TicketController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $filters = $_GET;
        if ((Auth::user()['role'] ?? '') === 'technical') {
            $filters['assigned_to'] = Auth::id();
        }
        $this->view('tickets/index', [
            'tickets' => (new Ticket())->list($filters),
            'filters' => $filters,
            'library' => new Library(),
            'users' => (new User())->technicalActive(),
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
            'slaStatuses' => Ticket::SLA_STATUSES,
        ]);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $ticketModel = new Ticket();
        $ticket = $ticketModel->find((int) $id);
        if (!$ticket) {
            http_response_code(404);
            exit('Ticket not found');
        }
        if (!$this->canView($ticketModel, $ticket)) {
            http_response_code(403);
            exit('Forbidden');
        }

        $this->view('tickets/show', [
            'ticket' => $ticket,
            'assignments' => $ticketModel->assignments((int) $id),
            'currentAssignees' => $ticketModel->currentAssignees((int) $id),
            'statusLogs' => $ticketModel->statusLogs((int) $id),
            'attachments' => $ticketModel->attachments((int) $id),
            'feedback' => $ticketModel->feedback((int) $id),
            'histories' => $ticketModel->histories((int) $id),
            'technicalUsers' => (new User())->technicalActive((int) $ticket['service_category_id']),
            'library' => new Library(),
            'statuses' => $ticketModel->availableTechnicalStatuses((int) $id),
        ]);
    }

    public function assign(string $id): void
    {
        Auth::requireLogin();
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketId = (int) $id;
        $ticket = (new Ticket())->find($ticketId);
        if (!$ticket) {
            http_response_code(404);
            exit('Ticket not found');
        }

        $assignedTo = Auth::canManage() ? (int) ($_POST['assigned_to'] ?? 0) : Auth::id();
        $role = Auth::canManage() ? ($_POST['assignment_role'] ?? 'primary') : 'primary';
        $userModel = new User();
        if (!$assignedTo || !$userModel->activeTechnicalCanHandle($assignedTo, (int) $ticket['service_category_id']) || ($ticket['assigned_to'] && $role === 'primary') || in_array($ticket['status'], ['Completed', 'Confirmed Completed', 'Cancelled'], true)) {
            flash('error', 'This ticket cannot be assigned by your account.');
            $this->redirect('tickets/' . $ticketId);
        }

        $ticketModel = new Ticket();
        $ticketModel->assign($ticketId, $assignedTo, Auth::id(), trim($_POST['notes'] ?? '') ?: null, $role);
        $updated = $ticketModel->find($ticketId);
        $assignee = $userModel->find($assignedTo);
        $mailer = new EmailService();
        $mailer->assignment($updated, $assignee);
        $mailer->assignmentRequester($updated, $assignee);
        (new Notification())->create(
            $assignedTo,
            'Ticket assigned to you',
            $updated['ticket_no'] . ' has been assigned to you.',
            'tickets/' . $ticketId
        );
        ActivityLogger::log('Ticket assignment', 'ticket', (string) $ticketId, ucfirst($role) . ' assignment to ' . $assignee['name']);
        flash('success', 'Ticket assigned successfully.');
        $this->redirect('tickets/' . $ticketId);
    }

    public function reassign(string $id): void
    {
        Auth::requireRole(['admin', 'unit_head', 'division_chief']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketId = (int) $id;
        $ticket = (new Ticket())->find($ticketId);
        $assignedTo = (int) ($_POST['assigned_to'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $userModel = new User();
        if (!$ticket || !$assignedTo || !$userModel->activeTechnicalCanHandle($assignedTo, (int) $ticket['service_category_id']) || $reason === '' || in_array($ticket['status'], ['Completed', 'Confirmed Completed', 'Cancelled'], true)) {
            flash('error', 'Reassignment requires a new primary assignee and reason.');
            $this->redirect('tickets/' . $ticketId);
        }

        $ticketModel = new Ticket();
        $ticketModel->reassign($ticketId, $assignedTo, Auth::id(), $reason, trim($_POST['notes'] ?? '') ?: null);
        $assignee = $userModel->find($assignedTo);
        (new Notification())->create($assignedTo, 'Ticket reassigned to you', $ticketModel->find($ticketId)['ticket_no'] . ' has been reassigned to you.', 'tickets/' . $ticketId);
        ActivityLogger::log('Ticket reassignment', 'ticket', (string) $ticketId, 'Reassigned to ' . ($assignee['name'] ?? 'technical personnel') . '. Reason: ' . $reason);
        flash('success', 'Ticket reassigned successfully.');
        $this->redirect('tickets/' . $ticketId);
    }

    public function updateStatus(string $id): void
    {
        Auth::requireLogin();
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketId = (int) $id;
        $status = $_POST['status'] ?? '';
        $remarks = trim($_POST['remarks'] ?? '') ?: null;
        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($ticketId);

        if (!$ticket || !in_array($status, $ticketModel->availableTechnicalStatuses($ticketId), true) || !$this->canUpdate($ticketModel, $ticket)) {
            flash('error', 'Status update is not allowed.');
            $this->redirect('tickets/' . $ticketId);
        }

        $ticketModel->updateStatus($ticketId, $status, Auth::id(), $remarks);
        $uploadError = $this->saveUploadedAttachments($ticketModel, $ticketId, 'technical', $remarks);
        $updated = $ticketModel->find($ticketId);
        ActivityLogger::log('Status update', 'ticket', (string) $ticketId, 'Changed to ' . $status);
        (new Notification())->createForUsers(
            array_column((new User())->supervisors(), 'id'),
            'Ticket status updated',
            $updated['ticket_no'] . ' is now ' . $status . '.',
            'tickets/' . $ticketId
        );

        if ($status === 'Completed') {
            $token = $ticketModel->createConfirmationToken($ticketId);
            (new EmailService())->completionConfirmation($updated, $token);
            ActivityLogger::log('Completion tagging', 'ticket', (string) $ticketId, 'Confirmation email sent to requester.');
        }

        flash($uploadError ? 'error' : 'success', $uploadError ?: 'Ticket status updated.');
        $this->redirect('tickets/' . $ticketId);
    }

    public function attach(string $id): void
    {
        Auth::requireLogin();
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketId = (int) $id;
        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($ticketId);
        if (!$ticket || !$this->canUpdate($ticketModel, $ticket)) {
            flash('error', 'Attachment upload is not allowed for this ticket.');
            $this->redirect('tickets/' . $ticketId);
        }
        $error = $this->saveUploadedAttachments($ticketModel, $ticketId, 'technical', trim($_POST['remarks'] ?? '') ?: null);
        ActivityLogger::log('Attachment upload', 'ticket', (string) $ticketId, $error ?: 'Technical attachment uploaded.');
        flash($error ? 'error' : 'success', $error ?: 'Attachment uploaded.');
        $this->redirect('tickets/' . $ticketId);
    }

    public function downloadAttachment(string $id): void
    {
        Auth::requireLogin();

        $ticketModel = new Ticket();
        $attachment = $ticketModel->attachment((int) $id);
        if (!$attachment) {
            http_response_code(404);
            exit('Attachment not found');
        }

        $ticket = $ticketModel->find((int) $attachment['ticket_id']);
        if (!$ticket || !$this->canView($ticketModel, $ticket)) {
            http_response_code(403);
            exit('Forbidden');
        }

        $path = $ticketModel->attachmentAbsolutePath($attachment);
        if ($path === null || !is_file($path) || !is_readable($path)) {
            http_response_code(404);
            exit('Attachment file not found');
        }

        $filename = preg_replace('/[^A-Za-z0-9._ -]/', '_', (string) $attachment['original_name']) ?: 'attachment';
        header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: attachment; filename="' . addcslashes($filename, '"\\') . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function endorse(string $id): void
    {
        Auth::requireRole(['admin', 'unit_head', 'division_chief']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketId = (int) $id;
        $ticket = (new Ticket())->find($ticketId);
        $categoryId = (int) ($_POST['service_category_id'] ?? 0);
        $serviceItemId = (int) ($_POST['service_item_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if (!$ticket || $categoryId <= 0 || $serviceItemId <= 0 || $reason === '' || in_array($ticket['status'], ['Completed', 'Confirmed Completed', 'Cancelled'], true)) {
            flash('error', 'Endorsement requires a target category, service, and reason.');
            $this->redirect('tickets/' . $ticketId);
        }

        $library = new Library();
        $valid = false;
        $toGroup = '';
        foreach ($library->categories() as $category) {
            if ((int) $category['id'] === $categoryId) {
                $toGroup = $category['name'];
            }
        }
        foreach ($library->serviceItems($categoryId) as $item) {
            if ((int) $item['id'] === $serviceItemId) {
                $valid = true;
            }
        }
        if (!$valid) {
            flash('error', 'Selected endorsement service is invalid.');
            $this->redirect('tickets/' . $ticketId);
        }

        (new Ticket())->endorse($ticketId, $categoryId, $serviceItemId, $toGroup, Auth::id(), $reason);
        ActivityLogger::log('Ticket endorsement', 'ticket', (string) $ticketId, 'Endorsed to ' . $toGroup . '. Reason: ' . $reason);
        flash('success', 'Ticket endorsed successfully. Please assign the proper technical personnel.');
        $this->redirect('tickets/' . $ticketId);
    }

    public function reopen(string $id): void
    {
        Auth::requireRole(['admin', 'unit_head', 'division_chief']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketId = (int) $id;
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            flash('error', 'Reopening requires a reason.');
            $this->redirect('tickets/' . $ticketId);
        }

        (new Ticket())->reopen($ticketId, Auth::id(), $reason, $_POST['status'] ?? Ticket::RETURNED_STATUS);
        ActivityLogger::log('Ticket reopened', 'ticket', (string) $ticketId, 'Reason: ' . $reason);
        flash('success', 'Ticket returned for further action.');
        $this->redirect('tickets/' . $ticketId);
    }

    public function escalateOverdue(): void
    {
        Auth::requireRole(['admin', 'unit_head', 'division_chief']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketModel = new Ticket();
        $created = $ticketModel->escalateOverdue(Auth::id());
        $notification = new Notification();
        $mailer = new EmailService();
        foreach ($created as $event) {
            $notification->create((int) $event['recipient']['id'], 'Ticket escalated', $event['ticket']['ticket_no'] . ' is ' . $event['ticket']['sla_status'] . '.', 'tickets/' . $event['ticket']['id']);
            $mailer->escalation($event['ticket'], $event['recipient']);
        }
        ActivityLogger::log('SLA escalation scan', 'ticket', null, count($created) . ' escalation notices created.');
        flash('success', count($created) . ' new escalation notice(s) created.');
        $this->redirect('dashboard');
    }

    private function canUpdate(Ticket $ticketModel, array $ticket): bool
    {
        return (Auth::user()['role'] ?? '') === 'technical' && $ticketModel->canUserUpdate((int) $ticket['id'], Auth::id());
    }

    private function canView(Ticket $ticketModel, array $ticket): bool
    {
        if (Auth::canManage()) {
            return true;
        }

        return (Auth::user()['role'] ?? '') === 'technical'
            && $ticketModel->canUserUpdate((int) $ticket['id'], (int) Auth::id());
    }

    private function saveUploadedAttachments(Ticket $ticketModel, int $ticketId, string $source, ?string $remarks): ?string
    {
        if (empty($_FILES['attachments']['name'][0])) {
            return null;
        }

        foreach ($_FILES['attachments']['name'] as $index => $name) {
            $file = [
                'name' => $name,
                'type' => $_FILES['attachments']['type'][$index] ?? '',
                'tmp_name' => $_FILES['attachments']['tmp_name'][$index] ?? '',
                'error' => $_FILES['attachments']['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES['attachments']['size'][$index] ?? 0,
            ];
            $error = $ticketModel->attachFile($ticketId, $file, $source, Auth::id(), Auth::user()['name'] ?? null, $remarks);
            if ($error) {
                return $error;
            }
        }

        return null;
    }
}
