<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin','manager']);
$employees = $pdo->query("SELECT id,name,email FROM users WHERE role='employee' AND is_active=1 ORDER BY name")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $employeeId = (int)($_POST['employee_id'] ?? 0);
    $periodStart = (string)($_POST['period_start'] ?? '');
    $periodEnd = (string)($_POST['period_end'] ?? '');
    $quality = (int)($_POST['quality_score'] ?? 0);
    $communication = (int)($_POST['communication_score'] ?? 0);
    $ownership = (int)($_POST['ownership_score'] ?? 0);
    $timeliness = (int)($_POST['timeliness_score'] ?? 0);
    $comments = trim((string)($_POST['comments'] ?? ''));
    if ($employeeId < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$periodStart) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$periodEnd) || $periodStart > $periodEnd || min($quality,$communication,$ownership,$timeliness)<1 || max($quality,$communication,$ownership,$timeliness)>5) {
        flash('error','Please check the employee, evaluation period, and all 1–5 scores.');
        redirect('/evaluations.php');
    }
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id=? AND role='employee' AND is_active=1");
    $stmt->execute([$employeeId]);
    if (!$stmt->fetch()) { flash('error','Select an active employee.'); redirect('/evaluations.php'); }
    $average = round(($quality+$communication+$ownership+$timeliness)/4,2);
    $stmt = $pdo->prepare('INSERT INTO evaluations (employee_id, reviewer_id, period_start, period_end, quality_score, communication_score, ownership_score, timeliness_score, overall_score, comments) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$employeeId,current_user()['id'],$periodStart,$periodEnd,$quality,$communication,$ownership,$timeliness,$average,$comments]);
    log_activity($pdo,current_user()['id'],'evaluation_created','Evaluated employee #'.$employeeId.' for '.$periodStart.' to '.$periodEnd);
    flash('success','Performance evaluation saved.');
    redirect('/evaluations.php');
}
$evaluations = $pdo->query('SELECT ev.*, emp.name employee_name, rev.name reviewer_name FROM evaluations ev JOIN users emp ON emp.id=ev.employee_id JOIN users rev ON rev.id=ev.reviewer_id ORDER BY ev.created_at DESC LIMIT 100')->fetchAll();
$pageTitle='Performance evaluations';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">PEOPLE DEVELOPMENT</span><h1>Performance evaluations</h1><p class="muted">Use a consistent 1–5 rubric and add actionable feedback.</p></div></div>
<div class="content-grid eval-layout"><section class="panel"><h2>New evaluation</h2><p class="muted small">Score each dimension from 1 (needs improvement) to 5 (excellent). Scores are guidance for a manager conversation, not an automated employment decision.</p>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Employee<select name="employee_id" required><option value="">Choose employee</option><?php foreach($employees as $emp): ?><option value="<?= (int)$emp['id'] ?>"><?= e($emp['name']) ?> — <?= e($emp['email']) ?></option><?php endforeach; ?></select></label>
<div class="form-grid"><label>Period start<input type="date" name="period_start" required value="<?= date('Y-m-01') ?>"></label><label>Period end<input type="date" name="period_end" required value="<?= date('Y-m-d') ?>"></label></div>
<?php foreach(['quality_score'=>'Quality of work','communication_score'=>'Communication','ownership_score'=>'Ownership / initiative','timeliness_score'=>'Timeliness'] as $field=>$label): ?><label><?= e($label) ?><select name="<?= e($field) ?>" required><option value="">Select score</option><?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>"><?= $i ?> — <?= ['','Needs improvement','Developing','Meets expectations','Strong','Excellent'][$i] ?></option><?php endfor; ?></select></label><?php endforeach; ?>
<label>Feedback / next steps<textarea name="comments" rows="4" placeholder="Recognize strengths and record specific development actions."></textarea></label><button class="btn btn-primary btn-block">Save evaluation</button></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Evaluation history</h2><p class="muted">Latest manager reviews</p></div><span class="counter"><?= count($evaluations) ?></span></div>
<?php if (!$evaluations): ?><div class="empty-state"><div>✦</div><strong>No evaluations yet</strong><p>Completed reviews will appear here.</p></div><?php else: ?><div class="evaluation-list"><?php foreach($evaluations as $ev): ?><article class="evaluation-card"><div class="evaluation-top"><div><strong><?= e($ev['employee_name']) ?></strong><small><?= e(date('d M Y',strtotime($ev['period_start']))) ?> – <?= e(date('d M Y',strtotime($ev['period_end']))) ?></small></div><div class="score-circle"><?= number_format((float)$ev['overall_score'],1) ?><small>/5</small></div></div><div class="score-bars"><?php foreach(['Quality'=>'quality_score','Communication'=>'communication_score','Ownership'=>'ownership_score','Timeliness'=>'timeliness_score'] as $label=>$field): ?><div><span><?= e($label) ?></span><strong><?= (int)$ev[$field] ?>/5</strong></div><?php endforeach; ?></div><?php if($ev['comments']): ?><p><?= nl2br(e($ev['comments'])) ?></p><?php endif; ?><small class="muted">Reviewed by <?= e($ev['reviewer_name']) ?></small></article><?php endforeach; ?></div><?php endif; ?></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
