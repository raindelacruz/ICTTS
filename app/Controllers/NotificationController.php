<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAllRead(): void
    {
        Auth::requireLogin();
        Csrf::validate($_POST['_csrf'] ?? null);

        (new Notification())->markAllRead((int) Auth::id());
        $this->redirect($_POST['return_to'] ?? 'dashboard');
    }

    public function open(string $id): void
    {
        Auth::requireLogin();

        $notification = (new Notification())->markReadAndFind((int) $id, (int) Auth::id());
        $this->redirect($notification['link'] ?? 'dashboard');
    }
}
