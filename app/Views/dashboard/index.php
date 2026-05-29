<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Dashboard</h1>
    <?php if (\App\Core\Auth::canManage()): ?>
        <form method="post" action="<?= url('tickets/escalate-overdue') ?>" data-confirm="Create escalation notices for overdue tickets?">
            <?= csrf_field() ?>
            <button class="btn btn-outline-danger btn-sm">Escalate Overdue</button>
        </form>
    <?php endif; ?>
</div>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        'Total Submitted' => $stats['total'],
        'Pending/Unassigned' => $stats['unassigned'],
        'Overdue SLA' => $stats['overdue'],
        'Assigned' => $stats['Assigned'],
        'In Progress' => $stats['In Progress'],
        'Completed' => $stats['Completed'],
        'Confirmed Completed' => $stats['Confirmed Completed'],
    ];
    ?>
    <?php foreach ($cards as $label => $value): ?>
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="stat-card">
                <div class="text-muted small"><?= e($label) ?></div>
                <div class="fs-3 fw-bold"><?= (int) $value ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($overdueTickets): ?>
<div class="card mb-3">
    <div class="card-header bg-white fw-semibold">Overdue Tickets</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Ticket</th><th>Severity</th><th>SLA</th><th>Assignee</th><th>Resolution Due</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($overdueTickets, 0, 10) as $ticket): ?>
                <tr>
                    <td><a href="<?= url('tickets/' . $ticket['id']) ?>"><?= e($ticket['ticket_no']) ?></a></td>
                    <td><?= e($ticket['priority']) ?></td>
                    <td><?= e($ticket['sla_status']) ?></td>
                    <td><?= e($ticket['active_assignee_names'] ?: ($ticket['assigned_to_name'] ?? 'Unassigned')) ?></td>
                    <td><?= e($ticket['resolution_due_at'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<div class="row g-3">
    <?php foreach ($breakdowns as $title => $rows): ?>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Tickets by <?= e($title === 'priority' ? 'Severity' : ucfirst($title)) ?></div>
                <div class="card-body">
                    <?php if (!$rows): ?>
                        <p class="text-muted mb-0">No data yet.</p>
                    <?php endif; ?>
                    <?php foreach ($rows as $row): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?= e($row['label']) ?></span>
                            <span class="fw-semibold"><?= (int) $row['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
