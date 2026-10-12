<?php
$pageTitle = $pageTitle ?? APP_NAME;
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= e(BASE_URL) ?>/index.php"><span class="brand-mark">W</span> WorkPulse</a>
    <?php if (is_logged_in()): ?>
    <nav class="nav-links">
        <a href="<?= e(BASE_URL) ?>/index.php">Dashboard</a>
        <a href="<?= e(BASE_URL) ?>/tasks.php">Tasks</a>
        <a href="<?= e(BASE_URL) ?>/updates.php">Daily updates</a>
        <?php if (can_manage_tasks()): ?>
            <a href="<?= e(BASE_URL) ?>/reports.php">Reports</a>
            <a href="<?= e(BASE_URL) ?>/evaluations.php">Evaluations</a>
        <?php endif; ?>
        <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
            <a href="<?= e(BASE_URL) ?>/users.php">Users</a>
        <?php endif; ?>
    </nav>
    <div class="user-menu">
        <span><?= e(current_user()['name'] ?? '') ?> <small>(<?= e(ucfirst(current_user()['role'] ?? '')) ?>)</small></span>
        <a class="btn btn-outline btn-small" href="<?= e(BASE_URL) ?>/logout.php">Sign out</a>
    </div>
    <?php endif; ?>
</header>
<main class="container">
    <?php if ($flashMessage): ?>
        <div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div>
    <?php endif; ?>
