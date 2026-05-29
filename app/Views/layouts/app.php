<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>?v=20260523-3" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand border-bottom">
            <img src="<?= asset('img/logo-nfa-da.jpg') ?>" alt="Department of Agriculture and National Food Authority logo" class="sidebar-logo brand-mark">
            <div>
                <div class="fw-bold">NFA ICTSD</div>
                <small class="text-white-50"><?= e(current_user()['name'] ?? '') ?></small>
            </div>
        </div>
        <nav class="nav flex-column p-2">
            <a class="nav-link" href="<?= url('dashboard') ?>">Dashboard</a>
            <a class="nav-link" href="<?= url('tickets') ?>">Tickets</a>
            <a class="nav-link" href="<?= url('reports') ?>">Reports</a>
            <a class="nav-link" href="<?= url('profile') ?>">My Profile</a>
            <?php if (\App\Core\Auth::isAdmin()): ?>
                <a class="nav-link" href="<?= url('users') ?>">Users</a>
                <a class="nav-link" href="<?= url('libraries/services') ?>">Service Library</a>
                <a class="nav-link" href="<?= url('libraries/locations') ?>">Location Library</a>
                <a class="nav-link" href="<?= url('logs') ?>">Activity Logs</a>
            <?php endif; ?>
        </nav>
    </aside>
    <div class="main-area">
        <header class="topbar">
            <div class="topbar-title">
                <img src="<?= asset('img/logo-nfa-da.jpg') ?>" alt="" class="topbar-logo brand-mark">
                <div>
                <div class="fw-semibold"><?= e(APP_NAME) ?></div>
                <small class="text-muted"><?= e(ucwords(str_replace('_', ' ', current_user()['role'] ?? ''))) ?></small>
                </div>
            </div>
            <div class="topbar-actions">
                <?php
                $notifications = [];
                $unreadNotifications = 0;
                $currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
                $basePath = trim(BASE_URL, '/');
                if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
                    $currentPath = trim(substr($currentPath, strlen($basePath)), '/');
                }
                $returnTo = $currentPath === '' ? 'dashboard' : $currentPath;
                try {
                    $notificationModel = new \App\Models\Notification();
                    $notifications = $notificationModel->recentForUser((int) (current_user()['id'] ?? 0));
                    $unreadNotifications = $notificationModel->unreadCount((int) (current_user()['id'] ?? 0));
                } catch (\Throwable $exception) {
                    $notifications = [];
                    $unreadNotifications = 0;
                }
                ?>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm notification-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <span aria-hidden="true">🔔</span>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="notification-badge"><?= $unreadNotifications > 99 ? '99+' : (int) $unreadNotifications ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-menu">
                        <div class="notification-menu-header">
                            <strong>Notifications</strong>
                            <?php if ($unreadNotifications > 0): ?>
                                <form method="post" action="<?= url('notifications/read-all') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                                    <button class="btn btn-link btn-sm p-0">Mark all read</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php foreach ($notifications as $notification): ?>
                            <a class="dropdown-item notification-item <?= $notification['read_at'] ? '' : 'is-unread' ?>" href="<?= url('notifications/' . $notification['id'] . '/open') ?>">
                                <span class="notification-title"><?= e($notification['title']) ?></span>
                                <span class="notification-message"><?= e($notification['message']) ?></span>
                                <span class="notification-time"><?= e($notification['created_at']) ?></span>
                            </a>
                        <?php endforeach; ?>
                        <?php if (!$notifications): ?>
                            <div class="notification-empty">No notifications yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="post" action="<?= url('logout') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-secondary btn-sm">Logout</button>
                </form>
            </div>
        </header>
        <main class="p-3 p-lg-4">
            <?php require __DIR__ . '/../partials/alerts.php'; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.ICTTS_BASE_URL = '<?= e(BASE_URL) ?>';</script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
