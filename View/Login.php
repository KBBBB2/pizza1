<?php require_once __DIR__ . '/../controller/AuthController.php'; ?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once "../Layout/Layout.php" ?>
    <link rel="stylesheet" href="../Styles/Login.css">
    <title>Belépés</title>
    
</head>
<body>



<!-- Belépési felület -->
<section class="login-section">
     <div class="login-form">
        <form method="post" action="">
            <div class="form-group">
                <input type="text" name="username" placeholder="felhasználónév" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Jelszó" required>
            </div>
            <div class="forgot-password">
                <a href="#">Elfelejtettem a jelszót</a>
            </div>
            <button type="submit" class="login-button">Belépés</button>
            <?php AuthController::login(); ?>
        </form>
       
    </div>
    <div class="pizza-image">
        <img src="../Images/designImages/pizza-image.png" alt="Pizza">
    </div>
</section>
    


</body>

<footer></footer>
</html>
