<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/media.php';

$user = require_auth();
$config = require __DIR__ . '/../includes/config.php';

$mediaId = isset($_GET['media_id']) ? (int) $_GET['media_id'] : null;
$type = $_GET['type'] ?? null;
$contentTitle = 'Proyector';
$contentHtml = '<p>Selecciona contenido para comenzar.</p>';

if ($mediaId) {
    $media = find_media($mediaId, $user['id']);
    if ($media) {
        $contentTitle = htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8');
        if ($media['file_path']) {
            $path = htmlspecialchars($media['file_path'], ENT_QUOTES, 'UTF-8');
            if ($media['category'] === 'imagenes') {
                $contentHtml = "<img src='{$path}' alt='Imagen'>";
            } elseif ($media['category'] === 'videos') {
                $contentHtml = "<video src='{$path}' controls autoplay></video>";
            } elseif ($media['category'] === 'pdfs') {
                $contentHtml = "<iframe src='{$path}' title='PDF'></iframe>";
            } else {
                $contentHtml = "<p>No se puede previsualizar este tipo de archivo.</p>";
            }
        } else {
            $text = nl2br(htmlspecialchars($media['content'] ?? '', ENT_QUOTES, 'UTF-8'));
            $contentHtml = "<div class='textual'>{$text}</div>";
        }
    } else {
        $contentHtml = '<p>No se encontró el elemento solicitado.</p>';
    }
} elseif ($type === 'bible') {
    $version = $_GET['version'] ?? 'rv1960';
    $book = $_GET['book'] ?? 'genesis';
    $chapter = $_GET['chapter'] ?? '1';
    $range = trim($_GET['range'] ?? '');
    $apiUrl = "https://bible-api.deno.dev/api/read/" . rawurlencode($version) . '/' . rawurlencode($book) . '/' . rawurlencode($chapter);
    if ($range !== '') {
        $apiUrl .= '/' . rawurlencode($range);
    }
    $response = @file_get_contents($apiUrl);
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['verses']) && is_array($data['verses'])) {
            $contentTitle = htmlspecialchars($data['reference'] ?? 'Pasaje bíblico', ENT_QUOTES, 'UTF-8');
            $versesHtml = '';
            foreach ($data['verses'] as $verse) {
                $number = htmlspecialchars((string) ($verse['verse'] ?? ''), ENT_QUOTES, 'UTF-8');
                $text = htmlspecialchars($verse['text'] ?? '', ENT_QUOTES, 'UTF-8');
                $versesHtml .= "<p><span class='badge'>{$number}</span> {$text}</p>";
            }
            $contentHtml = "<div class='bible'>{$versesHtml}</div>";
        } else {
            $contentHtml = '<p>No se pudo interpretar la respuesta de la Biblia.</p>';
        }
    } else {
        $contentHtml = '<p>No se pudo obtener el pasaje solicitado.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $contentTitle; ?> - Proyector</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { margin: 0; }
        .projector-screen .textual {
            font-size: 2.6rem;
            line-height: 1.35;
        }
        .projector-screen .bible {
            font-size: 2rem;
            line-height: 1.4;
            text-align: left;
        }
        .projector-screen .badge {
            display: inline-block;
            min-width: 2.6rem;
            min-height: 2.6rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            margin-right: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="projector-screen">
    <div class="content">
        <?php echo $contentHtml; ?>
    </div>
</body>
</html>
