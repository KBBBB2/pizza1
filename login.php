<?php
session_start();
include 'reg/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM account WHERE username='$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Jelszó ellenőrzése
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Hibás jelszó. Megadott: " . $password . " | Adatbázis: " . $user['password'];
        }
    } else {
        echo "Nincs ilyen felhasználónév.";
        
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
</head>
<body>
    <form method="post" action="login.php">
        <label for="username">Felhasználónév:</label>
        <input type="text" name="username" required><br>

        <label for="password">Jelszó:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Bejelentkezés</button>
    </form>
</body>
</html>
