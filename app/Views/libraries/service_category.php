<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="mb-2"><a class="btn btn-sm btn-outline-secondary" href="<?= url('libraries/services') ?>">Back to Categories</a></div>
        <h1 class="h4 mb-1"><?= e($category['name']) ?></h1>
        <div class="text-muted small">Manage up to 10 visible specific requests for this service category.</div>
    </div>
</div>

<section>
    <div class="row g-3">
        <div class="col-lg-4">
            <form class="card" method="post" action="<?= url('libraries/service-items') ?>">
                <div class="card-header bg-white fw-semibold">Add Specific Request</div>
                <div class="card-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="service_category_id" value="<?= (int) $category['id'] ?>">
                    <label class="form-label">Category</label>
                    <input class="form-control mb-3" value="<?= e($category['name']) ?>" disabled>
                    <label class="form-label">Specific Request Name</label>
                    <input name="name" class="form-control mb-3" required>
                    <label class="form-label">Default Severity</label>
                    <select name="default_priority" class="form-select" required>
                        <?php foreach ($priorities as $priority): ?><option value="<?= e($priority) ?>" <?= $priority === 'Medium' ? 'selected' : '' ?>><?= e($priority) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="card-footer bg-white"><button class="btn btn-primary w-100">Add Specific Request</button></div>
            </form>
            <div class="card mt-3">
                <div class="card-header bg-white fw-semibold">Severity Guide</div>
                <div class="card-body">
                    <div class="vstack gap-3 small">
                        <div><div class="fw-semibold">Critical</div><div class="text-muted">Service outage, many users affected, no workaround, or a core operation is stopped.</div></div>
                        <div><div class="fw-semibold">High</div><div class="text-muted">Major function is unavailable, a department or important process is affected, or workaround is limited.</div></div>
                        <div><div class="fw-semibold">Medium</div><div class="text-muted">Partial issue, limited users affected, work can continue through an available workaround.</div></div>
                        <div><div class="fw-semibold">Low</div><div class="text-muted">Minor request, cosmetic issue, documentation/update request, or little operational impact.</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <form class="filter-bar row g-2 mb-3" method="get">
                <div class="col-md-5"><input name="item_q" value="<?= e($filters['item_q'] ?? '') ?>" class="form-control" placeholder="Search request"></div>
                <div class="col-md-3">
                    <select name="item_priority" class="form-select">
                        <option value="">All severities</option>
                        <?php foreach ($priorities as $priority): ?><option value="<?= e($priority) ?>" <?= (($filters['item_priority'] ?? '') === $priority) ? 'selected' : '' ?>><?= e($priority) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="item_status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= (($filters['item_status'] ?? '') === $status) ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Specific Request</th><th>Severity</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <form method="post" action="<?= url('libraries/service-items/' . $item['id'] . '/update') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="service_category_id" value="<?= (int) $category['id'] ?>">
                                    <td><input name="name" class="form-control" value="<?= e($item['name']) ?>" required></td>
                                    <td><select name="default_priority" class="form-select"><?php foreach ($priorities as $priority): ?><option value="<?= e($priority) ?>" <?= ($item['default_priority'] ?? 'Medium') === $priority ? 'selected' : '' ?>><?= e($priority) ?></option><?php endforeach; ?></select></td>
                                    <td><select name="status" class="form-select"><?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary">Update</button>
                                </form>
                                        <form method="post" action="<?= url('libraries/service-items/' . $item['id'] . '/delete') ?>" class="d-inline" data-confirm="Delete this specific request? Existing records will be kept.">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$items): ?><tr><td colspan="4" class="text-center text-muted py-4">No specific requests found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
