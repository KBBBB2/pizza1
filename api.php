<?php
header("Content-Type: application/json; charset=UTF-8");

// Csak POST kéréseket engedünk meg. 2 endpoint kll póbág
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "error" => "Csak POST metódusú kérések engedélyezettek.",
        "status" => 405
    ]);
    exit;
}

// A bemenet JSON formátumú, így a php://input streamből olvassuk be.
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode([
        "error" => "Érvénytelen JSON bemenet.",
        "status" => 400
    ]);
    exit;
}

if (!isset($input['action'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Hiányzik az action paraméter.",
        "status" => 400
    ]);
    exit;
}

$action = $input['action'];

// Adatbázis kapcsolódási adatok
$db_host = 'localhost';
$db_name = 'pizzabazis';
$db_user = 'root';
$db_pass = '';

try {
    // PDO kapcsolat létrehozása
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Adatbázis kapcsolódási hiba: " . $e->getMessage(),
        "status" => 500
    ]);
    exit;
}

if ($action === 'register') {
    // Regisztrációs művelet: ellenőrizzük, hogy a username és password meg lett-e adva.
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            "error" => "Felhasználónév és jelszó megadása kötelező.",
            "status" => 400
        ]);
        exit;
    }
    $username = $input['username']; //trim-et kiszedtem
    $password = $input['password'];
    $firstname = $input['firstname'];
    $lastname = $input['lastname'];
    $email = $input['email'];
    $phonenumber = $input['phonenumber'];


    // Ellenőrizzük, hogy már létezik-e ilyen felhasználó.
    $stmt = $pdo->prepare("SELECT id FROM account WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            "error" => "A megadott felhasználónév már létezik.",
            "status" => 400
        ]);
        exit;
    }

    // Ellenőrizzük, hogy már létezik-e ilyen email cím.
    $stmt = $pdo->prepare("SELECT id FROM account WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            "error" => "A megadott email cím már foglalt.",
            "status" => 400
        ]);
        exit;
    }
    

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Új felhasználó beszúrása az adatbázisba
    $stmt = $pdo->prepare("CALL registAndCheck(?, ?, ?, ?, ?, ?, @p_result)");
    if ($stmt->execute([$username, $passwordHash, $firstname, $lastname, $email, $phonenumber])) {
        echo json_encode(["success" => "Sikeres regisztráció."]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => "Hiba történt a regisztráció során.",
            "status" => 500
        ]);
    }
} else if ($action === 'login') {
    // Belépési művelet: ellenőrizzük, hogy a username és password meg lett-e adva.
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            "error" => "Felhasználónév és jelszó megadása kötelező.",
            "status" => 400
        ]);
        exit;
    }
    $username = trim($input['username']);
    $password = $input['password'];

    // Felhasználó lekérése az adatbázisból
    $stmt = $pdo->prepare("CALL getAccountLogin(?)");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        http_response_code(400);
        echo json_encode([
            "error" => "Érvénytelen hitelesítési adatok.",
            "status" => 400
        ]);
        exit;
    }
    
    // Ellenőrizzük, hogy a fiók nincs-e letiltva
    if (isset($user['disabled']) && $user['disabled'] == 1) {
        http_response_code(403);
        echo json_encode([
            "error" => "A fiók véglegesen le van tiltva.",
            "status" => 403
        ]);
        exit;
    }
    if (isset($user['locked']) && $user['locked'] == 1) {
        http_response_code(403);
        echo json_encode([
            "error" => "A fiók ideiglenesen le van tiltva.",
            "status" => 403
        ]);
        exit;
    }
    
    // Ellenőrizzük a jelszót
    if (password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        echo json_encode([
            "success" => "Sikeres belépés.",
            "status" => 200
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "error" => "Érvénytelen hitelesítési adatok.",
            "status" => 400
        ]);
    }
}

?>

