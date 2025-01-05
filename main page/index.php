<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaBázis</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy(); // Munkamenet törlése
    header("Location: " . $_SERVER['PHP_SELF']); // Visszairányítás az aktuális oldalra
    exit();
}
?>

    <!-- Fejléc -->
    <header>
        <div class="header-left">
        <?php if (isset($_SESSION['username'])): ?>
            Üdv, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
            <form method="POST" style="display: inline;">
                <button type="submit" class="button" name="logout"><b>Kijelentkezés</button>
            </form>
        <?php else: ?>
            <button type="submit" class="button" onclick="window.location.href='<?php echo "../pizzabázis/login/login.php"; ?>';"><b>Bejelentkezés</b></button>
            <button type="submit" class="button" onclick="window.location.href='<?php echo "../reg/reg.php"; ?>';"><b>Regisztráció</b></button>
        <?php endif; ?>
            <button type="submit" class="cart-button">🛒</button>
        </div>
        
        
    </header>

    <!-- Üdvözlő szakasz -->
    <section class="welcome">
        <h1>Üdvözlünk a PizzaBázis oldalán</h1>
        <div class="welcome-content">
            <div class="text-box">
                <p>Nálunk eredeti olasz <br>recept alapján <br>készülnek a pizzák.</p><br>
                <button type="submit" class="order-button"><b>Rendelj tőlünk most!</b></button>
            </div>
            <img src="pizza images\pizza-image.png" alt="Pizza" class="pizza-img">
        </div>
    </section>

    <!-- Kiemelt pizzák szakasz -->
    <section class="highlight">
        <h2  class="highlight-title">Kiemelt pizzáink</h2>
        <div class="pizza-list">
            <div class="pizza-item">
                <h3>Négy sajtos pizza</h3>
                <img src="pizza images\cheese-pizza.png" alt="Négy sajtos pizza">
            </div>
            <div class="pizza-item">
                <h3>Szalámis pizza</h3>
                <img src="pizza images\salami-pizza.png" alt="Szalámis pizza">
                
            </div>
            <div class="pizza-item">
                <h3>Mediterrán pizza</h3>
                <img src="pizza images\mediterran-pizza.png" alt="Mediterrán pizza">
                
            </div>
        </div>
        <button type="submit" class="more-pizzas-button"><b>További pizzáink</b></button>
    </section>

   

    <!-- Miért rendelj szakasz -->
    <section class="why-order">
        <h2>Miért rendelj a PizzaBázistól?</h2>
        <ul>
            <li>Eredeti olasz kézzel készített pizzáink vannak</li>
            <li>Regisztrálva sok-sok kuponhoz hozzá tudsz jutni</li>
            <li>Első rendelésedet gyorsan és könnyen újra tudod rendelni</li>
        </ul>
    </section>

    <!-- Lábléc -->
    <footer>
        <p>Telefon: valami</p>
    </footer>

</body>
</html>
