<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$isManager = can_manage_tasks();
if ($isManager) {
    $totalTasks = (int)$pdo->query('SELECT COUNT(*) FROM tasks')->fetchColumn();
    $completedTasks = (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'completed'")->fetchColumn();
    $pendingTasks = (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('todo','in_progress','blocked')")->fetchColumn();
    $overdueTasks = (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date < CURDATE() AND status <> 'completed'")->fetchColumn();
    $stmt = $pdo->query("SELECT t.*, u.name AS employee_name, a.name AS assigned_by_name FROM tasks t JOIN users u ON u.id=t.assigned_to JOIN users a ON a.id=t.assigned_by ORDER BY (t.due_date < CURDATE() AND t.status <> 'completed') DESC, t.due_date ASC LIMIT 8");
} else {
    $count = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE assigned_to = ?');
    $count->execute([$user['id']]); $totalTasks = (int)$count->fetchColumn();
    $count = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status='completed'");
    $count->execute([$user['id']]); $completedTasks = (int)$count->fetchColumn();
    $count = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status IN ('todo','in_progress','blocked')");
    $count->execute([$user['id']]); $pendingTasks = (int)$count->fetchColumn();
    $count = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND due_date < CURDATE() AND status <> 'completed'");
    $count->execute([$user['id']]); $overdueTasks = (int)$count->fetchColumn();
    $stmt = $pdo->prepare("SELECT t.*, u.name AS employee_name, a.name AS assigned_by_name FROM tasks t JOIN users u ON u.id=t.assigned_to JOIN users a ON a.id=t.assigned_by WHERE t.assigned_to=? ORDER BY (t.due_date < CURDATE() AND t.status <> 'completed') DESC, t.due_date ASC LIMIT 8");
    $stmt->execute([$user['id']]);
}
$tasks = $stmt->fetchAll();
$completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
$todayStmt = $pdo->prepare('SELECT COUNT(*) FROM daily_updates WHERE user_id = ? AND work_date = CURDATE()');
$todayStmt->execute([$user['id']]);
$submittedToday = (int)$todayStmt->fetchColumn();
$recentUpdates = $isManager
    ? $pdo->query('SELECT d.*, u.name FROM daily_updates d JOIN users u ON u.id=d.user_id ORDER BY d.created_at DESC LIMIT 5')->fetchAll()
    : (function() use ($pdo, $user) { $s=$pdo->prepare('SELECT d.*, u.name FROM daily_updates d JOIN users u ON u.id=d.user_id WHERE d.user_id=? ORDER BY d.created_at DESC LIMIT 5'); $s->execute([$user['id']]); return $s->fetchAll(); })();
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading">
    <div><span class="eyebrow">OVERVIEW</span><h1>Hello, <?= e(explode(' ', $user['name'])[0]) ?> <span class="wave">✦</span></h1><p class="muted"><?= $isManager ? 'Here is what is happening across your team.' : 'Here is your current workload and daily progress.' ?></p></div>
    <div class="heading-actions"><a class="btn btn-primary" href="updates.php">＋ Submit daily update</a><?php if ($isManager): ?><a class="btn btn-outline" href="tasks.php?action=create">＋ Assign task</a><?php endif; ?></div>
</div>
<section class="stats-grid">
    <article class="stat-card"><div class="stat-top"><span>Total tasks</span><span class="stat-icon purple">▤</span></div><strong><?= $totalTasks ?></strong><small>Tasks in your scope</small></article>
    <article class="stat-card"><div class="stat-top"><span>Completed</span><span class="stat-icon green">✓</span></div><strong><?= $completedTasks ?></strong><small><?= $completionRate ?>% completion rate</small><div class="mini-progress"><span style="width:<?= $completionRate ?>%"></span></div></article>
    <article class="stat-card"><div class="stat-top"><span>In progress / pending</span><span class="stat-icon blue">◷</span></div><strong><?= $pendingTasks ?></strong><small>Work still to be done</small></article>
    <article class="stat-card"><div class="stat-top"><span>Overdue</span><span class="stat-icon red">!</span></div><strong><?= $overdueTasks ?></strong><small>Past due and not completed</small></article>
</section>
<div class="content-grid">
    <section class="panel">
        <div class="panel-heading"><div><h2><?= $isManager ? 'Team task overview' : 'My tasks' ?></h2><p class="muted">Priority work and upcoming deadlines</p></div><a href="tasks.php" class="text-link">View all →</a></div>
        <?php if (!$tasks): ?><div class="empty-state"><div>▤</div><strong>No tasks yet</strong><p>Tasks assigned to you will appear here.</p></div><?php else: ?>
        <div class="table-wrap"><table><thead><tr><th>Task</th><?php if ($isManager): ?><th>Employee</th><?php endif; ?><th>Due date</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($tasks as $task): $late = $task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed'; ?>
            <tr><td><strong><?= e($task['title']) ?></strong><small class="table-sub"><?= e($task['priority']) ?> priority</small></td><?php if ($isManager): ?><td><?= e($task['employee_name']) ?></td><?php endif; ?><td class="<?= $late ? 'text-danger' : '' ?>"><?= e($task['due_date']) ?><?= $late ? ' · overdue' : '' ?></td><td><span class="status status-<?= e($task['status']) ?>"><?= e(status_label($task['status'])) ?></span></td><td><a class="icon-link" href="tasks.php?view=<?= (int)$task['id'] ?>">↗</a></td></tr>
        <?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><h2>Daily check-in</h2><p class="muted">Keep progress visible</p></div><span class="stat-icon purple">✎</span></div>
        <div class="checkin-card"><div class="checkin-illustration">✦</div><h3><?= $submittedToday ? 'You checked in today' : 'Have you shared today’s progress?' ?></h3><p><?= $submittedToday ? 'Your daily update has been recorded. Add another update if your work changes.' : 'Log completed work, pending items, and any blockers to keep your manager informed.' ?></p><a href="updates.php" class="btn <?= $submittedToday ? 'btn-outline' : 'btn-primary' ?> btn-block"><?= $submittedToday ? 'View daily updates' : 'Submit daily update' ?> →</a></div>
        <div class="recent-updates"><h3>Recent updates</h3><?php if (!$recentUpdates): ?><p class="muted small">No updates submitted yet.</p><?php else: foreach ($recentUpdates as $update): ?><div class="update-row"><span class="avatar"><?= e(strtoupper(substr($update['name'],0,1))) ?></span><div><strong><?= e($update['name']) ?></strong><p><?= e(mb_strimwidth($update['completed_work'],0,65,'…')) ?></p><small><?= e($update['work_date']) ?></small></div></div><?php endforeach; endif; ?></div>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
