<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Tickets</h1>
    <a class="btn btn-outline-primary btn-sm" href="<?= url('request') ?>">Public Form</a>
</div>
<form class="filter-bar row g-2 mb-3">
    <div class="col-md-2"><input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control" aria-label="Date from"></div>
    <div class="col-md-2"><input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control" aria-label="Date to"></div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>><?= e($status) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="region_id" class="form-select">
            <option value="">All regions</option>
            <?php foreach ($library->regions() as $region): ?>
                <option value="<?= (int) $region['id'] ?>" <?= ((string)($filters['region_id'] ?? '') === (string)$region['id']) ? 'selected' : '' ?>><?= e($region['code']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="priority" class="form-select">
            <option value="">All severities</option>
            <?php foreach ($priorities as $priority): ?>
                <option value="<?= e($priority) ?>" <?= (($filters['priority'] ?? '') === $priority) ? 'selected' : '' ?>><?= e($priority) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="assigned_to" class="form-select">
            <option value="">All technical personnel</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= (int) $user['id'] ?>" <?= ((string)($filters['assigned_to'] ?? '') === (string)$user['id']) ? 'selected' : '' ?>><?= e($user['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="overdue" class="form-select">
            <option value="">All SLA</option>
            <option value="1" <?= (($filters['overdue'] ?? '') === '1') ? 'selected' : '' ?>>Overdue only</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Ticket</th><th>Requester</th><th>Service</th><th>Location</th><th>Severity/SLA</th><th>Assigned To</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><a href="<?= url('tickets/' . $ticket['id']) ?>" class="fw-semibold"><?= e($ticket['ticket_no']) ?></a><br><small class="text-muted"><?= e($ticket['requested_at']) ?></small></td>
                    <td><?= e($ticket['requester_name']) ?><br><small class="text-muted"><?= e($ticket['requester_email']) ?></small></td>
                    <td><?= e($ticket['category_name']) ?><br><small class="text-muted"><?= e($ticket['service_name']) ?></small></td>
                    <td><?= e($ticket['region_name']) ?><br><small class="text-muted"><?= e($ticket['office_name']) ?></small></td>
                    <td><span class="badge text-bg-dark"><?= e($ticket['priority']) ?></span><br><small class="text-muted"><?= e($ticket['sla_status']) ?></small></td>
                    <td><?= e($ticket['active_assignee_names'] ?: ($ticket['assigned_to_name'] ?? 'Unassigned')) ?></td>
                    <td><?= status_badge($ticket['status']) ?></td>
                    <td><a class="btn btn-sm btn-outline-secondary" href="<?= url('tickets/' . $ticket['id']) ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tickets): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No tickets found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
