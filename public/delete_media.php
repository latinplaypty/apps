<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/media.php';

$user = require_auth();
$config = require __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$mediaId = (int) ($_POST['media_id'] ?? 0);
$media = find_media($mediaId, $user['id']);

if ($media) {
    if ($media['file_path']) {
        $filePath = __DIR__ . '/' . $media['file_path'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
    delete_media($mediaId, $user['id']);
    $_SESSION['flash_success'] = 'Elemento eliminado.';
} else {
    $_SESSION['flash_error'] = 'Elemento no encontrado.';
}

header('Location: index.php');
exit;
