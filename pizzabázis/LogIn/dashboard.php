<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Védett oldal</title>
</head>
<body>
    <h1>Üdvözlünk, <?php echo $_SESSION['username']; ?>!</h1>
    <p>Ez egy védett oldal, csak bejelentkezett felhasználók számára elérhető.</p>
    <a href="logout.php">Kijelentkezés</a>
</body>
</html>
