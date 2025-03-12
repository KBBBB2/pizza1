<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /merged/view/customer/mainpage.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Felhasználók kezelése</title>
  <!-- jQuery betöltése egyszerű AJAX hívásokhoz -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    th, td {
      padding: 8px;
    }
    .action-container {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
  </style>
</head>
<body>
  <h1>Felhasználók kezelése</h1>
  <a href="account.php">account</a><br>
  <a href="coupon.php">coupon</a><br>
  <a href="menu.php">menü</a><br>
  <br>
  <a href="/merged/view/customer/mainpage.html">Customer nézet</a><br>
  <br>

  <!-- Kereső rész -->
  <input type="text" id="search" placeholder="Keresés...">
  <button id="searchBtn">Keresés</button>
  <br><br>
  
  <!-- Táblázat, ahol a felhasználók adatai jelennek meg -->
  <table id="accountsTable">
    <thead>
      <tr>
        <th>Firstname</th>
        <th>Lastname</th>
        <th>Username</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Created</th>
        <th>Locked</th>
        <th>Disabled</th>
        <th>Akciók</th>
      </tr>
    </thead>
    <tbody>
      <!-- Itt kerülnek majd a sorok -->
    </tbody>
  </table>
  
  <script src="/merged/assets/js/admin/account.js"></script>
</body>
</html>
