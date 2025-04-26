<?php
// public/adminPizza.php

// Állítsuk be a JSON válasz fejléceket és a CORS szabályokat
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Ha OPTIONS kérés érkezik, rögtön kilépünk
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// A szükséges fájlok betöltése: a Pizza modell és a vezérlő osztály
require_once '../model/Pizza.php';
require_once '../controller/AdminPizza.php';

// Példányosítjuk a Pizza modellt, majd injektáljuk a vezérlőbe
$pizzaModel = new Pizza();
$controller = new AdminPizza($pizzaModel);
echo $controller->handleRequest();
