<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: view/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Főoldal</title>
</head>
<body>
    <h2>Üdvözöllek, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <a href="logout.php">Kijelentkezés</a>
</body>
</html>
