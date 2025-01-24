<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM account WHERE username='$username'";
    //$query = "CALL getAccountLogin";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Jelszó ellenőrzése
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: ../../main page/index.php");
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
            <img src="design images/pizza_logo.png" alt="Pizza Logo">
        </div>
        <div class="header-left">
            <button type="submit" class="button"><b>Bejelentkezés</b></button>
            <button type="submit" class="button"><b>Regisztráció</b></button>
            <button type="submit" class="cart-button">
            <img src="design images/shopping-cart.png" alt="Shopping Cart" width="50" height="50">
        </button>
        </div>
        
        
    </header>

    <!-- Belépési felület -->
<section class="login-section">
    <div class="login-form">
        <form method="post" action="login.php">
            <div class="form-group">
                <input type="username" placeholder="Felhasználónév" name="username">
            </div>
            <div class="form-group">
                <input type="password" placeholder="Jelszó" name="password">
            </div>
            <div class="forgot-password">
                <a href="#">Elfelejtettem a jelszót</a>
            </div>
            <button type="submit" class="login-button">Belépés</button>
        </form>
    </div>
    <div class="pizza-image">
        <img src="design images/pizza-image.png" alt="Pizza">
    </div>
</section>


</body>
</html>
