<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Felhasználónév ellenőrzése az account táblában
    $checkUsernameQuery = "SELECT * FROM account WHERE username='$username'";
    $usernameResult = $conn->query($checkUsernameQuery);

    // Email ellenőrzése a users táblában
    $checkEmailQuery = "SELECT * FROM users WHERE email='$email'";
    $emailResult = $conn->query($checkEmailQuery);


    if ($usernameResult->num_rows > 0 || $emailResult->num_rows > 0) {
        echo "A felhasználónév vagy e-mail már foglalt.";
    } else {
        // Új felhasználó hozzáadása
        $insertQuery = "INSERT INTO account (username, email, password) VALUES ('$username', '$email', '$password')";
        if ($conn->query($insertQuery) === TRUE) {
            echo "Sikeres regisztráció!";
        } else {
            echo "Hiba történt: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
</head>
<body>
    <form method="post" action="register.php">
        <label for="username">Felhasználónév:</label>
        <input type="text" name="username" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="password">Jelszó:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Regisztráció</button>
    </form>
</body>
</html>

<!--
A regisztráció után lehet csak hozzá adni accountot
a titkosítás, miatt (fontos hogy meg kell jegyezni az admin,
 customer... jelszavait ezért)!!!
-->
