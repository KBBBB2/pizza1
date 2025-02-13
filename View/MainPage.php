<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once "../Layout/Layout.php" ?>
    <link rel="stylesheet" href="../Styles/MainPage.css">
    <title>PizzaBázis</title>
</head>
<body>
    <!-- Üdvözlő szakasz -->
    <section class="welcome">
        <h1>Üdvözlünk a PizzaBázis oldalán</h1>
        <div class="welcome-content">
            <div class="text-box">
                <p>Nálunk eredeti olasz <br>recept alapján <br>készülnek a pizzák.</p><br>
                <button type="submit" class="order-button"><b>Rendelj tőlünk most!</b></button>
            </div>
            <img src="../Layout/design images\pizza-image.png" alt="Pizza" class="pizza-img">
        </div>
    </section>

    <!-- Kiemelt pizzák szakasz -->
    <section class="highlight">
        <h2 class="highlight-title">Kiemelt pizzáink</h2>
        <div class="pizza-list">
            <div class="pizza-item">
                <h3>Négy sajtos pizza</h3>
                <img src="../Layout/pizza images\cheese-pizza.png" alt="Négy sajtos pizza">
            </div>
            <div class="pizza-item">
                <h3>Pepperonis pizza</h3>
                <img src="../Layout/pizza images\pepperoni-pizza.png" alt="Pepperoni pizza">
            </div>
            <div class="pizza-item">
                <h3>Vegetáriánus pizza</h3>
                <img src="../Layout/pizza images\veggie-pizza.png" alt="Vegetáriánus pizza">
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
        <p>Telefon: +36 70/314-7583</p>
    </footer>
</body>
</html>