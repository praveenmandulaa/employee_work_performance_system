<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin']);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim((string)($_POST['name']??'')); $email=strtolower(trim((string)($_POST['email']??'')));
    $role=(string)($_POST['role']??'employee'); $password=(string)($_POST['password']??'');
    if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || !in_array($role,['manager','employee'],true) || strlen($password)<8) {
        flash('error','Enter a name, valid email, allowed role, and password of at least 8 characters.'); redirect('/users.php');
    }
    try {
        $stmt=$pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
        $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role]);
        log_activity($pdo,current_user()['id'],'user_created','Created '.$role.' account for '.$email);
        flash('success','User account created. Share the credentials securely.'); redirect('/users.php');
    } catch (PDOException $e) {
        flash('error','Could not create account. The email may already exist.'); redirect('/users.php');
    }
}
$users=$pdo->query('SELECT id,name,email,role,is_active,created_at FROM users ORDER BY FIELD(role,"admin","manager","employee"),name')->fetchAll();
$pageTitle='User management';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">ADMINISTRATION</span><h1>User management</h1><p class="muted">Create manager and employee accounts for the workspace.</p></div></div>
<div class="content-grid users-layout"><section class="panel"><h2>Create account</h2><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Full name<input name="name" required maxlength="120"></label><label>Email<input type="email" name="email" required maxlength="190"></label><label>Role<select name="role"><option value="employee">Employee</option><option value="manager">Manager</option></select></label><label>Temporary password<input type="password" name="password" required minlength="8" autocomplete="new-password"><small class="muted">At least 8 characters. Ask the user to change it through your normal onboarding process.</small></label><button class="btn btn-primary btn-block">Create account</button></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Workspace users</h2><p class="muted">Active and inactive accounts</p></div><span class="counter"><?= count($users) ?></span></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Role</th><th>Status</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><strong><?= e($u['name']) ?></strong><small class="table-sub"><?= e($u['email']) ?></small></td><td><?= e(ucfirst($u['role'])) ?></td><td><span class="status <?= $u['is_active']?'status-completed':'status-blocked' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
