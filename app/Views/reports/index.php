<h1 class="h4 mb-3">Reports</h1>
<form class="filter-bar row g-2 mb-3">
    <div class="col-md-2"><input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control" aria-label="Date from"></div>
    <div class="col-md-2"><input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control" aria-label="Date to"></div>
    <div class="col-md-2">
        <select name="region_id" class="form-select">
            <option value="">All regions</option>
            <?php foreach ($library->regions() as $region): ?><option value="<?= (int) $region['id'] ?>" <?= ((string)($filters['region_id'] ?? '') === (string)$region['id']) ? 'selected' : '' ?>><?= e($region['code']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="service_category_id" class="form-select">
            <option value="">All categories</option>
            <?php foreach ($library->categories() as $category): ?><option value="<?= (int) $category['id'] ?>" <?= ((string)($filters['service_category_id'] ?? '') === (string)$category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="priority" class="form-select">
            <option value="">All severities</option>
            <?php foreach ($priorities as $priority): ?><option value="<?= e($priority) ?>" <?= (($filters['priority'] ?? '') === $priority) ? 'selected' : '' ?>><?= e($priority) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="sla_status" class="form-select">
            <option value="">All SLA statuses</option>
            <?php foreach ($slaStatuses as $sla): ?><option value="<?= e($sla) ?>" <?= (($filters['sla_status'] ?? '') === $sla) ? 'selected' : '' ?>><?= e($sla) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Run Report</button></div>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Ticket</th><th>Date</th><th>Region/Office</th><th>Category</th><th>Specific Request</th><th>Severity/SLA</th><th>Technical Personnel</th><th>Status</th><th>Feedback</th></tr></thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?= e($ticket['ticket_no']) ?></td>
                    <td><?= e($ticket['requested_at']) ?></td>
                    <td><?= e($ticket['region_name']) ?><br><small class="text-muted"><?= e($ticket['office_name']) ?></small></td>
                    <td><?= e($ticket['category_name']) ?></td>
                    <td><?= e($ticket['service_name']) ?></td>
                    <td><?= e($ticket['priority']) ?><br><small class="text-muted"><?= e($ticket['sla_status']) ?></small></td>
                    <td><?= e($ticket['active_assignee_names'] ?: ($ticket['assigned_to_name'] ?? 'Unassigned')) ?></td>
                    <td><?= status_badge($ticket['status']) ?></td>
                    <td><?= e($ticket['latest_feedback'] ?? 'No feedback') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tickets): ?><tr><td colspan="9" class="text-center text-muted py-4">No matching records.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
