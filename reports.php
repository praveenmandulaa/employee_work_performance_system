<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin','manager']);

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$to) || $from > $to) {
    $from = date('Y-m-01'); $to = date('Y-m-d');
}
$stmt = $pdo->prepare("SELECT u.id, u.name, u.email,
    COUNT(DISTINCT t.id) assigned_tasks,
    COUNT(DISTINCT CASE WHEN t.status='completed' THEN t.id END) completed_tasks,
    COUNT(DISTINCT CASE WHEN t.due_date < CURDATE() AND t.status <> 'completed' THEN t.id END) overdue_tasks,
    COUNT(DISTINCT d.id) update_count,
    COUNT(DISTINCT CASE WHEN d.blockers <> '' THEN d.id END) blocker_updates
    FROM users u
    LEFT JOIN tasks t ON t.assigned_to=u.id
    LEFT JOIN daily_updates d ON d.user_id=u.id AND d.work_date BETWEEN ? AND ?
    WHERE u.role='employee' AND u.is_active=1
    GROUP BY u.id, u.name, u.email ORDER BY completed_tasks DESC, overdue_tasks ASC");
$stmt->execute([$from,$to]); $rows = $stmt->fetchAll();
$export = $_GET['export'] ?? '';
if ($export === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="workpulse-report-'.$from.'-to-'.$to.'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Employee','Email','Assigned tasks','Completed tasks','Overdue tasks','Daily updates in period','Updates with blockers','Completion rate']);
    foreach($rows as $r) {
        $rate = (int)$r['assigned_tasks'] ? round(((int)$r['completed_tasks']/(int)$r['assigned_tasks'])*100,1) : 0;
        fputcsv($out,[$r['name'],$r['email'],$r['assigned_tasks'],$r['completed_tasks'],$r['overdue_tasks'],$r['update_count'],$r['blocker_updates'],$rate.'%']);
    }
    fclose($out); exit;
}
$pageTitle = 'Team reports';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">TEAM INSIGHTS</span><h1>Reports &amp; analytics</h1><p class="muted">Review task outcomes and daily update activity by employee.</p></div><a class="btn btn-primary" href="?from=<?= e($from) ?>&to=<?= e($to) ?>&export=csv">↓ Export CSV</a></div>
<section class="panel"><form method="get" class="report-filter"><label>Updates from<input type="date" name="from" value="<?= e($from) ?>" required></label><label>Updates to<input type="date" name="to" value="<?= e($to) ?>" required></label><button class="btn btn-outline" type="submit">Apply dates</button></form><p class="muted small">Task totals reflect current task status; the selected date range applies to daily update counts.</p>
<div class="table-wrap"><table><thead><tr><th>Employee</th><th>Assigned</th><th>Completed</th><th>Completion rate</th><th>Overdue</th><th>Updates</th><th>Blocker updates</th></tr></thead><tbody><?php foreach($rows as $r): $rate=(int)$r['assigned_tasks']?round(((int)$r['completed_tasks']/(int)$r['assigned_tasks'])*100):0; ?><tr><td><strong><?= e($r['name']) ?></strong><small class="table-sub"><?= e($r['email']) ?></small></td><td><?= (int)$r['assigned_tasks'] ?></td><td><?= (int)$r['completed_tasks'] ?></td><td><div class="rate-cell"><span><?= $rate ?>%</span><div class="mini-progress"><span style="width:<?= $rate ?>%"></span></div></div></td><td><span class="<?= (int)$r['overdue_tasks']?'text-danger':'' ?>"><?= (int)$r['overdue_tasks'] ?></span></td><td><?= (int)$r['update_count'] ?></td><td><?= (int)$r['blocker_updates'] ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$rows): ?><div class="empty-state"><strong>No active employees found</strong></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
