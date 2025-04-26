<?php
// backend/Controller/image.php?id=123

if (!isset($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

// csak számokat engedünk
$id = preg_replace('/\D/','', $_GET['id']);

// fájlrendszer‑útvonal a képhez
$baseDir = __DIR__ . "/../images";          // ez a backend/images
$pattern = "{$baseDir}/{$id}/pizza_{$id}.*";
$matches = glob($pattern);

$file = $matches ? $matches[0] : "{$baseDir}/default.jpg";

if (!file_exists($file)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// Kép kiszolgálása
header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
