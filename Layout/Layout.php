<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Layout/style.css">
</head>

<!-- Fejléc -->
<header>
        <div class="header-left">
            <form action="mainpage.php" method="post">
                <button style="border: none;
  cursor: pointer;
  appearance: none;
  background-color: inherit;">
                    <img src="../Layout/design images/pizza_logo.png" alt="Pizza Logo">
                </button>
            </form>
        </div>
        <div class="header-right">
<?php
session_start();
if (isset($_SESSION['username'])) {
    echo '
            <h2>Üdvözöllek, ' . htmlspecialchars($_SESSION["username"]) . '!</h2>
            <form action="logout.php" method="post">
            <button type="submit" class="button"><b>Kijelentkezés</b></button>  
            </form>';
} else {
    echo '
            <form action="login.php" method="">
            <button type="submit" class="button"><b>Bejelentkezés</b></button>
            </form>
            <form action="register.php" method="">
            <button type="submit" class="button"><b>Regisztráció</b></button>
            </form>
            <button type="submit" class="cart-button">';
}
?>
            <button type="submit" class="cart-button">
                <img src="../Layout/design images/shopping-cart.png" alt="Shopping Cart" width="50" height="50">
            </button>
        </div>
</header>