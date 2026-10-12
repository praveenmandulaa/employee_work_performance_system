<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect('/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        log_activity($pdo, (int)$user['id'], 'login', 'User signed in');
        redirect('/index.php');
    }
    $error = 'Invalid email or password, or the account is inactive.';
}
$pageTitle = 'Sign in';
require __DIR__ . '/includes/header.php';
?>
<section class="login-wrap">
    <div class="login-intro">
        <span class="eyebrow">EMPLOYEE OPERATIONS</span>
        <h1>Make daily work<br><span>visible and measurable.</span></h1>
        <p>Assign work, share progress, identify blockers, and review team performance from one workspace.</p>
        <div class="feature-pills"><span>Task tracking</span><span>Daily updates</span><span>Team insights</span></div>
    </div>
    <div class="panel login-panel">
        <h2>Welcome back</h2>
        <p class="muted">Sign in to your WorkPulse workspace.</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Email address<input type="email" name="email" required autocomplete="username" placeholder="you@company.com"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="Your password"></label>
            <button class="btn btn-primary btn-block" type="submit">Sign in <span>→</span></button>
        </form>
        <div class="demo-note"><strong>Demo accounts</strong><br>Admin: admin@workpulse.local<br>Manager: manager@workpulse.local<br>Employee: employee@workpulse.local<br><span>Use password: <code>password</code></span></div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
