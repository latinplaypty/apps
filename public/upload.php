<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/media.php';

$user = require_auth();
$config = require __DIR__ . '/../includes/config.php';
$categories = $config['categories'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$category = $_POST['category'] ?? '';
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if (!isset($categories[$category]) || $category === 'biblia') {
    header('Location: index.php');
    exit;
}

if ($title === '') {
    $title = ucfirst($category) . ' sin título';
}

$filePath = null;

if (in_array($category, ['imagenes', 'videos', 'pdfs'], true)) {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_error'] = 'Debe subir un archivo válido.';
        header('Location: index.php');
        exit;
    }

    $file = $_FILES['file'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = [
        'imagenes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'videos' => ['mp4', 'webm', 'ogg'],
        'pdfs' => ['pdf'],
    ];

    if (!in_array($extension, $allowed[$category], true)) {
        $_SESSION['flash_error'] = 'Tipo de archivo no permitido.';
        header('Location: index.php');
        exit;
    }

    $uploadDirs = $config['upload_dirs'];
    $targetDir = $uploadDirs[$category];
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        $_SESSION['flash_error'] = 'No se pudo crear el directorio de subida.';
        header('Location: index.php');
        exit;
    }

    $filename = uniqid($category . '_', true) . '.' . $extension;
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $_SESSION['flash_error'] = 'Error al guardar el archivo.';
        header('Location: index.php');
        exit;
    }

    $filePath = $config['public_upload_path'] . '/' . $category . '/' . $filename;
    $content = null;
} else {
    if ($content === '') {
        $_SESSION['flash_error'] = 'Debe ingresar contenido.';
        header('Location: index.php');
        exit;
    }
}

create_media($user['id'], $category, $title, $content ?: null, $filePath);
$_SESSION['flash_success'] = 'Contenido agregado correctamente.';

header('Location: index.php');
exit;
