<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Log;

class LogController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $log = new Log();
        $this->view('logs/index', [
            'activities' => $log->activity($_GET),
            'emails' => $log->emails(),
            'filters' => $_GET,
            'activityLimits' => Log::PAGE_SIZES,
        ]);
    }
}
