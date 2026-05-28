<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Users and Technical Personnel</h1>
    <a class="btn btn-primary btn-sm" href="<?= url('users/create') ?>">Add User</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>ID Number</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['id_number']) ?></td>
                    <td><?= e($user['name']) ?><br><small class="text-muted"><?= e($user['position']) ?></small></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e(ucwords(str_replace('_', ' ', $user['role']))) ?></td>
                    <td><span class="badge text-bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($user['status']) ?></span></td>
                    <td><a class="btn btn-sm btn-outline-secondary" href="<?= url('users/' . $user['id'] . '/edit') ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
