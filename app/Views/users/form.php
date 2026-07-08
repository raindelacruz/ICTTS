<h1 class="h4 mb-3"><?= $user ? 'Edit User' : 'Add User' ?></h1>
<form class="card" method="post" action="<?= $user ? url('users/' . $user['id'] . '/update') : url('users') ?>">
    <div class="card-body row g-3">
        <?= csrf_field() ?>
        <div class="col-md-6"><label class="form-label">ID Number</label><input name="id_number" class="form-control" required value="<?= e($user['id_number'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required value="<?= e($user['name'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Position</label><input name="position" class="form-control" required value="<?= e($user['position'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required value="<?= e($user['email'] ?? '') ?>"></div>
        <div class="col-md-4">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <?php foreach (['technical', 'unit_head', 'division_chief', 'admin'] as $role): ?>
                    <option value="<?= e($role) ?>" <?= (($user['role'] ?? 'technical') === $role) ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $role))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Service Category</label>
            <select name="service_category_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= ((int)($user['service_category_id'] ?? 0) === (int)$category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Required for unit heads and technical personnel.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['active', 'inactive'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= (($user['status'] ?? 'active') === $status) ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Password</label><input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?> minlength="8"></div>
    </div>
    <div class="card-footer bg-white text-end">
        <a class="btn btn-outline-secondary" href="<?= url('users') ?>">Cancel</a>
        <button class="btn btn-primary">Save User</button>
    </div>
</form>
