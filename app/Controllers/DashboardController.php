<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $ticket = new Ticket();
        $this->view('dashboard/index', [
            'stats' => $ticket->dashboardStats(Auth::user()),
            'breakdowns' => $ticket->breakdowns(Auth::user()),
            'overdueTickets' => $ticket->overdueTickets(Auth::user()),
        ]);
    }
}
