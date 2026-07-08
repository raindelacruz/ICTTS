<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>?v=20260708-1" rel="stylesheet">
</head>
<body class="public-bg">
<nav class="navbar navbar-expand-lg bg-white border-bottom public-navbar">
    <div class="container">
        <a class="navbar-brand brand-lockup" href="<?= url('request') ?>">
            <img src="<?= asset('img/logo-nfa-da.jpg') ?>" alt="Department of Agriculture and National Food Authority logo" class="brand-logo brand-mark">
            <span>NFA ICTSD Ticketing System</span>
        </a>
    </div>
</nav>
<main class="container py-4 py-lg-5">
    <?php require __DIR__ . '/../partials/alerts.php'; ?>
    <?= $content ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.ICTTS_BASE_URL = '<?= e(BASE_URL) ?>';</script>
<script src="<?= asset('js/app.js') ?>?v=20260708-1"></script>
</body>
</html>
