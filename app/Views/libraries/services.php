<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Service Library</h1>
        <div class="text-muted small">Manage service categories, then open a category to maintain its specific requests.</div>
    </div>
</div>

<section>
    <div class="row g-3">
        <div class="col-lg-4">
            <form class="card" method="post" action="<?= url('libraries/service-categories') ?>">
                <div class="card-header bg-white fw-semibold">Add Category</div>
                <div class="card-body">
                    <?= csrf_field() ?>
                    <label class="form-label">Category Name</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="card-footer bg-white"><button class="btn btn-primary w-100">Add Category</button></div>
            </form>
        </div>
        <div class="col-lg-8">
            <form class="filter-bar row g-2 mb-3" method="get">
                <div class="col-md-7"><input name="category_q" value="<?= e($filters['category_q'] ?? '') ?>" class="form-control" placeholder="Search category"></div>
                <div class="col-md-3">
                    <select name="category_status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= (($filters['category_status'] ?? '') === $status) ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <form method="post" action="<?= url('libraries/service-categories/' . $category['id'] . '/update') ?>">
                                    <?= csrf_field() ?>
                                    <td><input name="name" class="form-control" value="<?= e($category['name']) ?>" required></td>
                                    <td><select name="status" class="form-select"><?php foreach (['active', 'inactive'] as $status): ?><option value="<?= e($status) ?>" <?= $category['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('libraries/services/' . (int) $category['id']) ?>">View</a>
                                        <button class="btn btn-sm btn-primary">Update</button>
                                </form>
                                        <form method="post" action="<?= url('libraries/service-categories/' . $category['id'] . '/delete') ?>" class="d-inline" data-confirm="Delete this category? Existing records will be kept.">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$categories): ?><tr><td colspan="3" class="text-center text-muted py-4">No categories found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
