<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']['id']);
}

function current_user(): array {
    return $_SESSION['user'] ?? [];
}

function require_login(): void {
    if (!is_logged_in()) {
        flash('error', 'Please sign in to continue.');
        redirect('/login.php');
    }
}

function require_role(array $roles): void {
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied: you do not have permission to view this page.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Your form session expired. Please go back, refresh, and try again.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function log_activity(PDO $pdo, int $userId, string $action, string $details = ''): void {
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $action, $details]);
}

function status_label(string $status): string {
    return ucwords(str_replace('_', ' ', $status));
}

function can_manage_tasks(): bool {
    return in_array(current_user()['role'] ?? '', ['admin', 'manager'], true);
}
