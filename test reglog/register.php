<?php
// register.php
header('Content-Type: application/json');

// Csak POST metódus engedélyezett
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'error' => 'Csak POST metódus használható',
        'status' => 405
    ]);
    exit;
}

// JSON adat beolvasása
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Érvénytelen JSON formátum',
        'status' => 400
    ]);
    exit;
}

// Ellenőrizzük a kötelező adatokat
if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'A felhasználónév és jelszó megadása kötelező',
        'status' => 400
    ]);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

// Jelszó hash-elése
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

require 'config.php';

try {
    // Tárolt eljárás meghívása: sp_register_user
    $stmt = $pdo->prepare("CALL 	registAndCheck(?, ?, @p_result)");
    $stmt->execute([$username, $passwordHash]);

    // OUT paraméter lekérése
    $resultRow = $pdo->query("SELECT @p_result AS result")->fetch();
    if ($resultRow && $resultRow['result'] == 1) {
        http_response_code(201); // Created
        echo json_encode([
            'message' => 'Sikeres regisztráció',
            'status' => 201
        ]);
    } else {
        http_response_code(409); // Conflict
        echo json_encode([
            'error' => 'A felhasználónév már foglalt',
            'status' => 409
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Regisztráció sikertelen',
        'status' => 500
    ]);
}
?>
