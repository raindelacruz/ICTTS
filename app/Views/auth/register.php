<div class="row justify-content-center auth-screen">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="auth-brand mb-3">
                    <img src="<?= asset('img/logo-nfa-da.jpg') ?>" alt="Department of Agriculture and National Food Authority logo" class="auth-logo brand-mark" width="62" height="62">
                    <div>
                        <h1 class="h4 mb-1">ICTSD Personnel Registration</h1>
                        <p class="text-muted small mb-0">Accounts are reviewed and activated by the system administrator.</p>
                    </div>
                </div>
                <form method="post" action="<?= url('register') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Number <span class="text-danger">*</span></label>
                            <input name="id_number" class="form-control" required maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" required maxlength="160">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position <span class="text-danger">*</span></label>
                            <input name="position" class="form-control" required maxlength="160">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required maxlength="190">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 mt-3">Submit Registration</button>
                </form>
                <div class="text-center mt-3"><a href="<?= url('login') ?>">Back to login</a></div>
            </div>
        </div>
    </div>
</div>
