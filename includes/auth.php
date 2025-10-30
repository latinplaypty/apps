<?php
require_once __DIR__ . '/db.php';

function start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user(): ?array
{
    start_session();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        logout();
        return null;
    }

    $now = new DateTimeImmutable('now');
    if ($now > new DateTimeImmutable($user['expires_at'])) {
        logout();
        return null;
    }

    return $user;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function logout(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
