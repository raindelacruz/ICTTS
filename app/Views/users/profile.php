<h1 class="h4 mb-3">My Profile</h1>
<form class="card" method="post" action="<?= url('profile') ?>">
    <div class="card-body row g-3">
        <?= csrf_field() ?>
        <div class="col-md-6">
            <label class="form-label">ID Number</label>
            <input class="form-control" value="<?= e($user['id_number']) ?>" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <input class="form-control" value="<?= e(ucwords(str_replace('_', ' ', $user['role']))) ?>" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Service Category</label>
            <input class="form-control" value="<?= e($user['service_category_name'] ?? 'None') ?>" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input name="name" class="form-control" required value="<?= e($user['name']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Position <span class="text-danger">*</span></label>
            <input name="position" class="form-control" required value="<?= e($user['position']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" minlength="8">
            <div class="form-text">Leave blank to keep current password.</div>
        </div>
    </div>
    <div class="card-footer bg-white text-end">
        <button class="btn btn-primary">Save Profile</button>
    </div>
</form>
