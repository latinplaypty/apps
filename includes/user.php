<?php
require_once __DIR__ . '/db.php';

function find_user_by_username(string $username): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function create_user(string $username, string $password, string $expiresAt): array
{
    $db = get_db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $now = (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
    try {
        $expires = new DateTimeImmutable($expiresAt);
    } catch (Exception $e) {
        throw new InvalidArgumentException('Fecha de vencimiento inválida.');
    }
    $stmt = $db->prepare('INSERT INTO users (username, password_hash, expires_at, created_at) VALUES (:username, :password_hash, :expires_at, :created_at)');
    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $hash,
        ':expires_at' => $expires->format(DateTimeInterface::ATOM),
        ':created_at' => $now,
    ]);

    return ['id' => (int) $db->lastInsertId(), 'username' => $username];
}

function verify_user(string $username, string $password): ?array
{
    $user = find_user_by_username($username);
    if (!$user) {
        return null;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }

    $now = new DateTimeImmutable('now');
    if ($now > new DateTimeImmutable($user['expires_at'])) {
        return null;
    }

    return $user;
}
