<?php
// Adatbázis kapcsolat létrehozása
$conn = new mysqli('localhost', 'root', '', 'pizzabazis');

// Hibaellenőrzés
if ($conn->connect_error) {
    die("Kapcsolati hiba: " . $conn->connect_error);
}
?>
