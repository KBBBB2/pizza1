<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="reg.css">
</head>
<body>

    <?php
    session_start();
    include 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Felhasználónév ellenőrzése az account táblában
        $checkUsernameQuery = "SELECT * FROM account WHERE firstname='$firstname'";
        $usernameResult = $conn->query($checkUsernameQuery);

        // Email ellenőrzése a users táblában
        $checkEmailQuery = "SELECT * FROM account WHERE email='$email'";
        $emailResult = $conn->query($checkEmailQuery);


        if ($usernameResult->num_rows > 0 || $emailResult->num_rows > 0) {
            echo "A felhasználónév vagy e-mail már foglalt.";
        } else {
            // Új felhasználó hozzáadása
            $insertQuery = "INSERT INTO account (firstname, lastname, email, password) VALUES ('$firstname', 'lastname', '$email', '$password')";
            if ($conn->query($insertQuery) === TRUE) {
                echo "Sikeres regisztráció!";
            } else {
                echo "Hiba történt: " . $conn->error;
            }
        }
    }
?>



<!--
A regisztráció után lehet csak hozzá adni accountot
a titkosítás, miatt (fontos hogy meg kell jegyezni az admin,
 customer... jelszavait ezért)!!!
-->


     <!-- Fejléc -->
     <header>
        <div class="header-left">
            <button type="submit" class="button"><b>Bejelentkezés</b></button>
            <button type="submit" class="button"><b>Regisztráció</b></button>
            <button type="submit" class="cart-button">🛒</button>
        </div>
        
        
    </header>

    <!-- Regisztrációs felület -->
<section class="registration-section">
    <div class="registration-form">
        <form method="post" action="reg.php">


            <div class="form-group">
                <input type="text" placeholder="Vezetéknév" name="firstname" required>
                <input type="text" placeholder="Keresztnév">
            </div>
            <div class="form-group">
                <input type="email" placeholder="e-mail cím" name="email" required>
                <input type="tel" placeholder="Telefonszám">
            </div>
            <div class="form-group">
                <input type="password" placeholder="Jelszó" name="password" required>
                <input type="password" placeholder="Jelszó újra">
            </div>
            <button type="submit" class="create-account-button">Fiók létrehozása</button>
        </form>
    </div>
    <div class="pizza-image">
        <img src="pizza images/pizza-image.png" alt="Pizza">
    </div>
</section>


</body>
</html>
