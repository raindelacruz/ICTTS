<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Library;
use App\Models\Ticket;
use App\Models\User;

class ReportController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $filters = $_GET;
        if ((Auth::user()['role'] ?? '') === 'technical') {
            $filters['assigned_to'] = Auth::id();
        }
        $this->view('reports/index', [
            'tickets' => (new Ticket())->list($filters),
            'filters' => $filters,
            'library' => new Library(),
            'users' => (new User())->technicalActive(),
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
            'slaStatuses' => Ticket::SLA_STATUSES,
        ]);
    }
}
