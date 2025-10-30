<?php
return [
    'db_path' => __DIR__ . '/../data/app.db',
    'upload_dirs' => [
        'images' => __DIR__ . '/../public/storage/images',
        'videos' => __DIR__ . '/../public/storage/videos',
        'pdfs'   => __DIR__ . '/../public/storage/pdfs',
    ],
    'public_upload_path' => 'storage',
    'categories' => [
        'biblia' => 'Biblia',
        'canciones' => 'Canciones',
        'videos' => 'Videos',
        'imagenes' => 'Imágenes',
        'pdfs' => 'PDFs',
        'anuncios' => 'Anuncios',
    ],
];
