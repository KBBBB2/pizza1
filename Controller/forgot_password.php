<?php
use PHPMailer\PHPMailer\PHPMailer;
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json; charset=UTF-8');

function generateToken($userId, $expires, $secret) {
    $data = ['userId' => $userId, 'expires' => $expires];
    $payload = base64_encode(json_encode($data));
    $sig = hash_hmac('sha256', $payload, $secret);
    return "$payload.$sig";
}

if (defined('PHPUNIT_RUNNING')) {
    return;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Csak POST metódus engedélyezett.']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Érvényes email cím szükséges.']);
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=pizzabazis;charset=utf8', 'root', '');
$stmt = $pdo->prepare("SELECT id FROM account WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $secret = 'valami_egyedi_titkos_kulcs';
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));
    $token = generateToken($user['id'], $expires, $secret);

    $resetLink = "http://127.0.0.1:5500/view/customer/PasswordReset.html?token=" . urlencode($token);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pizzabazistest@gmail.com';
        $mail->Password = 'oblk memx rimh wcdq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('pizzabazistest@gmail.com', 'PizzaBazis Support');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Jelszó visszaállítás';
        $mail->Body = 'Kattints a linkre: <a href="' . $resetLink . '">' . $resetLink . '</a>';

        $mail->send();
        echo json_encode(['message' => 'Ha regisztrálva van, elküldtük a reset linket.']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Az email küldése sikertelen.']);
        exit;
    }
}

echo json_encode(['message' => 'Ha regisztrálva van, elküldtük a reset linket.']);
