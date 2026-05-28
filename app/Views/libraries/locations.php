<h1 class="h4 mb-3">Location Libraries</h1>

<section class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Region Management</h2>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <form class="card" method="post" action="<?= url('libraries/regions') ?>">
                <div class="card-header bg-white fw-semibold">Add Region</div>
                <div class="card-body row g-2">
                    <?= csrf_field() ?>
                    <div class="col-md-4"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
                    <div class="col-md-8"><label class="form-label">Region Name</label><input name="name" class="form-control" required></div>
                </div>
                <div class="card-footer bg-white"><button class="btn btn-primary w-100">Add Region</button></div>
            </form>
        </div>
        <div class="col-lg-8">
            <form class="filter-bar row g-2 mb-3" method="get">
                <div class="col-md-6"><input name="region_q" value="<?= e($filters['region_q'] ?? '') ?>" class="form-control" placeholder="Search code or region"></div>
                <div class="col-md-3">
                    <select name="region_status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= (($filters['region_status'] ?? '') === $status) ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><button class="btn btn-primary w-100">Filter Regions</button></div>
            </form>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Code</th><th>Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($regions as $region): ?>
                            <tr>
                                <form method="post" action="<?= url('libraries/regions/' . $region['id'] . '/update') ?>">
                                    <?= csrf_field() ?>
                                    <td><input name="code" class="form-control" value="<?= e($region['code']) ?>" required></td>
                                    <td><input name="name" class="form-control" value="<?= e($region['name']) ?>" required></td>
                                    <td><select name="status" class="form-select"><?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= $region['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary">Update</button>
                                </form>
                                        <form method="post" action="<?= url('libraries/regions/' . $region['id'] . '/delete') ?>" class="d-inline" data-confirm="Delete this region? Existing records will be kept.">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$regions): ?><tr><td colspan="4" class="text-center text-muted py-4">No regions found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Branch/Office Management</h2>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <form class="card" method="post" action="<?= url('libraries/offices') ?>">
                <div class="card-header bg-white fw-semibold">Add Branch/Office</div>
                <div class="card-body row g-2">
                    <?= csrf_field() ?>
                    <div class="col-12">
                        <label class="form-label">Region</label>
                        <select name="region_id" class="form-select" required>
                            <?php foreach ($activeRegions as $region): ?><option value="<?= (int) $region['id'] ?>"><?= e($region['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Branch/Office Name</label><input name="name" class="form-control" required></div>
                    <div class="col-12">
                        <label class="form-label">Type</label>
                        <select name="office_type" class="form-select">
                            <?php foreach ($officeTypes as $type): ?><option><?= e($type) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-white"><button class="btn btn-primary w-100">Add Branch/Office</button></div>
            </form>
        </div>
        <div class="col-lg-8">
            <form class="filter-bar row g-2 mb-3" method="get">
                <div class="col-md-3"><input name="office_q" value="<?= e($filters['office_q'] ?? '') ?>" class="form-control" placeholder="Search office"></div>
                <div class="col-md-3">
                    <select name="office_region_id" class="form-select">
                        <option value="">All regions</option>
                        <?php foreach ($activeRegions as $region): ?><option value="<?= (int) $region['id'] ?>" <?= ((string)($filters['office_region_id'] ?? '') === (string)$region['id']) ? 'selected' : '' ?>><?= e($region['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="office_type" class="form-select">
                        <option value="">All types</option>
                        <?php foreach ($officeTypes as $type): ?><option value="<?= e($type) ?>" <?= (($filters['office_type'] ?? '') === $type) ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="office_status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= (($filters['office_status'] ?? '') === $status) ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Region</th><th>Branch/Office</th><th>Type</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($offices as $office): ?>
                            <tr>
                                <form method="post" action="<?= url('libraries/offices/' . $office['id'] . '/update') ?>">
                                    <?= csrf_field() ?>
                                    <td><select name="region_id" class="form-select"><?php foreach ($activeRegions as $region): ?><option value="<?= (int) $region['id'] ?>" <?= (int)$office['region_id'] === (int)$region['id'] ? 'selected' : '' ?>><?= e($region['name']) ?></option><?php endforeach; ?></select></td>
                                    <td><input name="name" class="form-control" value="<?= e($office['name']) ?>" required></td>
                                    <td><select name="office_type" class="form-select"><?php foreach ($officeTypes as $type): ?><option value="<?= e($type) ?>" <?= $office['office_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></td>
                                    <td><select name="status" class="form-select"><?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= $office['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary">Update</button>
                                </form>
                                        <form method="post" action="<?= url('libraries/offices/' . $office['id'] . '/delete') ?>" class="d-inline" data-confirm="Delete this office? Existing records will be kept.">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$offices): ?><tr><td colspan="5" class="text-center text-muted py-4">No branches/offices found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
