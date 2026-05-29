<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use Throwable;

class EmailService
{
    public function send(?int $ticketId, string $to, string $subject, string $body): bool
    {
        $status = 'logged';
        $error = null;

        try {
            if ($this->phpMailerAvailable()) {
                $mail = new PHPMailer(true);
                $mail->CharSet = 'UTF-8';
                if (SMTP_ENABLED) {
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->Port = SMTP_PORT;
                    $mail->SMTPAuth = SMTP_USERNAME !== '';
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = SMTP_ENCRYPTION;
                }
                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
                $mail->send();
                $status = 'sent';
            } else {
                if (SMTP_ENABLED) {
                    throw new RuntimeException('SMTP is enabled, but PHPMailer is not installed or cannot be loaded. Run composer install on the server so vendor/autoload.php exists.');
                }

                $headers = 'From: ' . MAIL_FROM . "\r\nContent-Type: text/html; charset=UTF-8\r\n";
                $status = @mail($to, $subject, $body, $headers) ? 'sent' : 'logged';
            }
        } catch (Throwable $exception) {
            $status = 'failed';
            $error = $exception->getMessage();
        }

        $this->log($ticketId, $to, $subject, $body, $status, $error);
        return $status === 'sent';
    }

    public function diagnostics(): array
    {
        return [
            'smtp_enabled' => SMTP_ENABLED ? 'Yes' : 'No',
            'smtp_host' => SMTP_HOST,
            'smtp_port' => (string) SMTP_PORT,
            'smtp_username' => SMTP_USERNAME,
            'smtp_password_configured' => SMTP_PASSWORD !== '' ? 'Yes' : 'No',
            'smtp_encryption' => SMTP_ENCRYPTION !== '' ? SMTP_ENCRYPTION : '(none)',
            'mail_from' => MAIL_FROM,
            'mail_from_name' => MAIL_FROM_NAME,
            'ict_notification_email' => ICT_NOTIFICATION_EMAIL,
            'phpmailer_available' => $this->phpMailerAvailable() ? 'Yes' : 'No',
            'openssl_loaded' => extension_loaded('openssl') ? 'Yes' : 'No',
        ];
    }

    public function submissionRequester(array $ticket): void
    {
        $body = '<p>Dear ' . e($ticket['requester_name']) . ',</p>'
            . '<p>Thank you for submitting your request. Your service ticket has been created with the following details:</p>'
            . '<p>'
            . '<strong>Ticket Number:</strong> ' . e($ticket['ticket_no']) . '<br>'
            . '<strong>Name of Requestee:</strong> ' . e($ticket['requester_name']) . '<br>'
            . '<strong>Email Address:</strong> ' . e($ticket['requester_email']) . '<br>'
            . '<strong>Contact Number:</strong> ' . e($ticket['requester_contact']) . '<br>'
            . '<strong>Category:</strong> ' . e(strtoupper($ticket['category_name'])) . '<br>'
            . '<strong>Department:</strong> ' . e(strtoupper($ticket['office_name'])) . '<br>'
            . '<strong>Specific Request:</strong> ' . e(strtoupper($ticket['service_name'])) . '<br>'
            . '<strong>Description of Request:</strong> ' . nl2br(e($ticket['description']))
            . '</p>'
            . '<p>Our ICTSD team will contact you shortly regarding your request.</p>'
            . '<p>Best regards,<br>ICTSD Support Team</p>';
        $this->send((int) $ticket['id'], $ticket['requester_email'], 'ICTSD Request Submitted - ' . $ticket['ticket_no'], $body);
    }

    public function submissionIct(array $ticket): void
    {
        $body = '<p>A new ICTSD request was submitted.</p><p><strong>Ticket No:</strong> ' . e($ticket['ticket_no']) . '</p><p><strong>Requester:</strong> ' . e($ticket['requester_name']) . '</p>';
        $this->send((int) $ticket['id'], ICT_NOTIFICATION_EMAIL, 'New ICTSD Request - ' . $ticket['ticket_no'], $body);
    }

    public function assignment(array $ticket, array $assignee): void
    {
        $contactLine = trim((string) ($ticket['requester_contact'] ?? '')) !== ''
            ? '<strong>Contact Number:</strong> ' . e($ticket['requester_contact']) . '<br>'
            : '';

        $body = '<p>Good day ' . e($assignee['name']) . ',</p>'
            . '<p>You have been assigned an ICT service request. Please see the request details below:</p>'
            . '<p>'
            . '<strong>Ticket Number:</strong> ' . e($ticket['ticket_no']) . '<br>'
            . '<strong>Name of Requestee:</strong> ' . e($ticket['requester_name']) . '<br>'
            . '<strong>Email Address:</strong> ' . e($ticket['requester_email']) . '<br>'
            . $contactLine
            . '<strong>Department:</strong> ' . e($ticket['office_name']) . '<br>'
            . '<strong>Category:</strong> ' . e($ticket['category_name']) . '<br>'
            . '<strong>Specific Concern:</strong> ' . e($ticket['service_name']) . '<br>'
            . '<strong>Description:</strong> ' . nl2br(e($ticket['description']))
            . '</p>'
            . '<p>Kindly coordinate with the requester and provide the necessary technical assistance.</p>'
            . '<p>Regards,<br>ICTSD Support Team</p>';
        $this->send((int) $ticket['id'], $assignee['email'], 'ICTSD Ticket Assigned - ' . $ticket['ticket_no'], $body);
    }

    public function assignmentRequester(array $ticket, array $assignee): void
    {
        $body = '<p>Good day,</p>'
            . '<p>Thank you for submitting your service request with <strong>Ticket #: ' . e($ticket['ticket_no']) . '</strong>.</p>'
            . '<p>Please be informed that your request has been assigned to <strong>' . e($assignee['name']) . '</strong>, our Technical Support Personnel who will be assisting and coordinating with you regarding your concern. Our assigned personnel shall accommodate and attend to your request shortly.</p>'
            . '<p>We sincerely appreciate your patience and cooperation as we work to provide the necessary assistance and support.</p>'
            . '<p>Should you have additional concerns or clarifications, please feel free to coordinate with us.</p>'
            . '<p>Thank you.</p>'
            . '<p>Regards,<br>ICTSD Support Team</p>';

        $this->send((int) $ticket['id'], $ticket['requester_email'], 'ICTSD Ticket Assigned - ' . $ticket['ticket_no'], $body);
    }

    public function completionConfirmation(array $ticket, string $token): void
    {
        $link = public_url('confirm/' . $token);
        $completedBy = $ticket['completed_by_name'] ?? $ticket['assigned_to_name'] ?? 'ICTSD technical personnel';
        $body = '<p>The ICTSD technical personnel marked your request as completed.</p><p><strong>Ticket No:</strong> ' . e($ticket['ticket_no']) . '</p><p><strong>Marked Completed By:</strong> ' . e($completedBy) . '</p><p><a href="' . e($link) . '" style="display:inline-block;padding:10px 16px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:4px;">Confirm Completion</a></p>';
        $this->send((int) $ticket['id'], $ticket['requester_email'], 'Confirm ICTSD Ticket Completion - ' . $ticket['ticket_no'], $body);
    }

    public function requesterConfirmed(array $ticket, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            if (!empty($recipient['email'])) {
                $returned = ($ticket['status'] ?? '') === 'Returned for Further Action';
                $body = '<p>The requester ' . ($returned ? 'returned this ticket for further action' : 'confirmed completion') . '.</p><p><strong>Ticket No:</strong> ' . e($ticket['ticket_no']) . '</p>';
                $this->send((int) $ticket['id'], $recipient['email'], ($returned ? 'ICTSD Ticket Returned - ' : 'ICTSD Ticket Confirmed - ') . $ticket['ticket_no'], $body);
            }
        }
    }

    public function escalation(array $ticket, array $recipient): void
    {
        if (empty($recipient['email'])) {
            return;
        }

        $body = '<p>An ICTSD ticket requires escalation attention.</p>'
            . '<p><strong>Ticket No:</strong> ' . e($ticket['ticket_no']) . '<br>'
            . '<strong>Severity:</strong> ' . e($ticket['priority']) . '<br>'
            . '<strong>SLA Status:</strong> ' . e($ticket['sla_status']) . '<br>'
            . '<strong>Resolution Due:</strong> ' . e($ticket['resolution_due_at'] ?? '') . '</p>';
        $this->send((int) $ticket['id'], $recipient['email'], 'ICTSD SLA Escalation - ' . $ticket['ticket_no'], $body);
    }

    private function phpMailerAvailable(): bool
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }

        return class_exists(PHPMailer::class);
    }

    private function log(?int $ticketId, string $to, string $subject, string $body, string $status, ?string $error): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO email_logs (ticket_id, recipient_email, subject, body, status, error_message) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$ticketId, $to, $subject, $body, $status, $error]);
    }
}
