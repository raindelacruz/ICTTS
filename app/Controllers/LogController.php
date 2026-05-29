<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Log;
use App\Services\EmailService;

class LogController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $log = new Log();
        $mailer = new EmailService();
        $this->view('logs/index', [
            'activities' => $log->activity($_GET),
            'emails' => $log->emails(),
            'filters' => $_GET,
            'activityLimits' => Log::PAGE_SIZES,
            'emailDiagnostics' => $mailer->diagnostics(),
        ]);
    }

    public function testEmail(): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);

        $recipient = trim((string) ($_POST['recipient_email'] ?? ''));
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid test recipient email address.');
            $this->redirect('logs');
        }

        $body = '<p>This is a test email from the ICTSD Ticketing System.</p>'
            . '<p>If you received this message, the production SMTP configuration is working.</p>';
        $sent = (new EmailService())->send(null, $recipient, 'ICTSD SMTP Test', $body);

        flash($sent ? 'success' : 'error', $sent
            ? 'Test email sent. Please confirm it arrived in the recipient inbox.'
            : 'Test email failed. Check the latest Email Logs row for the exact error.');
        $this->redirect('logs');
    }
}
