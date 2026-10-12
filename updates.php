<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$canManage = can_manage_tasks();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $completed = trim((string)($_POST['completed_work'] ?? ''));
    $todo = trim((string)($_POST['todo_work'] ?? ''));
    $pending = trim((string)($_POST['pending_work'] ?? ''));
    $blockers = trim((string)($_POST['blockers'] ?? ''));
    $workDate = (string)($_POST['work_date'] ?? date('Y-m-d'));
    if ($completed === '' && $todo === '' && $pending === '' && $blockers === '') {
        flash('error', 'Add at least one detail before submitting your update.');
        redirect('/updates.php');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $workDate) || $workDate > date('Y-m-d')) {
        flash('error', 'Please choose a valid work date (today or earlier).');
        redirect('/updates.php');
    }
    $stmt = $pdo->prepare('INSERT INTO daily_updates (user_id, work_date, completed_work, todo_work, pending_work, blockers) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'], $workDate, $completed, $todo, $pending, $blockers]);
    log_activity($pdo, $user['id'], 'daily_update_created', 'Submitted daily update for '.$workDate);
    flash('success', 'Daily work update submitted successfully.');
    redirect('/updates.php');
}
if ($canManage) {
    $updates = $pdo->query('SELECT d.*, u.name, u.email FROM daily_updates d JOIN users u ON u.id=d.user_id ORDER BY d.work_date DESC, d.created_at DESC LIMIT 100')->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT d.*, u.name, u.email FROM daily_updates d JOIN users u ON u.id=d.user_id WHERE d.user_id=? ORDER BY d.work_date DESC, d.created_at DESC LIMIT 100');
    $stmt->execute([$user['id']]); $updates = $stmt->fetchAll();
}
$pageTitle = 'Daily work updates';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">DAILY CHECK-IN</span><h1>Daily work updates</h1><p class="muted">Capture completed work, next steps, pending items, and blockers.</p></div></div>
<div class="content-grid updates-layout">
<section class="panel"><div class="panel-heading"><div><h2>Submit an update</h2><p class="muted">Be specific about outcomes and what needs attention.</p></div><span class="stat-icon purple">✎</span></div>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Work date<input type="date" name="work_date" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required></label>
<label>Completed work<textarea name="completed_work" rows="3" placeholder="What did you finish today? Include outcomes or ticket references."></textarea></label>
<label>To-do / next steps<textarea name="todo_work" rows="3" placeholder="What will you work on next?"></textarea></label>
<label>Pending tasks<textarea name="pending_work" rows="3" placeholder="What remains unfinished and why?"></textarea></label>
<label>Blockers / support needed<textarea name="blockers" rows="2" placeholder="Any dependencies, risks, or help required?"></textarea></label>
<button class="btn btn-primary btn-block" type="submit">Submit daily update →</button></form></section>
<section class="panel"><div class="panel-heading"><div><h2><?= $canManage ? 'Team updates' : 'My update history' ?></h2><p class="muted">Most recent submissions</p></div><span class="counter"><?= count($updates) ?></span></div>
<?php if (!$updates): ?><div class="empty-state"><div>✎</div><strong>No updates yet</strong><p>Your submitted work updates will appear here.</p></div><?php else: ?><div class="timeline"><?php foreach($updates as $up): ?><article class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><div class="timeline-meta"><strong><?= e($up['name']) ?></strong><span><?= e(date('d M Y', strtotime($up['work_date']))) ?></span></div><?php if ($up['completed_work']): ?><h4>Completed</h4><p><?= nl2br(e($up['completed_work'])) ?></p><?php endif; ?><?php if ($up['todo_work']): ?><h4>To-do / next steps</h4><p><?= nl2br(e($up['todo_work'])) ?></p><?php endif; ?><?php if ($up['pending_work']): ?><h4>Pending</h4><p><?= nl2br(e($up['pending_work'])) ?></p><?php endif; ?><?php if ($up['blockers']): ?><div class="blocker-note"><strong>Blocker</strong><p><?= nl2br(e($up['blockers'])) ?></p></div><?php endif; ?><small class="muted">Submitted <?= e(date('d M, H:i', strtotime($up['created_at']))) ?></small></div></article><?php endforeach; ?></div><?php endif; ?></section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
