<?php
// public/delivery.php

// Ellenőrizzük, hogy van-e HTTP_ORIGIN fejléc, és használjuk azt vagy alapértelmezett értéket
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';

// OPTIONS preflight kérés esetén válaszolunk a megfelelő CORS fejlécekkel
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

// Minden más kérés esetén is ugyanazokat a fejléceket állítsuk be
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Fájlok betöltése
require_once '../model/Delivery.php';
require_once '../controller/Delivery.php';

// Kontroller példányosítása
$controller = new DeliveryController();

// Az útvonal az "action" GET paraméter alapján történik.
// Például: 
//   delivery.php?action=payment    – fizetés feldolgozása
//   delivery.php?action=update     – státusz frissítése
//   delivery.php?action=read       – egy adott szállítás lekérdezése
//   delivery.php?action=readall    – az összes szállítás lekérdezése
$action = $_GET['action'] ?? '';

if ($action === 'update') {
    $controller->updateStatus();
} else if ($action === 'payment') {
    $controller->handlePayment();
} else if ($action === 'read') {
    $controller->getDelivery();
} else if ($action === 'readall') {
    $controller->getAllDeliveries();
} else {
    echo json_encode(['error' => 'Érvénytelen vagy hiányzó action paraméter']);
}
