<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$canManage = can_manage_tasks();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'create' && $canManage) {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $assignedTo = (int)($_POST['assigned_to'] ?? 0);
        $dueDate = (string)($_POST['due_date'] ?? '');
        $priority = (string)($_POST['priority'] ?? 'medium');
        if ($title === '' || $assignedTo < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) || !in_array($priority, ['low','medium','high'], true)) {
            flash('error', 'Please complete all required task fields correctly.');
            redirect('/tasks.php?action=create');
        }
        $check = $pdo->prepare("SELECT id FROM users WHERE id=? AND role='employee' AND is_active=1");
        $check->execute([$assignedTo]);
        if (!$check->fetch()) { flash('error', 'Select an active employee.'); redirect('/tasks.php?action=create'); }
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, assigned_to, assigned_by, due_date, priority) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $assignedTo, $user['id'], $dueDate, $priority]);
        log_activity($pdo, $user['id'], 'task_created', 'Created task: '.$title);
        flash('success', 'Task created and assigned successfully.');
        redirect('/tasks.php');
    }
    if ($action === 'update_status') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        if (!in_array($status, ['todo','in_progress','blocked','completed'], true)) { flash('error','Invalid task status.'); redirect('/tasks.php'); }
        $sql = $canManage ? 'SELECT id, title, assigned_to FROM tasks WHERE id=?' : 'SELECT id, title, assigned_to FROM tasks WHERE id=? AND assigned_to=?';
        $stmt = $pdo->prepare($sql);
        $canManage ? $stmt->execute([$taskId]) : $stmt->execute([$taskId, $user['id']]);
        $task = $stmt->fetch();
        if (!$task) { flash('error','Task not found or access denied.'); redirect('/tasks.php'); }
        $up = $pdo->prepare('UPDATE tasks SET status=?, updated_at=NOW() WHERE id=?');
        $up->execute([$status, $taskId]);
        log_activity($pdo, $user['id'], 'task_status_changed', 'Task #'.$taskId.' changed to '.$status);
        flash('success', 'Task status updated.');
        redirect('/tasks.php?view='.$taskId);
    }
    if ($action === 'delete' && ($user['role'] === 'admin')) {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id=?');
        $stmt->execute([$taskId]);
        log_activity($pdo, $user['id'], 'task_deleted', 'Deleted task #'.$taskId);
        flash('success', 'Task deleted.');
        redirect('/tasks.php');
    }
}

$action = $_GET['action'] ?? '';
$viewId = (int)($_GET['view'] ?? 0);
$employees = $canManage ? $pdo->query("SELECT id,name,email FROM users WHERE role='employee' AND is_active=1 ORDER BY name")->fetchAll() : [];
$viewTask = null;
if ($viewId) {
    $sql = $canManage
        ? 'SELECT t.*, u.name employee_name, a.name assigned_by_name FROM tasks t JOIN users u ON u.id=t.assigned_to JOIN users a ON a.id=t.assigned_by WHERE t.id=?'
        : 'SELECT t.*, u.name employee_name, a.name assigned_by_name FROM tasks t JOIN users u ON u.id=t.assigned_to JOIN users a ON a.id=t.assigned_by WHERE t.id=? AND t.assigned_to=?';
    $st = $pdo->prepare($sql);
    $canManage ? $st->execute([$viewId]) : $st->execute([$viewId,$user['id']]);
    $viewTask = $st->fetch();
}
$filter = $_GET['status'] ?? '';
$params = [];
$where = [];
if (!$canManage) { $where[] = 't.assigned_to=?'; $params[] = $user['id']; }
if (in_array($filter, ['todo','in_progress','blocked','completed'], true)) { $where[]='t.status=?'; $params[]=$filter; }
if (($_GET['overdue'] ?? '') === '1') $where[] = "t.due_date < CURDATE() AND t.status <> 'completed'";
$sql = 'SELECT t.*, u.name employee_name, a.name assigned_by_name FROM tasks t JOIN users u ON u.id=t.assigned_to JOIN users a ON a.id=t.assigned_by';
if ($where) $sql .= ' WHERE '.implode(' AND ', $where);
$sql .= " ORDER BY (t.due_date < CURDATE() AND t.status <> 'completed') DESC, FIELD(t.priority,'high','medium','low'), t.due_date ASC";
$st = $pdo->prepare($sql); $st->execute($params); $tasks = $st->fetchAll();
$pageTitle = $action === 'create' ? 'Assign task' : ($viewTask ? 'Task details' : 'Tasks');
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">WORK MANAGEMENT</span><h1><?= $action === 'create' ? 'Assign a task' : ($viewTask ? 'Task details' : 'Tasks') ?></h1><p class="muted">Create clear ownership, track status, and stay ahead of deadlines.</p></div><div class="heading-actions"><?php if ($canManage): ?><a class="btn btn-primary" href="tasks.php?action=create">＋ New task</a><?php endif; ?><a class="btn btn-outline" href="updates.php">Daily updates</a></div></div>
<?php if ($action === 'create' && $canManage): ?>
<section class="panel narrow-panel"><h2>Task details</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create">
<label class="full">Task title *<input name="title" maxlength="180" required placeholder="e.g. Implement employee report export"></label>
<label class="full">Description<textarea name="description" rows="4" placeholder="Describe expected outcome, context, and acceptance criteria"></textarea></label>
<label>Assign to *<select name="assigned_to" required><option value="">Select employee</option><?php foreach($employees as $emp): ?><option value="<?= (int)$emp['id'] ?>"><?= e($emp['name']) ?> — <?= e($emp['email']) ?></option><?php endforeach; ?></select></label>
<label>Due date *<input type="date" name="due_date" min="<?= date('Y-m-d') ?>" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>"></label>
<label>Priority<select name="priority"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option></select></label>
<div class="full form-actions"><a class="btn btn-outline" href="tasks.php">Cancel</a><button class="btn btn-primary" type="submit">Create task</button></div></form></section>
<?php elseif ($viewTask): ?>
<section class="panel task-detail"><div class="panel-heading"><div><span class="status status-<?= e($viewTask['status']) ?>"><?= e(status_label($viewTask['status'])) ?></span><h2><?= e($viewTask['title']) ?></h2></div><span class="priority priority-<?= e($viewTask['priority']) ?>"><?= e(ucfirst($viewTask['priority'])) ?> priority</span></div>
<p><?= nl2br(e($viewTask['description'] ?: 'No description provided.')) ?></p><div class="detail-grid"><div><small>Assigned to</small><strong><?= e($viewTask['employee_name']) ?></strong></div><div><small>Assigned by</small><strong><?= e($viewTask['assigned_by_name']) ?></strong></div><div><small>Due date</small><strong class="<?= $viewTask['due_date'] < date('Y-m-d') && $viewTask['status'] !== 'completed' ? 'text-danger' : '' ?>"><?= e($viewTask['due_date']) ?></strong></div><div><small>Created</small><strong><?= e(date('d M Y', strtotime($viewTask['created_at']))) ?></strong></div></div>
<form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update_status"><input type="hidden" name="task_id" value="<?= (int)$viewTask['id'] ?>"><label for="status">Update status</label><select id="status" name="status"><?php foreach(['todo','in_progress','blocked','completed'] as $s): ?><option value="<?= $s ?>" <?= $viewTask['status']===$s?'selected':'' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">Save status</button></form>
<?php if ($user['role']==='admin'): ?><form method="post" onsubmit="return confirm('Delete this task permanently?')" class="delete-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= (int)$viewTask['id'] ?>"><button class="btn btn-danger btn-small">Delete task</button></form><?php endif; ?></section>
<?php else: ?>
<section class="panel"><div class="filter-bar"><form method="get" class="filters"><label>Status<select name="status" onchange="this.form.submit()"><option value="">All statuses</option><?php foreach(['todo','in_progress','blocked','completed'] as $s): ?><option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select></label><label class="check-filter"><input type="checkbox" name="overdue" value="1" <?= (($_GET['overdue']??'')==='1')?'checked':'' ?> onchange="this.form.submit()"> Overdue only</label></form><span class="muted small"><?= count($tasks) ?> task(s)</span></div>
<?php if (!$tasks): ?><div class="empty-state"><div>▤</div><strong>No matching tasks</strong><p>Try changing the filters or create a new task.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Task</th><th>Employee</th><th>Due date</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($tasks as $task): $late=$task['due_date']<date('Y-m-d') && $task['status']!=='completed'; ?><tr><td><strong><?= e($task['title']) ?></strong><small class="table-sub">Assigned by <?= e($task['assigned_by_name']) ?></small></td><td><?= e($task['employee_name']) ?></td><td class="<?= $late?'text-danger':'' ?>"><?= e($task['due_date']) ?><?= $late?' · overdue':'' ?></td><td><span class="priority priority-<?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span></td><td><span class="status status-<?= e($task['status']) ?>"><?= e(status_label($task['status'])) ?></span></td><td><a class="text-link" href="tasks.php?view=<?= (int)$task['id'] ?>">Open →</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
