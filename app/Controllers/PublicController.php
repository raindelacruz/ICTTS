<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Library;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\EmailService;

class PublicController extends Controller
{
    public function requestForm(): void
    {
        $library = new Library();
        $this->publicView('public/request_form', [
            'categories' => $library->categories(),
            'regions' => $library->regions(),
        ]);
    }

    public function submitRequest(): void
    {
        Csrf::validate($_POST['_csrf'] ?? null);
        $data = $this->requestData();
        $errors = $this->validateRequest($data);

        if ($errors) {
            flash('error', implode(' ', $errors));
            $this->redirect('request');
        }

        $ticketModel = new Ticket();
        $ticketId = $ticketModel->create($data);
        $uploadError = $this->saveUploadedAttachments($ticketModel, $ticketId, $data['requester_name']);
        $ticket = $ticketModel->find($ticketId);
        ActivityLogger::log('Ticket submission', 'ticket', (string) $ticketId, 'Public request submitted.', $data['requester_name']);

        $mailer = new EmailService();
        $mailer->submissionRequester($ticket);
        $mailer->submissionIct($ticket);

        $supervisors = (new User())->supervisors();
        (new Notification())->createForUsers(
            array_column($supervisors, 'id'),
            'New ticket submitted',
            $ticket['ticket_no'] . ' was submitted by ' . $ticket['requester_name'] . '.',
            'tickets/' . $ticketId
        );

        flash('success', 'Your request has been submitted. Ticket number: ' . $ticket['ticket_no'] . ($uploadError ? ' Attachment note: ' . $uploadError : ''));
        $this->redirect('request');
    }

    public function offices(): void
    {
        $regionId = (int) ($_GET['region_id'] ?? 0);
        $this->json(['offices' => (new Library())->offices($regionId)]);
    }

    public function services(): void
    {
        $categoryId = (int) ($_GET['category_id'] ?? 0);
        $this->json(['services' => (new Library())->serviceItems($categoryId)]);
    }

    public function confirmForm(string $token): void
    {
        $ticket = (new Ticket())->findByToken($token);
        $this->publicView('public/confirm', compact('ticket', 'token'));
    }

    public function confirmComplete(string $token): void
    {
        Csrf::validate($_POST['_csrf'] ?? null);
        $ticketModel = new Ticket();
        $resolved = ($_POST['resolved_yes_no'] ?? 'yes') === 'no' ? 'no' : 'yes';
        $comments = trim($_POST['feedback_comments'] ?? '');
        if ($resolved === 'no' && $comments === '') {
            flash('error', 'Please provide a reason when returning the ticket for further action.');
            $this->redirect('confirm/' . $token);
        }
        $ticket = $ticketModel->confirmByToken($token, [
            'resolved_yes_no' => $resolved,
            'rating' => $_POST['rating'] ?? null,
            'feedback_comments' => $comments,
        ]);
        if (!$ticket) {
            flash('error', 'This confirmation link is invalid, expired, or already used.');
            $this->redirect('confirm/' . $token);
        }

        ActivityLogger::log('Requester confirmation', 'ticket', (string) $ticket['id'], $resolved === 'yes' ? 'Requester confirmed completion.' : 'Requester returned ticket for further action.', $ticket['requester_name']);
        $userModel = new User();
        $supervisors = $userModel->supervisors();
        (new EmailService())->requesterConfirmed($ticket, array_merge($supervisors, $ticket['assigned_to'] ? [$userModel->find((int) $ticket['assigned_to'])] : []));
        (new Notification())->createForUsers(
            array_merge(array_column($supervisors, 'id'), $ticket['assigned_to'] ? [(int) $ticket['assigned_to']] : []),
            'Ticket confirmed completed',
            $ticket['ticket_no'] . ' was confirmed completed by ' . $ticket['requester_name'] . '.',
            'tickets/' . $ticket['id']
        );
        flash('success', $resolved === 'yes' ? 'Thank you. The ticket has been confirmed completed.' : 'Thank you. The ticket has been returned for further action.');
        $this->redirect('confirm/' . $token);
    }

    private function requestData(): array
    {
        $categoryId = (int) ($_POST['service_category_id'] ?? 0);
        $responsibleGroup = null;
        foreach ((new Library())->categories() as $category) {
            if ((int) $category['id'] === $categoryId) {
                $responsibleGroup = $category['name'];
                break;
            }
        }

        return [
            'requester_name' => trim($_POST['requester_name'] ?? ''),
            'requester_position' => trim($_POST['requester_position'] ?? ''),
            'requester_email' => trim($_POST['requester_email'] ?? ''),
            'requester_contact' => trim($_POST['requester_contact'] ?? ''),
            'region_id' => (int) ($_POST['region_id'] ?? 0),
            'office_id' => (int) ($_POST['office_id'] ?? 0),
            'requested_for' => str_replace('T', ' ', trim($_POST['requested_for'] ?? '')),
            'service_category_id' => $categoryId,
            'service_item_id' => (int) ($_POST['service_item_id'] ?? 0),
            'responsible_group' => $responsibleGroup,
            'description' => trim($_POST['description'] ?? ''),
        ];
    }

    private function validateRequest(array $data): array
    {
        $errors = [];
        foreach (['requester_name', 'requester_email', 'requested_for', 'description'] as $field) {
            if ($data[$field] === '') {
                $errors[] = 'Please complete all required fields.';
                break;
            }
        }
        if (!filter_var($data['requester_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($data['requester_contact'] !== '' && !preg_match('/^[0-9+\-\s().]{7,30}$/', $data['requester_contact'])) {
            $errors[] = 'Please enter a valid contact number.';
        }
        if (strlen($data['description']) < 10 || strlen($data['description']) > 5000) {
            $errors[] = 'Description must be between 10 and 5000 characters.';
        }
        foreach (['region_id', 'office_id', 'service_category_id', 'service_item_id'] as $field) {
            if ($data[$field] <= 0) {
                $errors[] = 'Please select valid dropdown values.';
                break;
            }
        }
        if (!$errors && !(new Library())->validRequestSelections($data['region_id'], $data['office_id'], $data['service_category_id'], $data['service_item_id'])) {
            $errors[] = 'Selected office or service does not match the chosen category/region.';
        }
        if (strtotime($data['requested_for']) === false) {
            $errors[] = 'Please enter a valid requested date and time.';
        }
        return array_unique($errors);
    }

    private function saveUploadedAttachments(Ticket $ticketModel, int $ticketId, string $requesterName): ?string
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
            $error = $ticketModel->attachFile($ticketId, $file, 'requester', null, $requesterName, 'Initial requester attachment');
            if ($error) {
                return $error;
            }
        }

        ActivityLogger::log('Requester attachment upload', 'ticket', (string) $ticketId, 'Requester uploaded attachment(s).', $requesterName);
        return null;
    }
}
