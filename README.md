# EasyProjector PHP

Aplicación web en PHP inspirada en EasyWorship para administrar y proyectar contenido bíblico, canciones y material multimedia.

## Requisitos

- PHP 8.1 o superior con extensiones `pdo_sqlite` y `sqlite3` habilitadas.

## Puesta en marcha

```bash
php -S localhost:8000 -t public
```

1. Abre `http://localhost:8000/login.php` en tu navegador.
2. Crea un usuario indicando una fecha de vencimiento para la cuenta.
3. Inicia sesión y utiliza el panel para cargar o proyectar contenido.

## Funcionalidades principales

- **Biblias**: consume la API `https://bible-api.deno.dev` para elegir versión, libro, capítulo y rango de versículos, y proyectarlos en pantalla completa.
- **Canciones y anuncios**: edita contenido textual y envíalo al proyector.
- **Imágenes, videos y PDFs**: sube archivos que quedarán asociados a tu usuario y con vista previa.
- **Proyector**: abre una pestaña dedicada para mostrar el contenido seleccionado en pantalla completa.
- **Cuentas con vencimiento**: los usuarios solo pueden acceder hasta la fecha configurada.

Los archivos subidos se almacenan en `public/storage/` y la base de datos en `data/app.db`.
