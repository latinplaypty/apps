<?php
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $dbPath = $config['db_path'];
    $shouldInit = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    if ($shouldInit) {
        init_db_schema($pdo);
    } else {
        ensure_schema($pdo);
    }

    return $pdo;
}

function init_db_schema(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        expires_at TEXT NOT NULL,
        created_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS media (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        category TEXT NOT NULL,
        title TEXT NOT NULL,
        content TEXT,
        file_path TEXT,
        created_at TEXT NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
}

function ensure_schema(PDO $pdo): void
{
    // Ensure expected columns exist when updating older databases.
    $columns = $pdo->query("PRAGMA table_info(media)")->fetchAll();
    $columnNames = array_column($columns, 'name');
    if (!in_array('content', $columnNames, true)) {
        $pdo->exec('ALTER TABLE media ADD COLUMN content TEXT');
    }
    if (!in_array('file_path', $columnNames, true)) {
        $pdo->exec('ALTER TABLE media ADD COLUMN file_path TEXT');
    }
}
