<?php
session_start();
include 'config.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Belépés</title>
    
</head>
<body>

     <!-- Fejléc -->
     <header>
        <div class="header-left">
            <button type="submit" class="button"><b>Bejelentkezés</b></button>
            <button type="submit" class="button"><b>Regisztráció</b></button>
            <button type="submit" class="cart-button">🛒</button>
        </div>
        
        
    </header>

    <!-- Belépési felület -->
<section class="login-section">
    <div class="login-form">
        <form>
            <div class="form-group">
                <input type="username" placeholder="Felhasználónév" name="username">
            </div>
            <div class="form-group">
                <input type="password" placeholder="Jelszó" name="password">
            </div>
            <button type="submit" class="login-button">Belépés</button>
        </form>
    </div>
    <div class="pizza-image">
        <img src="pizza images/pizza-image.png" alt="Pizza">
    </div>
</section>


</body>
</html>
