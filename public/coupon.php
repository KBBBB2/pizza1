<?php
// public/coupon.php

// Határozzuk meg az eredeti domaineket
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';

// CORS és HTTP fejlécek beállítása
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS esetén rögtön kilépünk
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Szükséges fájlok betöltése
require_once '../model/coupon.php';
require_once '../controller/Coupon.php';

// Példányosítjuk a Coupon modellt és injektáljuk a vezérlőbe
$couponModel = new Coupon();
$controller = new CouponController($couponModel);
echo $controller->handleRequest();
