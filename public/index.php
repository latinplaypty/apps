<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/media.php';

$user = require_auth();
$config = require __DIR__ . '/../includes/config.php';
$categories = $config['categories'];
foreach ($config['upload_dirs'] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}
$media = get_user_media($user['id']);
$grouped = [];
foreach ($media as $item) {
    $grouped[$item['category']][] = $item;
}

start_session();
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$expires = new DateTimeImmutable($user['expires_at']);
$expiresLabel = $expires->format('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyProjector - Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2>Contenido</h2>
        <a href="#biblia" class="active">Biblia</a>
        <a href="#canciones">Canciones</a>
        <a href="#anuncios">Anuncios</a>
        <a href="#imagenes">Imágenes</a>
        <a href="#videos">Videos</a>
        <a href="#pdfs">PDFs</a>
        <a href="projector.php" target="_blank">Abrir proyector</a>
        <a href="logout.php">Cerrar sesión</a>
    </aside>
    <main class="main">
        <div class="top-bar">
            <div>
                <h1>Hola, <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p>Tu acceso vence el <strong><?php echo $expiresLabel; ?></strong></p>
            </div>
            <div>
                <a class="btn primary" href="projector.php" target="_blank">Ver proyector</a>
            </div>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <section class="section" id="biblia">
            <h2>Biblia</h2>
            <p>Selecciona versión, libro y capítulo para proyectar pasajes.</p>
            <div class="grid">
                <div class="bible-controls">
                    <label>Versión
                        <select id="bible-version"></select>
                    </label>
                    <label>Libro
                        <select id="bible-book"></select>
                    </label>
                    <label>Capítulo
                        <select id="bible-chapter"></select>
                    </label>
                    <label>Versículos (ej. 1-5)
                        <input type="text" id="bible-range" placeholder="Opcional">
                    </label>
                    <button class="primary" id="bible-load">Buscar</button>
                    <button class="primary" id="bible-project" disabled>Proyectar</button>
                </div>
                <div class="bible-result" id="bible-result">Elige un pasaje para ver aquí.</div>
            </div>
        </section>

        <section class="section" id="canciones">
            <h2>Canciones</h2>
            <form action="upload.php" method="post" class="grid">
                <input type="hidden" name="category" value="canciones">
                <label>Título
                    <input type="text" name="title" placeholder="Nombre de la canción">
                </label>
                <label>Letra
                    <textarea name="content" placeholder="Ingresa la letra"></textarea>
                </label>
                <button type="submit" class="primary">Guardar canción</button>
            </form>
            <div class="media-grid">
                <?php foreach ($grouped['canciones'] ?? [] as $item): ?>
                    <div class="media-card">
                        <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="preview">
                            <p><?php echo nl2br(htmlspecialchars(substr($item['content'], 0, 280), ENT_QUOTES, 'UTF-8')); ?>...</p>
                        </div>
                        <div class="actions">
                            <a class="btn primary" href="projector.php?media_id=<?php echo $item['id']; ?>" target="_blank">Proyectar</a>
                            <form action="delete_media.php" method="post" onsubmit="return confirm('¿Eliminar elemento?');">
                                <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section" id="anuncios">
            <h2>Anuncios</h2>
            <form action="upload.php" method="post" class="grid">
                <input type="hidden" name="category" value="anuncios">
                <label>Título
                    <input type="text" name="title" placeholder="Título del anuncio">
                </label>
                <label>Contenido
                    <textarea name="content" placeholder="Mensaje del anuncio"></textarea>
                </label>
                <button type="submit" class="primary">Guardar anuncio</button>
            </form>
            <div class="media-grid">
                <?php foreach ($grouped['anuncios'] ?? [] as $item): ?>
                    <div class="media-card">
                        <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="preview">
                            <p><?php echo nl2br(htmlspecialchars(substr($item['content'], 0, 280), ENT_QUOTES, 'UTF-8')); ?>...</p>
                        </div>
                        <div class="actions">
                            <a class="btn primary" href="projector.php?media_id=<?php echo $item['id']; ?>" target="_blank">Proyectar</a>
                            <form action="delete_media.php" method="post" onsubmit="return confirm('¿Eliminar elemento?');">
                                <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section" id="imagenes">
            <h2>Imágenes</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data" class="grid">
                <input type="hidden" name="category" value="imagenes">
                <label>Título
                    <input type="text" name="title" placeholder="Título opcional">
                </label>
                <label>Archivo
                    <input type="file" name="file" accept="image/*" required>
                </label>
                <button type="submit" class="primary">Subir imagen</button>
            </form>
            <div class="media-grid">
                <?php foreach ($grouped['imagenes'] ?? [] as $item): ?>
                    <div class="media-card">
                        <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="preview">
                            <?php if ($item['file_path']): ?>
                                <img src="<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen">
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <a class="btn primary" href="projector.php?media_id=<?php echo $item['id']; ?>" target="_blank">Proyectar</a>
                            <form action="delete_media.php" method="post" onsubmit="return confirm('¿Eliminar elemento?');">
                                <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section" id="videos">
            <h2>Videos</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data" class="grid">
                <input type="hidden" name="category" value="videos">
                <label>Título
                    <input type="text" name="title" placeholder="Título opcional">
                </label>
                <label>Archivo
                    <input type="file" name="file" accept="video/mp4,video/webm,video/ogg" required>
                </label>
                <button type="submit" class="primary">Subir video</button>
            </form>
            <div class="media-grid">
                <?php foreach ($grouped['videos'] ?? [] as $item): ?>
                    <div class="media-card">
                        <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="preview">
                            <?php if ($item['file_path']): ?>
                                <video src="<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES, 'UTF-8'); ?>" controls></video>
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <a class="btn primary" href="projector.php?media_id=<?php echo $item['id']; ?>" target="_blank">Proyectar</a>
                            <form action="delete_media.php" method="post" onsubmit="return confirm('¿Eliminar elemento?');">
                                <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section" id="pdfs">
            <h2>PDFs</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data" class="grid">
                <input type="hidden" name="category" value="pdfs">
                <label>Título
                    <input type="text" name="title" placeholder="Título opcional">
                </label>
                <label>Archivo
                    <input type="file" name="file" accept="application/pdf" required>
                </label>
                <button type="submit" class="primary">Subir PDF</button>
            </form>
            <div class="media-grid">
                <?php foreach ($grouped['pdfs'] ?? [] as $item): ?>
                    <div class="media-card">
                        <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="preview">
                            <?php if ($item['file_path']): ?>
                                <iframe src="<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES, 'UTF-8'); ?>" title="PDF"></iframe>
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <a class="btn primary" href="projector.php?media_id=<?php echo $item['id']; ?>" target="_blank">Proyectar</a>
                            <form action="delete_media.php" method="post" onsubmit="return confirm('¿Eliminar elemento?');">
                                <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<script>
const versionSelect = document.getElementById('bible-version');
const bookSelect = document.getElementById('bible-book');
const chapterSelect = document.getElementById('bible-chapter');
const rangeInput = document.getElementById('bible-range');
const resultBox = document.getElementById('bible-result');
const projectButton = document.getElementById('bible-project');

async function fetchJSON(url) {
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error('Error al consultar la Biblia');
    }
    return await response.json();
}

async function loadVersions() {
    try {
        const data = await fetchJSON('https://bible-api.deno.dev/api/versions');
        versionSelect.innerHTML = data.versions.map(v => `<option value="${v.code}">${v.name}</option>`).join('');
        await loadBooks();
    } catch (error) {
        resultBox.textContent = 'No se pudieron cargar las versiones.';
    }
}

async function loadBooks() {
    const version = versionSelect.value;
    if (!version) return;
    try {
        const data = await fetchJSON(`https://bible-api.deno.dev/api/book/${version}`);
        bookSelect.innerHTML = data.books.map(book => `<option value="${book.slug}">${book.name}</option>`).join('');
        await loadChapters();
    } catch (error) {
        resultBox.textContent = 'No se pudieron cargar los libros.';
    }
}

async function loadChapters() {
    const version = versionSelect.value;
    const book = bookSelect.value;
    if (!version || !book) return;
    try {
        const data = await fetchJSON(`https://bible-api.deno.dev/api/book/${version}/${book}`);
        const options = [];
        for (let i = 1; i <= data.chapters; i++) {
            options.push(`<option value="${i}">${i}</option>`);
        }
        chapterSelect.innerHTML = options.join('');
    } catch (error) {
        resultBox.textContent = 'No se pudieron cargar los capítulos.';
    }
}

async function loadPassage() {
    const version = versionSelect.value;
    const book = bookSelect.value;
    const chapter = chapterSelect.value;
    const range = rangeInput.value.trim();
    if (!version || !book || !chapter) {
        return;
    }
    let url = `https://bible-api.deno.dev/api/read/${version}/${book}/${chapter}`;
    if (range) {
        url += `/${range}`;
    }
    try {
        const data = await fetchJSON(url);
        const verses = data.verses || [];
        if (!verses.length) {
            resultBox.textContent = 'No se encontraron versículos para esta referencia.';
            projectButton.disabled = true;
            projectButton.dataset.url = '';
            return;
        }
        const html = verses.map(v => `<strong>${v.verse}</strong> ${v.text}`).join('<br>');
        resultBox.innerHTML = `<h3>${data.reference}</h3><p>${html}</p>`;
        projectButton.disabled = false;
        const params = new URLSearchParams({
            type: 'bible',
            version,
            book,
            chapter,
            range
        });
        projectButton.dataset.url = `projector.php?${params.toString()}`;
    } catch (error) {
        resultBox.textContent = 'Ocurrió un error al buscar el pasaje.';
        projectButton.disabled = true;
        projectButton.dataset.url = '';
    }
}

versionSelect?.addEventListener('change', () => loadBooks().then(loadPassage));
bookSelect?.addEventListener('change', () => loadChapters().then(loadPassage));
chapterSelect?.addEventListener('change', loadPassage);
document.getElementById('bible-load')?.addEventListener('click', loadPassage);
projectButton?.addEventListener('click', () => {
    if (projectButton.dataset.url) {
        window.open(projectButton.dataset.url, '_blank');
    }
});

loadVersions();
</script>
</body>
</html>
