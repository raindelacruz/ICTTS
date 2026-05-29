<div class="row justify-content-center auth-screen">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="auth-brand mb-3">
                    <img src="<?= asset('img/logo-nfa-da.jpg') ?>" alt="Department of Agriculture and National Food Authority logo" class="auth-logo brand-mark" width="62" height="62">
                    <div>
                        <h1 class="h4 mb-1">ICTSD Personnel Login</h1>
                        <p class="text-muted small mb-0">NFA ticketing access</p>
                    </div>
                </div>
                <form method="post" action="<?= url('login') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= url('register') ?>">ICTSD Personnel Registration</a>
                </div>
            </div>
        </div>
    </div>
</div>
