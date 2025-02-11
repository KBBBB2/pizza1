<?php
// login.php
header('Content-Type: application/json');

// Csak POST kérések engedélyezettek
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Csak POST metódus használható']);
    exit;
}

// JSON adat beolvasása
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Érvénytelen JSON formátum']);
    exit;
}

// Ellenőrizzük, hogy a szükséges adatok meg vannak-e adva
if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'A felhasználónév és jelszó megadása kötelező']);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

// Adatbázis kapcsolat betöltése
require 'config.php';

try {
    // Felhasználó keresése az adatbázisban
    $stmt = $pdo->prepare('CALL getAccountLogin(?)');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $stmt->closeCursor();
    if (!$user) {
        http_response_code(401); // Unauthorized
        echo json_encode([
            'error' => 'Hibás felhasználónév vagy jelszó',
            'status' => 401
        ]);
        exit;
    }

    // Jelszó ellenőrzése
    if (password_verify($password, $user['password'])) {
        // Sikeres bejelentkezés
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $username;
        echo json_encode([
            'message' => 'Sikeres bejelentkezés',
            'status' => 200
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'error' => 'Hibás felhasználónév vagy jelszó',
            'status' => 401
    ]);
    }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Bejelentkezés sikertelen',
            'status' => 500
    ]);
}
?>
