<?php require_once __DIR__ . '/../controller/AuthController.php'; ?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/Reg.css">
    <?php include_once "../Layout/Layout.php" ?>
    <title>Regisztráció</title>
</head>
<body>

        <!-- Regisztrációs felület -->
    <section class="registration-section">
        <div class="registration-form">
            <form method="post" action="">
                <div class="form-group">
                    <input type="text" name="last_name" placeholder="Vezetéknév">
                    <input type="text" name="first_name" placeholder="Keresztnév">
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Felhasználónév">
                    <input type="email" name="email" placeholder="e-mail cím">
                </div>
                <div class="form-group">
                    <input type="tel" name="phonenumber" placeholder="Telefonszám">
                    <input type="password" name="password" placeholder="Jelszó">
                </div>
                <button type="submit" class="create-account-button">Fiók létrehozása</button>
            </form>
            <?php AuthController::register(); ?>
        </div>
        <div class="pizza-image">
            <img src="../Images/designImages/pizza-image.png" alt="Pizza">
        </div>
    </section>

</body>
<footer></footer>
</html>
