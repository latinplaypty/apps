<?php
require_once __DIR__ . '/db.php';

function get_user_media(int $userId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM media WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function find_media(int $mediaId, int $userId): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM media WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $mediaId, ':user_id' => $userId]);
    $media = $stmt->fetch();
    return $media ?: null;
}

function create_media(int $userId, string $category, string $title, ?string $content, ?string $filePath): int
{
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO media (user_id, category, title, content, file_path, created_at) VALUES (:user_id, :category, :title, :content, :file_path, :created_at)');
    $stmt->execute([
        ':user_id' => $userId,
        ':category' => $category,
        ':title' => $title,
        ':content' => $content,
        ':file_path' => $filePath,
        ':created_at' => (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM),
    ]);
    return (int) $db->lastInsertId();
}

function delete_media(int $mediaId, int $userId): void
{
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM media WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $mediaId, ':user_id' => $userId]);
}
