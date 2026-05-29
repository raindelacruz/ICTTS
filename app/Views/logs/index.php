<h1 class="h4 mb-3">Activity and Email Logs</h1>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activity" type="button">Activity Logs</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#emails" type="button">Email Logs</button></li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="activity">
        <form class="filter-bar row g-2 mb-3" method="get">
            <div class="col-md-2"><input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control" aria-label="Date from"></div>
            <div class="col-md-2"><input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control" aria-label="Date to"></div>
            <div class="col-md-2"><input name="action" value="<?= e($filters['action'] ?? '') ?>" class="form-control" placeholder="Action"></div>
            <div class="col-md-2">
                <select name="entity_type" class="form-select">
                    <option value="">All entities</option>
                    <?php foreach (['ticket', 'region', 'office', 'service_category', 'service_item', 'user'] as $entity): ?><option value="<?= e($entity) ?>" <?= (($filters['entity_type'] ?? '') === $entity) ? 'selected' : '' ?>><?= e(str_replace('_', ' ', ucfirst($entity))) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input name="activity_q" value="<?= e($filters['activity_q'] ?? '') ?>" class="form-control" placeholder="Search logs"></div>
            <div class="col-md-1">
                <select name="activity_limit" class="form-select" aria-label="Rows to show">
                    <?php foreach ($activityLimits as $limit): ?><option value="<?= (int) $limit ?>" <?= ((string)($filters['activity_limit'] ?? '10') === (string)$limit) ? 'selected' : '' ?>><?= (int) $limit ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Filter</button></div>
        </form>
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($activities as $log): ?><tr><td><?= e($log['created_at']) ?></td><td><?= e($log['actor_name']) ?></td><td><?= e($log['action']) ?></td><td><?= e(($log['entity_type'] ?? '') . ' ' . ($log['entity_id'] ?? '')) ?></td><td><?= e($log['details']) ?></td><td><?= e($log['ip_address']) ?></td></tr><?php endforeach; ?>
            <?php if (!$activities): ?><tr><td colspan="6" class="text-center text-muted py-4">No activity logs found.</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
    <div class="tab-pane fade" id="emails">
        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Email Configuration</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <?php foreach ($emailDiagnostics as $label => $value): ?>
                                <tr>
                                    <th class="text-nowrap"><?= e(ucwords(str_replace('_', ' ', $label))) ?></th>
                                    <td><?= e($value) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Send Test Email</div>
                    <div class="card-body">
                        <form method="post" action="<?= url('logs/email-test') ?>" class="row g-2">
                            <?= csrf_field() ?>
                            <div class="col-12">
                                <label class="form-label">Recipient email</label>
                                <input type="email" name="recipient_email" class="form-control" required value="<?= e(current_user()['email'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary">Send Test</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Ticket</th><th>Recipient</th><th>Subject</th><th>Status</th><th>Error</th></tr></thead>
            <tbody>
            <?php foreach ($emails as $log): ?><tr><td><?= e($log['created_at']) ?></td><td><?= e((string) $log['ticket_id']) ?></td><td><?= e($log['recipient_email']) ?></td><td><?= e($log['subject']) ?></td><td><?= e($log['status']) ?></td><td><?= e($log['error_message']) ?></td></tr><?php endforeach; ?>
            <?php if (!$emails): ?><tr><td colspan="6" class="text-center text-muted py-4">No email logs found.</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
