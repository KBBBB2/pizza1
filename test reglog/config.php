<?php
// config.php
$host    = 'localhost';
$db      = 'pizzabazis';    // A saját adatbázisod neve
$user    = 'root';    // Az adatbázis felhasználó neve
$pass    = '';    // Az adatbázis jelszava
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hibák kivételt váltanak ki
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Asszociatív tömbben adja vissza az eredményeket
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Az adatbázishoz való csatlakozás sikertelen']);
    exit;
}
?>
